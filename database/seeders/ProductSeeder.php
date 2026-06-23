<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::query()
            ->whereIn('slug', [
                'spinach',
                'carrot',
                'banana',
                'orange',
                'garlic',
                'baby-corn',
                'baby-potato',
                'celery-stick',
                'jamur-champingnon',
                'jamur-innoki',
                'kyuri',
                'lettuce',
                'romain',
                'tomat-cherry-merah',
                'wansui',
                'zucchini',
                'limo',
                'parsly',
                'telur-negeri',
            ])
            ->delete();

        $products = [
            ['category' => 'sayuran', 'name' => 'Telur Asin Matang', 'price' => 5000, 'unit' => 'pcs'],
            ['category' => 'sayuran', 'name' => 'Telur Ayam Negeri', 'price' => 30000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Telur Omega', 'price' => 35000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Jagung Muda', 'price' => 40000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Kentang Muda', 'price' => 20000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Bawang Bombay', 'price' => 35000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Bawang Merah', 'price' => 60000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Bawang Merah Kupas', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Bawang Putih', 'price' => 45000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Bawang Putih Kupas', 'price' => 55000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Cabe Merah Besar', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Cabe Merah Keriting', 'price' => 60000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Cabe Rawit Hijau', 'price' => 40000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Cabe Rawit Merah', 'price' => 70000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Seledri Batang', 'price' => 30000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Bawang', 'price' => 18000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Bawang Kecil', 'price' => 60000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Jeruk', 'price' => 6000, 'unit' => 'ons'],
            ['category' => 'herba', 'name' => 'Daun Pandan', 'price' => 20000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Pisang', 'price' => 12000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Singkong', 'price' => 10000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Jamur Champignon', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Jamur Enoki', 'price' => 5000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Jamur Shimeji', 'price' => 10000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Jamur Shitake', 'price' => 120000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Kacang Panjang', 'price' => 10000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Kembang Kol', 'price' => 25000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Kentang Dieng', 'price' => 18000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Kol', 'price' => 8000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Timun Jepang', 'price' => 24000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Selada', 'price' => 40000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Lobak', 'price' => 15000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Mesclun', 'price' => 35000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Paprika Hijau', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Paprika Merah', 'price' => 60000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Selada Romaine', 'price' => 40000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Singkong', 'price' => 6000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Terong', 'price' => 14000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Timun', 'price' => 10000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Toge', 'price' => 12000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Tomat Ceri Merah', 'price' => 45000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Tomat Hijau', 'price' => 18000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Tomat Merah', 'price' => 20000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Daun Ketumbar (Wansui)', 'price' => 80000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Wortel', 'price' => 16000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Wortel Brastagi', 'price' => 13000, 'unit' => 'kg'],
            ['category' => 'sayuran', 'name' => 'Zukini', 'price' => 30000, 'unit' => 'kg'],

            ['category' => 'bumbu', 'name' => 'Bambu Rendang', 'price' => 7500, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Ebi Kering', 'price' => 270000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Kapolaga', 'price' => 18000, 'unit' => 'ons'],
            ['category' => 'bumbu', 'name' => 'Kemiri', 'price' => 60000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Ketumbar', 'price' => 32000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Kluwek', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Kunyit', 'price' => 18000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Lada Bulat', 'price' => 18000, 'unit' => 'ons'],
            ['category' => 'bumbu', 'name' => 'Laos', 'price' => 16000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Lengkuas', 'price' => 18000, 'unit' => 'kg'],
            ['category' => 'bumbu', 'name' => 'Jeruk Limo', 'price' => 30000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Peterseli', 'price' => 50000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Rosemary', 'price' => 15000, 'unit' => 'kg'],
            ['category' => 'herba', 'name' => 'Salam', 'price' => 2000, 'unit' => 'ons'],
            ['category' => 'herba', 'name' => 'Sereh', 'price' => 12000, 'unit' => 'kg'],
        ];

        $categoryIds = ProductCategory::query()
            ->pluck('id', 'slug')
            ->all();

        foreach ($products as $index => $product) {
            $categoryId = $categoryIds[$product['category']] ?? null;
            if (! $categoryId) {
                continue;
            }

            $weight = match ($product['unit']) {
                'pcs' => 1,
                'ons' => 100,
                default => 1000,
            };

            $sku = 'SEG-'.strtoupper(substr(sha1(Str::slug($product['name'])), 0, 8));

            Product::query()->updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'product_category_id' => $categoryId,
                    'sku' => $sku,
                    'name' => $product['name'],
                    'description' => $product['name'].' - produk seeder',
                    'image' => $this->productImageUrl($product['name'], $product['category'], $index + 1),
                    'price' => $product['price'],
                    'stock' => 100,
                    'sell_count' => 0,
                    'weight' => $weight,
                    'unit' => $product['unit'],
                    'min_order_qty' => 1,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    private function productImageUrl(string $name, string $categorySlug, int $index): string
    {
        $categoryKeyword = match ($categorySlug) {
            'sayuran' => 'vegetable',
            'buah' => 'fruit',
            'bumbu' => 'spice',
            'herba' => 'herb',
            'paket' => 'grocery',
            default => 'food',
        };

        $nameKeyword = Str::of($name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ',')
            ->trim(',')
            ->toString();

        return sprintf(
            'https://loremflickr.com/1200/900/%s,%s?lock=%d',
            $categoryKeyword,
            $nameKeyword !== '' ? $nameKeyword : 'product',
            2000 + $index,
        );
    }
}
