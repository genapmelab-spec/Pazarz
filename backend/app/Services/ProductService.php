<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductAttributeValue;
use App\Models\Inventory;
use App\Models\Store;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    /**
     * Get paginated products with filters
     */
    public function getProducts(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['store', 'category', 'primaryImage', 'variants'])
            ->where('status', 'active');

        if (!empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters['q']}%")
                  ->orWhere('description', 'LIKE', "%{$filters['q']}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('base_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('base_price', '<=', $filters['max_price']);
        }

        if (!empty($filters['rating_min'])) {
            $query->where('rating_avg', '>=', $filters['rating_min']);
        }

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        $query = match($sort) {
            'price_asc' => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'best_selling' => $query->orderBy('sold_count', 'desc'),
            'rating' => $query->orderBy('rating_avg', 'desc'),
            default => $query->orderBy('created_at', 'desc'), // newest
        };

        $perPage = min($filters['per_page'] ?? 24, 48);
        return $query->paginate($perPage);
    }

    /**
     * Get product detail by slug
     */
    public function getProductBySlug(string $slug): ?Product
    {
        return Product::with([
            'store',
            'category',
            'images',
            'variants.inventory',
            'variants.attributeValues.attribute',
            'variants.image',
            'reviews' => fn($q) => $q->where('status', 'visible')->with('user', 'images'),
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->first();
    }

    /**
     * Create a product with variants
     */
    public function createProduct(Store $store, array $data): Product
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($store, $data) {
            $product = $store->products()->create([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $this->generateSlug($data['name'], $store->id),
                'description' => $data['description'] ?? null,
                'base_price' => $data['base_price'],
                'status' => $data['status'] ?? 'draft',
                'weight_grams' => $data['weight_grams'] ?? 0,
            ]);

            // Create images
            if (!empty($data['images'])) {
                foreach ($data['images'] as $index => $imageUrl) {
                    $product->images()->create([
                        'url' => $imageUrl,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Create variants
            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variant = $product->variants()->create([
                        'sku' => $variantData['sku'],
                        'price' => $variantData['price'] ?? null,
                        'image_id' => $variantData['image_id'] ?? null,
                    ]);

                    // Create inventory
                    Inventory::create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $variantData['stock'] ?? 0,
                        'low_stock_threshold' => $variantData['low_stock_threshold'] ?? 5,
                    ]);

                    // Create attribute values
                    if (!empty($variantData['attributes'])) {
                        foreach ($variantData['attributes'] as $attrId => $value) {
                            ProductAttributeValue::create([
                                'product_attribute_id' => $attrId,
                                'product_variant_id' => $variant->id,
                                'value' => $value,
                            ]);
                        }
                    }
                }
            } else {
                // Create default variant if none specified
                $sku = strtoupper(substr(uniqid('PZ-'), 0, 12));
                $variant = $product->variants()->create([
                    'sku' => $sku,
                ]);
                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $data['stock'] ?? 0,
                ]);
            }

            return $product;
        });
    }

    /**
     * Update a product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($product, $data) {
            $product->update([
                'name' => $data['name'] ?? $product->name,
                'description' => $data['description'] ?? $product->description,
                'base_price' => $data['base_price'] ?? $product->base_price,
                'category_id' => $data['category_id'] ?? $product->category_id,
                'status' => $data['status'] ?? $product->status,
                'weight_grams' => $data['weight_grams'] ?? $product->weight_grams,
            ]);

            // Update images if provided
            if (isset($data['images'])) {
                $product->images()->delete();
                foreach ($data['images'] as $index => $imageUrl) {
                    $product->images()->create([
                        'url' => $imageUrl,
                        'sort_order' => $index,
                        'is_primary' => $index === 0,
                    ]);
                }
            }

            // Update variants if provided
            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variant = $product->variants()->find($variantData['id'] ?? null);
                    if ($variant) {
                        $variant->update([
                            'sku' => $variantData['sku'] ?? $variant->sku,
                            'price' => $variantData['price'] ?? $variant->price,
                        ]);
                        if (isset($variantData['stock']) && $variant->inventory) {
                            $variant->inventory->update(['quantity' => $variantData['stock']]);
                        }
                    }
                }
            }

            return $product;
        });
    }

    protected function generateSlug(string $name, int $storeId): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Product::where('store_id', $storeId)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }
}
