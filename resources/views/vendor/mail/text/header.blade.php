@php
    $appName = trim((string) $slot);
    if ($appName === '' || strcasecmp($appName, 'Laravel') === 0) {
        $appName = 'SEGreens';
    }
@endphp
{{ $appName }}: {{ $url }}
