<?php
/**
 * Header Layout Panel Satgas - KawalAman
 */

require_once __DIR__ . '/../../includes/auth.php';
requireSatgasAuth();

$currentUser = getLoggedInSatgas();
$flash = getFlashMessage();
$currentFile = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= defined('PANEL_TITLE') ? PANEL_TITLE . ' - Panel Satgas ' . APP_NAME : 'Panel Satgas - ' . APP_NAME ?></title>
    
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
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234f46e5'><path d='M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3z'/></svg>">
</head>
<body class="bg-slate-100 text-slate-800 antialiased flex min-h-screen">

    <!-- Flash Notification -->
    <?php if ($flash): ?>
        <div class="fixed top-5 right-5 z-50 max-w-md w-full animate-bounce">
            <div class="p-4 rounded-xl shadow-xl flex items-start gap-3 border <?= $flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-900 border-emerald-300' : 'bg-rose-50 text-rose-900 border-rose-300' ?>">
                <div class="flex-1 text-sm font-medium">
                    <?= escapeHtml($flash['message']) ?>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Sidebar Desktop -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex-shrink-0 flex flex-col justify-between hidden md:flex border-r border-slate-800">
        <div>
            <!-- Brand -->
            <div class="h-20 flex items-center px-6 gap-3 border-b border-slate-800">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-white"><?= APP_NAME ?></h2>
                    <span class="text-[10px] text-indigo-400 font-semibold uppercase tracking-wider block">Panel Satgas</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 text-xs font-semibold">
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?= $currentFile === 'index.php' && !isset($_GET['status']) ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Dashboard & Kasus</span>
                </a>

                <a href="index.php?status=menunggu" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?= isset($_GET['status']) && $_GET['status'] === 'menunggu' ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' ?>">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    <span>Kasus Baru (Menunggu)</span>
                </a>

                <a href="index.php?status=diproses" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?= isset($_GET['status']) && $_GET['status'] === 'diproses' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' ?>">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                    <span>Sedang Ditangani</span>
                </a>

                <a href="index.php?status=selesai" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?= isset($_GET['status']) && $_GET['status'] === 'selesai' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' ?>">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Kasus Selesai</span>
                </a>

                <div class="pt-4 pb-2 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    Pengaturan
                </div>

                <a href="users.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors <?= $currentFile === 'users.php' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Anggota Satgas</span>
                </a>
            </nav>
        </div>

        <!-- User Profile in Sidebar -->
        <div class="p-4 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-indigo-700 text-white flex items-center justify-center font-bold text-xs uppercase">
                    <?= substr($currentUser['name'], 0, 2) ?>
                </div>
                <div class="truncate">
                    <span class="text-xs font-bold text-white block truncate"><?= escapeHtml($currentUser['name']) ?></span>
                    <span class="text-[10px] text-slate-400">@<?= escapeHtml($currentUser['username']) ?> (<?= escapeHtml($currentUser['role']) ?>)</span>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar dari panel Satgas?')" class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 border border-rose-500/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar (Logout)</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        
        <!-- Topbar Navbar -->
        <header class="h-20 bg-white border-b border-slate-200 px-6 sm:px-8 flex items-center justify-between sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <!-- Mobile toggle button could be added here if needed -->
                <h1 class="text-lg font-bold text-slate-900">
                    <?= defined('PANEL_TITLE') ? PANEL_TITLE : 'Dashboard Penanganan Kasus' ?>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="../index.php" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition-colors" title="Lihat Website Publik">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Website Publik</span>
                </a>
            </div>
        </header>

        <!-- Page Content Wrapper -->
        <main class="p-6 sm:p-8 flex-1">
