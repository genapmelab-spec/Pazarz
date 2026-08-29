<?php

namespace Tests\Feature\Api;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_get_cart(): void
    {
        $user = User::factory()->create();
        $user->cart()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonStructure(['success', 'data']);
    }

    public function test_user_can_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $user->cart()->create();

        $variant = ProductVariant::factory()->create();
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    public function test_user_cannot_add_out_of_stock_item(): void
    {
        $user = User::factory()->create();
        $user->cart()->create();

        $variant = ProductVariant::factory()->create();
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 0,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/cart/items', [
            'product_variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('error.code', 'INSUFFICIENT_STOCK');
    }

    public function test_user_can_update_cart_item(): void
    {
        $user = User::factory()->create();
        $cart = $user->cart()->create();

        $variant = ProductVariant::factory()->create();
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $cartItem = $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price_snapshot' => $variant->effective_price,
        ]);

        $response = $this->actingAs($user)->patchJson("/api/v1/cart/items/{$cartItem->id}", [
            'quantity' => 3,
        ]);

        $response->assertOk();
    }

    public function test_user_can_remove_cart_item(): void
    {
        $user = User::factory()->create();
        $cart = $user->cart()->create();

        $variant = ProductVariant::factory()->create();
        Inventory::factory()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $cartItem = $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'price_snapshot' => $variant->effective_price,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/cart/items/{$cartItem->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }
}
