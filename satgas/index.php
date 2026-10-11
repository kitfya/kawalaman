<?php
/**
 * Dashboard & Manajemen Laporan - Panel Satgas KawalAman
 */

define('PANEL_TITLE', 'Dashboard Penanganan Kasus');
require_once __DIR__ . '/includes/satgas_header.php';

$pdo = getDbConnection();

// 1. Ambil Statistik Ringkasan
$stats = [
    'total'    => 0,
    'menunggu' => 0,
    'diproses' => 0,
    'selesai'  => 0,
    'ditolak'  => 0,
];

try {
    $statQuery = $pdo->query("
        SELECT status, COUNT(*) as count 
        FROM reports 
        GROUP BY status
    ");
    while ($row = $statQuery->fetch()) {
        $st = strtolower($row['status']);
        if (isset($stats[$st])) {
            $stats[$st] = (int)$row['count'];
        }
    }
    $stats['total'] = array_sum($stats);
} catch (Exception $e) {
    // Abaikan error stat
}

// 2. Filter & Pencarian
$statusFilter = trim($_GET['status'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$whereClauses = [];
$params = [];

if (!empty($statusFilter) && in_array($statusFilter, ['menunggu', 'diproses', 'selesai', 'ditolak'])) {
    $whereClauses[] = "r.status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($categoryFilter) && in_array($categoryFilter, ['verbal', 'fisik', 'relasional', 'cyber', 'lainnya'])) {
    $whereClauses[] = "r.category = :category";
    $params[':category'] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(r.ticket_code LIKE :q OR r.reporter_name LIKE :q OR r.reporter_email LIKE :q OR r.incident_location LIKE :q)";
    $params[':q'] = "%{$searchQuery}%";
}

$whereSql = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

// 3. Ambil Daftar Laporan
$reportsStmt = $pdo->prepare("
    SELECT r.*, u.name as handler_name 
    FROM reports r
    LEFT JOIN users u ON r.handled_by = u.id
    {$whereSql}
    ORDER BY r.created_at DESC
");
$reportsStmt->execute($params);
$reports = $reportsStmt->fetchAll();
?>

<div class="space-y-8">
    
    <!-- STATISTIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Laporan -->
        <a href="index.php" class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Total Laporan</span>
                <span class="text-3xl font-extrabold text-slate-900"><?= $stats['total'] ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
        </a>

        <!-- Menunggu -->
        <a href="index.php?status=menunggu" class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between <?= $statusFilter === 'menunggu' ? 'ring-2 ring-amber-500' : '' ?>">
            <div>
                <span class="text-xs font-bold text-amber-600 uppercase tracking-wider block mb-1">Menunggu Verifikasi</span>
                <span class="text-3xl font-extrabold text-amber-600"><?= $stats['menunggu'] ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </a>

        <!-- Sedang Diproses -->
        <a href="index.php?status=diproses" class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between <?= $statusFilter === 'diproses' ? 'ring-2 ring-blue-500' : '' ?>">
            <div>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-wider block mb-1">Sedang Ditangani</span>
                <span class="text-3xl font-extrabold text-blue-600"><?= $stats['diproses'] ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
        </a>

        <!-- Selesai -->
        <a href="index.php?status=selesai" class="p-6 rounded-3xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between <?= $statusFilter === 'selesai' ? 'ring-2 ring-emerald-500' : '' ?>">
            <div>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider block mb-1">Selesai / Teratasi</span>
                <span class="text-3xl font-extrabold text-emerald-600"><?= $stats['selesai'] ?></span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </a>

    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <form action="index.php" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
            
            <!-- Input Cari -->
            <div class="sm:col-span-5 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="q" value="<?= escapeHtml($searchQuery) ?>" placeholder="Cari kode tiket, nama pelapor, lokasi..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs transition-all">
            </div>

            <!-- Filter Status -->
            <div class="sm:col-span-3">
                <select name="status" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs bg-white">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= $statusFilter === 'menunggu' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="diproses" <?= $statusFilter === 'diproses' ? 'selected' : '' ?>>Sedang Diproses</option>
                    <option value="selesai" <?= $statusFilter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= $statusFilter === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div class="sm:col-span-3">
                <select name="category" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs bg-white">
                    <option value="">Semua Kategori</option>
                    <option value="verbal" <?= $categoryFilter === 'verbal' ? 'selected' : '' ?>>Perundungan Verbal</option>
                    <option value="fisik" <?= $categoryFilter === 'fisik' ? 'selected' : '' ?>>Perundungan Fisik</option>
                    <option value="relasional" <?= $categoryFilter === 'relasional' ? 'selected' : '' ?>>Relasional / Sosial</option>
                    <option value="cyber" <?= $categoryFilter === 'cyber' ? 'selected' : '' ?>>Cyberbullying</option>
                    <option value="lainnya" <?= $categoryFilter === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>

            <!-- Submit Button -->
            <div class="sm:col-span-1 flex gap-2">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors flex items-center justify-center cursor-pointer" title="Terapkan Filter">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                </button>
                <?php if (!empty($statusFilter) || !empty($categoryFilter) || !empty($searchQuery)): ?>
                    <a href="index.php" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors flex items-center justify-center" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- REPORT TABLE CARD -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                Daftar Laporan Kasus Bullying
                <span class="text-xs font-normal text-slate-500 ml-1">(<?= count($reports) ?> ditemukan)</span>
            </h3>
        </div>

        <?php if (empty($reports)): ?>
            <div class="p-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Tidak ada laporan yang sesuai kriteria</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Belum ada pengaduan baru atau filter pencarian tidak menemukan hasil.
                </p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-6">Kode Tiket</th>
                            <th class="py-3.5 px-6">Waktu Masuk</th>
                            <th class="py-3.5 px-6">Pelapor</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Bukti</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($reports as $item): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                
                                <!-- Kode Tiket -->
                                <td class="py-4 px-6 font-mono font-bold text-indigo-950">
                                    <?= escapeHtml($item['ticket_code']) ?>
                                </td>

                                <!-- Waktu Masuk -->
                                <td class="py-4 px-6 text-slate-600">
                                    <span class="block font-medium text-slate-800"><?= formatDateIndonesia($item['created_at']) ?></span>
                                    <span class="text-[11px] text-slate-400">Kejadian: <?= formatDateIndonesia($item['incident_date'], false) ?></span>
                                </td>

                                <!-- Pelapor -->
                                <td class="py-4 px-6">
                                    <?php if ($item['is_anonymous']): ?>
                                        <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Anonim
                                        </span>
                                    <?php else: ?>
                                        <span class="font-bold text-slate-900 block"><?= escapeHtml($item['reporter_name']) ?></span>
                                        <span class="text-[11px] text-slate-400"><?= escapeHtml($item['reporter_email']) ?></span>
                                    <?php endif; ?>
                                </td>

                                <!-- Kategori -->
                                <td class="py-4 px-6">
                                    <?= renderCategoryBadge($item['category']) ?>
                                </td>

                                <!-- Bukti -->
                                <td class="py-4 px-6">
                                    <?php if ($item['evidence_type'] === 'image'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Foto
                                        </span>
                                    <?php elseif ($item['evidence_type'] === 'video'): ?>
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                            Video
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] italic">Tidak ada</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-6">
                                    <?= renderStatusBadge($item['status']) ?>
                                </td>

                                <!-- Aksi -->
                                <td class="py-4 px-6 text-right">
                                    <a href="detail.php?id=<?= $item['id'] ?>" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors shadow-sm">
                                        <span>Periksa Kasus</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php require_once __DIR__ . '/includes/satgas_footer.php'; ?>
