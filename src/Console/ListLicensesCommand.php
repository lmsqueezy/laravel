<?php

namespace LemonSqueezy\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use LemonSqueezy\Laravel\LemonSqueezy;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;

class ListLicensesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lmsqueezy:licenses
                            {product? : The ID of the product to list licenses for }
                            {--status= : The status of the license key}
                            {--order= : List licenses belonging to a specific order }
                            {--l|long : Display full license key instead of shorthand }
                            {--p|page=1 : Page to display }
                            {--s|size=100 : Items per page to display }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all generated licenses.';

    public function handle(): int
    {
        if (! $this->validate()) {
            return static::FAILURE;
        }

        $storeResponse = spin(fn() => $this->fetchStore(), '🍋 Fetching store information...');
        $store = $storeResponse->json('data.attributes');

        return $this->handleLicenses($store);
    }

    protected function validate(): bool
    {
        $arr = array_merge(
            config('lemon-squeezy'),
            ['page' => $this->option('page')],
            ['size' => $this->option('size')],
        );
        $validator = Validator::make($arr, [
            'api_key' => [
                'required',
            ],
            'store' => [
                'required',
            ],
            'page' => [
                'nullable', 'numeric', 'min:1',
            ],
            'size' => [
                'nullable', 'numeric', 'min:1', 'max:100',
            ],
        ], [
            'api_key.required' => 'Lemon Squeezy API key not set. You can add it to your .env file as LEMON_SQUEEZY_API_KEY.',
            'store.required' => 'Lemon Squeezy store ID not set. You can add it to your .env file as LEMON_SQUEEZY_STORE.',
        ]);

        if ($validator->passes()) {
            return true;
        }

        $this->newLine();

        foreach ($validator->errors()->all() as $error) {
            error($error);
        }

        return false;
    }

    protected function fetchStore(): Response
    {
        return LemonSqueezy::api('GET', sprintf('stores/%s', config('lemon-squeezy.store')));
    }

    protected function handleLicenses(array $store): int
    {
        $licensesResponse = spin(
            fn() => LemonSqueezy::api(
                'GET',
                sprintf('license-keys'),
                [
                    'filter[store_id]' => config('lemon-squeezy.store'),
                    'page[size]' => (int) $this->option('size'),
                    'page[number]' => (int) $this->option('page'),
                    'filter[product_id]' => $this->argument('product'),
                    'filter[order_id]' => $this->option('order'),
                    'filter[status]' => $this->option('status'),
                ],
            ),
            '🍋 Fetching licenses...',
        );

        $currPage = $licensesResponse->json('meta.page.currentPage');
        $lastPage = $licensesResponse->json('meta.page.lastPage');

        if ($lastPage > 1 && $currPage <= $lastPage) {
            info(sprintf('Showing page %d of %d', $currPage, $lastPage));
        }

        $licenses = collect($licensesResponse->json('data'));
        $licenses->each(function ($license) {
            $this->displayLicense($license, $this->option('long'));

            $this->newLine();
        });

        return static::SUCCESS;
    }

    private function displayStatus(array $license): string
    {
        $status = Arr::get($license, 'attributes.status_formatted');
        $limit = Arr::get($license, 'attributes.activation_limit') ?? '0';
        $usage = Arr::get($license, 'attributes.activation_usage') ?? '0';

        return "{$status} ({$usage}/{$limit})";
    }

    private function displayProductInfo(array $license): void
    {
        $productId = Arr::get($license, 'attributes.product_id');
        $variantId = Arr::get($license, 'attributes.variant_id') ?? 'None';

        $this->components->twoColumnDetail(
            '<fg=gray>Product:Variant</>',
            "<fg=gray>{$productId}:{$variantId}</>",
        );
    }

    private function displayCustomer(array $license): void
    {
        $customerName = Arr::get($license, 'attributes.user_name');
        $customerEmail = Arr::get($license, 'attributes.user_email');

        $this->components->twoColumnDetail(
            '<fg=gray>Customer</>',
            "<fg=gray>{$customerName} [{$customerEmail}]</>",
        );
    }

    protected function displayLicense(array $license, bool $long): void
    {
        $key = Arr::get($license, $long ? 'attributes.key' : 'attributes.key_short');
        $orderId = Arr::get($license, 'attributes.order_id');

        $this->components->twoColumnDetail(
            sprintf('<fg=green;options=bold>%s</>', $key),
            $this->displayStatus($license),
        );
        $this->displayProductInfo($license);
        $this->displayCustomer($license);
        $this->components->twoColumnDetail(
            '<fg=gray>Order ID</>',
            "<fg=gray>{$orderId}</>",
        );
    }
}
