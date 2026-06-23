<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Models\Store;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    public function mount(): void
    {
        parent::mount();

        $store = Store::query()->first();
        if (! $store) {
            $this->redirect(StoreResource::getUrl('create'), navigate: false);

            return;
        }

        $this->redirect(StoreResource::getUrl('edit', ['record' => $store]), navigate: false);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
