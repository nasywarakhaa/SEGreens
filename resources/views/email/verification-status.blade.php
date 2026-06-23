@php
    $palettes = [
        'success' => [
            'surface' => '#ecfdf5',
            'ring' => '#10b981',
            'title' => '#065f46',
            'text' => '#065f46',
            'badge' => 'Berhasil',
        ],
        'info' => [
            'surface' => '#eff6ff',
            'ring' => '#3b82f6',
            'title' => '#1d4ed8',
            'text' => '#1e3a8a',
            'badge' => 'Info',
        ],
        'error' => [
            'surface' => '#fef2f2',
            'ring' => '#ef4444',
            'title' => '#b91c1c',
            'text' => '#7f1d1d',
            'badge' => 'Gagal',
        ],
    ];

    $palette = $palettes[$state] ?? $palettes['info'];
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $appName }}</title>
    @if ($logoUrl !== '')
        <link rel="icon" href="{{ $logoUrl }}">
        <link rel="apple-touch-icon" href="{{ $logoUrl }}">
    @endif
    <style>
        :root {
            color-scheme: light;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            --card-max-width: 580px;
            --brand-accent: #16a34a;
            --brand-accent-soft: rgba(22, 163, 74, 0.14);
            --brand-accent-softer: rgba(34, 197, 94, 0.09);
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(900px 420px at -10% -15%, var(--brand-accent-soft) 0%, transparent 60%),
                radial-gradient(760px 360px at 120% 120%, var(--brand-accent-softer) 0%, transparent 60%),
                linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            color: #0f172a;
            padding: 24px;
        }
        .viewport {
            width: min(var(--card-max-width), 100%);
        }
        .card {
            width: 100%;
            border-radius: 24px;
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 24px 56px rgba(15, 23, 42, 0.12);
            padding: 28px;
            background: #ffffff;
            overflow: hidden;
            position: relative;
        }
        .card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand-accent) 0%, #22c55e 100%);
            opacity: 0.9;
        }
        .card-head {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .brand {
            display: inline-flex;
            justify-content: center;
        }
        .brand-logo {
            width: min(240px, 90%);
            max-height: 72px;
            height: auto;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.25);
            background: #ffffff;
        }
        .brand-logo-fallback {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            border-radius: 999px;
            font-size: 24px;
            font-weight: 700;
            color: #14532d;
            background: rgba(34, 197, 94, 0.18);
            border: 1px solid rgba(34, 197, 94, 0.45);
        }
        .status-block {
            background: {{ $palette['surface'] }};
            border: 1px solid rgba(148, 163, 184, 0.24);
            border-radius: 18px;
            padding: 16px;
            margin-bottom: 18px;
            text-align: center;
        }
        .status-content {
            min-width: 0;
        }
        h1 {
            margin: 0 0 10px 0;
            font-size: 29px;
            line-height: 1.2;
            color: {{ $palette['title'] }};
        }
        .description {
            margin: 0;
            font-size: 16px;
            line-height: 1.6;
            color: {{ $palette['text'] }};
            text-wrap: balance;
        }
        .foot-note {
            margin: 0;
            border-top: 1px solid rgba(148, 163, 184, 0.22);
            padding-top: 14px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            text-align: center;
        }
        @media (max-width: 560px) {
            body {
                padding: 16px;
            }
            .card {
                padding: 22px;
                border-radius: 20px;
            }
            .card-head {
                margin-bottom: 16px;
            }
            .status-block {
                padding: 14px;
                margin-bottom: 16px;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <main class="viewport">
        <section class="card" role="status" aria-live="polite">
            <header class="card-head">
                <div class="brand">
                    @if ($logoUrl !== '')
                        <img class="brand-logo" src="{{ $logoUrl }}" alt="{{ $appName }} Logo">
                    @else
                        <span class="brand-logo brand-logo-fallback">{{ strtoupper(substr($appName, 0, 1)) }}</span>
                    @endif
                </div>
            </header>

            <div class="status-block">
                <div class="status-content">
                    <h1>{{ $title }}</h1>
                    <p class="description">{{ $description }}</p>
                </div>
            </div>

            <p class="foot-note">Anda dapat menutup halaman ini dan kembali ke aplikasi mobile.</p>
        </section>
    </main>
</body>
</html>
