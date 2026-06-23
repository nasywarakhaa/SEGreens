<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', self::generateUniqueSlug($state)))
                    ->unique(ignoreRecord: true),
                TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated(true)
                    ->unique(ignoreRecord: true),
                FileUpload::make('image')
                    ->label(__('admin.product_categories.fields.image'))
                    ->disk('public')
                    ->directory('categories/images')
                    ->image(),
                TextInput::make('description'),
                Select::make('sort_order')
                    ->required()
                    ->native(false)
                    ->options(fn (?ProductCategory $record): array => self::sortOrderOptions($record))
                    ->default(fn (): int => self::nextSortOrder())
                    ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                    ->rules(fn (?ProductCategory $record): array => [
                        'integer',
                        'min:1',
                        Rule::unique('product_categories', 'sort_order')->ignore($record?->getKey()),
                    ]),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function sortOrderOptions(?ProductCategory $record): array
    {
        $query = ProductCategory::query();

        if ($record?->exists) {
            $query->whereKeyNot($record->getKey());
        }

        $usedOrders = $query
            ->pluck('sort_order')
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique()
            ->values();

        $currentOrder = max(0, (int) ($record?->sort_order ?? 0));
        $maxOrder = max((int) ($usedOrders->max() ?? 0), $currentOrder);
        $limit = max(1, $maxOrder + 1);

        $options = [];
        for ($order = 1; $order <= $limit; $order++) {
            $isCurrent = $currentOrder === $order;
            if ($isCurrent || ! $usedOrders->contains($order)) {
                $options[$order] = (string) $order;
            }
        }

        return $options;
    }

    private static function nextSortOrder(): int
    {
        $usedOrders = ProductCategory::query()
            ->pluck('sort_order')
            ->map(fn (mixed $value): int => (int) $value)
            ->filter(fn (int $value): bool => $value > 0)
            ->unique();

        $maxOrder = (int) ($usedOrders->max() ?? 0);
        for ($order = 1; $order <= $maxOrder + 1; $order++) {
            if (! $usedOrders->contains($order)) {
                return $order;
            }
        }

        return 1;
    }

    private static function generateUniqueSlug(?string $name): string
    {
        $baseSlug = Str::slug((string) $name);
        if ($baseSlug === '') {
            $baseSlug = 'kategori';
        }

        return $baseSlug.'-'.now()->valueOf();
    }
}
