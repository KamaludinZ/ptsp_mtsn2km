<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ config('app.description', 'Pelayanan Terpadu Satu Pintu ' . config('app.name_full', 'MTsN 2 Kota Malang') . ' - Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014') }}">
    <meta name="keywords" content="PTSP, {{ config('app.name_full', 'MTsN 2 Kota Malang') }}, Pelayanan, Permen PANRB, Layanan Digital, Satu Pintu">
    <meta name="author" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">
    <meta name="robots" content="index, follow">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }} - Pelayanan Terpadu Satu Pintu">
    <meta property="og:description" content="{{ config('app.description', 'Layanan cepat, transparan, dan akuntabel sesuai Permen PANRB 15/2014') }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('images/ptsp-social.jpg') }}">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}">
    <meta name="twitter:description" content="{{ config('app.description', 'Pelayanan Terpadu Satu Pintu - Cepat, Transparan, Akuntabel') }}">
    <meta name="twitter:image" content="{{ asset('images/ptsp-social.jpg') }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url('/') }}">

    <title>{{ config('app.name', 'PTSP MTsN 2 Kota Malang') }}</title>

    <!-- Favicon and PWA -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#14532d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="PTSP MTsN 2">
    <meta name="msapplication-TileColor" content="#14532d">
    <meta name="msapplication-config" content="{{ asset('browserconfig.xml') }}">

    {{-- Vite Assets: Bootstrap, Font Awesome, AOS, Tailwind (Local - No CDN) --}}
    @vite([
        'resources/css/bootstrap-custom.css',
        'resources/js/bootstrap-bundle.js',
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/dark-mode.css'
    ])

    <!-- Custom CSS -->
    <style>
        /* CSS Variables for Theme Support */
        :root {
            /* Light theme colors */
            --bs-primary: #14532d;
            --bs-primary-dark: #052e16;
            --bs-primary-light: #166534;
            --bs-primary-rgb: 20, 83, 45;

            --bs-secondary: #ea580c;
            --bs-secondary-dark: #c2410c;
            --bs-secondary-light: #f97316;
            --bs-secondary-rgb: 234, 88, 12;

            --bs-white: #ffffff;
            --bs-gray-50: #f9fafb;
            --bs-gray-100: #f3f4f6;
            --bs-gray-200: #e5e7eb;
            --bs-gray-300: #d1d5db;
            --bs-gray-400: #9ca3af;
            --bs-gray-500: #6b7280;
            --bs-gray-600: #4b5563;
            --bs-gray-700: #374151;
            --bs-gray-800: #1f2937;
            --bs-gray-900: #111827;

            /* Semantic colors */
            --bs-success: #10b981;
            --bs-warning: #f59e0b;
            --bs-danger: #ef4444;
            --bs-info: #3b82f6;

            /* Accessibility colors */
            --bs-focus: #2563eb;
            --bs-text: #1f2937;
            --bs-bg: #ffffff;
            --bs-surface: #f9fafb;
            --bs-border: #e5e7eb;

            /* Typography */
            --bs-font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;

            /* Shadows */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-2xl: 0 25px 50px -12px rgb(0 0 0 / 0.25);

            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-secondary-dark) 100%);
            --gradient-hero: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-primary-dark) 50%, var(--bs-secondary) 100%);
        }

        /* Dark theme with adaptive color system */
        [data-theme="dark"] {
            /* Dark theme colors */
            --bs-primary: #14532d;
            --bs-primary-dark: #052e16;
            --bs-primary-light: #166534;
            --bs-primary-rgb: 20, 83, 45;

            --bs-secondary: #ea580c;
            --bs-secondary-dark: #c2410c;
            --bs-secondary-light: #f97316;
            --bs-secondary-rgb: 234, 88, 12;

            --bs-white: #1f2937;
            --bs-gray-50: #374151;
            --bs-gray-100: #4b5563;
            --bs-gray-200: #6b7280;
            --bs-gray-300: #9ca3af;
            --bs-gray-400: #d1d5db;
            --bs-gray-500: #e5e7eb;
            --bs-gray-600: #f3f4f6;
            --bs-gray-700: #f9fafb;
            --bs-gray-800: #ffffff;
            --bs-gray-900: #ffffff;

            /* Semantic colors */
            --bs-success: #10b981;
            --bs-warning: #f59e0b;
            --bs-danger: #ef4444;
            --bs-info: #3b82f6;

            /* Accessibility colors */
            --bs-focus: #60a5fa;
            --bs-text: #f9fafb;
            --bs-bg: #111827;
            --bs-surface: #1f2937;
            --bs-border: #374151;

            /* ADAPTIVE COLOR SYSTEM */
            /* Background colors with corresponding text colors */
            --bg-dark-1: #1f2937;      /* Dark surface 1 */
            --text-on-dark-1: #f9fafb; /* Light text on dark 1 */

            --bg-dark-2: #374151;      /* Dark surface 2 */
            --text-on-dark-2: #ffffff; /* Light text on dark 2 */

            --bg-dark-3: #4b5563;      /* Dark surface 3 */
            --text-on-dark-3: #ffffff; /* Light text on dark 3 */

            --bg-light-1: #f3f4f6;     /* Light surface 1 */
            --text-on-light-1: #1f2937; /* Dark text on light 1 */

            --bg-light-2: #e5e7eb;     /* Light surface 2 */
            --text-on-light-2: #111827; /* Dark text on light 2 */

            --bg-light-3: #ffffff;     /* Light surface 3 */
            --text-on-light-3: #000000; /* Dark text on light 3 */

            /* Specific theme colors */
            --theme-primary-bg: #052e16;     /* Primary dark background */
            --theme-primary-text: #86efac;   /* Primary light text */

            --theme-secondary-bg: #7c2d12;   /* Secondary dark background */
            --theme-secondary-text: #fdba74; /* Secondary light text */

            --theme-accent-bg: #1e40af;      /* Accent dark background */
            --theme-accent-text: #93c5fd;    /* Accent light text */

            --theme-success-bg: #064e3b;     /* Success dark background */
            --theme-success-text: #6ee7b7;   /* Success light text */

            --theme-warning-bg: #78350f;     /* Warning dark background */
            --theme-warning-text: #fcd34d;   /* Warning light text */

            --theme-danger-bg: #7f1d1d;      /* Danger dark background */
            --theme-danger-text: #fca5a5;    /* Danger light text */

            --theme-info-bg: #1e3a8a;        /* Info dark background */
            --theme-info-text: #93c5fd;      /* Info light text */
        }

        /* Base styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
        }

        body {
            font-family: var(--bs-font-sans);
            background-color: var(--bs-bg);
            color: var(--bs-text);
            transition: background-color 0.3s ease, color 0.3s ease;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Skip to main content link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--bs-primary);
            color: var(--bs-white);
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
            font-weight: 600;
            font-size: 14px;
            transition: top 0.3s ease;
        }

        .skip-link:focus {
            top: 6px;
        }

        /* Accessibility Sidebar - International WCAG Standards */
        .accessibility-sidebar {
            position: fixed;
            left: -320px;
            top: 50%;
            transform: translateY(-50%);
            width: 320px;
            max-height: 80vh;
            z-index: 1000;
            background: var(--bs-white);
            border: 2px solid var(--bs-border);
            border-radius: 0 16px 16px 0;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .accessibility-sidebar.open {
            left: 0;
        }

        .accessibility-toggle {
            position: fixed !important;
            left: 20px !important;
            top: 100px !important;
            width: 60px !important;
            height: 60px !important;
            background: #ff6b35 !important;
            color: white !important;
            border: 3px solid #ff6b35 !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 9999 !important;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.5) !important;
            font-size: 24px;
        }

        .accessibility-toggle:hover {
            background: #ff5722 !important;
            transform: scale(1.1) !important;
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.7) !important;
        }

        .accessibility-toggle i {
            font-size: 28px !important;
        }

        .accessibility-toggle svg {
            transition: transform 0.3s ease;
        }

        .accessibility-sidebar.open .accessibility-toggle svg {
            transform: rotate(180deg);
        }

        /* Hero Content Fix */
        .hero-content {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 10 !important;
        }

        .hero-title {
            display: block !important;
            visibility: visible !important;
            color: #ffffff !important;
        }

        .hero-description {
            display: block !important;
            visibility: visible !important;
            color: #ffffff !important;
        }

        .btn-hero {
            display: inline-block !important;
            visibility: visible !important;
        }

        .accessibility-header {
            padding: 1.5rem;
            background: var(--gradient-primary);
            color: var(--bs-white);
            border-bottom: 3px solid var(--bs-primary);
        }

        .accessibility-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-header p {
            margin: 0.5rem 0 0 0;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .accessibility-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .accessibility-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--bs-border);
        }

        .accessibility-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .accessibility-section h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--bs-text);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-section h4 i {
            color: var(--bs-primary);
        }

        .accessibility-controls {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
        }

        .accessibility-control {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: var(--bs-gray-50);
            border: 1px solid var(--bs-border);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .accessibility-control:hover {
            background: var(--bs-gray-100);
            border-color: var(--bs-primary);
        }

        .accessibility-control label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bs-text);
            margin: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-control label i {
            color: var(--bs-primary);
            font-size: 1rem;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 48px;
            height: 24px;
            background: var(--bs-gray-300);
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .toggle-switch.active {
            background: var(--bs-primary);
        }

        .toggle-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: var(--bs-white);
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .toggle-switch.active::after {
            transform: translateX(24px);
        }

        /* Font Size Controls */
        .font-size-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .font-size-btn {
            flex: 1;
            padding: 0.5rem;
            background: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--bs-text);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .font-size-btn:hover {
            background: var(--bs-gray-100);
            border-color: var(--bs-primary);
        }

        .font-size-btn.active {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        /* Button Controls */
        .accessibility-btn {
            padding: 0.75rem 1rem;
            background: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bs-text);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: left;
        }

        .accessibility-btn:hover {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        .accessibility-btn.active {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        .accessibility-btn i {
            font-size: 1rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: var(--bs-success);
            color: var(--bs-white);
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.inactive {
            background: var(--bs-gray-400);
        }

        /* Enhanced Accessibility Features - WCAG 2.1 AA */

        /* Line Height Enhancement */
        body.enhanced-line-height {
            line-height: 1.8;
        }

        /* Letter Spacing Enhancement */
        body.enhanced-letter-spacing {
            letter-spacing: 0.05em;
        }

        /* Large Cursor */
        body.large-cursor,
        body.large-cursor * {
            cursor: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><circle cx="16" cy="16" r="14" fill="black" opacity="0.8"/></svg>') 16 16, auto !important;
        }

        /* Grayscale Mode */
        body.grayscale-mode {
            filter: grayscale(100%);
        }

        /* Blue Light Filter */
        body.blue-light-filter {
            filter: sepia(20%) hue-rotate(180deg) saturate(140%);
        }

        /* Inverted Colors */
        body.inverted-colors {
            filter: invert(100%);
        }

        body.inverted-colors img,
        body.inverted-colors video,
        body.inverted-colors iframe {
            filter: invert(100%);
        }

        /* Reading Guide */
        #reading-guide {
            transition: opacity 0.3s ease;
        }

        /* Screen Reader Mode */
        body.screen-reader-mode {
            /* Additional semantic markers */
        }

        body.screen-reader-mode .feature-card:hover,
        body.screen-reader-mode .service-component:hover {
            /* Reduce animations for screen readers */
            animation: none !important;
            transform: none !important;
        }

        /* Enhanced Focus States */
        .enhanced-keyboard-nav *:focus {
            outline: 3px solid #0066cc !important;
            outline-offset: 2px !important;
            background: rgba(0, 102, 204, 0.1) !important;
        }

        /* High Contrast Improvements */
        body.high-contrast .accessibility-sidebar {
            border: 3px solid #000000;
            background: #ffffff;
        }

        body.high-contrast .accessibility-header {
            background: #000000;
            color: #ffffff;
        }

        body.high-contrast .accessibility-control {
            border: 2px solid #000000;
        }

        body.high-contrast .toggle-switch {
            border: 2px solid #000000;
        }

        body.high-contrast .toggle-switch.active {
            background: #000000;
        }

        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            .accessibility-sidebar,
            .accessibility-toggle,
            .toggle-switch,
            .font-size-btn,
            .accessibility-btn {
                transition: none !important;
                animation: none !important;
            }
        }

        /* Screen Reader Only Content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Print Styles for Accessibility */
        @media print {
            .accessibility-sidebar,
            .accessibility-toggle,
            .theme-toggle,
            .sr-only {
                display: none !important;
            }

            body.grayscale-mode,
            body.blue-light-filter,
            body.inverted-colors {
                filter: none !important;
            }
        }

        /* RTL Support for Arabic */
        body.rtl {
            direction: rtl;
            text-align: right;
        }

        body.rtl .navbar-nav {
            padding-right: 0;
        }

        body.rtl .dropdown-menu {
            right: 0;
            left: auto;
            text-align: right;
        }

        body.rtl .hero-content {
            text-align: right !important;
        }

        body.rtl .feature-card,
        body.rtl .service-component {
            text-align: right;
        }

        body.rtl .footer-section {
            text-align: right;
        }

        body.rtl .ml-auto {
            margin-left: 0;
            margin-right: auto;
        }

        body.rtl .mr-2 {
            margin-right: 0;
            margin-left: 0.5rem;
        }

        body.rtl .ml-3 {
            margin-left: 0;
            margin-right: 1rem;
        }

        /* Theme Toggle Button */
        .theme-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            background: var(--bs-white);
            border: 2px solid var(--bs-border);
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--shadow-xl);
            transition: all 0.3s ease;
        }

        .theme-toggle:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-2xl);
        }

        .theme-toggle:focus-visible {
            outline: 3px solid var(--bs-focus);
            outline-offset: 2px;
        }

        /* Navigation CSS removed - now using shared navigation component from layouts.navigation */

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
        }

        .hero-slider {
            position: relative;
            width: 100%;
            height: 100vh;
            overflow: hidden;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: brightness(0.8);
        }

        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            transition: background 0.3s ease;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 900;
            margin-bottom: 1.5rem;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            transition: color 0.3s ease;
        }

        .hero-subtitle {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 600;
            margin-bottom: 2rem;
            transition: color 0.3s ease;
        }

        .hero-description {
            font-size: clamp(1rem, 2vw, 1.25rem);
            margin-bottom: 3rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            transition: color 0.3s ease;
        }

        /* Slider Controls */
        .slider-controls {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 15px;
        }

        .slider-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .slider-dot:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.1;
            background-image:
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.2) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.2) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 900;
            color: var(--bs-white);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        /* White text override for orange background */
        .hero-title span,
        .hero-subtitle,
        .hero-description {
            color: var(--bs-white) !important;
        }

        .hero-subtitle {
            font-size: clamp(1.25rem, 3vw, 2rem);
            font-weight: 600;
            color: var(--bs-white);
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .hero-description {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: var(--bs-white);
            margin-bottom: 3rem;
            opacity: 0.9;
            max-width: 600px;
        }

        /* Buttons */
        .btn-hero {
            padding: 1rem 2rem;
            font-size: 1.125rem;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .btn-hero-primary {
            background: var(--bs-white);
            color: var(--bs-primary);
        }

        .btn-hero-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            background: var(--bs-gray-50);
        }

        .btn-hero-secondary {
            background: transparent;
            color: var(--bs-white);
            border: 2px solid var(--bs-white);
        }

        .btn-hero-secondary:hover {
            background: var(--bs-white);
            color: var(--bs-primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255,255,255,0.3);
        }

        /* Cards */
        .feature-card {
            background: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .feature-card:hover::before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 64px;
            height: 64px;
            background: var(--gradient-primary);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            color: var(--bs-white);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bs-gray-900);
            margin-bottom: 1rem;
        }

        [data-theme="dark"] .feature-title {
            color: var(--bs-gray-100);
        }

        .feature-description {
            color: var(--bs-gray-600);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        [data-theme="dark"] .feature-description {
            color: var(--bs-gray-400);
        }

        /* Stats Cards */
        .stat-card {
            background: var(--bs-white);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 1px solid var(--bs-border);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--bs-gray-700);
        }

        [data-theme="dark"] .stat-label {
            color: var(--bs-gray-300);
        }

        .stat-progress {
            height: 8px;
            background: var(--bs-gray-200);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 1rem;
        }

        [data-theme="dark"] .stat-progress {
            background: var(--bs-gray-700);
        }

        .stat-progress-bar {
            height: 100%;
            background: var(--gradient-primary);
            border-radius: 4px;
            transition: width 1s ease;
        }

        /* Service Cards */
        .service-card {
            background: var(--bs-white);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--bs-border);
            height: 100%;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
        }

        .service-header {
            padding: 2rem;
            color: var(--bs-white);
            position: relative;
            overflow: hidden;
        }

        .service-header-primary {
            background: var(--gradient-primary);
        }

        .service-header-secondary {
            background: var(--gradient-secondary);
        }

        .service-header-emerald {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .service-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .service-subtitle {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .service-body {
            padding: 2rem;
        }

        .service-list {
            list-style: none;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .service-list li {
            padding: 0.5rem 0;
            color: var(--bs-gray-600);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        [data-theme="dark"] .service-list li {
            color: var(--bs-gray-400);
        }

        .service-list i {
            color: var(--bs-success);
            flex-shrink: 0;
        }

        /* Footer */
        .footer {
            background: var(--bs-gray-900);
            color: var(--bs-gray-300);
            padding: 4rem 0 2rem;
            margin-top: 6rem;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .footer-brand h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--bs-white);
            margin: 0;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        
        /* Footer Links Styling */
        .footer-links li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .footer-links i {
            color: #f97316; /* Orange-500 */
            flex-shrink: 0;
            margin-top: 2px;
        }

        .footer-links a {
            color: var(--bs-gray-400);
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #f97316; /* Orange-500 */
            transform: translateX(2px);
        }

        .footer-links .fas.fa-external-link-alt {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-left: 0.25rem;
        }

        .footer-links a:hover .fas.fa-external-link-alt {
            opacity: 1;
        }

        /* Footer Statistics Styling */
        .footer-stats {
            padding: 0;
            margin: 0;
        }

        .stat-item {
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-item:hover {
            transform: translateX(5px);
        }

        .stat-number {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.2;
            transition: all 0.3s ease;
        }

        .stat-label {
            font-size: 0.8rem;
            line-height: 1.3;
            margin-top: 2px;
        }

        .stat-item:hover .stat-number {
            color: var(--bs-primary) !important;
            transform: scale(1.05);
        }

        .stat-item:hover .stat-label {
            color: var(--bs-gray-200) !important;
        }

        /* Animated counter effect */
        @keyframes countUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-number.animated {
            animation: countUp 0.6s ease-out;
        }

        /* Responsive adjustments for footer stats */
        @media (max-width: 768px) {
            .stat-item {
                padding: 10px 0;
            }

            .stat-number {
                font-size: 1rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }
        }

        /* Responsive adjustments for footer */
        @media (max-width: 992px) {
            .footer .col-lg-3 {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 576px) {
            .footer-links li {
                flex-direction: column;
                gap: 0.5rem;
            }

            .footer-links i {
                margin-top: 0;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .accessibility-sidebar {
                width: 280px;
                left: -280px;
            }

            .accessibility-toggle {
                right: -44px;
                width: 44px;
                height: 44px;
            }

            .accessibility-header {
                padding: 1rem;
            }

            .accessibility-header h3 {
                font-size: 1.125rem;
            }

            .accessibility-content {
                padding: 1rem;
            }

            .accessibility-section {
                margin-bottom: 1rem;
                padding-bottom: 1rem;
            }

            .theme-toggle {
                bottom: 5rem;
                right: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.5rem;
            }

            .stat-number {
                font-size: 2rem;
            }

            .feature-card,
            .service-card {
                margin-bottom: 2rem;
            }
        }

        /* Print styles */
        @media print {
            .accessibility-toolbar,
            .theme-toggle,
            .navbar,
            footer {
                display: none;
            }

            body {
                font-size: 12pt;
                color: black;
                background: white;
            }

            .feature-card,
            .service-card {
                break-inside: avoid;
                border: 1px solid #ccc;
            }
        }

        /* Focus styles for accessibility */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        textarea:focus-visible,
        select:focus-visible {
            outline: 3px solid var(--bs-focus);
            outline-offset: 2px;
        }

        /* Smooth transitions for theme changes */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease !important;
        }

        /* Font size adjustments */
        body.font-small {
            font-size: 14px;
        }

        body.font-normal {
            font-size: 16px;
        }

        body.font-large {
            font-size: 18px;
        }

        body.font-extra-large {
            font-size: 20px;
        }

        /* High contrast mode active */
        body.high-contrast {
            --bs-border: #000000;
            --bs-text: #000000;
            --bs-focus: #0000ff;
            --bs-bg: #ffffff;
            --bs-surface: #ffffff;
        }

        body.high-contrast[data-theme="dark"] {
            --bs-border: #ffffff;
            --bs-text: #ffffff;
            --bs-focus: #ffff00;
            --bs-bg: #000000;
            --bs-surface: #000000;
        }

        body.underline-links a {
            text-decoration: underline !important;
        }

        /* Screen reader only content */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Buttons specific styling - keep button colors intact */
        [data-theme="dark"] .btn,
        [data-theme="dark"] .btn * {
            color: inherit !important;
        }

        [data-theme="dark"] .btn-primary {
            color: #ffffff !important;
        }

        [data-theme="dark"] .btn-outline-secondary {
            color: #ffffff !important;
            border-color: var(--bs-gray-600) !important;
        }

        [data-theme="dark"] .btn-outline-secondary:hover {
            color: #ffffff !important;
            background-color: var(--bs-secondary) !important;
            border-color: var(--bs-secondary) !important;
        }

        /* Footer dark mode fixes */
        [data-theme="dark"] .footer {
            background: #0f172a !important;
        }

        /* Card backgrounds */
        [data-theme="dark"] .card,
        [data-theme="dark"] .feature-card,
        [data-theme="dark"] .service-card,
        [data-theme="dark"] .stat-card {
            background: var(--bs-surface) !important;
            border-color: var(--bs-border) !important;
        }

        /* Service header backgrounds with opacity */
        [data-theme="dark"] .service-header .bg-white.bg-opacity-20 {
            background: rgba(255, 255, 255, 0.1) !important;
        }

        /* Floating action buttons */
        .floating-action-buttons {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .floating-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            color: white;
            border: none;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .back-to-top-btn {
            background: var(--bs-primary);
        }

        .whatsapp-btn {
            background: #25D366;
        }

        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.98);
                filter: blur(2px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px) scale(0.98);
                filter: blur(2px);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
                filter: blur(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px) scale(0.98);
                filter: blur(2px);
            }
            to {
                opacity: 1;
                transform: translateX(0) scale(1);
                filter: blur(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
                filter: blur(4px);
            }
            to {
                opacity: 1;
                transform: scale(1);
                filter: blur(0);
            }
        }

        @keyframes slideInFromTop {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Enhanced loading animation */
        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        /* Page Loading Animation */
        .page-loading {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .page-loaded {
            opacity: 1;
            transform: translateY(0);
        }

        /* Stagger Animation Classes */
        .stagger-fade-in > * {
            opacity: 0;
            animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .stagger-fade-in > *:nth-child(1) { animation-delay: 0.1s; }
        .stagger-fade-in > *:nth-child(2) { animation-delay: 0.2s; }
        .stagger-fade-in > *:nth-child(3) { animation-delay: 0.3s; }
        .stagger-fade-in > *:nth-child(4) { animation-delay: 0.4s; }
        .stagger-fade-in > *:nth-child(5) { animation-delay: 0.5s; }
        .stagger-fade-in > *:nth-child(6) { animation-delay: 0.6s; }

        /* Enhanced Hero Animations */
        .hero-title {
            opacity: 0;
            animation: scaleIn 1.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .hero-btn-primary:hover,
        .hero-btn-secondary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .hero-btn-primary:active,
        .hero-btn-secondary:active {
            transform: translateY(-2px) scale(1.02);
            transition: all 0.1s ease;
        }

        .info-item:hover {
            transform: translateY(-8px) scale(1.05);
        }

        .info-item:hover div:first-child {
            color: #ffd700;
            transform: scale(1.1);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Smooth Scroll Behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Enhanced loading animation */
        @keyframes heroGradientShift {
            0%, 100% {
                background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            }
            25% {
                background: linear-gradient(135deg, #ff8c42 0%, #ff9d47 100%);
            }
            50% {
                background: linear-gradient(135deg, #ff6b35 0%, #ff8c42 100%);
            }
            75% {
                background: linear-gradient(135deg, #ff8c42 0%, #f7931e 100%);
            }
        }

        /* Micro-interactions */
        .hero-content > * {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Parallax effect for background elements */
        .hero-bg-element {
            will-change: transform;
        }

        /* Enhanced bounce animation */
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% {
                transform: translate3d(0, 0, 0);
            }
            40%, 43% {
                transform: translate3d(0, -15px, 0);
            }
            70% {
                transform: translate3d(0, -7px, 0);
            }
            90% {
                transform: translate3d(0, -2px, 0);
            }
        }

        @keyframes scroll {
            0% {
                opacity: 1;
                top: 8px;
            }
            100% {
                opacity: 0;
                top: 20px;
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0) translateX(-50%);
            }
            40% {
                transform: translateY(-10px) translateX(-50%);
            }
            60% {
                transform: translateY(-5px) translateX(-50%);
            }
        }

        @keyframes scroll {
            0% {
                opacity: 1;
                top: 8px;
            }
            100% {
                opacity: 0;
                top: 20px;
            }
        }

        /* Service Component Styles */
        .service-component {
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .service-component::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 165, 0, 0.3), transparent);
            transition: left 0.5s;
        }

        .service-component:hover::before {
            left: 100%;
        }

        .service-component:hover {
            background: linear-gradient(135deg, rgba(255, 165, 0, 0.9), rgba(255, 140, 0, 0.8)) !important;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 35px rgba(255, 165, 0, 0.4);
            border: 2px solid rgba(255, 165, 0, 0.6);
        }

        .icon-hover {
            transition: all 0.3s ease;
            display: inline-block;
        }

        .service-component:hover .icon-hover {
            transform: scale(1.15) rotateY(360deg);
            filter: drop-shadow(0 0 12px rgba(255, 165, 0, 0.8));
            color: #ffffff !important;
        }

        /* Text styling for service components */
        .service-component .text-dark {
            color: #000000 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .service-component:hover .text-dark {
            color: #ffffff !important;
            transform: translateY(-1px) scale(1.05);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* Icon colors change to white on hover */
        .service-component:hover .text-warning.icon-hover,
        .service-component:hover .text-info.icon-hover,
        .service-component:hover .text-success.icon-hover,
        .service-component:hover .text-primary.icon-hover,
        .service-component:hover .text-danger.icon-hover {
            color: #ffffff !important;
            filter: drop-shadow(0 0 12px rgba(255, 255, 255, 0.8));
        }

        /* Default icon colors */
        .text-warning.icon-hover {
            color: #ffc107 !important;
        }

        .text-info.icon-hover {
            color: #0dcaf0 !important;
        }

        .text-success.icon-hover {
            color: #198754 !important;
        }

        .text-primary.icon-hover {
            color: #0d6efd !important;
        }

        .text-danger.icon-hover {
            color: #dc3545 !important;
        }

        /* Animation keyframes */
        @keyframes iconPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes orangeGlow {
            0%, 100% {
                box-shadow: 0 12px 35px rgba(255, 165, 0, 0.4);
            }
            50% {
                box-shadow: 0 12px 45px rgba(255, 165, 0, 0.6);
            }
        }

        .service-component:hover .icon-hover {
            animation: iconPulse 0.6s ease-in-out;
        }

        .service-component:hover {
            animation: orangeGlow 1.5s ease-in-out infinite;
        }

        /* Dark mode adjustments */
        [data-theme="dark"] .service-component:hover {
            background: linear-gradient(135deg, rgba(255, 165, 0, 0.8), rgba(255, 140, 0, 0.7)) !important;
            box-shadow: 0 12px 35px rgba(255, 165, 0, 0.3);
            border: 2px solid rgba(255, 165, 0, 0.5);
        }

        [data-theme="dark"] .service-component .text-dark {
            color: #000000 !important;
        }

        [data-theme="dark"] .service-component:hover .text-dark {
            color: #ffffff !important;
        }

        /* Service cards specific fixes */
        [data-theme="dark"] .service-card {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-card * {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-card .service-header * {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-card .service-body * {
            color: #ffffff !important;
        }

        /* Override any remaining dark text */
        [data-theme="dark"] .service-card [class*="text-"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-card .text-muted {
            color: #d1d5db !important;
        }

        /* Additional comprehensive fixes for service text */
        [data-theme="dark"] article[class*="service-"] * {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-title {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-subtitle {
            color: #e5e7eb !important;
        }

        [data-theme="dark"] h3.service-title {
            color: #ffffff !important;
        }

        [data-theme="dark"] p.service-subtitle {
            color: #e5e7eb !important;
        }

        /* Force override any inline styles or specific Bootstrap classes */
        [data-theme="dark"] .service-body [style*="color"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-list li[style*="color"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] .service-list span[style*="color"] {
            color: #ffffff !important;
        }

        /* Convert specific colors to white in dark mode */
        [data-theme="dark"] [style*="color: #14532d"],
        [data-theme="dark"] [style*="color:#14532d"],
        [data-theme="dark"] [style*="#14532d"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="color: #86efac"],
        [data-theme="dark"] [style*="color:#86efac"],
        [data-theme="dark"] [style*="#86efac"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="color: #9ca3af"],
        [data-theme="dark"] [style*="color:#9ca3af"],
        [data-theme="dark"] [style*="#9ca3af"] {
            color: #ffffff !important;
        }

        /* Also handle RGB formats */
        [data-theme="dark"] [style*="color: rgb(20, 83, 45)"],
        [data-theme="dark"] [style*="color:rgb(20, 83, 45)"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="color: rgb(134, 239, 172)"],
        [data-theme="dark"] [style*="color:rgb(134, 239, 172)"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="color: rgb(156, 163, 175)"],
        [data-theme="dark"] [style*="color:rgb(156, 163, 175)"] {
            color: #ffffff !important;
        }

        /* Only target specific inline styles with these exact colors */
        [data-theme="dark"] [style*="color: #14532d"],
        [data-theme="dark"] [style*="color:#14532d"],
        [data-theme="dark"] [style*="color: #86efac"],
        [data-theme="dark"] [style*="color:#86efac"],
        [data-theme="dark"] [style*="color: #9ca3af"],
        [data-theme="dark"] [style*="color:#9ca3af"] {
            color: #ffffff !important;
        }

        /* Only target specific RGB formats */
        [data-theme="dark"] [style*="color: rgb(20, 83, 45)"],
        [data-theme="dark"] [style*="color:rgb(20, 83, 45)"],
        [data-theme="dark"] [style*="color: rgb(134, 239, 172)"],
        [data-theme="dark"] [style*="color:rgb(134, 239, 172)"],
        [data-theme="dark"] [style*="color: rgb(156, 163, 175)"],
        [data-theme="dark"] [style*="color:rgb(156, 163, 175)"] {
            color: #ffffff !important;
        }

        /* Service Body Button */
        [data-theme="dark"] .service-body .btn-primary {
            background-color: var(--theme-primary-bg) !important;
            border-color: var(--theme-primary-text) !important;
            color: var(--theme-primary-text) !important;
        }

        [data-theme="dark"] .service-body .btn-primary:hover {
            background-color: var(--theme-secondary-bg) !important;
            border-color: var(--theme-secondary-text) !important;
        }

        [data-theme="dark"] .service-body .btn-primary i {
            color: var(--theme-primary-text) !important;
        }

        /* 14 Komponen Standar Pelayanan specific fixes */
        [data-theme="dark"] .bg-white.bg-opacity-20 {
            background-color: rgba(156, 163, 175, 0.2) !important; /* Gray-400 with opacity */
            border-color: rgba(156, 163, 175, 0.3) !important;
        }

        [data-theme="dark"] .bg-white.bg-opacity-20:hover {
            background-color: rgba(156, 163, 175, 0.3) !important; /* Gray-400 with higher opacity */
            border-color: rgba(156, 163, 175, 0.4) !important;
        }

        /* Additional fix for elements with inline style */
        [data-theme="dark"] [style*="background-color: rgba(255,255,255,0.2)"] {
            background-color: rgba(156, 163, 175, 0.2) !important;
        }

        [data-theme="dark"] [style*="background-color: rgba(255,255,255,0.2)"]:hover {
            background-color: rgba(156, 163, 175, 0.3) !important;
        }

        /* 14 Komponen heading text */
        [data-theme="dark"] [style*="background: var(--gradient-primary)"] h3 {
            color: var(--theme-primary-text) !important;
        }

        [data-theme="dark"] [style*="background: var(--gradient-primary)"] * {
            color: var(--theme-primary-text) !important;
        }

        /* Gradient backgrounds text */
        [data-theme="dark"] [style*="background: var(--gradient-primary)"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="background: var(--gradient-secondary)"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="background: linear-gradient"] {
            color: #ffffff !important;
        }

        /* Button fixes */
        [data-theme="dark"] [style*="background: rgba(255,255,255,0.2)"] {
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.3) !important;
        }

        [data-theme="dark"] [style*="background: transparent"][style*="border: 2px solid white"] {
            color: #ffffff !important;
            border-color: #ffffff !important;
        }

        /* Floating buttons */
        [data-theme="dark"] [style*="color: white"][style*="background: var(--bs-primary)"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] [style*="color: white"][style*="background: #25D366"] {
            color: #ffffff !important;
        }

        /* Hero section gradient text */
        [data-theme="dark"] .hero-content [style*="color: white"] {
            color: #ffffff !important;
        }

        /* Service cards with inline styles */
        [data-theme="dark"] [style*="background: var(--gradient-primary)"] *,
        [data-theme="dark"] [style*="background: var(--gradient-secondary)"] * {
            color: #ffffff !important;
        }

        /* Specific inline style overrides for common patterns */
        [data-theme="dark"] div[style*="color: white"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] span[style*="color: white"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] p[style*="color: white"] {
            color: #ffffff !important;
        }

        [data-theme="dark"] h1[style*="color: white"],
        [data-theme="dark"] h2[style*="color: white"],
        [data-theme="dark"] h3[style*="color: white"],
        [data-theme="dark"] h4[style*="color: white"],
        [data-theme="dark"] h5[style*="color: white"],
        [data-theme="dark"] h6[style*="color: white"] {
            color: #ffffff !important;
        }

        /* Button inline styles with specific patterns */
        [data-theme="dark"] [style*="background: rgba(255,255,255,0.2)"] {
            color: #ffffff !important;
            border-color: rgba(255,255,255,0.3) !important;
        }

        [data-theme="dark"] [style*="background: transparent"][style*="border: 2px solid white"] {
            color: #ffffff !important;
            border-color: #ffffff !important;
        }

        /* Ensure all links with white inline style stay white */
        [data-theme="dark"] a.text-white {
            color: #ffffff !important;
        }

        /* Small text elements */
        [data-theme="dark"] .small.text-dark {
            color: #f3f4f6 !important;
        }

        [data-theme="dark"] .small.fw-semibold.text-dark {
            color: #f3f4f6 !important;
        }

        /* Service headers and content */
        [data-theme="dark"] .service-header [style*="color: white"] {
            color: #ffffff !important;
        }

        /* Stats and numbers */
        [data-theme="dark"] .stat-number.text-white {
            color: #ffffff !important;
        }

        /* Footer links and text */
        [data-theme="dark"] footer a.text-white {
            color: #ffffff !important;
        }

        [data-theme="dark"] footer a.text-white:hover {
            color: #e5e7eb !important;
        }

        /* Social media icons */
        [data-theme="dark"] .fab.fa-facebook,
        [data-theme="dark"] .fab.fa-twitter,
        [data-theme="dark"] .fab.fa-instagram,
        [data-theme="dark"] .fab.fa-youtube {
            color: #ffffff !important;
        }

        /* COMPREHENSIVE DARK MODE FIX - ALL TEXT WHITE */
        /* Make ALL text elements white in dark mode */
        [data-theme="dark"] *:not(.btn):not(.badge):not([class*="bg-white"]):not([class*="bg-light"]) {
            color: #ffffff !important;
        }

        /* Override all colored text classes */
        [data-theme="dark"] .text-success,
        [data-theme="dark"] .text-primary,
        [data-theme="dark"] .text-secondary,
        [data-theme="dark"] .text-info,
        [data-theme="dark"] .text-warning,
        [data-theme="dark"] .text-danger,
        [data-theme="dark"] .text-dark,
        [data-theme="dark"] .text-body,
        [data-theme="dark"] .text-body-secondary {
            color: #ffffff !important;
        }

        /* Override inline styles with colors */
        [data-theme="dark"] [style*="color: #10b981"],
        [data-theme="dark"] [style*="color:#10b981"],
        [data-theme="dark"] [style*="color: #3b82f6"],
        [data-theme="dark"] [style*="color:#3b82f6"],
        [data-theme="dark"] [style*="color: #f59e0b"],
        [data-theme="dark"] [style*="color:#f59e0b"],
        [data-theme="dark"] [style*="color: #ef4444"],
        [data-theme="dark"] [style*="color:#ef4444"],
        [data-theme="dark"] [style*="color: rgb(16, 185, 129)"],
        [data-theme="dark"] [style*="color: rgb(59, 130, 246)"],
        [data-theme="dark"] [style*="color: rgb(245, 158, 11)"],
        [data-theme="dark"] [style*="color: rgb(239, 68, 68)"] {
            color: #ffffff !important;
        }

        /* All headings, paragraphs, spans, divs white */
        [data-theme="dark"] h1,
        [data-theme="dark"] h2,
        [data-theme="dark"] h3,
        [data-theme="dark"] h4,
        [data-theme="dark"] h5,
        [data-theme="dark"] h6,
        [data-theme="dark"] p,
        [data-theme="dark"] span,
        [data-theme="dark"] div,
        [data-theme="dark"] a,
        [data-theme="dark"] li,
        [data-theme="dark"] td,
        [data-theme="dark"] th,
        [data-theme="dark"] label,
        [data-theme="dark"] strong,
        [data-theme="dark"] em,
        [data-theme="dark"] small,
        [data-theme="dark"] .small {
            color: #ffffff !important;
        }

        /* Display and lead text */
        [data-theme="dark"] .display-1,
        [data-theme="dark"] .display-2,
        [data-theme="dark"] .display-3,
        [data-theme="dark"] .display-4,
        [data-theme="dark"] .display-5,
        [data-theme="dark"] .display-6,
        [data-theme="dark"] .lead {
            color: #ffffff !important;
        }

        /* Icons - keep them white too */
        [data-theme="dark"] i,
        [data-theme="dark"] .fas,
        [data-theme="dark"] .far,
        [data-theme="dark"] .fab,
        [data-theme="dark"] .fa {
            color: #ffffff !important;
        }

        /* Exception: elements with white/light background should have dark text */
        [data-theme="dark"] .bg-white,
        [data-theme="dark"] .bg-light,
        [data-theme="dark"] [class*="bg-white"],
        [data-theme="dark"] [class*="bg-light"] {
            color: #000000 !important;
        }

        [data-theme="dark"] .bg-white *,
        [data-theme="dark"] .bg-light *,
        [data-theme="dark"] [class*="bg-white"] *,
        [data-theme="dark"] [class*="bg-light"] * {
            color: #000000 !important;
        }

        /* Form inputs - keep readable with dark background and white text */
        [data-theme="dark"] input,
        [data-theme="dark"] textarea,
        [data-theme="dark"] select {
            background-color: var(--bs-surface) !important;
            color: #ffffff !important;
            border-color: var(--bs-border) !important;
        }

        [data-theme="dark"] input::placeholder,
        [data-theme="dark"] textarea::placeholder {
            color: #9ca3af !important;
        }

        /* All remaining text elements */
        [data-theme="dark"] * {
            text-shadow: none !important;
        }

        /* Force visibility for all text in dark mode */
        [data-theme="dark"] {
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Hover effects for all interactive elements */
        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-scale:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        /* Focus states for accessibility */
        .focus-ring:focus {
            outline: 3px solid var(--bs-focus);
            outline-offset: 2px;
        }

        /* Animation delays for staggered effects */
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
        .delay-500 { animation-delay: 0.5s; }
        .delay-600 { animation-delay: 0.6s; }
        .delay-700 { animation-delay: 0.7s; }
        .delay-800 { animation-delay: 0.8s; }

        /* Custom utility classes */
        .text-shadow {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .text-shadow-lg {
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .bg-gradient-custom {
            background: linear-gradient(135deg, var(--bs-primary) 0%, var(--bs-secondary) 100%);
        }

        /* Responsive utilities */
        @media (max-width: 576px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-subtitle {
                font-size: 1.25rem;
            }
            
            .hero-description {
                font-size: 1rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
            
            .service-title {
                font-size: 1.25rem;
            }
        }

        /* Additional animations */
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes ping {
            75%, 100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        .animate-ping {
            animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bs-gray-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--bs-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--bs-primary-dark);
        }

        [data-theme="dark"] ::-webkit-scrollbar-track {
            background: var(--bs-gray-800);
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb {
            background: var(--bs-primary-light);
        }

        [data-theme="dark"] ::-webkit-scrollbar-thumb:hover {
            background: var(--bs-primary);
        }

        /* Floating Action Buttons - Left Side */
        .floating-action-buttons-left {
            position: fixed;
            left: 20px;
            bottom: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Floating Action Button - Right Side */
        .floating-action-buttons-right {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 9999;
        }

        .floating-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            color: white;
            border: none;
            z-index: 1000;
        }

        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .back-to-top-btn {
            background: var(--bs-primary);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .back-to-top-btn.visible {
            opacity: 1;
            visibility: visible;
        }

        .accessibility-btn {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(22, 163, 74, 0.4);
            font-size: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .accessibility-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.2) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .accessibility-btn:hover::before {
            transform: translateX(100%);
        }

        .accessibility-btn:hover {
            box-shadow: 0 8px 30px rgba(22, 163, 74, 0.6);
            border-color: rgba(255, 255, 255, 0.5);
            background: linear-gradient(135deg, #15803d 0%, #16a34a 100%);
        }

        .accessibility-btn:active {
            transform: scale(0.95);
        }

        @keyframes accessibilityPulse {
            0%, 100% { box-shadow: 0 4px 20px rgba(22, 163, 74, 0.4); }
            50% { box-shadow: 0 4px 20px rgba(22, 163, 74, 0.8); }
        }

        .accessibility-btn.pulse {
            animation: accessibilityPulse 2s infinite;
        }

        .whatsapp-btn {
            background: #25D366;
        }

        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--bs-primary);
            color: var(--bs-white);
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
            font-weight: 600;
            font-size: 14px;
            transition: top 0.3s ease;
        }

        .skip-link:focus {
            top: 6px;
        }

        /* Accessibility Panel */
        .accessibility-panel {
            position: fixed;
            top: 0;
            left: 0;
            width: 320px;
            max-height: 100vh;
            background: var(--bs-white);
            z-index: 10000;
            box-shadow: 2px 0 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            transform: translateX(-100%);
            display: flex;
            flex-direction: column;
        }

        .accessibility-panel.open {
            transform: translateX(0);
        }

        .accessibility-panel-header {
            padding: 1.5rem;
            background: var(--gradient-primary);
            color: var(--bs-white);
            border-bottom: 3px solid var(--bs-primary);
        }

        .accessibility-panel-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-panel-subtitle {
            margin: 0.5rem 0 0 0;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .accessibility-close-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: var(--bs-white);
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            cursor: pointer;
            font-size: 18px;
            font-weight: 300;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .accessibility-close-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .accessibility-panel-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .accessibility-section {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--bs-border);
        }

        .accessibility-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .accessibility-section-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--bs-text);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-section-title i {
            color: var(--bs-primary);
        }

        .accessibility-controls {
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
        }

        .accessibility-control {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem;
            background: var(--bs-gray-50);
            border: 1px solid var(--bs-border);
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .accessibility-control:hover {
            background: var(--bs-gray-100);
            border-color: var(--bs-primary);
        }

        .accessibility-control label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bs-text);
            margin: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .accessibility-control label i {
            color: var(--bs-primary);
            font-size: 1rem;
        }

        /* Toggle Switch Container */
        .accessibility-toggle-switch {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            background: var(--bs-gray-50);
            border: 1px solid var(--bs-border);
            border-radius: 8px;
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .accessibility-toggle-switch:hover {
            background: var(--bs-gray-100);
            border-color: var(--bs-primary);
        }

        .accessibility-toggle-switch:last-child {
            margin-bottom: 0;
        }

        /* Toggle Label */
        .accessibility-toggle-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bs-text);
            margin: 0;
            cursor: pointer;
        }

        .accessibility-toggle-label i {
            color: var(--bs-primary);
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* Toggle Switch */
        .accessibility-switch {
            position: relative;
            width: 48px;
            height: 24px;
            background: var(--bs-gray-300);
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s ease;
            flex-shrink: 0;
        }

        .accessibility-switch.active {
            background: var(--bs-primary);
        }

        .accessibility-switch::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: var(--bs-white);
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .accessibility-switch.active::after {
            transform: translateX(24px);
        }

        /* Font Size Controls */
        .font-size-controls {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .font-size-btn {
            flex: 1;
            padding: 0.5rem;
            background: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--bs-text);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
        }

        .font-size-btn:hover {
            background: var(--bs-gray-100);
            border-color: var(--bs-primary);
        }

        .font-size-btn.active {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        /* Button Controls */
        .accessibility-btn {
            padding: 0.75rem 1rem;
            background: var(--bs-white);
            border: 1px solid var(--bs-border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--bs-text);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-align: left;
        }

        .accessibility-btn:hover {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        .accessibility-btn.active {
            background: var(--bs-primary);
            color: var(--bs-white);
            border-color: var(--bs-primary);
        }

        .accessibility-btn i {
            font-size: 1rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: var(--bs-success);
            color: var(--bs-white);
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-badge.inactive {
            background: var(--bs-gray-400);
        }
    </style>
</head>
<body class="font-normal">
    <!-- Contact Header -->
    <div style="background-color: #111827; color: white; padding: 0.5rem 0; font-size: 0.75rem;">
        <div style="max-width: 1280px; margin: 0 auto; padding: 0 0.5rem;">
            <div style="display: flex; align-items: center; justify-content: flex-start; gap: 0.5rem; padding: 0; white-space: nowrap; overflow-x: auto; scrollbar-width: none; -ms-overflow-style: none;">
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-map-marker-alt" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-phone" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>(0341) 711500</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-envelope" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>mtsnmalang2adm@gmail.com</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fas fa-globe" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>www.mtsn2kotamalang.sch.id</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fab fa-whatsapp" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>0851 8336 7500 (PTSP)</span>
                </div>
                <div style="display: flex; align-items: center; white-space: nowrap; min-width: max-content;">
                    <i class="fab fa-whatsapp" style="color: #f97316; margin-right: 0.25rem; font-size: 0.75rem;"></i>
                    <span>0851 8337 5008 (Pengaduan)</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Skip to main content for accessibility -->
    <a href="#main-content" class="skip-link">Lewati ke konten utama</a>

    <!-- Floating Action Buttons - Left Side -->
    <div class="floating-action-buttons-left">
        <!-- Accessibility Button -->
        <button id="accessibility-btn"
                class="floating-btn accessibility-btn pulse">
            <span style="line-height: 1;">♿</span>
        </button>
        
        <!-- Back to Top Button -->
        <button onclick="scrollToTop()"
                id="back-to-top-btn"
                class="floating-btn back-to-top-btn">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>

    <!-- Floating Action Button - Right Side -->
    <div class="floating-action-buttons-right">
        <!-- WhatsApp Button -->
        <a href="https://wa.me/{{ config('app.whatsapp_number', '628123456789') }}?text={{ urlencode('Halo, saya ingin bertanya tentang layanan PTSP MTsN 2 KOTA MALANG') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="floating-btn whatsapp-btn">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Modern Accessibility Panel -->
    <div id="accessibilityPanel" class="accessibility-panel">
        <!-- Panel Header -->
        <div class="accessibility-panel-header">
            <h3 class="accessibility-panel-title">
                <span>🎯</span>
                <span>Pusat Aksesibilitas</span>
            </h3>
            <p class="accessibility-panel-subtitle">WCAG 2.1 AA Compliant • Inklusif untuk Semua</p>
            <button onclick="toggleAccessibilitySidebar()" class="accessibility-close-btn" aria-label="Tutup panel aksesibilitas">
                ✕
            </button>
        </div>

        <!-- Panel Content -->
        <div class="accessibility-panel-content">
            <!-- Font Size Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>📝</span>
                    <span>Ukuran Teks</span>
                </h4>
                <div class="accessibility-controls">
                    <button onclick="setFontSize('small')" class="accessibility-btn font-size-btn" id="fontSmallBtn">A-</button>
                    <button onclick="setFontSize('normal')" class="accessibility-btn font-size-btn" id="fontNormalBtn">A</button>
                    <button onclick="setFontSize('large')" class="accessibility-btn font-size-btn" id="fontLargeBtn">A+</button>
                    <button onclick="setFontSize('extra-large')" class="accessibility-btn font-size-btn" id="fontXLargeBtn">A++</button>
                </div>
            </div>

            <!-- Visual Adjustments Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>🎨</span>
                    <span>Penyesuaian Visual</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>👁️</span>
                        <span>Kontras Tinggi</span>
                    </div>
                    <div class="accessibility-switch" id="highContrastControl" onclick="toggleHighContrast()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🔗</span>
                        <span>Garis Bawah Link</span>
                    </div>
                    <div class="accessibility-switch" id="underlineControl" onclick="toggleUnderline()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>📏</span>
                        <span>Jarak Baris</span>
                    </div>
                    <div class="accessibility-switch" id="lineHeightControl" onclick="toggleLineHeight()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>↔️</span>
                        <span>Jarak Huruf</span>
                    </div>
                    <div class="accessibility-switch" id="letterSpacingControl" onclick="toggleLetterSpacing()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🖱️</span>
                        <span>Kursor Besar</span>
                    </div>
                    <div class="accessibility-switch" id="cursorControl" onclick="toggleLargeCursor()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Advanced Features Section -->
            <div class="accessibility-section">
                <h4 class="accessibility-section-title">
                    <span>⚙️</span>
                    <span>Fitur Lanjutan</span>
                </h4>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🎬</span>
                        <span>Nonaktifkan Animasi</span>
                    </div>
                    <div class="accessibility-switch" id="animationControl" onclick="toggleAnimations()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>⌨️</span>
                        <span>Navigasi Keyboard Ditingkatkan</span>
                    </div>
                    <div class="accessibility-switch" id="keyboardControl" onclick="toggleKeyboardNavigation()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>

                <div class="accessibility-toggle-switch">
                    <div class="accessibility-toggle-label">
                        <span>🔊</span>
                        <span>Mode Screen Reader</span>
                    </div>
                    <div class="accessibility-switch" id="screenReaderControl" onclick="toggleScreenReaderMode()" role="switch" aria-checked="false" tabindex="0"></div>
                </div>
            </div>

            <!-- Reset Section -->
            <div class="accessibility-section">
                <button onclick="resetAllAccessibility()" class="btn btn-danger w-100" aria-label="Reset semua pengaturan aksesibilitas">
                    <i class="fas fa-sync-alt me-2"></i>
                    Reset Semua Pengaturan
                </button>
            </div>
        </div>
    </div>

    <script>
    function toggleAccessibilitySidebar() {
        const panel = document.getElementById('accessibilityPanel');
        const btn = document.getElementById('accessibility-btn');

        // Toggle panel using class
        if (panel.classList.contains('open')) {
            panel.classList.remove('open');
            btn.innerHTML = '<span style="line-height: 1;">♿</span>';
            btn.classList.add('pulse');
        } else {
            panel.classList.add('open');
            btn.innerHTML = '<i class="fas fa-times" style="line-height: 1;"></i>';
            btn.classList.remove('pulse');
        }

        // Update button states
        if (typeof updateAccessibilityButtonStates === 'function') {
            updateAccessibilityButtonStates();
        }

        // Announce to screen reader
        announceToScreenReader(panel.classList.contains('open') ? 'Panel aksesibilitas dibuka' : 'Panel aksesibilitas ditutup');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const panel = document.getElementById('accessibilityPanel');
        const btn = document.getElementById('accessibility-btn');

        if (btn) {
            btn.addEventListener('click', function() {
                toggleAccessibilitySidebar();
            });
        }

        document.addEventListener('click', function(event) {
            const isClickInsidePanel = panel.contains(event.target);
            const isClickOnButton = btn.contains(event.target);

            if (!isClickInsidePanel && !isClickOnButton && panel.classList.contains('open')) {
                toggleAccessibilitySidebar();
            }
        });
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        const backToTopBtn = document.getElementById('back-to-top-btn');
        if (window.pageYOffset > 300) {
            backToTopBtn.style.opacity = '1';
            backToTopBtn.style.visibility = 'visible';
        } else {
            backToTopBtn.style.opacity = '0';
            backToTopBtn.style.visibility = 'hidden';
        }
    });
    </script>

  
    <!-- Navigation -->
    @include('layouts.navigation')

    <!-- Main Content -->
    <main id="main-content" role="main">
        <!-- Hero Section -->
        <section style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; color: white; text-align: center; position: relative; z-index: 1;" aria-labelledby="hero-heading">
            <!-- Animated Background Elements -->
            <div class="hero-background" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow: hidden; z-index: 1;">
                <div class="hero-bg-element" style="position: absolute; top: 20%; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
                <div class="hero-bg-element" style="position: absolute; top: 60%; right: 10%; width: 150px; height: 150px; background: rgba(255,255,255,0.05); border-radius: 50%; animation: float 8s ease-in-out infinite 2s;"></div>
                <div class="hero-bg-element" style="position: absolute; bottom: 20%; left: 20%; width: 80px; height: 80px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 7s ease-in-out infinite 1s;"></div>
            </div>

            <div class="hero-content stagger-fade-in" style="max-width: 900px; padding: 20px; z-index: 2; position: relative;">
                <div id="current-datetime" style="font-size: clamp(1rem, 2vw, 1.5rem); font-weight: 600; margin-bottom: 12px; color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.3); opacity: 0; animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.1s forwards;"></div>
                <h1 id="hero-heading" class="hero-title" style="font-size: clamp(2.5rem, 5vw, 4rem); font-weight: 800; margin-bottom: 24px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); line-height: 1.1;">
                    PTSP MTsN 2 KOTA MALANG
                </h1>

                <div class="hero-subtitle" style="font-size: clamp(1.25rem, 3vw, 2rem); font-weight: 300; margin-bottom: 32px; opacity: 0; animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.3s forwards; line-height: 1.3;">
                    Pelayanan Terpadu Satu Pintu
                </div>

                <p class="hero-description" style="font-size: clamp(1rem, 2vw, 1.25rem); margin-bottom: 48px; opacity: 0; max-width: 600px; margin-left: auto; margin-right: auto; animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.5s forwards; line-height: 1.6;">
                    Melayani dengan Hati, Cepat, Transparan, dan Akuntabel
                </p>

                <div class="hero-buttons" style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; margin-bottom: 48px; opacity: 0; animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.7s forwards;">
                    <a href="{{ route('onlineportal.service.catalog') }}"
                       class="hero-btn-primary"
                       style="background: rgba(255,255,255,0.2); color: white; padding: 16px 32px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.1rem; backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: inline-flex; align-items: center; gap: 12px; transform: translateY(0);">
                        <i class="fas fa-th-large"></i>
                        Lihat Semua Layanan
                    </a>

                    <a href="{{ route('onlineportal.track.ticket.form') }}"
                       class="hero-btn-secondary"
                       style="background: transparent; color: white; padding: 16px 32px; border-radius: 50px; text-decoration: none; font-weight: 600; font-size: 1.1rem; border: 2px solid white; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: inline-flex; align-items: center; gap: 12px; transform: translateY(0);">
                        <i class="fas fa-search"></i>
                        Lacak Status Tiket
                    </a>
                </div>

                <div class="hero-info" style="display: flex; justify-content: center; gap: 48px; margin-top: 72px; opacity: 0; animation: fadeInUp 1s cubic-bezier(0.4, 0, 0.2, 1) 0.9s forwards; flex-wrap: wrap;">
                    <div class="info-item" style="text-align: center; transform: translateY(0); transition: transform 0.3s ease;">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 08:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Buka</div>
                    </div>
                    <div class="info-item" style="text-align: center; transform: translateY(0); transition: transform 0.3s ease;">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-clock"></i> 15:00
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Waktu Tutup</div>
                    </div>
                    <div class="info-item" style="text-align: center; transform: translateY(0); transition: transform 0.3s ease;">
                        <div style="font-size: clamp(2rem, 4vw, 2.5rem); font-weight: 700; margin-bottom: 8px; color: white;">
                            <i class="fas fa-calendar-week"></i> Senin-Jumat
                        </div>
                        <div style="font-size: 0.9rem; opacity: 0.9;">Hari Operasional</div>
                    </div>
                </div>
            </div>

            <!-- Scroll Indicator -->
            <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); z-index: 2; animation: bounce 2s infinite;">
                <div style="width: 30px; height: 50px; border: 2px solid rgba(255,255,255,0.5); border-radius: 15px; position: relative;">
                    <div style="width: 4px; height: 10px; background: white; border-radius: 2px; position: absolute; top: 8px; left: 50%; transform: translateX(-50%); animation: scroll 2s infinite;"></div>
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="tentang" class="py-5" aria-labelledby="about-heading" style="position: relative; z-index: 2;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6" data-aos="fade-right">
                        <h2 id="about-heading" class="display-4 fw-bold mb-4">
                            Tentang <span style="color: var(--bs-primary);">PTSP</span> MTsN 2 Kota Malang
                        </h2>
                        <p class="lead mb-4">
                            Pelayanan Terpadu Satu Pintu (PTSP) MTsN 2 Kota Malang adalah sistem pelayanan terpadu yang dirancang untuk memberikan kemudahan akses layanan bagi seluruh sivitas akademika dan masyarakat.
                        </p>
                        <p class="mb-4">
                            Sistem ini dibangun sesuai dengan <strong style="color: var(--bs-primary);">Permen PANRB 15/2014</strong> tentang Pedoman Standar Pelayanan yang menjamin transparansi, akuntabilitas, dan kecepatan pelayanan.
                        </p>

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(20, 83, 45, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-check-double" style="color: var(--bs-primary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Transparan & Akuntabel</h5>
                                        <p class="text-muted mb-0">Setiap proses layanan dapat dilacak secara real-time dengan sistem monitoring terintegrasi</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(234, 88, 12, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-clock" style="color: var(--bs-secondary);"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Cepat & Tepat Waktu</h5>
                                        <p class="text-muted mb-0">Jaminan penyelesaian sesuai standar waktu layanan dengan SLA yang jelas</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-start gap-3">
                                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="fas fa-mobile-alt" style="color: #10b981;"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-2">Mudah Diakses</h5>
                                        <p class="text-muted mb-0">Layanan online 24/7 dan offline di loket PTSP dengan antarmuka yang user-friendly</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6" data-aos="fade-left">
                        <div class="p-4 rounded-4" style="background: var(--gradient-primary); color: white;">
                            <h3 class="h2 fw-bold mb-4 text-center">14 Komponen Standar Pelayanan</h3>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-gavel fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Dasar Hukum</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-list-alt fs-3 mb-2 text-info icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Persyaratan</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-sitemap fs-3 mb-2 text-success icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Prosedur</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-clock fs-3 mb-2 text-primary icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Jangka Waktu</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-money-bill-wave fs-3 mb-2 text-warning icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Biaya/Tarif</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="bg-white bg-opacity-20 backdrop-blur-sm rounded-3 p-3 text-center service-component hover-scale transition-all">
                                        <i class="fas fa-file-contract fs-3 mb-2 text-danger icon-hover"></i>
                                        <p class="small fw-semibold mb-0 text-dark">Produk Layanan</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <p class="mb-0">+ 8 Komponen Lainnya</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Performance Stats Section -->
        <section class="py-5" style="background-color: var(--bs-gray-50);" aria-labelledby="stats-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-chart-line me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Statistik Kinerja</span>
                    </div>
                    <h2 id="stats-heading" class="display-4 fw-bold mb-3">
                        Kinerja <span style="color: var(--bs-primary);">Pelayanan</span> Kami
                    </h2>
                    <p class="lead text-muted">
                        Transparansi dan Akuntabilitas dalam Setiap Layanan
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="100">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Tingkat kepuasan pelanggan 98%">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-primary);">
                                <i class="fas fa-smile-beam text-white"></i>
                            </div>
                            <div class="stat-number">98%</div>
                            <h3 class="stat-label">Tingkat Kepuasan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-chart-line me-2"></i>Berdasarkan SKM 2025
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 98%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="200">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Rata-rata waktu penyelesaian 2 hari">
                            <div class="feature-icon mb-3 mx-auto" style="background: var(--gradient-secondary);">
                                <i class="fas fa-clock text-white"></i>
                            </div>
                            <div class="stat-number">2 Hari</div>
                            <h3 class="stat-label">Rata-rata Waktu</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-hourglass-half me-2"></i>Penyelesaian Layanan
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 85%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="300">
                        <article class="stat-card" tabindex="0" role="article" aria-label="15+ jenis layanan tersedia">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="fas fa-tasks text-white"></i>
                            </div>
                            <div class="stat-number">15+</div>
                            <h3 class="stat-label">Jenis Layanan</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-clipboard-check me-2"></i>Sesuai Standar
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="400">
                        <article class="stat-card" tabindex="0" role="article" aria-label="Akses online 24/7">
                            <div class="feature-icon mb-3 mx-auto" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                                <i class="fas fa-globe text-white"></i>
                            </div>
                            <div class="stat-number">24/7</div>
                            <h3 class="stat-label">Akses Online</h3>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-wifi me-2"></i>Kapan Saja, Dimana Saja
                            </p>
                            <div class="stat-progress">
                                <div class="stat-progress-bar" style="width: 100%;"></div>
                            </div>
                        </article>
                    </div>
                </div>

                <!-- Additional Stats Bar -->
                <div class="mt-5 p-4 rounded-3 border-2 border-dashed" style="border-color: var(--bs-primary); background-color: var(--bs-white);" data-aos="fade-up" data-aos-delay="500">
                    <div class="row text-center">
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-primary);">5000+</div>
                            <div class="text-muted small">Layanan Diproses</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: var(--bs-secondary);">100%</div>
                            <div class="text-muted small">Digitalisasi</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #10b981;">4.8/5</div>
                            <div class="text-muted small">Rating Layanan</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="display-6 fw-bold" style="color: #3b82f6;">99.9%</div>
                            <div class="text-muted small">Uptime</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services Section -->
        <section id="layanan" class="py-5" aria-labelledby="services-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <div class="d-inline-flex align-items-center px-4 py-2 rounded-pill mb-3"
                         style="background-color: rgba(20, 83, 45, 0.1);">
                        <i class="fas fa-th-large me-2" style="color: var(--bs-primary);"></i>
                        <span class="fw-bold text-uppercase" style="color: var(--bs-primary);">Layanan Kami</span>
                    </div>
                    <h2 id="services-heading" class="display-4 fw-bold mb-3">
                        Jenis <span style="color: var(--bs-primary);">Pelayanan</span> Tersedia
                    </h2>
                    <p class="lead text-muted">
                        Melayani berbagai kebutuhan sivitas akademika dan masyarakat
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Layanan Akademik -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                        <article class="service-card">
                            <div class="service-header service-header-primary">
                                <div class="service-icon">
                                    <i class="fas fa-user-graduate text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Akademik</h3>
                                <p class="service-subtitle">Untuk Siswa & Alumni</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Siswa Aktif</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Legalisir Ijazah & Transkrip</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Keterangan Berkelakuan Baik</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Wali Murid -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                        <article class="service-card">
                            <div class="service-header service-header-secondary">
                                <div class="service-icon">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Wali Murid</h3>
                                <p class="service-subtitle">Untuk Orang Tua</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Informasi Akademik Anak</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Tidak Masuk Sekolah</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Panggilan Orang Tua</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Konsultasi BK</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>

                    <!-- Layanan Instansi -->
                    <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                        <article class="service-card">
                            <div class="service-header service-header-emerald">
                                <div class="service-icon">
                                    <i class="fas fa-briefcase text-white"></i>
                                </div>
                                <h3 class="service-title">Layanan Instansi</h3>
                                <p class="service-subtitle">Untuk Mitra Kerja</p>
                            </div>
                            <div class="service-body">
                                <ul class="service-list">
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Permohonan Kerjasama</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Izin Kegiatan & Penelitian</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Permohonan Data Statistik</span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span>Surat Rekomendasi Instansi</span>
                                    </li>
                                </ul>
                                <a href="{{ route('onlineportal.service.catalog') }}" class="btn btn-primary w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Lihat Detail
                                </a>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <!-- Quick Access Section -->
        <section class="py-5" style="background-color: var(--bs-gray-50);" aria-labelledby="access-heading">
            <div class="container">
                <div class="text-center mb-5" data-aos="fade-up">
                    <h2 id="access-heading" class="display-4 fw-bold mb-3">
                        Akses <span style="color: var(--bs-primary);">Cepat</span>
                    </h2>
                    <p class="lead text-muted">
                        Layanan penting dalam satu klik
                    </p>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <a href="{{ route('onlineportal.service.catalog') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto">
                                    <i class="fas fa-file-medical text-white"></i>
                                </div>
                                <h4 class="feature-title">Permohonan Online</h4>
                                <p class="feature-description">
                                    Ajukan layanan secara digital tanpa harus datang ke loket
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Mulai Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <a href="{{ route('onlineportal.track.ticket.form') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: var(--gradient-secondary);">
                                    <i class="fas fa-search text-white"></i>
                                </div>
                                <h4 class="feature-title">Lacak Tiket</h4>
                                <p class="feature-description">
                                    Cek status permohonan Anda secara real-time
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Track Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                        <a href="{{ route('public.visitor.book') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                    <i class="fas fa-book text-white"></i>
                                </div>
                                <h4 class="feature-title">Buku Tamu</h4>
                                <p class="feature-description">
                                    Daftar kunjungan fisik ke sekolah
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Daftar Kunjungan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="400">
                        <a href="{{ route('supervision.complaints.dashboard') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                    <i class="fas fa-exclamation-circle text-white"></i>
                                </div>
                                <h4 class="feature-title">Pengaduan</h4>
                                <p class="feature-description">
                                    Sampaikan keluhan atau saran untuk perbaikan layanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Buat Pengaduan</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
                        <a href="{{ route('supervision.whistleblowing.form') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                                <h4 class="feature-title">Whistleblowing</h4>
                                <p class="feature-description">
                                    Laporkan pelanggaran secara aman dan terlindungi
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Laporkan Sekarang</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="600">
                        <a href="{{ route('supervision.skm.survey') }}" class="text-decoration-none">
                            <div class="feature-card h-100">
                                <div class="feature-icon mx-auto" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                                    <i class="fas fa-poll text-white"></i>
                                </div>
                                <h4 class="feature-title">Survei SKM</h4>
                                <p class="feature-description">
                                    Bantu kami tingkatkan kualitas pelayanan
                                </p>
                                <div class="d-flex align-items-center text-primary">
                                    <span>Isi Survei</span>
                                    <i class="fas fa-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

            </main>

    <!-- Footer -->
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="footer-brand">
                        @if(config('app.logo'))
                            <img src="{{ asset(config('app.logo')) }}" alt="{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }} Logo"
                                 style="width: 48px; height: 48px; object-fit: contain; border-radius: 12px;"
                                 class="footer-logo">
                        @else
                            <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building text-white"></i>
                            </div>
                        @endif
                        <div>
                            <h3>{{ config('app.name_full', 'PTSP MTsN 2 KOTA MALANG') }}</h3>
                            <p class="text-muted small mb-0">Pelayanan Terpadu Satu Pintu</p>
                        </div>
                    </div>
                    <p class="mb-3">
                        Pelayanan Terpadu Satu Pintu sesuai Permen PANRB 15/2014 untuk kemudahan akses layanan masyarakat.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white fs-5" aria-label="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white fs-5" aria-label="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Kontak Kami</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Raya Cemorokandang 77 Kota Malang, Jawa Timur</span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span>(0341) 711500</span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span>mtsnmalang2adm@gmail.com</span>
                        </li>
                        <li>
                            <i class="fas fa-globe"></i>
                            <span>www.mtsn2kotamalang.sch.id</span>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp"></i>
                            <span>0851 8336 7500 (PTSP)</span>
                        </li>
                        <li>
                            <i class="fab fa-whatsapp"></i>
                            <span>0851 8337 5008 (Pengaduan)</span>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Jam Operasional</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Senin - Kamis</strong><br>
                                <span class="text-white">07.00 - 15.00 WIB</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Jumat</strong><br>
                                <span class="text-white">07.00 - 11.00 WIB</span>
                            </div>
                        </li>
                        <li>
                            <i class="fas fa-calendar-times"></i>
                            <div>
                                <strong>Sabtu - Minggu</strong><br>
                                <span class="text-white">Tutup</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-3 mb-4">
                    <h5 class="text-white mb-3">Link Terkait</h5>
                    <ul class="footer-links">
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kemenag.go.id" target="_blank" rel="noopener noreferrer">
                                Kementerian Agama RI
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kanwil.kemenag.go.id/jatim" target="_blank" rel="noopener noreferrer">
                                Kanwil Kemenag Jatim
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://kankemenag.malangkota.go.id" target="_blank" rel="noopener noreferrer">
                                Kemenag Kota Malang
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://lapor.go.id" target="_blank" rel="noopener noreferrer">
                                SP4N Lapor
                            </a>
                        </li>
                        <li>
                            <i class="fas fa-external-link-alt"></i>
                            <a href="https://ppid.kemenag.go.id" target="_blank" rel="noopener noreferrer">
                                PPID Kemenag
                            </a>
                        </li>
                    </ul>
                </div>

              </div>

            <hr class="my-4" style="border-color: var(--bs-gray-700);">

  
            <div class="row align-items-center">
                <div class="col-md-7">
                    <p class="mb-0">
                        <i class="fas fa-code-branch text-info me-2" style="font-size: 0.8rem;"></i>
                        <span class="me-3" style="font-size: 0.8rem; color: var(--text-on-dark-3);">v1.0.0</span>
                        &copy; {{ date('Y') }} {{ config('app.name_full', 'PTSP MTsN 2 Kota Malang') }}. Hak Cipta Dilindungi.
                    </p>
                </div>
                <div class="col-md-5 text-md-end">
                    <p class="mb-0">
                        <i class="fas fa-code me-1" style="font-size: 0.8rem;"></i> Dikembangkan dengan <i class="fas fa-heart text-danger mx-1" style="font-size: 0.8rem;"></i> oleh Tim PUSKOM
                    </p>
                </div>
            </div>
        </div>
    </footer>

    {{-- Scripts now loaded via Vite (bootstrap-bundle.js includes Bootstrap JS and AOS) --}}

    <!-- Enhanced JavaScript for accessibility, theme toggle, and interactions -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS with enhanced settings
            AOS.init({
                duration: 1200,
                once: true,
                offset: 120,
                easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                delay: 0,
                anchorPlacement: 'center-bottom'
            });

            // Custom AOS animations
            AOS.init({
                duration: 1000,
                easing: 'ease-out-quart',
                once: true,
                offset: 100,
                delay: 100
            });

            // Add smooth reveal for hero content
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                heroContent.classList.add('page-loading');
                setTimeout(() => {
                    heroContent.classList.remove('page-loading');
                    heroContent.classList.add('page-loaded');
                }, 100);
            }

            // Initialize theme and accessibility
            initializeTheme();
            initializeAccessibility();
            initializeInteractions();
            initializeAnimations();
            initializeKeyboardNavigation();
            initializeEnhancedAnimations();
            initializeFooterStats();
        });

        // Theme Management
        function initializeTheme() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeIcon = document.getElementById('theme-icon');

            setTheme(savedTheme);

            // Listen for system theme changes
            if (window.matchMedia) {
                const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
                darkModeQuery.addListener((e) => {
                    if (!localStorage.getItem('theme')) {
                        setTheme(e.matches ? 'dark' : 'light');
                    }
                });
            }
        }

        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
            localStorage.setItem('theme', newTheme);

            // Announce theme change for screen readers
            announceToScreenReader(`Tema diubah ke ${newTheme === 'dark' ? 'gelap' : 'terang'}`);
        }

        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            const themeIcon = document.getElementById('theme-icon');
            if (themeIcon) {
                themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }

        // Enhanced Accessibility Management - WCAG 2.1 AA Compliant
        function initializeAccessibility() {
            // Load all saved accessibility settings
            const savedFontSize = localStorage.getItem('fontSize') || 'normal';
            const highContrast = localStorage.getItem('highContrast') === 'true';
            const underlineLinks = localStorage.getItem('underlineLinks') === 'true';
            const lineHeight = localStorage.getItem('lineHeight') === 'true';
            const letterSpacing = localStorage.getItem('letterSpacing') === 'true';
            const largeCursor = localStorage.getItem('largeCursor') === 'true';
            const animationsDisabled = localStorage.getItem('animationsDisabled') === 'true';
            const grayscaleMode = localStorage.getItem('grayscaleMode') === 'true';
            const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
            const invertedColors = localStorage.getItem('invertedColors') === 'true';
            const enhancedKeyboardNav = localStorage.getItem('enhancedKeyboardNav') === 'true';
            const screenReaderMode = localStorage.getItem('screenReaderMode') === 'true';

            // Apply text settings
            document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');
            document.body.classList.add(`font-${savedFontSize}`);

            // Apply visual settings
            if (highContrast) {
                document.body.classList.add('high-contrast');
                updateToggleSwitch('highContrastControl', true);
            }

            if (underlineLinks) {
                document.body.classList.add('underline-links');
                updateToggleSwitch('underlineControl', true);
            }

            if (lineHeight) {
                document.body.classList.add('enhanced-line-height');
                updateToggleSwitch('lineHeightControl', true);
            }

            if (letterSpacing) {
                document.body.classList.add('enhanced-letter-spacing');
                updateToggleSwitch('letterSpacingControl', true);
            }

            if (largeCursor) {
                document.body.classList.add('large-cursor');
                updateToggleSwitch('cursorControl', true);
            }

            if (animationsDisabled) {
                // Disable animations
                const style = document.createElement('style');
                style.id = 'no-animations';
                style.textContent = `
                    *, *::before, *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                        scroll-behavior: auto !important;
                    }
                `;
                document.head.appendChild(style);
                updateToggleSwitch('animationControl', true);
            }

            // Apply color filters
            if (grayscaleMode) {
                document.body.classList.add('grayscale-mode');
            }

            if (blueLightFilter) {
                document.body.classList.add('blue-light-filter');
            }

            if (invertedColors) {
                document.body.classList.add('inverted-colors');
            }

            if (enhancedKeyboardNav) {
                document.body.classList.add('enhanced-keyboard-nav');
                // Add keyboard navigation styles
                const style = document.createElement('style');
                style.id = 'keyboard-nav-style';
                style.textContent = `
                    .enhanced-keyboard-nav *:focus {
                        outline: 3px solid #0066cc !important;
                        outline-offset: 2px !important;
                        background: rgba(0, 102, 204, 0.1) !important;
                    }
                `;
                document.head.appendChild(style);
            }

            if (screenReaderMode) {
                document.body.classList.add('screen-reader-mode');
                addSemanticAnnouncements();
            }

            // Update UI states
            updateAccessibilityButtonStates();
        }

        function updateToggleSwitch(controlId, isActive) {
            const control = document.getElementById(controlId);
            if (control) {
                control.classList.toggle('active', isActive);
                control.setAttribute('aria-checked', isActive);
            }
        }

        function updateAccessibilityButtonStates() {
            const savedFontSize = localStorage.getItem('fontSize') || 'normal';
            const highContrast = localStorage.getItem('highContrast') === 'true';
            const underlineLinks = localStorage.getItem('underlineLinks') === 'true';
            const lineHeight = localStorage.getItem('lineHeight') === 'true';
            const letterSpacing = localStorage.getItem('letterSpacing') === 'true';
            const largeCursor = localStorage.getItem('largeCursor') === 'true';
            const animationsDisabled = localStorage.getItem('animationsDisabled') === 'true';
            const grayscaleMode = localStorage.getItem('grayscaleMode') === 'true';
            const blueLightFilter = localStorage.getItem('blueLightFilter') === 'true';
            const invertedColors = localStorage.getItem('invertedColors') === 'true';
            const enhancedKeyboardNav = localStorage.getItem('enhancedKeyboardNav') === 'true';
            const screenReaderMode = localStorage.getItem('screenReaderMode') === 'true';

            // Update toggle switch states
            updateToggleSwitch('lineHeightControl', lineHeight);
            updateToggleSwitch('letterSpacingControl', letterSpacing);
            updateToggleSwitch('cursorControl', largeCursor);
            updateToggleSwitch('animationControl', animationsDisabled);
            updateToggleSwitch('underlineControl', underlineLinks);
            updateToggleSwitch('highContrastControl', highContrast);
            updateToggleSwitch('grayscaleControl', grayscaleMode);
            updateToggleSwitch('blueLightControl', blueLightFilter);
            updateToggleSwitch('invertControl', invertedColors);
            updateToggleSwitch('keyboardControl', enhancedKeyboardNav);
            updateToggleSwitch('screenReaderControl', screenReaderMode);

            // Update font size buttons
            document.querySelectorAll('[onclick*="setFontSize"]').forEach(btn => {
                btn.classList.remove('active');
            });
            const activeBtn = document.querySelector(`[onclick="setFontSize('${savedFontSize}')"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }

            // Update accessibility button state
            const accessibilityBtn = document.getElementById('accessibility-btn');
            if (accessibilityBtn) {
                const panel = document.getElementById('accessibilityPanel');
                if (panel && panel.classList.contains('open')) {
                    accessibilityBtn.innerHTML = '<i class="fas fa-times" style="line-height: 1;"></i>';
                    accessibilityBtn.classList.remove('pulse');
                } else {
                    accessibilityBtn.innerHTML = '<span style="line-height: 1;">♿</span>';
                    accessibilityBtn.classList.add('pulse');
                }
            }
        }

        function toggleFontSize(size) {
            // Remove all font size classes
            document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');

            // Add new font size class
            document.body.classList.add(`font-${size}`);
            localStorage.setItem('fontSize', size);

            // Update button states
            document.querySelectorAll('.accessibility-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Find and activate the clicked button
            const clickedBtn = document.querySelector(`[onclick="toggleFontSize('${size}')"]`);
            if (clickedBtn) {
                clickedBtn.classList.add('active');
            }

            announceToScreenReader(`Ukuran teks diubah ke ${getFontSizeDescription(size)}`);
        }

        function getFontSizeDescription(size) {
            const descriptions = {
                'small': 'kecil',
                'normal': 'normal',
                'large': 'besar',
                'extra-large': 'sangat besar'
            };
            return descriptions[size] || 'normal';
        }

        function toggleHighContrast() {
            const isHighContrast = document.body.classList.toggle('high-contrast');
            localStorage.setItem('highContrast', isHighContrast);

            // Update button state
            const contrastBtn = document.getElementById('highContrastControl');
            if (contrastBtn) {
                contrastBtn.classList.toggle('active', isHighContrast);
                contrastBtn.setAttribute('aria-checked', isHighContrast);
            }

            announceToScreenReader(`Mode kontras tinggi ${isHighContrast ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        function toggleUnderline() {
            const isUnderlined = document.body.classList.toggle('underline-links');
            localStorage.setItem('underlineLinks', isUnderlined);

            // Update button state
            const underlineBtn = document.getElementById('underlineControl');
            if (underlineBtn) {
                underlineBtn.classList.toggle('active', isUnderlined);
                underlineBtn.setAttribute('aria-checked', isUnderlined);
            }

            announceToScreenReader(`Garis bawah link ${isUnderlined ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Screen Reader Announcements
        function announceToScreenReader(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('role', 'status');
            announcement.setAttribute('aria-live', 'polite');
            announcement.className = 'sr-only';
            announcement.textContent = message;

            document.body.appendChild(announcement);

            setTimeout(() => {
                document.body.removeChild(announcement);
            }, 1000);
        }

        // Enhanced Animation Functions
        function initializeEnhancedAnimations() {
            // Add parallax effect to background elements
            const bgElements = document.querySelectorAll('.hero-bg-element');
            bgElements.forEach(element => {
                element.classList.add('hero-bg-element');
            });

            // Enhanced mouse tracking for subtle effects
            document.addEventListener('mousemove', (e) => {
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;

                // Subtle parallax for hero elements
                const heroContent = document.querySelector('.hero-content');
                if (heroContent) {
                    const offsetX = (mouseX - 0.5) * 10;
                    const offsetY = (mouseY - 0.5) * 10;
                    heroContent.style.transform = `translateX(${offsetX}px) translateY(${offsetY}px)`;
                }

                // Parallax for background bubbles
                bgElements.forEach((element, index) => {
                    const speed = (index + 1) * 0.5;
                    const offsetX = (mouseX - 0.5) * speed * 20;
                    const offsetY = (mouseY - 0.5) * speed * 20;
                    element.style.transform = `translateX(${offsetX}px) translateY(${offsetY}px)`;
                });
            });

            // Add entrance animation to sections
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const sectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        entry.target.classList.add('section-revealed');
                    }
                });
            }, observerOptions);

            // Observe all sections for entrance animations
            document.querySelectorAll('section').forEach(section => {
                section.style.opacity = '0';
                section.style.transform = 'translateY(30px)';
                section.style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';
                sectionObserver.observe(section);
            });

            // Enhanced hover effects with magnetic cursor
            const buttons = document.querySelectorAll('.hero-btn-primary, .hero-btn-secondary');
            buttons.forEach(button => {
                button.addEventListener('mousemove', (e) => {
                    const rect = button.getBoundingClientRect();
                    const x = e.clientX - rect.left - rect.width / 2;
                    const y = e.clientY - rect.top - rect.height / 2;

                    button.style.transform = `translate(${x * 0.2}px, ${y * 0.2}px) scale(1.02)`;
                });

                button.addEventListener('mouseleave', () => {
                    button.style.transform = 'translateY(0) scale(1)';
                });
            });

            // Smooth reveal for info items
            const infoItems = document.querySelectorAll('.info-item');
            infoItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 1000 + (index * 100));
            });

            // Add typing effect for subtitle (optional enhancement)
            const subtitle = document.querySelector('.hero-subtitle');
            if (subtitle) {
                const text = subtitle.textContent;
                subtitle.textContent = '';
                subtitle.style.opacity = '1';

                let charIndex = 0;
                const typeInterval = setInterval(() => {
                    if (charIndex < text.length) {
                        subtitle.textContent += text[charIndex];
                        charIndex++;
                    } else {
                        clearInterval(typeInterval);
                    }
                }, 50);
            }

            // Initialize scroll-triggered animations
            initializeScrollAnimations();
        }

        function initializeScrollAnimations() {
            // Smooth scroll indicator animation
            const scrollIndicator = document.querySelector('[style*="animation: bounce"]');
            if (scrollIndicator) {
                window.addEventListener('scroll', () => {
                    const scrolled = window.pageYOffset;
                    const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
                    const scrollPercent = scrolled / maxScroll;

                    if (scrollPercent > 0.1) {
                        scrollIndicator.style.opacity = 1 - scrollPercent;
                    }
                });
            }

            // Remove parallax effect for hero section to prevent overlapping
            // Parallax effect was causing the hero section to overlap with other sections
        }

        // Footer Statistics Functions
        function initializeFooterStats() {
            // Simulate real-time statistics
            updateVisitorCount();
            updateActiveUsers();

            // Update statistics every 30 seconds
            setInterval(() => {
                updateActiveUsers();
            }, 30000);

            // Update visitor count every 5 minutes
            setInterval(() => {
                updateVisitorCount();
            }, 300000);
        }

        function updateVisitorCount() {
            const visitorElement = document.getElementById('visitorCount');
            if (visitorElement) {
                // Get current count or start with base number
                let currentCount = parseInt(visitorElement.textContent.replace(',', ''));
                if (isNaN(currentCount)) currentCount = 15234;

                // Simulate gradual increase
                const increment = Math.floor(Math.random() * 5) + 1;
                currentCount += increment;

                // Animate the counter
                animateCounter(visitorElement, currentCount - increment, currentCount, 1000);
            }
        }

        function updateActiveUsers() {
            const activeUsersElement = document.getElementById('activeUsers');
            if (activeUsersElement) {
                // Simulate realistic active user count
                const baseCount = 200;
                const variation = Math.floor(Math.random() * 100) - 50;
                const newCount = Math.max(baseCount + variation, 50);

                // Add animation class
                activeUsersElement.classList.add('animated');

                // Update the count
                animateCounter(activeUsersElement, parseInt(activeUsersElement.textContent), newCount, 800);

                // Remove animation class after animation completes
                setTimeout(() => {
                    activeUsersElement.classList.remove('animated');
                }, 1000);
            }
        }

        function animateCounter(element, start, end, duration) {
            const startTime = performance.now();
            const startValue = start;
            const endValue = end;
            const difference = endValue - startValue;

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);

                // Easing function for smooth animation
                const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                const currentValue = Math.floor(startValue + (difference * easeOutQuart));

                // Format number with commas
                element.textContent = currentValue.toLocaleString('id-ID');

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            }

            requestAnimationFrame(updateCounter);
        }

        // Enhanced Accessibility Functions - WCAG 2.1 AA Compliant

        // Keyboard Navigation Support
        document.addEventListener('keydown', function(e) {
            // ESC key to close panel
            if (e.key === 'Escape') {
                const panel = document.getElementById('accessibilityPanel');
                if (panel && panel.classList.contains('open')) {
                    toggleAccessibilitySidebar();
                }
            }

            // Alt + A to open accessibility panel
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                toggleAccessibilitySidebar();
            }
        });

        // Add keyboard support for toggle switches
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.accessibility-switch');
            toggles.forEach(toggle => {
                toggle.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Add keyboard support for accessibility buttons
            const accessibilityBtns = document.querySelectorAll('.accessibility-btn');
            accessibilityBtns.forEach(btn => {
                btn.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });
        });

        // Additional Accessibility Toggle Functions
        function toggleLineHeight() {
            const isLineHeight = document.body.classList.toggle('line-height');
            localStorage.setItem('lineHeight', isLineHeight);
            updateToggleSwitch('lineHeightControl', isLineHeight);
            announceToScreenReader(`Jarak baris ${isLineHeight ? 'ditingkatkan' : 'normal'}`);
        }

        function toggleLetterSpacing() {
            const isLetterSpacing = document.body.classList.toggle('letter-spacing');
            localStorage.setItem('letterSpacing', isLetterSpacing);
            updateToggleSwitch('letterSpacingControl', isLetterSpacing);
            announceToScreenReader(`Jarak huruf ${isLetterSpacing ? 'ditingkatkan' : 'normal'}`);
        }

        function toggleLargeCursor() {
            const isLargeCursor = document.body.classList.toggle('large-cursor');
            localStorage.setItem('largeCursor', isLargeCursor);
            updateToggleSwitch('cursorControl', isLargeCursor);
            announceToScreenReader(`Kursor besar ${isLargeCursor ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        function toggleAnimations() {
            const isAnimationsDisabled = document.body.classList.toggle('no-animations');
            localStorage.setItem('animationsDisabled', isAnimationsDisabled);
            updateToggleSwitch('animationControl', isAnimationsDisabled);
            announceToScreenReader(`Animasi ${isAnimationsDisabled ? 'dinonaktifkan' : 'diaktifkan'}`);
        }

        function toggleKeyboardNavigation() {
            const isEnhancedKeyboard = document.body.classList.toggle('enhanced-keyboard');
            localStorage.setItem('enhancedKeyboardNav', isEnhancedKeyboard);
            updateToggleSwitch('keyboardControl', isEnhancedKeyboard);
            announceToScreenReader(`Navigasi keyboard ditingkatkan ${isEnhancedKeyboard ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        function toggleScreenReaderMode() {
            const isScreenReader = document.body.classList.toggle('screen-reader-mode');
            localStorage.setItem('screenReaderMode', isScreenReader);
            updateToggleSwitch('screenReaderControl', isScreenReader);
            announceToScreenReader(`Mode screen reader ${isScreenReader ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        function resetAllAccessibility() {
            // Remove all accessibility classes
            document.body.classList.remove(
                'font-small', 'font-large', 'font-extra-large',
                'high-contrast', 'underline-links', 'line-height',
                'letter-spacing', 'large-cursor', 'no-animations',
                'enhanced-keyboard', 'screen-reader-mode'
            );

            // Reset font size to normal
            document.body.classList.add('font-normal');

            // Clear all localStorage items
            localStorage.removeItem('fontSize');
            localStorage.removeItem('highContrast');
            localStorage.removeItem('underlineLinks');
            localStorage.removeItem('lineHeight');
            localStorage.removeItem('letterSpacing');
            localStorage.removeItem('largeCursor');
            localStorage.removeItem('animationsDisabled');
            localStorage.removeItem('enhancedKeyboardNav');
            localStorage.removeItem('screenReaderMode');

            // Update all button states
            updateAccessibilityButtonStates();

            // Announce reset
            announceToScreenReader('Semua pengaturan aksesibilitas telah direset ke default');

            // Add visual feedback
            const resetBtn = document.querySelector('.accessibility-reset-btn');
            if (resetBtn) {
                resetBtn.style.background = '#16a34a';
                resetBtn.innerHTML = '<span>✓</span><span>Berhasil Direset!</span>';
                setTimeout(() => {
                    resetBtn.style.background = '';
                    resetBtn.innerHTML = '<span>🔄</span><span>Reset Semua Pengaturan</span>';
                }, 2000);
            }
        }

        // Enhanced Font Size Control
        function setFontSize(size) {
            // Remove all font size classes
            document.body.classList.remove('font-small', 'font-normal', 'font-large', 'font-extra-large');

            // Add new font size class
            document.body.classList.add(`font-${size}`);
            localStorage.setItem('fontSize', size);

            // Update button states
            document.querySelectorAll('.font-size-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Find and activate the clicked button
            const clickedBtn = document.querySelector(`[onclick="setFontSize('${size}')"]`);
            if (clickedBtn) {
                clickedBtn.classList.add('active');
            }

            announceToScreenReader(`Ukuran teks diubah ke ${getFontSizeDescription(size)}`);
        }

        function getFontSizeDescription(size) {
            const descriptions = {
                'small': 'kecil',
                'normal': 'normal',
                'large': 'besar',
                'extra-large': 'sangat besar'
            };
            return descriptions[size] || 'normal';
        }

        // Line Height Control
        function toggleLineHeight() {
            const control = document.getElementById('lineHeightControl');
            const isActive = control.classList.contains('active');

            document.body.classList.toggle('enhanced-line-height');
            control.classList.toggle('active');
            control.setAttribute('aria-checked', !isActive);
            localStorage.setItem('lineHeight', !isActive);

            announceToScreenReader(`Jarak baris ${!isActive ? 'ditingkatkan' : 'normal'}`);
        }

        // Letter Spacing Control
        function toggleLetterSpacing() {
            const control = document.getElementById('letterSpacingControl');
            const isActive = control.classList.contains('active');

            document.body.classList.toggle('enhanced-letter-spacing');
            control.classList.toggle('active');
            control.setAttribute('aria-checked', !isActive);
            localStorage.setItem('letterSpacing', !isActive);

            announceToScreenReader(`Jarak huruf ${!isActive ? 'ditingkatkan' : 'normal'}`);
        }

        // Large Cursor Control
        function toggleLargeCursor() {
            const control = document.getElementById('cursorControl');
            const isActive = control.classList.contains('active');

            document.body.classList.toggle('large-cursor');
            control.classList.toggle('active');
            control.setAttribute('aria-checked', !isActive);
            localStorage.setItem('largeCursor', !isActive);

            announceToScreenReader(`Kursor besar ${!isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Animation Control
        function toggleAnimations() {
            const control = document.getElementById('animationControl');
            const isActive = control.classList.contains('active');

            if (!isActive) {
                // Disable animations
                const style = document.createElement('style');
                style.id = 'no-animations';
                style.textContent = `
                    *, *::before, *::after {
                        animation-duration: 0.01ms !important;
                        animation-iteration-count: 1 !important;
                        transition-duration: 0.01ms !important;
                        scroll-behavior: auto !important;
                    }
                `;
                document.head.appendChild(style);
            } else {
                // Enable animations
                const style = document.getElementById('no-animations');
                if (style) style.remove();
            }

            control.classList.toggle('active');
            control.setAttribute('aria-checked', !isActive);
            localStorage.setItem('animationsDisabled', !isActive);

            announceToScreenReader(`Animasi ${!isActive ? 'dihentikan' : 'diaktifkan'}`);
        }

        // Grayscale Mode
        function setGrayscale() {
            const isActive = document.body.classList.contains('grayscale-mode');

            document.body.classList.toggle('grayscale-mode');
            localStorage.setItem('grayscaleMode', !isActive);

            announceToScreenReader(`Mode grayscale ${!isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Blue Light Filter
        function setBlueLightFilter() {
            const isActive = document.body.classList.contains('blue-light-filter');

            document.body.classList.toggle('blue-light-filter');
            localStorage.setItem('blueLightFilter', !isActive);

            announceToScreenReader(`Filter cahaya biru ${!isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Inverted Colors
        function setInvertedColors() {
            const isActive = document.body.classList.contains('inverted-colors');

            document.body.classList.toggle('inverted-colors');
            localStorage.setItem('invertedColors', !isActive);

            announceToScreenReader(`Warna terbalik ${!isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Reading Guide
        function toggleReadingGuide() {
            const existingGuide = document.getElementById('reading-guide');

            if (existingGuide) {
                existingGuide.remove();
                announceToScreenReader('Panduan baca dinonaktifkan');
            } else {
                const guide = document.createElement('div');
                guide.id = 'reading-guide';
                guide.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 2px;
                    background: #ff6b6b;
                    z-index: 9999;
                    pointer-events: none;
                    box-shadow: 0 0 10px rgba(255, 107, 107, 0.8);
                `;
                document.body.appendChild(guide);

                // Follow mouse cursor
                document.addEventListener('mousemove', (e) => {
                    if (document.getElementById('reading-guide')) {
                        guide.style.top = e.clientY + 'px';
                    }
                });

                announceToScreenReader('Panduan baca diaktifkan. Gerakkan mouse untuk memandu mata Anda');
            }
        }

        // Focus to Main Content
        function focusToMainContent() {
            const mainContent = document.getElementById('main-content');
            if (mainContent) {
                mainContent.focus();
                mainContent.scrollIntoView({ behavior: 'smooth' });
                announceToScreenReader('Fokus dipindahkan ke konten utama');
            }
        }

        // Keyboard Navigation Enhancement
        function toggleKeyboardNavigation() {
            const isActive = document.body.classList.contains('enhanced-keyboard-nav');

            document.body.classList.toggle('enhanced-keyboard-nav');

            if (!isActive) {
                // Add focus indicators
                const style = document.createElement('style');
                style.id = 'keyboard-nav-style';
                style.textContent = `
                    .enhanced-keyboard-nav *:focus {
                        outline: 3px solid #0066cc !important;
                        outline-offset: 2px !important;
                        background: rgba(0, 102, 204, 0.1) !important;
                    }
                `;
                document.head.appendChild(style);
            } else {
                const style = document.getElementById('keyboard-nav-style');
                if (style) style.remove();
            }

            localStorage.setItem('enhancedKeyboardNav', !isActive);
            announceToScreenReader(`Navigasi keyboard ${!isActive ? 'ditingkatkan' : 'normal'}`);
        }

        // Screen Reader Mode
        function toggleScreenReaderMode() {
            const isActive = document.body.classList.contains('screen-reader-mode');

            document.body.classList.toggle('screen-reader-mode');
            localStorage.setItem('screenReaderMode', !isActive);

            if (!isActive) {
                // Add semantic announcements for screen readers
                addSemanticAnnouncements();
            } else {
                removeSemanticAnnouncements();
            }

            announceToScreenReader(`Mode screen reader ${!isActive ? 'diaktifkan' : 'dinonaktifkan'}`);
        }

        // Page Summary for Screen Readers
        function speakPageSummary() {
            const summary = `
                Halaman PTSP MTsN 2 KOTA MALANG.
                Ini adalah halaman utama layanan terpadu satu pintu.
                Bagian utama termasuk: slider hero, statistik kinerja, akses cepat, dan informasi layanan.
                Gunakan tombol Tab untuk navigasi atau panel aksesibilitas untuk pengaturan tambahan.
            `;

            announceToScreenReader(summary);
        }

        // Reset All Settings
        function resetAllAccessibilitySettings() {
            // Clear localStorage
            const accessibilityKeys = [
                'fontSize', 'highContrast', 'underlineLinks', 'lineHeight',
                'letterSpacing', 'largeCursor', 'animationsDisabled',
                'grayscaleMode', 'blueLightFilter', 'invertedColors',
                'enhancedKeyboardNav', 'screenReaderMode'
            ];

            accessibilityKeys.forEach(key => {
                localStorage.removeItem(key);
            });

            // Remove all classes
            document.body.className = 'font-normal';

            // Remove dynamic styles
            const dynamicStyles = ['no-animations', 'keyboard-nav-style'];
            dynamicStyles.forEach(id => {
                const style = document.getElementById(id);
                if (style) style.remove();
            });

            // Remove reading guide
            const readingGuide = document.getElementById('reading-guide');
            if (readingGuide) readingGuide.remove();

            // Reset all toggle switches
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.classList.remove('active');
                toggle.setAttribute('aria-checked', 'false');
            });

            // Reset font size buttons
            document.querySelectorAll('.font-size-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelector('[onclick="setFontSize(\'normal\')"]').classList.add('active');

            announceToScreenReader('Semua pengaturan aksesibilitas telah direset ke pengaturan default');
        }

        // Helper Functions
        function addSemanticAnnouncements() {
            // Add region labels for better screen reader navigation
            const main = document.querySelector('main');
            if (main) main.setAttribute('role', 'main');

            const header = document.querySelector('header');
            if (header) header.setAttribute('role', 'banner');

            const footer = document.querySelector('footer');
            if (footer) footer.setAttribute('role', 'contentinfo');
        }

        function removeSemanticAnnouncements() {
            // Remove additional role attributes if needed
        }

        // Initialize Interactions
        function initializeInteractions() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    const href = this.getAttribute('href');
                    if (href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });

                            // Close mobile menu if open
                            const navbarCollapse = document.querySelector('.navbar-collapse');
                            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                                navbarCollapse.classList.remove('show');
                            }

                            // Focus on target for accessibility
                            target.setAttribute('tabindex', '-1');
                            target.focus();
                        }
                    }
                });
            });

            // Add hover effects for cards
            document.querySelectorAll('.feature-card, .service-card, .stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });

                // Add keyboard interaction
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Add keyboard support for toggle switches
            document.querySelectorAll('.toggle-switch').forEach(toggle => {
                toggle.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Add keyboard support for accessibility sidebar
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const sidebar = document.getElementById('accessibilitySidebar');
                    if (sidebar && sidebar.classList.contains('open')) {
                        toggleAccessibilitySidebar();
                    }
                }
            });
        }

        // Initialize Animations
        function initializeAnimations() {
            // Animate progress bars when they come into view
            const progressObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const progressBars = entry.target.querySelectorAll('.stat-progress-bar');
                        progressBars.forEach(bar => {
                            const width = bar.style.width;
                            bar.style.width = '0%';
                            setTimeout(() => {
                                bar.style.width = width;
                            }, 200);
                        });

                        progressObserver.unobserve(entry.target);
                    }
                });
            });

            document.querySelectorAll('.stat-card').forEach(card => {
                progressObserver.observe(card);
            });
        }

        // Keyboard Navigation
        function initializeKeyboardNavigation() {
            // Focus management
            document.addEventListener('keydown', function(e) {
                // ESC key to close mobile menu
                if (e.key === 'Escape') {
                    const navbarCollapse = document.querySelector('.navbar-collapse');
                    if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.remove('show');
                        document.querySelector('.navbar-toggler').focus();
                    }
                }
            });

            // Skip link functionality
            const skipLink = document.querySelector('.skip-link');
            if (skipLink) {
                skipLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector('#main-content');
                    if (target) {
                        target.setAttribute('tabindex', '-1');
                        target.focus();
                        target.scrollIntoView();
                    }
                });
            }
        }

        // Performance monitoring
        if (window.performance && window.performance.timing && window.performance.timing.loadEventEnd > 0) {
            window.addEventListener('load', function() {
                const loadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
                if (loadTime > 0) {
                    console.log(`Page load time: ${loadTime}ms`);

                    // Log performance metrics for monitoring
                    if (window.gtag) {
                        gtag('event', 'page_load_time', {
                            value: loadTime,
                            custom_parameter: 'ptsp_page'
                        });
                    }
                }
            });
        }

              // Hero Slider Functionality
        let currentSlide = 0;
        let slides = [];
        let slideInterval;

        function initHeroSlider() {
            slides = document.querySelectorAll('.slide');
            if (slides.length <= 1) return;

            // Set initial active slide
            updateSlide(0);

            // Auto-play slider
            slideInterval = setInterval(nextSlide, 5000);

            // Pause on hover
            const heroSection = document.querySelector('.hero-section');
            if (heroSection) {
                heroSection.addEventListener('mouseenter', () => clearInterval(slideInterval));
                heroSection.addEventListener('mouseleave', () => {
                    slideInterval = setInterval(nextSlide, 5000);
                });
            }
        }

        function updateSlide(index) {
            // Remove active class from all slides
            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                const dot = document.querySelector(`[data-slide="${i}"]`);
                if (dot) dot.classList.remove('active');
            });

            // Add active class to current slide
            if (slides[index]) {
                slides[index].classList.add('active');
                const dot = document.querySelector(`[data-slide="${index}"]`);
                if (dot) dot.classList.add('active');

                // Apply custom colors from data attributes
                const textColor = slides[index].getAttribute('data-text-color');
                const overlayColor = slides[index].getAttribute('data-overlay-color');

                if (textColor) {
                    slides[index].querySelector('.hero-title').style.color = textColor;
                    slides[index].querySelector('.hero-subtitle').style.color = textColor;
                    slides[index].querySelector('.hero-description').style.color = textColor;
                }

                if (overlayColor) {
                    slides[index].querySelector('.overlay').style.background = overlayColor;
                }
            }

            currentSlide = index;
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            updateSlide(currentSlide);
        }

        function goToSlide(index) {
            if (index >= 0 && index < slides.length) {
                updateSlide(index);
                // Reset auto-play timer
                clearInterval(slideInterval);
                slideInterval = setInterval(nextSlide, 5000);
            }
        }

        // Initialize slider when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initHeroSlider, 100);
        });

        // Service Worker registration for PWA capabilities
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(error) {
                        console.log('ServiceWorker registration failed');
                    });
            });
        }

        // Multi-language Support
        const translations = {
            'ina': {
                'home': 'Beranda',
                'visitor-book': 'Buku Tamu',
                'services': 'Layanan',
                'about': 'Tentang',
                'contact': 'Kontak',
                'login': 'Login',
                'survey': 'Survei',
                'complaints': 'Pengaduan',
                'track-ticket': 'Lacak Tiket',
                'hero-title': 'Selamat Datang di PTSP MTsN 2 Kota Malang',
                'hero-subtitle': 'Pelayanan Terpadu Satu Pintu untuk Kemudahan dan Efisiensi Layanan Masyarakat',
                'services-title': 'Layanan Kami',
                'services-subtitle': 'Berbagai layanan yang tersedia untuk memenuhi kebutuhan masyarakat',
                'quick-access': 'Akses Cepat',
                'contact-title': 'Kontak Kami',
                'stats-visitors': 'Total Tamu Hari Ini',
                'stats-active': 'Sedang Aktif',
                'stats-date': 'Tanggal',
                'hours': 'Senin - Jumat: 07:30 - 15:00',
                'saturday': 'Sabtu: 07:30 - 12:00',
                'closed': 'Minggu & Libur: Tutup'
            },
            'eng': {
                'home': 'Home',
                'visitor-book': 'Visitor Book',
                'services': 'Services',
                'about': 'About',
                'contact': 'Contact',
                'login': 'Login',
                'survey': 'Survey',
                'complaints': 'Complaints',
                'track-ticket': 'Track Ticket',
                'hero-title': 'Welcome to PTSP MTsN 2 Kota Malang',
                'hero-subtitle': 'Integrated One-Stop Service for Convenience and Efficiency of Public Services',
                'services-title': 'Our Services',
                'services-subtitle': 'Various services available to meet community needs',
                'quick-access': 'Quick Access',
                'contact-title': 'Contact Us',
                'stats-visitors': 'Total Visitors Today',
                'stats-active': 'Currently Active',
                'stats-date': 'Date',
                'hours': 'Monday - Friday: 07:30 - 15:00',
                'saturday': 'Saturday: 07:30 - 12:00',
                'closed': 'Sunday & Holidays: Closed'
            },
            'arb': {
                'home': 'الرئيسية',
                'visitor-book': 'سجل الزوار',
                'services': 'الخدمات',
                'about': 'حول',
                'contact': 'اتصل',
                'login': 'تسجيل الدخول',
                'survey': 'استطلاع',
                'complaints': 'الشكاوى',
                'track-ticket': 'تتبع التذكرة',
                'hero-title': 'مرحبا بكم في PTSP MTsN 2 كوتا مالانج',
                'hero-subtitle': 'خدمة النافذة الواحدة المتكاملة لراحة وكفاءة الخدمات العامة',
                'services-title': 'خدماتنا',
                'services-subtitle': 'مختلف الخدمات المتاحة لتلبية احتياجات المجتمع',
                'quick-access': 'وصول سريع',
                'contact-title': 'اتصل بنا',
                'stats-visitors': 'إجمالي الزوار اليوم',
                'stats-active': 'نشط حاليا',
                'stats-date': 'التاريخ',
                'hours': 'الإثنين - الجمعة: 07:30 - 15:00',
                'saturday': 'السبت: 07:30 - 12:00',
                'closed': 'الأحد والعطلات: مغلق'
            }
        };

        // Initialize language on page load
        document.addEventListener('DOMContentLoaded', function() {
            const currentLang = localStorage.getItem('language') || 'ina';
            updateLanguage(currentLang);
        });

        // Change language function
        function changeLanguage(lang) {
            localStorage.setItem('language', lang);
            updateLanguage(lang);
        }

        function updateLanguage(lang) {
            // Validate language - default to 'ina' if invalid
            if (!translations[lang]) {
                console.warn(`Language '${lang}' not found, defaulting to 'ina'`);
                lang = 'ina';
                localStorage.setItem('language', 'ina');
            }

            // Update current language display
            const currentLangSpan = document.getElementById('currentLang');
            if (currentLangSpan) {
                currentLangSpan.textContent = lang.toUpperCase();
            }

            // Update RTL for Arabic
            const html = document.documentElement;
            if (lang === 'arb') {
                html.setAttribute('dir', 'rtl');
                html.setAttribute('lang', 'ar');
                document.body.classList.add('rtl');
            } else {
                html.setAttribute('dir', 'ltr');
                html.setAttribute('lang', lang === 'eng' ? 'en' : 'id');
                document.body.classList.remove('rtl');
            }

            // Update all elements with data-lang-key
            const elements = document.querySelectorAll('[data-lang-key]');
            elements.forEach(element => {
                const key = element.getAttribute('data-lang-key');
                if (translations[lang] && translations[lang][key]) {
                    const translation = translations[lang][key];
                    if (element.tagName === 'INPUT' && element.type === 'submit') {
                        element.value = translation;
                    } else {
                        element.textContent = translation;
                    }
                } else if (key) {
                    console.warn(`Translation key '${key}' not found for language '${lang}'`);
                }
            });
        }

        // Error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
        });

        // Floating Action Buttons Functionality
        function initFloatingButtons() {
            const backToTopBtn = document.getElementById('back-to-top-btn');
            
            // Show/hide back to top button based on scroll position
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopBtn.classList.add('visible');
                } else {
                    backToTopBtn.classList.remove('visible');
                }
            });
        }

        // Initialize floating buttons when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initFloatingButtons();
        });

        // Back to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Function to update current date and time
        function updateCurrentDateTime() {
            const now = new Date();
            const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const optionsTime = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            const formattedDate = now.toLocaleDateString('id-ID', optionsDate);
            const formattedTime = now.toLocaleTimeString('id-ID', optionsTime);
            const dateTimeElement = document.getElementById('current-datetime');
            if (dateTimeElement) {
                dateTimeElement.textContent = `${formattedDate}, ${formattedTime} WIB`;
            }
        }

        // Update date and time every second
        document.addEventListener('DOMContentLoaded', function() {
            updateCurrentDateTime();
            setInterval(updateCurrentDateTime, 1000);
        });
    </script>
</body>
</html>