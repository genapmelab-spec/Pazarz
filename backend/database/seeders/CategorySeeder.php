<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Marketplace category tree: parent => subcategories.
     * Slugs are prefixed with the parent slug so they stay globally unique
     * even when subcategory names repeat across parents.
     */
    public function run(): void
    {
        $tree = [
            'Elektronik' => ['Smartphone', 'Laptop & PC', 'Tablet', 'Kamera', 'Audio', 'Aksesoris Elektronik'],
            'Fashion Pria' => ['Atasan', 'Bawahan', 'Sepatu', 'Tas & Dompet', 'Jam Tangan', 'Aksesoris Fashion'],
            'Fashion Wanita' => ['Atasan', 'Bawahan', 'Dress', 'Sepatu', 'Tas & Dompet', 'Aksesoris Fashion'],
            'Kesehatan & Kecantikan' => ['Skincare', 'Makeup', 'Perawatan Tubuh', 'Vitamin & Suplemen'],
            'Rumah Tangga' => ['Peralatan Dapur', 'Furnitur', 'Dekorasi', 'Pembersih Rumah'],
            'Olahraga & Hobi' => ['Pakaian Olahraga', 'Alat Fitness', 'Sepeda', 'Gaming', 'Musik'],
            'Buku & Alat Tulis' => ['Buku', 'Alat Tulis', 'Perlengkapan Sekolah'],
            'Otomotif' => ['Aksesoris Mobil', 'Aksesoris Motor', 'Spare Part', 'Perawatan Kendaraan'],
            'Makanan & Minuman' => ['Makanan Ringan', 'Minuman', 'Bahan Pokok', 'Makanan Beku'],
            'Bayi & Anak' => ['Perlengkapan Bayi', 'Pakaian Anak', 'Mainan', 'Susu & Makanan Bayi'],
        ];

        $sort = 0;

        foreach ($tree as $parentName => $subcategories) {
            $parent = $this->firstOrCreate($parentName, null, $sort++);

            foreach ($subcategories as $subName) {
                $this->firstOrCreate($subName, $parent->id, 0);
            }
        }

        $this->command->info('Categories seeded: '.Category::count().' total.');
    }

    /**
     * Find an existing category by slug or create it. Idempotent across re-runs.
     */
    private function firstOrCreate(string $name, ?int $parentId, int $sortOrder): Category
    {
        $slug = $parentId
            ? $this->slugFor($name, $parentId)
            : Str::slug($name);

        return Category::withTrashed()->firstOrCreate(
            ['slug' => $slug],
            [
                'parent_id' => $parentId,
                'name' => $name,
                'icon_url' => null,
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]
        );
    }

    /**
     * Subcategory slugs are prefixed with the parent slug to keep them unique.
     */
    private function slugFor(string $name, int $parentId): string
    {
        $parentSlug = Category::withTrashed()->find($parentId)?->slug ?? 'kategori';

        return $parentSlug.'-'.Str::slug($name);
    }
}
