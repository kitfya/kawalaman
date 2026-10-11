<?php
/**
 * Header Template Publik - KawalAman
 */
require_once __DIR__ . '/functions.php';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('PAGE_TITLE') ? PAGE_TITLE . ' - ' . APP_NAME : APP_NAME . ' | ' . APP_TAGLINE ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        },
                        danger: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            500: '#f43f5e',
                            600: '#e11d48',
                            700: '#be123c',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
    
    <!-- Favicon SVG (Shield/Protection Icon) -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234f46e5'><path d='M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3z'/></svg>">
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen antialiased selection:bg-indigo-500 selection:text-white">

    <!-- Flash Message Notification (jika ada) -->
    <?php if ($flash): ?>
        <div class="fixed top-5 right-5 z-50 max-w-md w-full animate-bounce">
            <div class="p-4 rounded-xl shadow-xl flex items-start gap-3 border <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : 'bg-rose-50 text-rose-900 border-rose-300' ?>">
                <div class="mt-0.5">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1 text-sm font-medium">
                    <?= escapeHtml($flash['message']) ?>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Navigation Header -->
    <header class="sticky top-0 z-40 glass-nav border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                <!-- Logo & Brand -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 to-indigo-950 bg-clip-text text-transparent"><?= APP_NAME ?></span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-indigo-100 text-indigo-700">Official</span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block">Layanan Pengaduan & Edukasi Terpadu</p>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="index.php#edukasi" class="hover:text-indigo-600 transition-colors">Edukasi Bullying</a>
                    <a href="index.php#jenis" class="hover:text-indigo-600 transition-colors">Bentuk Perundungan</a>
                    <a href="index.php#alur" class="hover:text-indigo-600 transition-colors">Alur Penanganan</a>
                    <a href="index.php#bantuan" class="hover:text-indigo-600 transition-colors">Kontak Bantuan</a>
                    <a href="cek-status.php" class="flex items-center gap-1.5 text-indigo-600 hover:text-indigo-700 font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Cek Laporan
                    </a>
                </nav>

                <!-- Actions: Button Laporkan & Satgas -->
                <div class="hidden sm:flex items-center gap-3">
                    <a href="satgas/login.php" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-all" title="Portal Masuk Petugas Satgas">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Portal Satgas
                    </a>
                    
                    <button type="button" class="btn-open-report inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-600 shadow-md shadow-rose-500/25 hover:shadow-lg hover:shadow-rose-500/35 hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer">
                        <svg class="w-4 h-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Laporkan Pembullyan
                    </button>
                </div>

                <!-- Mobile Hamburger Button -->
                <div class="flex items-center gap-2 sm:hidden">
                    <button type="button" class="btn-open-report px-3 py-2 rounded-lg text-xs font-bold text-white bg-rose-600 shadow-sm">
                        Lapor
                    </button>
                    <button id="mobileMenuBtn" type="button" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-3 shadow-lg">
            <a href="index.php#edukasi" class="block py-2 text-sm font-semibold text-slate-700 hover:text-indigo-600">Edukasi Bullying</a>
            <a href="index.php#jenis" class="block py-2 text-sm font-semibold text-slate-700 hover:text-indigo-600">Bentuk Perundungan</a>
            <a href="index.php#alur" class="block py-2 text-sm font-semibold text-slate-700 hover:text-indigo-600">Alur Penanganan</a>
            <a href="index.php#bantuan" class="block py-2 text-sm font-semibold text-slate-700 hover:text-indigo-600">Kontak Bantuan</a>
            <a href="cek-status.php" class="block py-2 text-sm font-bold text-indigo-600">Cek Status Laporan</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                <a href="satgas/login.php" class="text-center py-2 text-xs font-bold text-slate-600 bg-slate-100 rounded-lg">Masuk Panel Satgas</a>
            </div>
        </div>
    </header>

    <main class="flex-grow">
