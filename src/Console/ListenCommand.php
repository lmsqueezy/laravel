<?php

namespace LemonSqueezy\Laravel\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Http\Client\Response;
use Illuminate\Process\InvokedProcess;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use LemonSqueezy\Laravel\LemonSqueezy;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\select;

class ListenCommand extends Command implements Isolatable, PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lmsqueezy:listen
                            {service : The service to use for listening to webhooks, either ngrok or expose.}
                            {--cleanup : Remove all webhooks for the given service.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listens to Lemon Squeezy webhooks via expose or ngrok.';

    /**
     * The available services.
     */
    protected array $services = [
        'expose' => [
            'domain' => 'sharedwithexpose.com',
        ],
        'ngrok' => [
            'api' => 'http://localhost:4040/api',
            'domain' => 'ngrok-free.app',
        ],
    ];

    /**
     * The currently invoked process instance.
     */
    protected InvokedProcess $process;

    /**
     * The currently in-use Lemon Squeezy webhook ID.
     */
    protected int $webhookId;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (windows_os()) {
            error('lmsqueezy:listen is not supported on Windows because it lacks support for signal handling.');

            return static::FAILURE;
        }

        $this->validateArguments();

        $errorCode = $this->handleEnvironment();

        if ($errorCode !== null) {
            return $errorCode;
        }

        $errorCode = $this->handleCleanup();

        if ($errorCode !== null) {
            return $errorCode;
        }

        return $this->handleService();
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
            'store' => [
                'required',
            ],
        ], [
            'api_key.required' => 'The LEMON_SQUEEZY_API_KEY environment variable is required.',
            'store.required' => 'The LEMON_SQUEEZY_STORE environment variable is required.',
        ])->validate();
    }

    protected function handleEnvironment(): ?int
    {
        if ($this->argument('service') === 'test') {
            info('lmsqueezy:listen is using the test service.');

            return Command::SUCCESS;
        }

        if (! App::environment('local')) {
            error('lmsqueezy:listen can only be used in local environment.');

            return Command::FAILURE;
        }

        return null;
    }

    protected function handleCleanup(): ?int
    {
        if ($this->option('cleanup')) {
            note("Cleaning up webhooks for '{$this->argument('service')}' service...");

            $cleaned = $this->cleanupWebhooks();

            if ($cleaned === 0) {
                info('No webhooks found to clean.');
            }

            return Command::SUCCESS;
        }

        return null;
    }

    protected function handleService(): int
    {
        note('Setting up webhooks domain with '.$this->argument('service').'...');

        $this->trap([SIGINT], fn (int $signal) => $this->teardownWebhook());

        return $this->{$this->argument('service')}();
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

    protected function cleanOutput($output)
    {
        if (preg_match(
            '/Remaining time:\s+\d{2}:\d{2}:\d{2}\\n/',
            $output,
            $matches
        )) {
            $output = preg_replace('/Remaining time:\s+\d{2}:\d{2}:\d{2}\\n/', '', $output);
        }

        if ($output) {
            $lines = explode("\n", $output);
            $cleaned_lines = [];

            foreach ($lines as $line) {
                // Trim leading and trailing whitespace
                $line = trim($line);
                // Replace multiple spaces with a single space
                $line = preg_replace('/\s+/', ' ', $line);

                if (!empty($line)) {
                    $cleaned_lines[] = $line;
                }
            }
            // Join cleaned lines back into a single string
            $output = implode("\n", $cleaned_lines);
        }

        return $output;
    }

    protected function process(array $commands): InvokedProcess
    {
        return $this->process = Process::timeout(120)
            ->start($commands, function (string $type, string $output) {
                if ($this->option('verbose') || isset($this->webhookId)) {
                    $output = $this->cleanOutput($output);
                    if ($output) {
                        note($output);
                    }
                }
            });
    }

    protected function expose(): int
    {
        $tunnel = null;

        $this->process([
            'expose',
            'share',
            route('lemon-squeezy.webhook'),
            sprintf('--subdomain=%s', sha1(time())),
            '--no-interaction',
        ]);

        while ($this->process->running()) {
            if (is_null($tunnel)) {
                if (preg_match(
                    '/Public HTTPS:\s+(http[s]?:\/\/[^\s]+)/',
                    $this->process->latestOutput(),
                    $matches
                )) {
                    $tunnel = $matches[1];

                    $errorCode = $this->setupWebhook($tunnel);

                    if ($errorCode !== null) {
                        return $errorCode;
                    }
                }
            }

            sleep(1);
        }

        return Command::SUCCESS;
    }

    protected function ngrok(): int
    {
        $logs = [];
        $tunnel = null;

        $this->process([
            'ngrok',
            'http',
            route('lemon-squeezy.webhook'),
            '--host-header=rewrite',
            '--no-interaction',
        ]);

        while ($this->process->running()) {
            if (is_null($tunnel)) {
                $result = Http::retry(5, 1000)
                    ->get("{$this->services['ngrok']['api']}/tunnels")
                    ->json();

                $tunnel = $result['tunnels'][0]['public_url'] ?? null;

                if (Str::startsWith($tunnel ?? '', ['https://', 'http://'])) {
                    $errorCode = $this->setupWebhook($tunnel);

                    if ($errorCode !== null) {
                        return $errorCode;
                    }
                }
            }

            if ($tunnel) {
                $result = Http::get("{$this->services['ngrok']['api']}/requests/http?limit=50")
                    ->json('requests');

                foreach ($result as $request) {
                    if (! in_array($request['id'], $logs)) {
                        $logs[] = $request['id'];

                        note(sprintf(
                            '%s %s %s %s',
                            $request['response']['status_code'],
                            $request['request']['method'],
                            Str::padRight(Str::limit($request['request']['uri'], 48, ''), 48, '.'),
                            Carbon::parse($request['response']['headers']['Date'][0])->format('H:i:s'),
                        ));
                    }
                }
            }

            sleep(1);
        }

        return Command::SUCCESS;
    }

    protected function setupWebhook(string $tunnel): ?int
    {
        note("Found webhook endpoint: {$tunnel}");
        note('Sending webhook to Lemon Squeezy...');

        $data = [
            'data' => [
                'type' => 'webhooks',
                'attributes' => [
                    'url' => $tunnel.'/'.config('lemon-squeezy.path').'/webhook',
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
                        'subscription_plan_changed',
                        'license_key_created',
                        'license_key_updated',
                    ],
                    'secret' => config('lemon-squeezy.signing_secret') ?: Str::random(32),
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
            ->post(LemonSqueezy::API.'/webhooks', $data);

        if ($result->status() !== 201) {
            error('Failed to setup webhook.');

            return Command::FAILURE;
        }

        $this->webhookId = $result['data']['id'];

        info('✅ Webhook setup successfully.');
        note('Listening for webhooks...');

        return null;
    }

    protected function teardownWebhook(): void
    {
        if (! isset($this->webhookId)) {
            return;
        }

        note("\nCleaning up webhook on Lemon Squeezy...");

        if ($this->deleteWebhook($this->webhookId)->status() !== 204) {
            error("Failed to remove webhook, use --cleanup to remove all {$this->argument('service')}. domains");

            return;
        }

        unset($this->webhookId);

        info('✅ Webhook removed successfully.');
    }

    protected function cleanupWebhooks(): int
    {
        return collect($this->fetchWebhooks())
            ->filter(function ($url, $id) {
                collect($this->services[$this->argument('service')]['domain'])
                    ->reduce(fn ($carry, $domain) => $carry || Str::endsWith($url, $domain), false);
            })
            ->each(function ($url, $id) {
                $this->deleteWebhook($id)->status() === 204
                    ? info("✅ Webhook {$id} removed successfully.")
                    : error("Failed to remove webhook {$id}.");
            })
            ->count();
    }

    protected function fetchWebhooks(): array
    {
        $fetch = true;
        $fetchPage = 0;
        $webhooks = [];

        while ($fetch) {
            $result = Http::withToken(config('lemon-squeezy.api_key'))
                ->retry(3, 250)
                ->get(sprintf(
                    '%s/webhooks/?filter[store_id]=%s%s',
                    LemonSqueezy::API,
                    config('lemon-squeezy.store'),
                    $fetchPage > 0 ? "&page[number]={$fetchPage}" : '',
                ))
                ->json();

            $fetchPage++;

            $page = $result['meta']['page'];

            if ($page['currentPage'] === $page['lastPage']) {
                $fetch = false;
            }

            foreach (collect($result['data'])->pluck('attributes.url', 'id') as $id => $url) {
                $webhooks[$id] = $url;
            }
        }

        return $webhooks;
    }

    protected function deleteWebhook(int $webhookId): Response
    {
        return Http::withToken(config('lemon-squeezy.api_key'))
            ->retry(3, 250)
            ->delete(LemonSqueezy::API."/webhooks/{$webhookId}");
    }
}
