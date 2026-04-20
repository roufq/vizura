<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Category::query()->exists()) {
            $this->call(CategorySeeder::class);
        }

        if (! Unit::query()->exists()) {
            $this->call(UnitSeeder::class);
        }

        $categories = Category::query()->pluck('id', 'name')->all();
        $units = Unit::query()->pluck('id', 'name')->all();

        $products = [
            ['sku' => 'SKU-10001', 'barcode' => '89910001', 'name' => 'Premium Rice 5Kg', 'category' => 'Essential Goods', 'unit' => 'Kg', 'sale_price' => 75000, 'cost_price' => 68000, 'is_taxable' => false],
            ['sku' => 'SKU-10002', 'barcode' => '89910002', 'name' => 'Sugar 1Kg', 'category' => 'Essential Goods', 'unit' => 'Kg', 'sale_price' => 16000, 'cost_price' => 14000, 'is_taxable' => false],
            ['sku' => 'SKU-10003', 'barcode' => '89910003', 'name' => 'Cooking Oil 1L', 'category' => 'Groceries', 'unit' => 'Liter', 'sale_price' => 19000, 'cost_price' => 17000, 'is_taxable' => false],
            ['sku' => 'SKU-10004', 'barcode' => '89910004', 'name' => 'Salt 500g', 'category' => 'Kitchen Spices', 'unit' => 'Gram', 'sale_price' => 4000, 'cost_price' => 3000, 'is_taxable' => false],
            ['sku' => 'SKU-10005', 'barcode' => '89910005', 'name' => 'Chicken Eggs 1 Dozen', 'category' => 'Fresh Products', 'unit' => 'Dozen', 'sale_price' => 30000, 'cost_price' => 27000, 'is_taxable' => false],
            ['sku' => 'SKU-10006', 'barcode' => '89910006', 'name' => 'UHT Milk 1L', 'category' => 'Beverages', 'unit' => 'Liter', 'sale_price' => 18000, 'cost_price' => 15000, 'is_taxable' => false],
            ['sku' => 'SKU-10007', 'barcode' => '89910007', 'name' => 'Mineral Water 600ml', 'category' => 'Beverages', 'unit' => 'Ml', 'sale_price' => 3500, 'cost_price' => 2500, 'is_taxable' => false],
            ['sku' => 'SKU-10008', 'barcode' => '89910008', 'name' => 'Bottled Tea 350ml', 'category' => 'Beverages', 'unit' => 'Ml', 'sale_price' => 5000, 'cost_price' => 3800, 'is_taxable' => false],
            ['sku' => 'SKU-10009', 'barcode' => '89910009', 'name' => 'Instant Coffee Sachet', 'category' => 'Beverages', 'unit' => 'Sachet', 'sale_price' => 1500, 'cost_price' => 1100, 'is_taxable' => false],
            ['sku' => 'SKU-10010', 'barcode' => '89910010', 'name' => 'Instant Noodles', 'category' => 'Food', 'unit' => 'Pcs', 'sale_price' => 3500, 'cost_price' => 2700, 'is_taxable' => false],
            ['sku' => 'SKU-10011', 'barcode' => '89910011', 'name' => 'Chocolate Biscuits', 'category' => 'Snack', 'unit' => 'Pack', 'sale_price' => 9000, 'cost_price' => 7000, 'is_taxable' => false],
            ['sku' => 'SKU-10012', 'barcode' => '89910012', 'name' => 'Potato Chips', 'category' => 'Snack', 'unit' => 'Pack', 'sale_price' => 12000, 'cost_price' => 9500, 'is_taxable' => false],
            ['sku' => 'SKU-10013', 'barcode' => '89910013', 'name' => 'Canned Sardines', 'category' => 'Food', 'unit' => 'Can', 'sale_price' => 18000, 'cost_price' => 15000, 'is_taxable' => false],
            ['sku' => 'SKU-10014', 'barcode' => '89910014', 'name' => 'Chili Sauce', 'category' => 'Kitchen Spices', 'unit' => 'Bottle', 'sale_price' => 14000, 'cost_price' => 11000, 'is_taxable' => false],
            ['sku' => 'SKU-10015', 'barcode' => '89910015', 'name' => 'Detergent Powder', 'category' => 'Cleaning Supplies', 'unit' => 'Pack', 'sale_price' => 18000, 'cost_price' => 15000, 'is_taxable' => true],
            ['sku' => 'SKU-10016', 'barcode' => '89910016', 'name' => 'Dishwashing Soap', 'category' => 'Cleaning Supplies', 'unit' => 'Bottle', 'sale_price' => 13000, 'cost_price' => 10000, 'is_taxable' => true],
            ['sku' => 'SKU-10017', 'barcode' => '89910017', 'name' => 'Shampoo 170ml', 'category' => 'Personal Care', 'unit' => 'Bottle', 'sale_price' => 22000, 'cost_price' => 18000, 'is_taxable' => true],
            ['sku' => 'SKU-10018', 'barcode' => '89910018', 'name' => 'Tissue Box', 'category' => 'Cleaning Supplies', 'unit' => 'Box', 'sale_price' => 15000, 'cost_price' => 12000, 'is_taxable' => true],
            ['sku' => 'SKU-10019', 'barcode' => '89910019', 'name' => 'Health Mask', 'category' => 'Health Care', 'unit' => 'Box', 'sale_price' => 25000, 'cost_price' => 20000, 'is_taxable' => true],
            ['sku' => 'SKU-10020', 'barcode' => '89910020', 'name' => 'Vitamin C', 'category' => 'Health Care', 'unit' => 'Pcs', 'sale_price' => 5000, 'cost_price' => 3500, 'is_taxable' => true],
            ['sku' => 'SKU-10021', 'barcode' => '89910021', 'name' => 'Sliced Bread', 'category' => 'Bakery', 'unit' => 'Pack', 'sale_price' => 15000, 'cost_price' => 12000, 'is_taxable' => false],
            ['sku' => 'SKU-10022', 'barcode' => '89910022', 'name' => 'Chicken Meat 1Kg', 'category' => 'Fresh Products', 'unit' => 'Kg', 'sale_price' => 38000, 'cost_price' => 34000, 'is_taxable' => false],
            ['sku' => 'SKU-10023', 'barcode' => '89910023', 'name' => 'Fillet Fish 1Kg', 'category' => 'Fresh Products', 'unit' => 'Kg', 'sale_price' => 52000, 'cost_price' => 47000, 'is_taxable' => false],
            ['sku' => 'SKU-10024', 'barcode' => '89910024', 'name' => 'Baby Diapers', 'category' => 'Baby Care', 'unit' => 'Pack', 'sale_price' => 65000, 'cost_price' => 60000, 'is_taxable' => true],
            ['sku' => 'SKU-10025', 'barcode' => '89910025', 'name' => 'Black Ballpoint', 'category' => 'Stationery', 'unit' => 'Pcs', 'sale_price' => 3000, 'cost_price' => 2000, 'is_taxable' => true],
        ];

        foreach ($products as $item) {
            $categoryId = $categories[$item['category']] ?? null;
            $unitId = $units[$item['unit']] ?? null;

            if (! $unitId) {
                continue;
            }

            Product::updateOrCreate(
                ['sku' => $item['sku']],
                [
                    'barcode' => $item['barcode'],
                    'name' => $item['name'],
                    'category_id' => $categoryId,
                    'unit_id' => $unitId,
                    'sale_price' => $item['sale_price'],
                    'cost_price' => $item['cost_price'],
                    'is_taxable' => $item['is_taxable'],
                    'is_active' => true,
                    'block_when_out_of_stock' => false,
                    'batch_code' => null,
                    'expires_at' => null,
                ]
            );
        }
    }
}
