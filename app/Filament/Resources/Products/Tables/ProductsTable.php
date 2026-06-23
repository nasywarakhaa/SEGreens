<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->label(__('admin.products.fields.category'))
                    ->searchable()
                    ->sortable(),
                ImageColumn::make('image')
                    ->disk('public'),
                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('stock')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label(__('admin.products.fields.category'))
                    ->relationship('category', 'name'),
                Filter::make('price_range')
                    ->label(__('admin.products.filters.price_range'))
                    ->form([
                        TextInput::make('price_min')
                            ->label(__('admin.products.filters.price_min'))
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('0'),
                        TextInput::make('price_max')
                            ->label(__('admin.products.filters.price_max'))
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('100000'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (($data['price_min'] ?? null) !== null && $data['price_min'] !== '') {
                            $query->where('price', '>=', (float) $data['price_min']);
                        }

                        if (($data['price_max'] ?? null) !== null && $data['price_max'] !== '') {
                            $query->where('price', '<=', (float) $data['price_max']);
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
