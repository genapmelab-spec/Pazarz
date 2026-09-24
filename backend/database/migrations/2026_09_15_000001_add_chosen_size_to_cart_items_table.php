<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fashion products without seller-defined variants still get a standard size
 * choice (S/M/L/XL) on the storefront. The chosen size is persisted on the
 * cart line so it survives Cart -> Checkout -> Order. The previous unique
 * constraint (cart_id, product_variant_id) is relaxed to a normal index so
 * the same variant can be added once per chosen size.
 *
 * InnoDB binds the composite unique index to the product_variants foreign
 * key, so the FK is dropped before the unique index and re-added afterwards.
 * Every step re-checks the live schema so the migration also completes
 * cleanly on a database left half-migrated by a failed earlier run.
 */
return new class extends Migration
{
    protected function hasUniqueIndex(): bool
    {
        return collect(Schema::getIndexes('cart_items'))
            ->pluck('name')
            ->contains('cart_items_cart_id_product_variant_id_unique');
    }

    protected function hasPlainIndex(): bool
    {
        return collect(Schema::getIndexes('cart_items'))
            ->pluck('name')
            ->contains('cart_items_cart_id_product_variant_id_index');
    }

    protected function hasCartIdIndex(): bool
    {
        return collect(Schema::getIndexes('cart_items'))
            ->pluck('name')
            ->contains('cart_items_cart_id_index');
 }

    protected function hasVariantForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('cart_items'))
            ->pluck('name')
            ->contains('cart_items_product_variant_id_foreign');
    }

    public function up(): void
    {
        if (! Schema::hasColumn('cart_items', 'chosen_size')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->string('chosen_size', 16)->nullable()->after('product_variant_id');
            });
        }

        if ($this->hasVariantForeignKey()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign('cart_items_product_variant_id_foreign');
            });
        }

        // The cart_id foreign key is backed by the composite unique index
        // (leftmost column cart_id). Give it a dedicated index BEFORE the
        // unique index can be dropped, otherwise MySQL raises error 1553.
        if (! $this->hasCartIdIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index('cart_id');
            });
        }

        if ($this->hasUniqueIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropUnique('cart_items_cart_id_product_variant_id_unique');
            });
        }

        if (! $this->hasPlainIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->index(['cart_id', 'product_variant_id']);
            });
        }

        if (! $this->hasVariantForeignKey()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('product_variant_id')->references('id')->on('product_variants');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasVariantForeignKey()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropForeign('cart_items_product_variant_id_foreign');
            });
        }

        // Collapse duplicate variant rows (same variant, different sizes)
        // back to one before restoring the unique constraint.
        $rows = DB::table('cart_items')
            ->select('cart_id', 'product_variant_id')
            ->selectRaw('MAX(id) as keep_id')
            ->groupBy('cart_id', 'product_variant_id');

        DB::table('cart_items')
            ->joinSub($rows, 'latest', function ($join) {
                $join->on('cart_items.cart_id', '=', 'latest.cart_id')
                    ->on('cart_items.product_variant_id', '=', 'latest.product_variant_id')
                    ->whereColumn('cart_items.id', '!=', 'latest.keep_id');
            })
            ->delete();

        if (! $this->hasUniqueIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unique(['cart_id', 'product_variant_id']);
            });
        }

        if ($this->hasPlainIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('cart_items_cart_id_product_variant_id_index');
            });
        }

        // Only drop the dedicated cart_id index once the composite unique
        // index is back in place to back the cart_id foreign key.
        if ($this->hasCartIdIndex() && $this->hasUniqueIndex()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropIndex('cart_items_cart_id_index');
            });
        }

        if (! $this->hasVariantForeignKey()) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->foreign('product_variant_id')->references('id')->on('product_variants');
            });
        }

        if (Schema::hasColumn('cart_items', 'chosen_size')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->dropColumn('chosen_size');
            });
        }
    }
};
