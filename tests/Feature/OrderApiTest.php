<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_is_rejected_when_stock_is_insufficient(): void
    {
        $customer = Customer::factory()->create();

        $product = Product::factory()->create([
            'price' => 500.00,
            'tax_percentage' => 18,
            'stock_quantity' => 3,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                ],
            ],
        ]);

        $response->assertUnprocessable();

        $response->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('orders', 0);

        $this->assertSame(
            3,
            $product->fresh()->stock_quantity
        );
    }


    public function test_order_is_created_and_stock_is_decreased(): void
    {
        $customer = Customer::factory()->create();

        $product = Product::factory()->create([
            'price' => 500.00,
            'tax_percentage' => 18,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated();

        $response->assertJsonPath('data.subtotal', '1000.00');
        $response->assertJsonPath('data.tax_total', '180.00');
        $response->assertJsonPath('data.grand_total', '1180.00');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);

        $this->assertSame(
            8,
            $product->fresh()->stock_quantity
        );
    }


    public function test_customer_can_retrieve_order_history_by_email(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'history@example.com',
        ]);

        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_percentage' => 10,
            'stock_quantity' => 5,
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'subtotal' => 200.00,
            'tax_total' => 20.00,
            'grand_total' => 220.00,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 100.00,
            'tax_percentage' => 10,
            'line_subtotal' => 200.00,
            'line_tax' => 20.00,
            'line_total' => 220.00,
        ]);

        $response = $this->getJson(
            '/api/customers/orders?email=history@example.com'
        );

        $response->assertOk();

        $response->assertJsonPath(
            'customer.email',
            'history@example.com'
        );

        $response->assertJsonPath('orders.0.grand_total', '220.00');
        $response->assertJsonPath('orders.0.items.0.quantity', 2);
        $response->assertJsonPath(
            'orders.0.items.0.product.name',
            $product->name
        );
    }


    public function test_low_stock_api_returns_only_products_below_threshold(): void
    {
        Product::factory()->create([
            'name' => 'Low Stock Item',
            'stock_quantity' => 2,
        ]);

        Product::factory()->create([
            'name' => 'Threshold Stock Item',
            'stock_quantity' => 5,
        ]);

        Product::factory()->create([
            'name' => 'Healthy Stock Item',
            'stock_quantity' => 10,
        ]);

        $response = $this->getJson(
            '/api/products/low-stock?threshold=5'
        );

        $response->assertOk();

        $response->assertJsonPath('threshold', 5);

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.name', 'Low Stock Item');
    }
}
