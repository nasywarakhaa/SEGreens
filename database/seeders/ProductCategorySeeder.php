<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['slug' => 'sayuran', 'legacy_slugs' => ['vegetables'], 'name' => 'Sayuran', 'description' => 'Kategori sayuran segar'],
            ['slug' => 'buah', 'legacy_slugs' => ['fruits'], 'name' => 'Buah', 'description' => 'Kategori buah segar'],
            ['slug' => 'bumbu', 'legacy_slugs' => ['spices'], 'name' => 'Bumbu', 'description' => 'Kategori bumbu dan rempah'],
            ['slug' => 'herba', 'legacy_slugs' => ['herbs'], 'name' => 'Herba', 'description' => 'Kategori daun dan herba'],
            ['slug' => 'paket', 'legacy_slugs' => ['bundles'], 'name' => 'Paket', 'description' => 'Kategori paket produk'],
        ];

        foreach ($categories as $index => $category) {
            $record = ProductCategory::query()
                ->where('name', $category['name'])
                ->orWhere('slug', $category['slug'])
                ->orWhereIn('slug', $category['legacy_slugs'])
                ->first();

            if ($record) {
                $record->update([
                    'name' => $category['name'],
                    'slug' => $category['slug'],
                    'icon' => $this->categoryIconUrl($category['slug'], $index + 1),
                    'image' => $this->categoryImageUrl($category['slug'], $index + 1),
                    'description' => $category['description'],
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]);

                continue;
            }

            ProductCategory::query()->create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'icon' => $this->categoryIconUrl($category['slug'], $index + 1),
                'image' => $this->categoryImageUrl($category['slug'], $index + 1),
                'description' => $category['description'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }
    }

    private function categoryImageUrl(string $slug, int $index): string
    {
        $keyword = match ($slug) {
            'sayuran' => 'vegetable,fresh-produce',
            'buah' => 'fruit,fresh-produce',
            'bumbu' => 'spice,seasoning',
            'herba' => 'herb,leaf',
            'paket' => 'grocery,basket',
            default => 'groceries,food',
        };

        return sprintf('https://loremflickr.com/1200/800/%s?lock=%d', $keyword, 1000 + $index);
    }

    private function categoryIconUrl(string $slug, int $index): string
    {
        return sprintf('https://loremflickr.com/400/400/icon,%s?lock=%d', $slug, 3000 + $index);
    }
}
