<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#0d3b52">
    <title>Villa Amabel - Experiencing a Wonderful Living</title>
    <meta name="description" content="Selamat datang di kawasan hunian terpadu Villa Amabel. Silakan isi buku tamu atau antar paket.">
    <link rel="icon" type="image/jpeg" href="{{ asset('favicon.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;0,800;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root {
            --sky-top: #6fb9e4;
            --sky-mid: #a9d9f0;
            --sky-low: #dceff7;
            --sun: #ffe1ab;
            --teal: #1f6f6a;
            --teal-deep: #14504d;
            --maroon: #9a3324;
            --magenta: #cf2f7f;
            --sand: #e9dcc2;
            --sand-deep: #cbb894;
            --ink: #0e2a31;
            --ink-soft: #3d5a61;
            --cream: #fffaf2;
            --persp: 1000px;
            --ground-top: 60%;
            --font-display: "Open Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            --font-body: "Open Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow: hidden;
            font-family: var(--font-body);
            color: var(--ink);
            background: var(--sky-low);
            -webkit-font-smoothing: antialiased;
        }

        .stage {
            position: relative;
            width: 100%;
            height: 100svh;
            min-height: 540px;
            overflow: hidden;
            perspective: var(--persp);
            perspective-origin: 50% 46%;
            touch-action: pan-y;
            background: linear-gradient(180deg, var(--sky-top) 0%, var(--sky-mid) 45%, var(--sky-low) 100%);
        }

        .scene {
            --px: 0;
            --py: 0;
            --rx: 0deg;
            --ry: 0deg;
            position: absolute;
            inset: 0;
            transform-style: preserve-3d;
            transform: rotateX(var(--rx)) rotateY(var(--ry));
            will-change: transform;
        }

        .layer {
            position: absolute;
            inset: 0;
            transform-style: preserve-3d;
        }

        .layer--sky {
            transform: translate3d(calc(var(--px) * -6px), calc(var(--py) * -4px), -900px) scale(1.9);
        }
        .layer--canopy {
            transform: translate3d(calc(var(--px) * -10px), calc(var(--py) * -6px), -600px) scale(1.6);
        }
        .layer--world {
            transform: translate3d(calc(var(--px) * -16px), calc(var(--py) * -9px), -180px) scale(1.18);
        }
        .layer--ground {
            transform: translate3d(calc(var(--px) * -20px), calc(var(--py) * -10px), -60px) scale(1.08);
        }
        .layer--mascot {
            transform: translate3d(calc(var(--px) * -32px), calc(var(--py) * -16px), 50px) scale(0.96);
        }
        .layer--foreground {
            transform: translate3d(calc(var(--px) * -64px), calc(var(--py) * -30px), 240px) scale(0.74);
            pointer-events: none;
        }

        /* Langit & Matahari */
        .sun {
            position: absolute;
            top: 6%;
            right: 14%;
            width: 34vmin;
            height: 34vmin;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 244, 214, 0.95), rgba(255, 214, 150, 0.35) 45%, transparent 70%);
            filter: blur(2px);
            animation: sunPulse 9s ease-in-out infinite;
        }

        .cloud {
            position: absolute;
            border-radius: 999px;
            background: radial-gradient(ellipse at 50% 60%, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.55) 60%, transparent 75%);
            filter: blur(6px);
            opacity: 0.85;
        }
        .cloud--1 {
            top: 12%;
            left: -18%;
            width: 46%;
            height: 12%;
            animation: drift 64s linear infinite;
        }
        .cloud--2 {
            top: 24%;
            left: -35%;
            width: 34%;
            height: 9%;
            animation: drift 92s linear infinite 6s;
            opacity: 0.6;
        }
        .cloud--3 {
            top: 5%;
            left: -55%;
            width: 28%;
            height: 7%;
            animation: drift 78s linear infinite 18s;
            opacity: 0.5;
        }

        @keyframes drift {
            to { transform: translateX(190vw); }
        }
        @keyframes sunPulse {
            0%, 100% { opacity: 0.85; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.06); }
        }

        /* Kanopi latar */
        .layer--canopy {
            color: rgba(31, 82, 62, 0.28);
        }
        .layer--canopy svg {
            position: absolute;
            left: -4%;
            width: 108%;
            height: 16%;
            top: 40%;
            filter: blur(1.5px);
        }

        /* Background Pemandangan Kawasan Villa Amabel (Memenuhi Full Screen Width & Height) */
        .world {
            position: absolute;
            inset: -4%;
            display: flex;
            justify-content: center;
            align-items: center;
            pointer-events: none;
            z-index: 1;
        }
        .world__img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center bottom;
            filter: saturate(1.06) contrast(1.02);
        }

        /* Bidang tanah semi 3D */
        .ground__plane {
            position: absolute;
            left: -10%;
            right: -10%;
            top: var(--ground-top);
            bottom: -10%;
            background: linear-gradient(180deg, rgba(120, 142, 96, 0.12) 0%, rgba(96, 118, 78, 0.28) 40%, rgba(60, 80, 48, 0.45) 100%);
            pointer-events: none;
        }
        .ground__haze {
            position: absolute;
            left: -5%;
            right: -5%;
            top: calc(var(--ground-top) - 7%);
            height: 14%;
            background: linear-gradient(180deg, rgba(233, 220, 194, 0) 0%, rgba(233, 220, 194, 0.4) 55%, rgba(233, 220, 194, 0.7) 100%);
            filter: blur(7px);
        }

        /* Maskot Abel (Komodo Villa Amabel) */
        .mascot {
            position: absolute;
            left: 78%;
            bottom: 4%;
            transform: translateX(-50%);
            pointer-events: auto;
            z-index: 2;
        }
        .mascot__body {
            position: relative;
            display: block;
            padding: 0;
            border: 0;
            background: none;
            cursor: pointer;
            -webkit-tap-highlight-color: transparent;
            animation: bob 4.6s ease-in-out infinite;
            transition: transform 0.3s cubic-bezier(0.2, 0.9, 0.3, 1.2);
        }
        .mascot__body img {
            display: block;
            height: clamp(240px, 46svh, 500px);
            width: auto;
            filter: drop-shadow(0 20px 24px rgba(20, 50, 40, 0.38));
        }
        .mascot__body:hover {
            transform: translateY(-6px) scale(1.03);
        }
        .mascot__body.is-waving {
            animation: bob 4.6s ease-in-out infinite, wave 0.6s ease-in-out 2;
        }

        @keyframes bob {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(-1deg); }
        }
        @keyframes wave {
            0%, 100% { rotate: 0deg; }
            25% { rotate: 4deg; }
            75% { rotate: -4deg; }
        }

        .mascot__shadow {
            position: absolute;
            left: 50%;
            bottom: -16px;
            width: 75%;
            height: 22px;
            transform: translateX(-50%);
            border-radius: 50%;
            background: radial-gradient(ellipse, rgba(28, 44, 26, 0.55), rgba(28, 44, 26, 0.18) 55%, transparent 72%);
            filter: blur(5px);
            animation: shadowPulse 4.6s ease-in-out infinite;
        }
        @keyframes shadowPulse {
            0%, 100% { transform: translateX(-50%) scale(1); opacity: 0.75; }
            50% { transform: translateX(-50%) scale(0.88); opacity: 0.55; }
        }

        /* Latar depan blur & efek partikel */
        .leaf {
            position: absolute;
            bottom: -8%;
            width: 46%;
            height: 52%;
            background: radial-gradient(ellipse at 30% 70%, rgba(24, 66, 44, 0.85), rgba(24, 66, 44, 0) 62%);
            filter: blur(14px);
            opacity: 0.7;
        }
        .leaf--left {
            left: -14%;
            animation: sway 11s ease-in-out infinite;
        }
        .leaf--right {
            right: -14%;
            transform: scaleX(-1);
            animation: sway 13s ease-in-out infinite reverse;
        }
        @keyframes sway {
            0%, 100% { translate: 0 0; }
            50% { translate: 10px -8px; }
        }

        .spark {
            position: absolute;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 248, 220, 0.95), rgba(255, 226, 150, 0));
            filter: blur(1px);
        }
        .spark--1 { left: 18%; bottom: 30%; animation: float 12s ease-in-out infinite; }
        .spark--2 { left: 72%; bottom: 44%; animation: float 15s ease-in-out infinite 2s; }
        .spark--3 { left: 48%; bottom: 22%; animation: float 18s ease-in-out infinite 5s; }
        @keyframes float {
            0%, 100% { transform: translate(0, 0); opacity: 0.35; }
            50% { transform: translate(18px, -34px); opacity: 0.9; }
        }

        /* HUD & Konten Teks */
        .hud {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            padding: max(16px, env(safe-area-inset-top)) 20px calc(18px + env(safe-area-inset-bottom));
            pointer-events: none;
            z-index: 10;
        }
        .hud > * {
            pointer-events: auto;
        }
        .hud::before {
            content: "";
            position: absolute;
            inset: auto 0 0 0;
            height: 70%;
            z-index: -1;
            pointer-events: none;
            background: linear-gradient(
                to top,
                rgba(252, 247, 238, 0.95) 0%,
                rgba(252, 247, 238, 0.75) 30%,
                rgba(252, 247, 238, 0.35) 55%,
                rgba(252, 247, 238, 0) 80%
            );
        }

        .header-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px 8px 10px;
            border-radius: 999px;
            background: rgba(255, 250, 242, 0.85);
            border: 1px solid rgba(31, 111, 106, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 6px 18px rgba(9, 40, 45, 0.12);
            text-decoration: none;
        }
        .brand__mark {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--teal);
            color: var(--cream);
        }
        .brand__text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        .brand__text strong {
            font-size: 15px;
            font-weight: 700;
            color: var(--teal-deep);
        }
        .brand__text small {
            font-size: 9.5px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-soft);
        }

        .portal-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(31, 111, 106, 0.25);
            font-size: 12px;
            font-weight: 700;
            color: var(--teal-deep);
            text-decoration: none;
            backdrop-filter: blur(8px);
            box-shadow: 0 4px 12px rgba(9, 40, 45, 0.08);
            transition: transform 0.2s, background 0.2s;
        }
        .portal-btn:hover {
            background: #fff;
            transform: translateY(-1px);
        }

        .copy {
            margin-top: auto;
            max-width: 560px;
            text-shadow: 0 1px 14px rgba(255, 250, 242, 0.55);
            pointer-events: none;
        }
        .copy > * {
            pointer-events: auto;
        }

        .copy__eyebrow {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--maroon);
        }

        .copy__title {
            margin: 0 0 10px;
            font-weight: 800;
            font-size: clamp(26px, 6.5vw, 46px);
            line-height: 1.15;
            letter-spacing: -0.015em;
            color: var(--ink);
        }
        .copy__title em {
            font-style: italic;
            color: var(--teal);
        }

        .copy__lead {
            margin: 0 0 18px;
            font-size: clamp(13.5px, 3.4vw, 15.5px);
            line-height: 1.55;
            color: var(--ink-soft);
            max-width: 44ch;
        }

        /* Action Grid */
        .action-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
            max-width: 480px;
        }

        .btn-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 14px;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(9, 40, 45, 0.1);
        }
        .btn-card--visitor {
            background: linear-gradient(145deg, #0e2a31, #18424a);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }
        .btn-card--visitor:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(9, 40, 45, 0.25);
        }
        .btn-card--package {
            background: rgba(255, 255, 255, 0.95);
            color: var(--ink);
            border: 1px solid rgba(31, 111, 106, 0.2);
        }
        .btn-card--package:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(9, 40, 45, 0.18);
        }
        .btn-card__icon {
            font-size: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            flex-shrink: 0;
        }
        .btn-card--package .btn-card__icon {
            background: rgba(31, 111, 106, 0.1);
            color: var(--teal);
        }
        .btn-card__content {
            display: flex;
            flex-direction: column;
        }
        .btn-card__title {
            font-size: 13.5px;
            font-weight: 700;
        }
        .btn-card__desc {
            font-size: 11px;
            opacity: 0.8;
        }

        .gyro-chip {
            display: inline-block;
            margin-top: 4px;
            padding: 6px 12px;
            border-radius: 999px;
            border: 1px solid rgba(31, 111, 106, 0.3);
            background: rgba(255, 250, 242, 0.85);
            color: var(--teal-deep);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(8px);
        }

        /* Responsif */
        @media (max-width: 640px) {
            .stage { --ground-top: 50%; }
            .hud { padding: max(14px, env(safe-area-inset-top)) 18px max(18px, env(safe-area-inset-bottom)); }
            .hud::before { 
                height: 44%; 
                background: linear-gradient(to top, rgba(252, 247, 238, 0.98) 0%, rgba(252, 247, 238, 0.92) 40%, rgba(252, 247, 238, 0.1) 75%, transparent 100%);
            }
            .world { 
                inset: auto;
                top: auto; 
                bottom: 35%; 
                left: 50%; 
                width: 180%; 
                max-width: 160vw;
                transform: translateX(-50%); 
                display: flex;
                justify-content: center;
                align-items: flex-end;
                z-index: 2;
            }
            .world__img { 
                width: 100%;
                max-width: 160vw;
                height: auto;
                max-height: clamp(190px, 34svh, 290px); 
                object-fit: contain;
                object-position: center bottom;
                filter: drop-shadow(0 14px 20px rgba(18, 48, 38, 0.26)) saturate(1.06);
            }
            .mascot { 
                top: auto; 
                bottom: 31%; 
                left: auto; 
                right: 2%; 
                transform: none; 
                z-index: 5;
            }
            .mascot__body img { 
                height: clamp(140px, 24svh, 200px); 
            }
            .copy { 
                margin-bottom: 0; 
                position: relative;
                z-index: 6;
            }
            .action-group { 
                grid-template-columns: 1fr; 
                position: relative;
                z-index: 6;
            }
        }

        @media (min-width: 900px) {
            .stage { --ground-top: 78%; }
            .hud { padding: max(24px, env(safe-area-inset-top)) 40px 40px; }
            .hud::before {
                height: 100%;
                background: linear-gradient(to right, rgba(252, 247, 238, 0.94) 0%, rgba(252, 247, 238, 0.62) 28%, rgba(252, 247, 238, 0.12) 46%, transparent 62%),
                            linear-gradient(to top, rgba(252, 247, 238, 0.85) 0%, rgba(252, 247, 238, 0.3) 30%, transparent 50%);
            }
            .copy { margin-top: auto; margin-bottom: 3vh; max-width: min(46%, 540px); }
            .world { 
                inset: auto;
                left: 50%; 
                bottom: 4%; 
                top: auto;
                width: 100vw;
                transform: translateX(-50%); 
                display: flex;
                justify-content: center;
                align-items: flex-end;
            }
            .world__img { 
                width: 100%;
                max-width: 100vw;
                height: auto;
                max-height: clamp(340px, 78svh, 860px); 
                object-fit: contain;
                object-position: center bottom;
                filter: drop-shadow(0 18px 24px rgba(18, 48, 38, 0.22)) saturate(1.06);
            }
            .mascot { 
                left: 82%; 
                bottom: 3%; 
                transform: translateX(-50%); 
            }
            .mascot__body img { 
                height: clamp(280px, 52svh, 540px); 
            }
        }
    </style>
</head>
<body>
    <main class="stage" id="stage">
        <div class="scene" id="scene">
            <!-- 1. Lapisan langit & matahari -->
            <div class="layer layer--sky" aria-hidden="true">
                <span class="sun"></span>
                <span class="cloud cloud--1"></span>
                <span class="cloud cloud--2"></span>
                <span class="cloud cloud--3"></span>
            </div>

            <!-- 2. Siluet pepohonan kanopi -->
            <div class="layer layer--canopy" aria-hidden="true">
                <svg viewBox="0 0 1200 220" preserveAspectRatio="none">
                    <path d="M0,220 L0,150 C60,120 90,160 140,132 C190,104 230,140 285,118 C340,96 380,138 440,120 C500,102 545,142 600,124 C655,106 700,146 760,126 C820,106 860,148 915,128 C970,108 1015,150 1070,130 C1125,110 1160,152 1200,138 L1200,220 Z" fill="currentColor" />
                </svg>
            </div>

            <!-- 3. Foto Kawasan Villa Amabel Background Memenuhi Lebar Layar -->
            <div class="layer layer--world">
                <div class="world">
                    <img class="world__img" src="{{ asset('assets/villa-amabel.png') }}" alt="Gerbang dan kawasan Villa Amabel" loading="eager" decoding="async">
                </div>
            </div>

            <!-- 4. Bidang tanah semi-3D -->
            <div class="layer layer--ground" aria-hidden="true">
                <div class="ground__plane"></div>
                <div class="ground__haze"></div>
            </div>

            <!-- 5. Objek 3D Maskot Abel (Komodo Villa Amabel) -->
            <div class="layer layer--mascot">
                <div class="mascot">
                    <button type="button" class="mascot__body" id="mascotBody" aria-label="Sapa Abel, maskot Villa Amabel">
                        <img src="{{ asset('assets/maskot-cut.png') }}" alt="Abel, maskot Villa Amabel" loading="eager">
                    </button>
                    <span class="mascot__shadow" aria-hidden="true"></span>
                </div>
            </div>

            <!-- 6. Dedaunan foreground blur & spark -->
            <div class="layer layer--foreground" aria-hidden="true">
                <span class="leaf leaf--left"></span>
                <span class="leaf leaf--right"></span>
                <span class="spark spark--1"></span>
                <span class="spark spark--2"></span>
                <span class="spark spark--3"></span>
            </div>
        </div>

        <!-- HUD: Brand, Copy & Tombol Aksi -->
        <div class="hud">
            <header class="header-bar">
                <a href="{{ url('/') }}" class="brand">
                    <span class="brand__mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18">
                            <path d="M12 3 3 10.5V21h6.4v-5.4h5.2V21H21V10.5z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="brand__text">
                        <strong>Villa Amabel</strong>
                        <small>Experiencing a Wonderful Living</small>
                    </span>
                </a>

                <a href="{{ url('/internal') }}" class="portal-btn">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Portal Petugas</span>
                </a>
            </header>

            <section class="copy">
                <p class="copy__eyebrow">Selamat Datang di Perumahan Kami,</p>
                <h1 class="copy__title">
                    Villa Amabel - <em>Berteman</em>
                </h1>
                <p class="copy__lead">
                    <strong style="color: var(--teal-deep); font-weight: 700; font-size: 1.05em;">Bersih, Tentram, Aman, Nyaman</strong>
                </p>

                <div class="action-group">
                    <a href="{{ route('visitors.create') }}" class="btn-card btn-card--visitor">
                        <span class="btn-card__icon" aria-hidden="true">
                            <i class="fa-solid fa-users"></i>
                        </span>
                        <span class="btn-card__content">
                            <span class="btn-card__title">Saya Tamu</span>
                            <span class="btn-card__desc">Isi buku tamu kunjungan</span>
                        </span>
                    </a>

                    <a href="{{ route('packages.create') }}" class="btn-card btn-card--package">
                        <span class="btn-card__icon" aria-hidden="true">
                            <i class="fa-solid fa-boxes-packing"></i>
                        </span>
                        <span class="btn-card__content">
                            <span class="btn-card__title">Saya Antar Paket</span>
                            <span class="btn-card__desc">Titip paket di kotak kurir</span>
                        </span>
                    </a>
                </div>

                <button type="button" class="gyro-chip" id="gyroBtn" style="display: none;">
                    Aktifkan Gerak 3D Giroskop
                </button>
            </section>
        </div>
    </main>

    <script>
        (function() {
            const stage = document.getElementById('stage');
            const scene = document.getElementById('scene');
            const mascot = document.getElementById('mascotBody');
            const gyroBtn = document.getElementById('gyroBtn');

            if (!stage || !scene) return;

            const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
            const lerp = (a, b, t) => a + (b - a) * t;

            let targetX = 0;
            let targetY = 0;
            let currentX = 0;
            let currentY = 0;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const setFromPoint = (clientX, clientY) => {
                const r = stage.getBoundingClientRect();
                targetX = clamp(((clientX - r.left) / r.width - 0.5) * 2, -1, 1);
                targetY = clamp(((clientY - r.top) / r.height - 0.5) * 2, -1, 1);
            };

            stage.addEventListener('pointermove', (e) => {
                if (e.pointerType === 'touch') return;
                setFromPoint(e.clientX, e.clientY);
            }, { passive: true });

            stage.addEventListener('touchmove', (e) => {
                const t = e.touches[0];
                if (t) setFromPoint(t.clientX, t.clientY);
            }, { passive: true });

            stage.addEventListener('pointerleave', () => {
                targetX = 0;
                targetY = 0;
            });

            // Parallax Animation Frame Loop
            function tick() {
                const ease = reducedMotion ? 1 : 0.075;
                currentX = lerp(currentX, targetX, ease);
                currentY = lerp(currentY, targetY, ease);

                const x = reducedMotion ? 0 : currentX;
                const y = reducedMotion ? 0 : currentY;

                scene.style.setProperty('--px', x.toFixed(4));
                scene.style.setProperty('--py', y.toFixed(4));
                scene.style.setProperty('--ry', (x * 5).toFixed(3) + 'deg');
                scene.style.setProperty('--rx', (-y * 3.2).toFixed(3) + 'deg');

                requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);

            // Mascot waving on interaction (No speech bubble)
            if (mascot) {
                let waveTimer = null;
                mascot.addEventListener('click', () => {
                    mascot.classList.add('is-waving');
                    if (waveTimer) clearTimeout(waveTimer);
                    waveTimer = setTimeout(() => {
                        mascot.classList.remove('is-waving');
                    }, 1200);
                });
            }

            // Gyroscope / DeviceOrientation Support
            const handleOrientation = (e) => {
                if (e.gamma == null || e.beta == null) return;
                const landscape = Math.abs(window.orientation ?? 0) === 90;
                const g = landscape ? e.beta : e.gamma;
                const b = landscape ? -e.gamma : e.beta;
                targetX = clamp(g / 32, -1, 1);
                targetY = clamp((b - 45) / 32, -1, 1);
            };

            const DOE = window.DeviceOrientationEvent;
            const isTouch = window.matchMedia('(hover: none)').matches;
            if (DOE && isTouch) {
                if (typeof DOE.requestPermission === 'function') {
                    gyroBtn.style.display = 'inline-block';
                    gyroBtn.addEventListener('click', async () => {
                        try {
                            const res = await DOE.requestPermission();
                            if (res === 'granted') {
                                window.addEventListener('deviceorientation', handleOrientation, { passive: true });
                                gyroBtn.style.display = 'none';
                            }
                        } catch (err) {
                            gyroBtn.style.display = 'none';
                        }
                    });
                } else {
                    window.addEventListener('deviceorientation', handleOrientation, { passive: true });
                }
            }
        })();
    </script>
</body>
</html>
