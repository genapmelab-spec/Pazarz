<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create cart for user
     */
    public function getCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Load cart with all needed relationships
     */
    protected function freshLoadCart(Cart $cart): Cart
    {
        $cart->unsetRelation('items');
        return $cart->load('items.variant.product.store', 'items.variant.product.images', 'items.variant.attributeValues.attribute');
    }

    /**
     * Add item to cart
     */
    public function addItem(User $user, int $variantId, int $quantity = 1): Cart
    {
        $variant = ProductVariant::with('inventory')->findOrFail($variantId);

        // Validate stock
        if (!$variant->inventory || !$variant->inventory->hasStock($quantity)) {
            throw new \App\Exceptions\InsufficientStockException(
                'Insufficient stock for ' . $variant->product->name
            );
        }

        $cart = $this->getCart($user);

        $existingItem = $cart->items()->where('product_variant_id', $variantId)->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if (!$variant->inventory->hasStock($newQuantity)) {
                throw new \App\Exceptions\InsufficientStockException(
                    'Insufficient stock for ' . $variant->product->name
                );
            }
            $existingItem->update([
                'quantity' => $newQuantity,
                'price_snapshot' => $variant->effective_price,
            ]);
        } else {
            $cart->items()->create([
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price_snapshot' => $variant->effective_price,
            ]);
        }

        return $this->freshLoadCart($cart);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(User $user, int $cartItemId, int $quantity): Cart
    {
        $cart = $this->getCart($user);
        $item = $cart->items()->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $item->delete();
            return $this->freshLoadCart($cart);
        }

        $variant = ProductVariant::with('inventory')->findOrFail($item->product_variant_id);

        if (!$variant->inventory || !$variant->inventory->hasStock($quantity)) {
            throw new \App\Exceptions\InsufficientStockException(
                'Insufficient stock for ' . $variant->product->name
            );
        }

        $item->update([
            'quantity' => $quantity,
            'price_snapshot' => $variant->effective_price,
        ]);

        return $this->freshLoadCart($cart);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(User $user, int $cartItemId): Cart
    {
        $cart = $this->getCart($user);
        $cart->items()->where('id', $cartItemId)->delete();
        return $this->freshLoadCart($cart);
    }

    /**
     * Clear entire cart
     */
    public function clear(User $user): Cart
    {
        $cart = $this->getCart($user);
        $cart->items()->delete();
        return $cart;
    }
}
