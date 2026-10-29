<?php


namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        Product::factory()->create([
            'name' => 'Wireless Mouse',
            'code' => 'PRD-1001',
            'price' => 500.00,
            'tax_percentage' => 18,
            'stock_quantity' => 20,
        ]);

        Product::factory()->create([
            'name' => 'Mechanical Keyboard',
            'code' => 'PRD-1002',
            'price' => 1500.00,
            'tax_percentage' => 18,
            'stock_quantity' => 3,
        ]);

        Product::factory()->create([
            'name' => 'USB-C Cable',
            'code' => 'PRD-1003',
            'price' => 250.00,
            'tax_percentage' => 5,
            'stock_quantity' => 50,
        ]);

        Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
        ]);

        Customer::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'demo@example.com',
        ]);
    }
}
