<?php
/**
 * Login Petugas Satgas - KawalAman
 */

require_once __DIR__ . '/../includes/auth.php';

// Jika sudah login, redirect langsung ke dashboard
if (isLoggedInSatgas()) {
    header('Location: index.php');
    exit;
}

$error = '';
$usernameVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error = 'Sesi formulir kedaluwarsa. Silakan refresh halaman dan coba lagi.';
    } else {
        $usernameVal = trim($_POST['username'] ?? '');
        $passwordVal = trim($_POST['password'] ?? '');

        if (empty($usernameVal) || empty($passwordVal)) {
            $error = 'Username dan password wajib diisi!';
        } else {
            $loginRes = attemptSatgasLogin($usernameVal, $passwordVal);
            if ($loginRes['success']) {
                $target = $_SESSION['redirect_after_login'] ?? 'index.php';
                unset($_SESSION['redirect_after_login']);
                setFlashMessage('success', $loginRes['message']);
                header("Location: {$target}");
                exit;
            } else {
                $error = $loginRes['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Panel Satgas - <?= APP_NAME ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%234f46e5'><path d='M12 2L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-3z'/></svg>">
</head>
<body class="bg-slate-900 text-slate-800 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background glow -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Login Card -->
        <div class="bg-white rounded-3xl p-8 sm:p-10 shadow-2xl border border-slate-100 space-y-6">
            
            <!-- Branding -->
            <div class="text-center space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center mx-auto shadow-lg shadow-indigo-600/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900">Portal Satgas</h2>
                    <p class="text-xs text-slate-500">KawalAman - Ruang Manajemen Pelaporan Kasus</p>
                </div>
            </div>

            <!-- Error Notification -->
            <?php if (!empty($error)): ?>
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-rose-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span><?= escapeHtml($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="login.php" method="POST" class="space-y-4">
                <?= csrfField() ?>

                <div class="space-y-1.5">
                    <label for="username" class="block text-xs font-bold text-slate-700">Username Petugas</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <input type="text" id="username" name="username" value="<?= escapeHtml($usernameVal) ?>" placeholder="Masukkan username Anda" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required autofocus>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required>
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer mt-2">
                    <span>Masuk ke Panel Satgas</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <!-- Akun Bawaan Helper -->
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 space-y-1">
                <span class="font-bold text-slate-700 block">Akun Bawaan Sistem:</span>
                <div class="flex justify-between items-center">
                    <span>Username: <code class="font-mono font-bold text-indigo-700">satgas</code></span>
                    <span>Password: <code class="font-mono font-bold text-indigo-700">satgas123</code></span>
                </div>
            </div>

            <!-- Back to Landing Page -->
            <div class="text-center pt-2">
                <a href="../index.php" class="text-xs text-slate-400 hover:text-slate-600 font-semibold inline-flex items-center gap-1">
                    &larr; Kembali ke Beranda Publik
                </a>
            </div>

        </div>

    </div>

</body>
</html>
