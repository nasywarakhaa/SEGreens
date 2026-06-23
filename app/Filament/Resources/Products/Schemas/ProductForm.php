<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_category_id')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                TextInput::make('sku')
                    ->label(__('admin.products.fields.sku'))
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state): mixed => $set('slug', self::generateUniqueSlug($state)))
                    ->required(),
                TextInput::make('slug')
                    ->required()
                    ->disabled()
                    ->dehydrated(true)
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->disk('public')
                    ->directory('products/images')
                    ->image(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('stock')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('sell_count')
                    ->visible(fn (string $operation): bool => $operation === 'edit')
                    ->disabled()
                    ->dehydrated(false)
                    ->numeric()
                    ->default(0),
                TextInput::make('weight')
                    ->label(__('admin.products.fields.weight'))
                    ->required()
                    ->numeric(),
                TextInput::make('unit')
                    ->label(__('admin.products.fields.weight_unit'))
                    ->required(),
                TextInput::make('min_order_qty')
                    ->required()
                    ->numeric()
                    ->default(1),
                Select::make('sort_order')
                    ->label(__('admin.products.fields.sort_order'))
                    ->required()
                    ->native(false)
                    ->options(fn (?Product $record): array => self::sortOrderOptions($record))
                    ->default(fn (): int => self::nextSortOrder())
                    ->dehydrateStateUsing(fn (mixed $state): int => (int) $state)
                    ->rules(fn (?Product $record): array => [
                        'integer',
                        'min:1',
                        Rule::unique('products', 'sort_order')->ignore($record?->getKey()),
                    ]),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    private static function generateUniqueSlug(?string $name): string
    {
        $baseSlug = Str::slug((string) $name);
        if ($baseSlug === '') {
            $baseSlug = 'produk';
        }

        return $baseSlug.'-'.now()->valueOf();
    }

    /**
     * @return array<int, string>
     */
    private static function sortOrderOptions(?Product $record): array
    {
        $query = Product::query();

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
        $usedOrders = Product::query()
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
}
