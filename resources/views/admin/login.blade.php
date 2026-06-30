<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — June Shop</title>
    @if (file_exists(public_path('build/manifest.json')))
        @php $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true); @endphp
        <link rel="stylesheet" href="{{ asset('build/' . ($manifest['resources/css/app.css']['file'] ?? '')) }}">
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fdf2f6;
            position: relative;
            overflow: hidden;
        }
        /* Background blobs */
        .bg-blob {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(80px);
        }
        .blob--1 {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(168, 213, 186, 0.25), transparent 70%);
            top: -200px; left: -100px;
            animation: blobFloat 12s ease-in-out infinite;
        }
        .blob--2 {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(240, 200, 180, 0.2), transparent 70%);
            bottom: -150px; right: -80px;
            animation: blobFloat 15s ease-in-out infinite 2s;
        }
        .blob--3 {
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(200, 225, 240, 0.15), transparent 70%);
            top: 40%; left: 30%;
            animation: blobFloat 10s ease-in-out infinite 4s;
        }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.05); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
        }
        .bg-glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            filter: blur(120px);
        }
        .glow--tl {
            width: 300px; height: 300px;
            background: rgba(168, 213, 186, 0.15);
            top: 10%; left: 15%;
            animation: glowPulse 6s ease-in-out infinite;
        }
        .glow--br {
            width: 250px; height: 250px;
            background: rgba(230, 200, 180, 0.12);
            bottom: 20%; right: 10%;
            animation: glowPulse 8s ease-in-out infinite 2s;
        }
        @keyframes glowPulse {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.2); }
        }
        /* Botanical decorations */
        .botanical {
            position: absolute;
            pointer-events: none;
            opacity: 0.5;
            z-index: 1;
        }
        .botanical--leaf-1 {
            top: 10%; left: 5%;
            width: 80px;
            animation: sway 8s ease-in-out infinite;
        }
        .botanical--leaf-2 {
            bottom: 18%; left: 8%;
            width: 60px;
            animation: sway 10s ease-in-out infinite 2s;
        }
        .botanical--flower-1 {
            top: 22%; right: 35%;
            width: 50px;
            animation: sway 7s ease-in-out infinite 1s;
        }
        .botanical--flower-2 {
            bottom: 25%; right: 5%;
            width: 45px;
            animation: sway 9s ease-in-out infinite 3s;
        }
        @keyframes sway {
            0%, 100% { transform: rotate(-2deg) translateY(0); }
            50% { transform: rotate(2deg) translateY(-5px); }
        }
        /* Card */
        .auth-container {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.06),
                0 8px 24px rgba(168, 213, 186, 0.08),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            padding: 40px 36px 32px;
        }
        /* Logo */
        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .logo-circle {
            position: relative;
            width: 72px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-circle svg:first-child {
            position: absolute;
            inset: 0;
        }
        /* Title */
        .auth-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 26px;
            font-weight: 700;
            color: #3d3d3d;
            text-align: center;
            margin: 0 0 6px;
        }
        .auth-subtitle {
            font-size: 14px;
            color: #9a8a8a;
            text-align: center;
            margin: 0 0 28px;
            line-height: 1.5;
        }
        /* Form */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .form-group {
            width: 100%;
        }
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #6a5a5a;
            margin-bottom: 6px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.7);
            border: 1.5px solid rgba(200, 190, 180, 0.2);
            border-radius: 14px;
            padding: 0 14px;
            transition: all 0.25s ease;
        }
        .input-wrapper:focus-within {
            border-color: rgba(168, 213, 186, 0.5);
            box-shadow: 0 0 0 4px rgba(168, 213, 186, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        .input-icon {
            color: #b8a8a8;
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            transition: color 0.25s ease;
        }
        .input-wrapper:focus-within .input-icon {
            color: #8ab894;
        }
        .form-input {
            flex: 1;
            padding: 14px 10px;
            border: none;
            background: transparent;
            font-size: 14px;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #3d3d3d;
            outline: none;
            min-width: 0;
        }
        .form-input::placeholder { color: #c0b0b0; font-weight: 400; }
        .form-input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px rgba(255, 255, 255, 0.7) inset !important;
            -webkit-text-fill-color: #3d3d3d !important;
        }
        /* Error */
        .error-alert {
            background: rgba(232, 96, 112, 0.08);
            border: 1px solid rgba(232, 96, 112, 0.2);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #c2415b;
            font-size: 13px;
            font-weight: 500;
        }
        /* Button */
        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            border: none;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            color: #fff;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 4px;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        /* Footer */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: #9a8a8a;
        }
        .auth-footer a {
            color: #8ab894;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .auth-footer a:hover { color: #6a9a74; }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: #c0b0b0;
            font-size: 12px;
        }
        .auth-divider-line {
            flex: 1;
            height: 1px;
            background: rgba(200, 190, 180, 0.3);
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #9a8a8a;
            text-decoration: none;
            margin-bottom: 16px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: #6a9a74; }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="bg-blob blob--1"></div>
    <div class="bg-blob blob--2"></div>
    <div class="bg-blob blob--3"></div>
    <div class="bg-glow glow--tl"></div>
    <div class="bg-glow glow--br"></div>

    <!-- Botanical decorations -->
    <div class="botanical botanical--leaf-1">
        <svg viewBox="0 0 60 80" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M30 80C30 80 5 55 5 30C5 13.4 18.4 0 35 0C42 0 48 2.5 52 7C56 11.5 58 17.5 58 24C58 38 45 50 35 55" stroke="#a8d5ba" stroke-width="1.5" fill="none" opacity="0.6"/>
            <path d="M30 80C30 80 55 55 55 30C55 13.4 41.6 0 25 0C18 0 12 2.5 8 7C4 11.5 2 17.5 2 24C2 38 15 50 25 55" stroke="#c5e0c9" stroke-width="1" fill="none" opacity="0.4"/>
        </svg>
    </div>
    <div class="botanical botanical--leaf-2">
        <svg viewBox="0 0 50 70" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="25" cy="35" rx="22" ry="32" fill="rgba(200, 230, 200, 0.15)" stroke="#b8d8be" stroke-width="1"/>
            <path d="M25 10C25 10 15 22 15 35C15 45 20 55 25 60" stroke="#a8d5ba" stroke-width="1.2" fill="none" opacity="0.5"/>
            <path d="M25 10C25 10 35 22 35 35C35 45 30 55 25 60" stroke="#c5e0c9" stroke-width="0.8" fill="none" opacity="0.4"/>
        </svg>
    </div>
    <div class="botanical botanical--flower-1">
        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="20" cy="20" r="8" fill="#f5d5d5" opacity="0.3"/>
            <circle cx="20" cy="12" r="8" fill="#fce8e8" opacity="0.3"/>
            <circle cx="28" cy="20" r="8" fill="#fce8e8" opacity="0.3"/>
            <circle cx="12" cy="20" r="8" fill="#fce8e8" opacity="0.3"/>
            <circle cx="20" cy="28" r="8" fill="#fce8e8" opacity="0.3"/>
            <circle cx="20" cy="20" r="4" fill="#f5e6c8" opacity="0.5"/>
        </svg>
    </div>
    <div class="botanical botanical--flower-2">
        <svg viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="17.5" cy="17.5" r="7" fill="#e8f0e8" opacity="0.25"/>
            <circle cx="17.5" cy="10.5" r="7" fill="#f0f8f0" opacity="0.25"/>
            <circle cx="24.5" cy="17.5" r="7" fill="#f0f8f0" opacity="0.25"/>
            <circle cx="10.5" cy="17.5" r="7" fill="#f0f8f0" opacity="0.25"/>
            <circle cx="17.5" cy="24.5" r="7" fill="#f0f8f0" opacity="0.25"/>
            <circle cx="17.5" cy="17.5" r="3.5" fill="#f7f0d8" opacity="0.5"/>
        </svg>
    </div>

    <!-- Auth Card -->
    <div class="auth-container">
        <div class="auth-card">
            <!-- Back to store link -->
            <a href="/" class="btn-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Back to store
            </a>

            <!-- Logo -->
            <div class="auth-logo">
                <div class="logo-circle">
                    <svg viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="30" cy="30" r="28" stroke="url(#logoGrad)" stroke-width="2" fill="url(#logoBg)"/>
                        <path d="M16 30C16 22.3 22.3 16 30 16C37.7 16 44 22.3 44 30C44 33.5 42.5 36.7 40 39" stroke="url(#logoGrad)" stroke-width="2" stroke-linecap="round" fill="none"/>
                        <path d="M30 22V30L36 34" stroke="url(#logoGrad)" stroke-width="2.5" stroke-linecap="round" fill="none"/>
                        <defs>
                            <linearGradient id="logoGrad" x1="10" y1="10" x2="50" y2="50">
                                <stop offset="0%" stop-color="#667eea"/>
                                <stop offset="100%" stop-color="#764ba2"/>
                            </linearGradient>
                            <radialGradient id="logoBg" cx="0.5" cy="0.5" r="0.5">
                                <stop offset="0%" stop-color="rgba(255,255,255,0.9)"/>
                                <stop offset="100%" stop-color="rgba(255,255,255,0.5)"/>
                            </radialGradient>
                        </defs>
                    </svg>
                    <div style="position:relative;z-index:1;width:28px;height:28px;">
                        <svg viewBox="0 0 24 24" fill="none" width="28" height="28">
                            <path d="M12 2L15 9L22 9L16.5 14L18.5 21L12 17L5.5 21L7.5 14L2 9L9 9L12 2Z" fill="url(#starGrad)"/>
                            <defs>
                                <linearGradient id="starGrad" x1="2" y1="2" x2="22" y2="21">
                                    <stop offset="0%" stop-color="#667eea"/>
                                    <stop offset="100%" stop-color="#764ba2"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Title -->
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to manage your store</p>

            <!-- Errors -->
            @if($errors->any())
                <div class="error-alert" style="margin-bottom: 16px;">
                    <svg viewBox="0 0 20 20" width="16" height="16" fill="none">
                        <circle cx="10" cy="10" r="10" fill="#e86070" opacity="0.15"/>
                        <path d="M10 6V11" stroke="#e86070" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="10" cy="14" r="1" fill="#e86070"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('admin.login.post') }}" class="auth-form">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 20 16" fill="none" width="18" height="18" aria-hidden="true">
                            <rect x="1" y="1" width="18" height="14" rx="3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M1 3L10 10L19 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        </svg>
                        <input class="form-input" id="email" type="email" name="email" value="rina@gmail.com" placeholder="Enter your email" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 18 20" fill="none" width="18" height="18" aria-hidden="true">
                            <rect x="2" y="9" width="14" height="10" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/>
                            <path d="M5 9V5C5 2.8 6.8 1 9 1C11.2 1 13 2.8 13 5V9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/>
                        </svg>
                        <input class="form-input" id="password" type="password" name="password" value="password" placeholder="Enter your password" required>
                    </div>
                </div>
                <button class="btn-submit" type="submit">Sign In</button>
            </form>

            <div class="auth-divider">
                <span class="auth-divider-line"></span>
                <span>Admin Access</span>
                <span class="auth-divider-line"></span>
            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <span>No account?</span>
                <a href="{{ route('admin.register') }}">Create one here</a>
            </div>
        </div>
    </div>
</body>
</html>