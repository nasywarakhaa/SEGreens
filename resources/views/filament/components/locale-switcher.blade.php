@php
    $currentLocale = app()->getLocale();
@endphp

<x-filament::dropdown placement="bottom-end" teleport>
    <x-slot name="trigger">
        <x-filament::icon-button
            color="gray"
            icon="heroicon-o-language"
            :label="__('admin.locale.switch')"
        />
    </x-slot>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item
            :color="$currentLocale === 'id' ? 'primary' : 'gray'"
            :href="route('locale.switch', ['locale' => 'id'])"
            tag="a"
        >
            {{ __('admin.locale.indonesian') }}
        </x-filament::dropdown.list.item>

        <x-filament::dropdown.list.item
            :color="$currentLocale === 'en' ? 'primary' : 'gray'"
            :href="route('locale.switch', ['locale' => 'en'])"
            tag="a"
        >
            {{ __('admin.locale.english') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
</x-filament::dropdown>

