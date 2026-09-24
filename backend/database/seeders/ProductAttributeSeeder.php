<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Database\Seeder;

/**
 * Seeds the product attributes used by the seller variant form
 * (Size, Color) and backfills existing multi-variant products whose
 * variants carry no attribute values yet — their "Size"-like value is
 * recovered from the SKU tail (e.g. PZR-CORD-BRN-M -> M).
 *
 * Idempotent: safe to run on an already-seeded database.
 */
class ProductAttributeSeeder extends Seeder
{
    public function run(): void
    {
        $size = ProductAttribute::firstOrCreate(['name' => 'Size'], ['category_id' => null]);
        $color = ProductAttribute::firstOrCreate(['name' => 'Color'], ['category_id' => null]);

        // Backfill: multi-variant products whose variants have no attribute
        // values (created before attributes existed). Single-variant products
        // are skipped on purpose — they have nothing to differentiate.
        Product::with(['variants.attributeValues', 'variants.inventory'])
            ->has('variants', '>', 1)
            ->chunkById(100, function ($products) use ($size) {
                foreach ($products as $product) {
                    foreach ($product->variants as $variant) {
                        if ($variant->attributeValues->isNotEmpty()) {
                            continue;
                        }

                        $label = $this->labelFromSku($variant->sku);

                        if ($label === null) {
                            continue;
                        }

                        ProductAttributeValue::firstOrCreate([
                            'product_attribute_id' => $size->id,
                            'product_variant_id' => $variant->id,
                        ], [
                            'value' => $label,
                        ]);
                    }
                }
            });
    }

    /**
     * Recover the variant label from a SKU tail, e.g.
     * "PZR-CORD-BRN-M" -> "M", "JL-BLACK-M" -> "M".
     * Returns null when the SKU gives no usable signal.
     */
    protected function labelFromSku(?string $sku): ?string
    {
        if (! $sku || ! str_contains($sku, '-')) {
            return null;
        }

        $tail = strtoupper(substr($sku, strrpos($sku, '-') + 1));

        // Accept plain sizes and short alphanumeric codes; reject long
        // generated tails like "X0QI5V" that carry no meaning.
        if (in_array($tail, ['S', 'M', 'L', 'XL', 'XXL', 'XS', '2XL', '3XL'], true)) {
            return $tail;
        }

        if (preg_match('/^[A-Z]{1,2}\d?$/', $tail) && ! preg_match('/[A-Z]{3,}/', $tail)) {
            return $tail;
        }

        return null;
    }
}
