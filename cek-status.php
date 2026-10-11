<?php
/**
 * Halaman Cek Status Laporan Bullying - KawalAman
 */

define('PAGE_TITLE', 'Lacak Status Laporan');
require_once __DIR__ . '/includes/header.php';

$ticketCode = trim($_GET['kode'] ?? '');
$report = null;
$logs = [];
$searched = false;
$notFound = false;

if (!empty($ticketCode)) {
    $searched = true;
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT r.*, u.name as handler_name 
            FROM reports r
            LEFT JOIN users u ON r.handled_by = u.id
            WHERE r.ticket_code = :ticket_code
            LIMIT 1
        ");
        $stmt->execute([':ticket_code' => $ticketCode]);
        $report = $stmt->fetch();

        if ($report) {
            // Ambil riwayat logs
            $logStmt = $pdo->prepare("
                SELECT l.*, u.name as user_name 
                FROM report_logs l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.report_id = :report_id
                ORDER BY l.created_at ASC
            ");
            $logStmt->execute([':report_id' => $report['id']]);
            $logs = $logStmt->fetchAll();
        } else {
            $notFound = true;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}
?>

<div class="py-16 bg-slate-50 min-h-[80vh]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="text-center space-y-3 mb-10">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">Pelacakan Transparan</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Lacak Status Laporan Anda
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto">
                Masukkan Kode Tiket yang Anda terima saat mengirimkan laporan untuk memantau perkembangan investigasi tim Satgas.
            </p>
        </div>

        <!-- Search Form Card -->
        <div class="glass-card p-6 sm:p-8 rounded-3xl border border-slate-200/80 shadow-lg mb-10">
            <form action="cek-status.php" method="GET" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-grow">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="kode" value="<?= escapeHtml($ticketCode) ?>" placeholder="Contoh: KWL-20261011-XXXX" class="w-full pl-11 pr-4 py-3.5 rounded-2xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm uppercase tracking-wider font-mono font-semibold transition-all placeholder:normal-case placeholder:font-sans placeholder:font-normal" required>
                </div>
                <button type="submit" class="px-7 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/25 transition-all flex items-center justify-center gap-2 cursor-pointer whitespace-nowrap">
                    <span>Cari Laporan</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>
        </div>

        <!-- Result: Not Found -->
        <?php if ($searched && $notFound): ?>
            <div class="p-8 rounded-3xl bg-white border border-slate-200 text-center space-y-4 shadow-sm">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-slate-900">Laporan Tidak Ditemukan</h3>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Kode tiket <code class="font-mono font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded"><?= escapeHtml($ticketCode) ?></code> tidak cocok dengan laporan manapun di sistem. Pastikan penulisan kode sudah benar.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="cek-status.php" class="inline-block text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        Coba Cari Lagi &rarr;
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Result: Found -->
        <?php if ($report): ?>
            <div class="space-y-8">
                
                <!-- Status Summary Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                        <div>
                            <span class="text-xs font-mono font-bold text-indigo-600 tracking-wider block mb-1">
                                TIKET: <?= escapeHtml($report['ticket_code']) ?>
                            </span>
                            <h2 class="text-xl font-bold text-slate-900">
                                Laporan Kasus <?= ucfirst(escapeHtml($report['category'])) ?>
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Dikirim pada <?= formatDateIndonesia($report['created_at']) ?>
                            </p>
                        </div>
                        <div>
                            <?= renderStatusBadge($report['status']) ?>
                        </div>
                    </div>

                    <!-- Grid Info Ringkas -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Identitas Pelapor</span>
                            <span class="font-bold text-slate-800">
                                <?= $report['is_anonymous'] ? 'Dirahasiakan (Anonim)' : escapeHtml($report['reporter_name']) ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Tanggal Kejadian</span>
                            <span class="font-bold text-slate-800">
                                <?= formatDateIndonesia($report['incident_date'], false) ?>
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Petugas Penanggung Jawab</span>
                            <span class="font-bold text-slate-800">
                                <?= $report['handler_name'] ? escapeHtml($report['handler_name']) : 'Tim Satgas Siaga' ?>
                            </span>
                        </div>
                    </div>

                    <!-- Catatan Resmi Satgas (jika ada) -->
                    <?php if (!empty($report['satgas_notes'])): ?>
                        <div class="p-5 rounded-2xl bg-indigo-50/70 border border-indigo-200 space-y-2">
                            <div class="flex items-center gap-2 text-indigo-900 font-bold text-xs">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Pemberitahuan & Catatan Resmi dari Tim Satgas:</span>
                            </div>
                            <p class="text-xs text-indigo-950 leading-relaxed whitespace-pre-line pl-6">
                                <?= escapeHtml($report['satgas_notes']) ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline / Log Perkembangan -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Riwayat Penanganan Laporan</span>
                    </h3>

                    <?php if (empty($logs)): ?>
                        <p class="text-xs text-slate-500 italic">Belum ada catatan aktivitas tambahan.</p>
                    <?php else: ?>
                        <div class="relative pl-6 space-y-6 border-l-2 border-indigo-200">
                            <?php foreach ($logs as $index => $log): ?>
                                <div class="relative group">
                                    <!-- Dot -->
                                    <div class="absolute -left-[31px] top-0.5 w-4 h-4 rounded-full bg-indigo-600 border-4 border-white shadow-sm"></div>
                                    
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-slate-800">
                                                Status: <?= ucfirst(escapeHtml($log['status_after'])) ?>
                                            </span>
                                            <span class="text-[11px] text-slate-400">
                                                &bull; <?= formatDateIndonesia($log['created_at']) ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($log['notes'])): ?>
                                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">
                                                <?= escapeHtml($log['notes']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Call to action jika butuh bantuan lebih lanjut -->
                <div class="p-6 rounded-3xl bg-slate-900 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold">Mengalami Tekanan atau Butuh Perlindungan Darurat?</h4>
                        <p class="text-xs text-slate-300">Hubungi langsung hotline Satgas atau kunjungi ruang konseling.</p>
                    </div>
                    <a href="index.php#bantuan" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition-colors whitespace-nowrap">
                        Lihat Kontak Bantuan
                    </a>
                </div>

            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
