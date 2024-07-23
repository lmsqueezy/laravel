<?php

namespace LemonSqueezy\Laravel\Console;

use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use LemonSqueezy\Laravel\LemonSqueezy;

use function Laravel\Prompts\error;
use function Laravel\Prompts\spin;

class ListProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lmsqueezy:products
                            {product? : The ID of the product to list variants for.}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists all products and their variants.';

    public function handle(): int
    {
        if (! $this->validate()) {
            return Command::FAILURE;
        }

        $storeResponse = spin(fn () => $this->fetchStore(), '🍋 Fetching store information...');
        $store = $storeResponse->json('data.attributes');

        $productId = $this->argument('product');

        if ($productId) {
            return $this->handleProduct($store, $productId);
        }

        return $this->handleProducts($store);
    }

    protected function validate(): bool
    {
        $validator = Validator::make(config('lemon-squeezy'), [
            'api_key' => [
                'required',
            ],
            'store' => [
                'required',
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

    protected function handleProduct(array $store, string $productId): int
    {
        $response = spin(
            fn () => LemonSqueezy::api(
                'GET',
                sprintf('products/%s', $productId),
                ['include' => 'variants']
            ),
            '🍋 Fetching product information...'
        );

        $product = $response->json('data');

        $this->newLine();
        $this->displayTitle();
        $this->newLine();

        $this->displayProduct($product);

        $variants = collect($response->json('included'))
            ->filter(fn ($item) => $item['type'] === 'variants')
            ->sortBy('sort');

        $variants->each(fn (array $variant) => $this->displayVariant(
            $variant,
            Arr::get($store, 'currency'),
            $variants->count() > 1
        ));

        $this->newLine();

        return Command::SUCCESS;
    }

    protected function handleProducts(array $store): int
    {
        $productsResponse = spin(
            fn () => LemonSqueezy::api(
                'GET',
                'products',
                [
                    'include' => 'variants',
                    'filter[store_id]' => config('lemon-squeezy.store'),
                    'page[size]' => 100,
                ]
            ),
            '🍋 Fetching products information...',
        );

        $products = collect($productsResponse->json('data'));

        $this->newLine();
        $this->displayTitle();
        $this->newLine();

        $products->each(function ($product) use ($productsResponse, $store) {
            $this->displayProduct($product);

            $variantIds = collect(Arr::get($product, 'relationships.variants.data'))->pluck('id');
            $variants = collect($productsResponse->json('included'))
                ->filter(fn ($item) => $item['type'] === 'variants')
                ->filter(fn ($item) => $variantIds->contains($item['id']))
                ->sortBy('sort');

            $variants->each(fn ($variant) => $this->displayVariant(
                $variant,
                Arr::get($store, 'currency'),
                $variants->count() > 1
            ));

            $this->newLine();
        });

        return Command::SUCCESS;
    }

    protected function displayTitle(): void
    {
        $this->components->twoColumnDetail('<fg=gray>Product/Variant</>', '<fg=gray>ID</>');
    }

    protected function displayProduct(array $product): void
    {
        $this->components->twoColumnDetail(
            sprintf('<fg=green;options=bold>%s</>', Arr::get($product, 'attributes.name')),
            Arr::get($product, 'id')
        );
    }

    protected function displayVariant(array $variant, string $currency, bool $hidePending = false): void
    {
        if (Arr::get($variant, 'attributes.status') === 'pending' && $hidePending) {
            return;
        }

        $name = Arr::get($variant, 'attributes.name');

        $price = LemonSqueezy::formatAmount(
            Arr::get($variant, 'attributes.price'),
            $currency,
        );

        $id = Arr::get($variant, 'id');

        $this->components->twoColumnDetail(sprintf('%s <fg=gray>%s</>', $name, $price), $id);
    }
}
