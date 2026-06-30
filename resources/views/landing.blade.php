@php
    $galleryImages = [
        'https://images.pexels.com/photos/143133/pexels-photo-143133.jpeg?auto=compress&cs=tinysrgb&w=1400',
        'https://images.pexels.com/photos/1300972/pexels-photo-1300972.jpeg?auto=compress&cs=tinysrgb&w=1400',
        'https://images.pexels.com/photos/1656666/pexels-photo-1656666.jpeg?auto=compress&cs=tinysrgb&w=1400',
        'https://images.pexels.com/photos/2252584/pexels-photo-2252584.jpeg?auto=compress&cs=tinysrgb&w=1400',
        'https://images.pexels.com/photos/2382325/pexels-photo-2382325.jpeg?auto=compress&cs=tinysrgb&w=1400',
    ];

    $galleryItems = __('landing.gallery.items');

    $appScreens = [
        'https://res.cloudinary.com/ii8qzx20/image/upload/v1782828986/landingpage.png',
        'https://res.cloudinary.com/ii8qzx20/image/upload/v1782828986/produk.png',
        'https://res.cloudinary.com/ii8qzx20/image/upload/v1782828985/checkout.png',
    ];

    $previewItems = __('landing.preview.items');
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('landing.meta.title') }}</title>
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <link rel="icon" href="{{ asset('ic_segreens.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('ic_segreens.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Manrope", sans-serif;
            color: #1a1a1a;
            background: #ffffff;
            -webkit-font-smoothing: antialiased;
        }

        .heading {
            font-family: "Syne", sans-serif;
            letter-spacing: -0.02em;
        }

        .container {
            max-width: 1120px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── HERO ── */
        .hero {
            padding: 48px 0 64px;
            background: #ffffff;
            overflow: hidden;
        }

        .hero .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 40px;
        }

        .hero-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }

        .hero-brand img {
            width: 56px;
            height: 56px;
            border-radius: 16px;
        }

        .hero-brand span {
            font-size: 20px;
            font-weight: 700;
            color: #14532d;
        }

        .hero h1 {
            font-size: clamp(2.6rem, 5vw, 3.8rem);
            font-weight: 800;
            line-height: 1.05;
            color: #14532d;
        }

        .hero-subtitle {
            margin-top: 16px;
            font-size: 1rem;
            line-height: 1.6;
            color: #475569;
            max-width: 400px;
        }

        .hero-buttons {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 14px;
            background: #15803d;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-primary:hover { background: #14532d; }

        .btn-outline {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .btn-outline:hover { background: #f8fafc; }

        /* ── PHONES ── */
        .phones-group {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 14px;
        }

        .phone {
            border-radius: 28px;
            background: linear-gradient(145deg, #0f172a, #1e293b);
            padding: 6px;
            box-shadow: 0 20px 50px rgba(15,23,42,.22);
            flex-shrink: 0;
        }

        .phone.lg { width: 185px; }
        .phone.md { width: 155px; }
        .phone.sm { width: 140px; }

        .phone-notch {
            width: 36%;
            height: 10px;
            margin: 0 auto 6px;
            border-radius: 999px;
            background: #111827;
        }

        .phone-screen {
            border-radius: 20px;
            overflow: hidden;
            background: #fff;
        }

        .phone-screen img {
            width: 100%;
            display: block;
        }

        /* ── FEATURE SECTIONS ── */
        .feature {
            padding: 72px 0;
        }

        .feature .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            align-items: center;
            gap: 48px;
        }

        .feature.green-bg {
            background: #f0fdf4;
        }

        .feature h2 {
            font-size: clamp(1.5rem, 3vw, 2.1rem);
            font-weight: 800;
            line-height: 1.2;
            color: #14532d;
        }

        .feature-desc {
            margin-top: 14px;
            font-size: 0.92rem;
            line-height: 1.7;
            color: #475569;
            max-width: 440px;
        }

        /* ── GALLERY MARQUEE ── */
        .gallery {
            padding: 64px 0;
        }

        .gallery h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #14532d;
            margin-bottom: 28px;
        }

        .gallery-strip {
            overflow: hidden;
            position: relative;
        }

        .gallery-track {
            display: flex;
            width: max-content;
            gap: 20px;
            animation: marquee 38s linear infinite;
            will-change: transform;
        }

        .gallery-strip:hover .gallery-track {
            animation-play-state: paused;
        }

        .gallery-card {
            width: 280px;
            flex: 0 0 auto;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #fff;
            box-shadow: 0 18px 48px rgba(15,23,42,.1);
        }

        .gallery-card-img {
            height: 220px;
            overflow: hidden;
        }

        .gallery-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .gallery-card:hover .gallery-card-img img {
            transform: scale(1.05);
        }

        .gallery-card-body {
            padding: 16px;
        }

        .gallery-card-body h3 {
            font-size: 18px;
            font-weight: 800;
            color: #14532d;
        }

        .gallery-card-body p {
            margin-top: 4px;
            font-size: 13px;
            color: #64748b;
        }

        @keyframes marquee {
            from { transform: translateX(0); }
            to { transform: translateX(calc(-50% - 10px)); }
        }

        /* ── APP PREVIEW ── */
        .preview {
            padding: 64px 0;
        }

        .preview h2 {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: #14532d;
            margin-bottom: 28px;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .preview-label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 12px;
        }

        /* ── CTA ── */
        .cta {
            margin: 0 24px 48px;
            border-radius: 28px;
            background: #14532d;
            padding: 56px 32px;
            text-align: center;
            color: #fff;
            box-shadow: 0 34px 80px rgba(21,128,61,.2);
        }

        .cta-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.22em;
            color: #bbf7d0;
        }

        .cta h3 {
            margin-top: 16px;
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 800;
            line-height: 1.1;
        }

        .cta-desc {
            margin: 16px auto 0;
            max-width: 560px;
            font-size: 0.9rem;
            line-height: 1.7;
            color: #bbf7d0;
        }

        .cta .btn-white {
            display: inline-flex;
            margin-top: 24px;
            padding: 12px 24px;
            border-radius: 14px;
            background: #fff;
            color: #14532d;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .cta .btn-white:hover { background: #f0fdf4; }

        /* ── FOOTER ── */
        .footer {
            padding: 28px 24px 36px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero-subtitle { margin-left: auto; margin-right: auto; }
            .hero-brand { justify-content: center; }
            .hero-buttons { justify-content: center; }
            .phones-group { margin-top: 32px; }
            .phone.lg { width: 145px; }
            .phone.md { width: 120px; }
            .phone.sm { width: 110px; }

            .feature .container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .feature-desc { margin-left: auto; margin-right: auto; }
            .phones-group { margin-top: 24px; }

            .feature .container.reverse > .phones-group { order: -1; }

            .preview-grid {
                grid-template-columns: 1fr;
                max-width: 220px;
                margin: 0 auto;
            }

            .gallery-track { animation-duration: 26s; }

            .cta { margin: 0 16px 32px; padding: 40px 20px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .gallery-strip { overflow-x: auto; }
            .gallery-track { animation: none; }
        }
    </style>
</head>
<body>

    {{-- ═══════ HERO ═══════ --}}
    <section class="hero">
        <div class="container">
            <div>
                <div class="hero-brand">
                    <img src="{{ asset('ic_segreens.png') }}" alt="SEGreens Logo">
                    <span class="heading">SEGreens</span>
                </div>
                <h1 class="heading">{{ __('landing.hero.title') }}</h1>
                <p class="hero-subtitle">{{ __('landing.hero.subtitle') }}</p>
                <div class="hero-buttons">
                    <a href="#galeri" class="btn-primary">{{ __('landing.buttons.view_products') }}</a>
                    <a href="#preview" class="btn-outline">{{ __('landing.buttons.view_app') }}</a>
                </div>
            </div>

            <div class="phones-group">
                <div class="phone md" style="transform: translateY(18px);">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[1] }}" alt="Product screen">
                    </div>
                </div>
                <div class="phone lg">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[0] }}" alt="Home screen">
                    </div>
                </div>
                <div class="phone md" style="transform: translateY(28px);">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[2] }}" alt="Order screen">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════ FEATURE — Variety ═══════ --}}
    <section class="feature green-bg">
        <div class="container">
            <div class="phones-group">
                <div class="phone sm" style="transform: translateY(14px);">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[2] }}" alt="Order screen">
                    </div>
                </div>
                <div class="phone md">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[1] }}" alt="Product screen">
                    </div>
                </div>
            </div>

            <div>
                <h2 class="heading">{{ __('landing.feature_variety.title') }}</h2>
                <p class="feature-desc">{{ __('landing.feature_variety.description') }}</p>
            </div>
        </div>
    </section>

    {{-- ═══════ FEATURE — Delivery ═══════ --}}
    <section class="feature">
        <div class="container reverse">
            <div>
                <h2 class="heading">{{ __('landing.feature_delivery.title') }}</h2>
                <p class="feature-desc">{{ __('landing.feature_delivery.description') }}</p>
            </div>

            <div class="phones-group">
                <div class="phone md">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[0] }}" alt="Home screen">
                    </div>
                </div>
                <div class="phone sm" style="transform: translateY(18px);">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ $appScreens[2] }}" alt="Order screen">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════ GALLERY ═══════ --}}
    <section id="galeri" class="gallery">
        <div class="container">
            <h2 class="heading">{{ __('landing.gallery.heading') }}</h2>
        </div>

        <div class="gallery-strip">
            <div class="gallery-track">
                @for ($round = 0; $round < 2; $round++)
                    @foreach ($galleryItems as $i => $card)
                        <article class="gallery-card">
                            <div class="gallery-card-img">
                                <img src="{{ $galleryImages[$i] }}" alt="{{ $card['title'] }}">
                            </div>
                            <div class="gallery-card-body">
                                <h3 class="heading">{{ $card['title'] }}</h3>
                                <p>{{ $card['subtitle'] }}</p>
                            </div>
                        </article>
                    @endforeach
                @endfor
            </div>
        </div>
    </section>

    {{-- ═══════ APP PREVIEW ═══════ --}}
    <section id="preview" class="preview">
        <div class="container">
            <h2 class="heading">{{ __('landing.preview.heading') }}</h2>

            <div class="preview-grid">
                @foreach ($previewItems as $i => $item)
                    <article>
                        <p class="preview-label">{{ $item['title'] }}</p>
                        <div class="phone lg" style="width: 100%;">
                            <div class="phone-notch"></div>
                            <div class="phone-screen">
                                <img src="{{ $appScreens[$i] }}" alt="{{ $item['title'] }}">
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════ CTA ═══════ --}}
    <section class="cta">
        <p class="cta-label">{{ __('landing.cta.label') }}</p>
        <h3 class="heading">{{ __('landing.cta.title') }}</h3>
        <p class="cta-desc">{{ __('landing.cta.description') }}</p>
        <a href="#preview" class="btn-white">{{ __('landing.cta.button') }}</a>
    </section>

    {{-- ═══════ FOOTER ═══════ --}}
    <footer class="footer">
        {{ __('landing.footer.copyright', ['year' => date('Y')]) }}
    </footer>

</body>
</html>
