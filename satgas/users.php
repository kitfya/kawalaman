<?php
/**
 * Manajemen Anggota Satgas - Panel Satgas KawalAman
 */

define('PANEL_TITLE', 'Manajemen Anggota Satgas');
require_once __DIR__ . '/includes/satgas_header.php';

$pdo = getDbConnection();
$errors = [];

// 1. Tambah Anggota Satgas Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_user') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? 'satgas');

        if (empty($name)) $errors[] = 'Nama lengkap wajib diisi.';
        if (empty($username) || strlen($username) < 3) $errors[] = 'Username minimal 3 karakter tanpa spasi.';
        if (empty($password) || strlen($password) < 6) $errors[] = 'Kata sandi minimal 6 karakter.';
        if (!in_array($role, ['admin', 'satgas'])) $role = 'satgas';

        // Cek username unik
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            if ($stmt->fetch()) {
                $errors[] = "Username '{$username}' sudah digunakan oleh pengguna lain.";
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (:name, :username, :password, :role)");
                $ins->execute([
                    ':name'     => $name,
                    ':username' => $username,
                    ':password' => $hashed,
                    ':role'     => $role
                ]);
                setFlashMessage('success', "Anggota satgas baru '{$name}' berhasil ditambahkan!");
                header('Location: users.php');
                exit;
            }
        }
    }
}

// 2. Hapus Anggota (Kecuali diri sendiri)
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    if ($delId === (int)$currentUser['id']) {
        setFlashMessage('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
    } else {
        $delStmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $delStmt->execute([':id' => $delId]);
        setFlashMessage('success', 'Akun anggota berhasil dihapus.');
    }
    header('Location: users.php');
    exit;
}

// 3. Ambil Semua User
$users = $pdo->query("SELECT id, name, username, role, created_at FROM users ORDER BY id ASC")->fetchAll();
?>

<div class="space-y-8 max-w-6xl">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Daftar Anggota Satgas & Admin</h2>
            <p class="text-xs text-slate-500">Kelola akun petugas yang memiliki wewenang memverifikasi dan menangani laporan.</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <span class="font-bold block">Gagal menambahkan anggota:</span>
            <ul class="list-disc list-inside">
                <?php foreach ($errors as $err): ?>
                    <li><?= escapeHtml($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Tabel Anggota (8 COLS) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Semua Akun Petugas</h3>
                <span class="text-xs text-slate-400 font-semibold"><?= count($users) ?> Akun Terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-5">Nama Lengkap</th>
                            <th class="py-3 px-5">Username</th>
                            <th class="py-3 px-5">Peran</th>
                            <th class="py-3 px-5">Terdaftar Sejak</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="py-3.5 px-5 font-bold text-slate-900">
                                    <?= escapeHtml($u['name']) ?>
                                    <?php if ((int)$u['id'] === (int)$currentUser['id']): ?>
                                        <span class="text-[10px] text-indigo-600 font-normal ml-1">(Anda)</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-5 font-mono text-slate-600">
                                    @<?= escapeHtml($u['username']) ?>
                                </td>
                                <td class="py-3.5 px-5">
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">Admin</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Satgas</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-5 text-slate-400 text-[11px]">
                                    <?= date('d M Y', strtotime($u['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <?php if ((int)$u['id'] !== (int)$currentUser['id']): ?>
                                        <a href="users.php?delete_id=<?= $u['id'] ?>" onclick="return confirm('Hapus akun <?= escapeHtml($u['name']) ?>?')" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition-colors inline-block" title="Hapus Akun">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-[11px] text-slate-400 italic">Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Tambah Anggota (4 COLS) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-5">
            <div class="pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">Tambah Anggota Satgas Baru</h3>
                <p class="text-[11px] text-slate-400">Buat akun untuk rekan tim atau konselor</p>
            </div>

            <form action="users.php" method="POST" class="space-y-4 text-xs">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_user">

                <div class="space-y-1">
                    <label for="name" class="font-bold text-slate-700">Nama Lengkap</label>
                    <input type="text" id="name" name="name" placeholder="Misal: Budi Santoso, S.Psi" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="space-y-1">
                    <label for="username" class="font-bold text-slate-700">Username Login</label>
                    <input type="text" id="username" name="username" placeholder="misal: budi_satgas" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="space-y-1">
                    <label for="password" class="font-bold text-slate-700">Kata Sandi</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="space-y-1">
                    <label for="role" class="font-bold text-slate-700">Peran Akun</label>
                    <select id="role" name="role" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        <option value="satgas">Petugas Satgas</option>
                        <option value="admin">Administrator Utama</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition-all shadow-md shadow-indigo-600/20 cursor-pointer mt-2">
                    Tambah Petugas Satgas
                </button>
            </form>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/satgas_footer.php'; ?>
