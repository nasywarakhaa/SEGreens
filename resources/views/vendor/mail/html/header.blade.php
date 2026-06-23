@props(['url'])
@php
    /** @var \App\Services\BrandAssetService $branding */
    $branding = app(\App\Services\BrandAssetService::class);

    $appName = $branding->getBrandName();
    $logoUrl = trim((string) $branding->getLogoUrl());
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoUrl !== '')
<img src="{{ $logoUrl }}" class="logo" alt="{{ $appName }} Logo">
@else
{{ $appName }}
@endif
</a>
</td>
</tr>
