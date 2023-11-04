<?php

namespace LemonSqueezy\Laravel\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Http\Client\Response;
use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LemonSqueezy\Laravel\Exceptions\ListenException;
use Ramsey\Uuid\Uuid;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;

class Listen extends Command implements Isolatable, PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lmsqueezy:listen {service} {--cleanup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listens to Lemon Squeezy webhooks via expose or ngrok';

    protected array $api = [
        'ngrok' => 'http://localhost:4040/api',
        'lemonsqueezy' => 'https://api.lemonsqueezy.com/v1',
    ];

    protected array $cleanupWebhookDomains = [
        'expose' => [
            'sharedwithexpose.com',
        ],
        'ngrok' => [
            'ngrok-free.app',
        ],
    ];

    protected InvokedProcess $process;

    protected int $webhookId;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->validateArguments();
            $this->handleEnvironment();
            $this->handleCleanup();
            $this->handleService();
        } catch (\Throwable $th) {
            if ($th instanceof ListenException) {
                match($th->getCode()) {
                    Command::SUCCESS => info($th->getMessage()),
                    Command::FAILURE => error($th->getMessage()),
                };

                return $th->getCode();
            }
        
            error($th->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function handleEnvironment(): void
    {
        throw_if(
            $this->argument('service') === 'test',
            ListenException::usingTestService()
        );

        throw_if(
            !App::environment('local'),
            ListenException::notLocalEnvironment()
        );
    }

    protected function handleCleanup(): void
    {
        if ($this->option('cleanup')) {
            note("Cleaning up webhooks for '{$this->argument('service')}' service...");
            $this->cleanupWebhooks();

            exit(Command::SUCCESS);
        }
    }

    protected function handleService(): void
    {
        note('Setting up webhooks domain with '.$this->argument('service').'...');

        $this->trap(
            [SIGINT],
            fn (int $signal) => $this->handleTrap($signal)
        );

        $this->{$this->argument('service')}();
    }

    protected function handleOutput(string $type, string $output): void
    {
        if ($this->option('verbose') || isset($this->webhookId)) {
            note($output);
        }
    }

    protected function handleTrap(int $signal): void
    {
        $this->teardownWebhook();
    }

    protected function validateArguments()
    {
        Validator::make($this->arguments() + config('lemon-squeezy'), [
            'api_key' => [
                'required',
            ],
            'service' => [
                'required',
                'string',
                'in:expose,ngrok,test',
            ],
            'signing_secret' => [
                'required',
            ],
            'store' => [
                'required',
            ],
        ],[
            'api_key.required' => 'The LEMON_SQUEEZY_API_KEY environment variable is required.',
            'signing_secret.required' => 'The LEMON_SQUEEZY_SIGNING_SECRET environment variable is required.',
            'store.required' => 'The LEMON_SQUEEZY_STORE environment variable is required.',
        ])->validate();
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'service' => fn () => select(
                label: 'Please choose a service',
                default: 'expose',
                options: [
                    'expose',
                    'ngrok',
                ],
                validate: fn ($val) => in_array($val, ['expose', 'ngrok'])
                    ? null
                    : 'Please choose a valid service.',
            ),
        ];
    }

    protected function process(array $commands): InvokedProcess
    {
        return $this->process = Process::timeout(120)->start(
            $commands,
            fn (string $type, string $output) => $this->handleOutput($type, $output),
        );
    }

    protected function expose(): void
    {
        $tunnel = null;

        $this->process([
            'expose',
            'share',
            config('app.url'),
            sprintf('--subdomain=%s', Uuid::uuid4()),
        ]);

        while ($this->process->running()) {
            if (is_null($tunnel)) {
                if (preg_match(
                    '/Public HTTPS:\s+(http[s]?:\/\/[^\s]+)/',
                    $this->process->latestOutput(),
                    $matches)
                ) {
                    $tunnel = $matches[1];
                    $this->setupWebhook($tunnel);
                }
            }
            sleep(1);
        }
    }

    protected function ngrok(): void
    {
        $logs = [];
        $tunnel = null;

        $this->process([
            'ngrok',
            'http',
            config('app.url'),
            '--host-header=rewrite',
        ]);

        while ($this->process->running()) {
            if (is_null($tunnel)) {
                $result = Http::retry(5, 1000)
                    ->get("{$this->api['ngrok']}/tunnels")
                    ->json();

                $tunnel = $result['tunnels'][0]['public_url'] ?? null;

                if (Str::startsWith(
                    $tunnel ?? '',
                    ['https://', 'http://']
                )) {
                    $this->setupWebhook($tunnel);
                }
            }

            if ($tunnel) {
                $result = Http::get("{$this->api['ngrok']}/requests/http?limit=50")->json('requests');

                foreach ($result as $request) {
                    if (! in_array($request['id'], $logs)) {
                        $logs[] = $request['id'];

                        note(sprintf(
                            '%s %s %s %s',
                            $request['response']['status_code'],
                            $request['request']['method'],
                            Str::padRight(
                                Str::limit($request['request']['uri'], 48, ''),
                                48,
                                '.'
                            ),
                            Carbon::parse($request['response']['headers']['Date'][0])->format('H:i:s'),
                        ));
                    }
                }
            }

            sleep(1);
        }
    }

    protected function setupWebhook(string $tunnel): void
    {
        note("Found webhook endpoint: {$tunnel}");
        note('Sending webhook to Lemon Squeezy...');

        $data = [
            'data' => [
                'type' => 'webhooks',
                'attributes' => [
                    'url' => $tunnel,
                    'events' => [
                        'order_created',
                        'order_refunded',
                        'subscription_created',
                        'subscription_updated',
                        'subscription_cancelled',
                        'subscription_resumed',
                        'subscription_expired',
                        'subscription_paused',
                        'subscription_unpaused',
                        'subscription_payment_success',
                        'subscription_payment_failed',
                        'subscription_payment_recovered',
                        'subscription_payment_refunded',
                        'license_key_created',
                        'license_key_updated',
                    ],
                    'secret' => config('lemon-squeezy.signing_secret'),
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => config('lemon-squeezy.store'),
                        ],
                    ],
                ],
            ],
        ];

        $result = Http::withToken(config('lemon-squeezy.api_key'))
            ->retry(3, 250)
            ->post(
                "{$this->api['lemonsqueezy']}/webhooks",
                $data
            );

        if ($result->status() !== 201) {
            error('Failed to setup webhook.');
            exit(1);
        }

        $this->webhookId = $result['data']['id'];

        info('✅ Webhook setup successfully.');
        note('Listening for webhooks...');
    }

    protected function teardownWebhook(): void
    {
        if (! isset($this->webhookId)) {
            return;
        }

        note("\nCleaning up webhook on Lemon Squeezy...");

        if ($this->deleteWebhook($this->webhookId)->status() !== 204) {
            error(
                "Failed to remove webhook, use --cleanup to remove all {$this->argument('service')}. domains"
            );

            return;
        }

        unset($this->webhookId);
        info('✅ Webhook removed successfully.');
    }

    protected function deleteWebhook(int $webhookId): Response
    {
        return Http::withToken(config('lemon-squeezy.api_key'))
            ->retry(3, 250)
            ->delete(
                "{$this->api['lemonsqueezy']}/webhooks/{$webhookId}"
            );
    }

    protected function fetchWebhooks(): array
    {
        $fetch = true;
        $fetchPage = 0;
        $webhooks = [];

        while($fetch) {

            $result = Http::withToken(config('lemon-squeezy.api_key'))
                ->retry(3, 250)
                ->get(sprintf(
                    '%s/webhooks/?filter[store_id]=%s%s',
                    $this->api['lemonsqueezy'],
                    config('lemon-squeezy.store'),
                    $fetchPage > 0 ? "&page[number]={$fetchPage}" : '',
                ))
                ->json();
            
            $fetchPage++;

            $page = $result['meta']['page'];

            if($page['currentPage'] === $page['lastPage']) {
                $fetch = false;
            }

            collect($result['data'])->pluck('attributes.url', 'id')->each(
                function($url, $id) use (&$webhooks) {
                    $webhooks[$id] = $url;
                }
            );
        }
        
        return $webhooks;
    }

    protected function cleanupWebhooks(): void
    {
        collect($this->fetchWebhooks())
            ->filter(fn ($url, $id) => collect($this->cleanupWebhookDomains[$this->argument('service')])
                ->reduce(fn ($carry, $domain) => $carry || Str::endsWith($url, $domain), false)
            )
            ->tap(function($collection) {
                throw_if(
                    $collection->count() < 1,
                    ListenException::noWebhooksFound()
                );

                return $collection;
            })->each(function ($url, $id) {
                $this->deleteWebhook($id)->status() === 204
                    ? info("✅ Webhook {$id} removed successfully.")
                    : error("Failed to remove webhook {$id}.");
            });
    }
}
