<?php
/**
 * Detail Laporan & Manajemen Kasus - Panel Satgas KawalAman
 */

define('PANEL_TITLE', 'Pemeriksaan & Tindak Lanjut Kasus');
require_once __DIR__ . '/includes/satgas_header.php';

$pdo = getDbConnection();
$reportId = (int)($_GET['id'] ?? 0);

if ($reportId <= 0) {
    setFlashMessage('error', 'ID laporan tidak valid.');
    header('Location: index.php');
    exit;
}

// 1. Handle Update Status & Catatan Satgas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Token keamanan tidak valid.');
    } else {
        $newStatus = trim($_POST['status'] ?? '');
        $newNotes = trim($_POST['satgas_notes'] ?? '');
        $logNotes = trim($_POST['log_notes'] ?? '');

        if (!in_array($newStatus, ['menunggu', 'diproses', 'selesai', 'ditolak'])) {
            setFlashMessage('error', 'Status laporan yang dipilih tidak valid.');
        } else {
            try {
                // Ambil status sebelumnya
                $currStmt = $pdo->prepare("SELECT status FROM reports WHERE id = :id LIMIT 1");
                $currStmt->execute([':id' => $reportId]);
                $curr = $currStmt->fetch();
                $statusBefore = $curr['status'] ?? null;

                // Update data laporan
                $updateStmt = $pdo->prepare("
                    UPDATE reports SET 
                        status = :status,
                        satgas_notes = :satgas_notes,
                        handled_by = :handled_by,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':status'       => $newStatus,
                    ':satgas_notes' => $newNotes,
                    ':handled_by'   => $currentUser['id'],
                    ':id'           => $reportId
                ]);

                // Simpan log audit
                $noteForLog = !empty($logNotes) ? $logNotes : "Status diperbarui menjadi " . ucfirst($newStatus) . " oleh " . $currentUser['name'];
                $logStmt = $pdo->prepare("
                    INSERT INTO report_logs (report_id, user_id, status_before, status_after, notes)
                    VALUES (:report_id, :user_id, :status_before, :status_after, :notes)
                ");
                $logStmt->execute([
                    ':report_id'     => $reportId,
                    ':user_id'       => $currentUser['id'],
                    ':status_before' => $statusBefore,
                    ':status_after'  => $newStatus,
                    ':notes'         => $noteForLog
                ]);

                setFlashMessage('success', 'Status dan catatan penanganan berhasil diperbarui!');
                header("Location: detail.php?id={$reportId}");
                exit;

            } catch (Exception $e) {
                setFlashMessage('error', 'Gagal memperbarui status: ' . $e->getMessage());
            }
        }
    }
}

// 2. Ambil data laporan
$stmt = $pdo->prepare("
    SELECT r.*, u.name as handler_name, u.username as handler_username 
    FROM reports r
    LEFT JOIN users u ON r.handled_by = u.id
    WHERE r.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $reportId]);
$report = $stmt->fetch();

if (!$report) {
    setFlashMessage('error', 'Laporan tidak ditemukan di database.');
    header('Location: index.php');
    exit;
}

// 3. Ambil log riwayat penanganan
$logStmt = $pdo->prepare("
    SELECT l.*, u.name as user_name 
    FROM report_logs l
    LEFT JOIN users u ON l.user_id = u.id
    WHERE l.report_id = :report_id
    ORDER BY l.created_at DESC
");
$logStmt->execute([':report_id' => $reportId]);
$logs = $logStmt->fetchAll();
?>

<div class="space-y-6">
    
    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <a href="index.php" class="p-2.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm" title="Kembali ke Dashboard">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200"><?= escapeHtml($report['ticket_code']) ?></span>
                    <?= renderStatusBadge($report['status']) ?>
                </div>
                <h2 class="text-xl font-bold text-slate-900 mt-1">Pemeriksaan Kasus <?= ucfirst(escapeHtml($report['category'])) ?></h2>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="cetak.php?id=<?= $report['id'] ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Berita Acara</span>
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT COLUMN: DETAIL INFORMASI & BUKTI (7 COLS) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Card 1: Data Pelapor & Kejadian -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400 pb-3 border-b border-slate-100">
                    Informasi Pelapor & Kejadian
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-slate-400 block font-medium">Nama Pelapor</span>
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-900 text-sm"><?= escapeHtml($report['reporter_name']) ?></span>
                            <?php if ($report['is_anonymous']): ?>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Anonim</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-slate-400 block font-medium">Email Pelapor</span>
                        <span class="font-bold text-slate-900 text-sm block"><?= escapeHtml($report['reporter_email']) ?></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-slate-400 block font-medium">Tanggal Kejadian</span>
                        <span class="font-bold text-slate-900 text-sm block"><?= formatDateIndonesia($report['incident_date'], false) ?></span>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                        <span class="text-slate-400 block font-medium">Lokasi / Platform</span>
                        <span class="font-bold text-slate-900 text-sm block"><?= escapeHtml($report['incident_location']) ?></span>
                    </div>
                </div>

                <!-- Kronologi Kejadian -->
                <div class="space-y-2">
                    <span class="text-xs font-bold text-slate-700 block">Kronologi Kejadian (Deskripsi):</span>
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                        <?= escapeHtml($report['incident_description']) ?>
                    </div>
                </div>
            </div>

            <!-- Card 2: Bukti Pelaporan (Media Viewer) -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider text-slate-400">
                        Berkas Bukti Pelaporan
                    </h3>
                    <span class="text-xs font-semibold text-slate-500">
                        Tipe: <?= strtoupper(escapeHtml($report['evidence_type'])) ?>
                    </span>
                </div>

                <?php if ($report['evidence_type'] === 'image' && !empty($report['evidence_file'])): ?>
                    <?php $filePath = '../uploads/evidence/' . $report['evidence_file']; ?>
                    <div class="space-y-4">
                        <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 text-center">
                            <img src="<?= escapeHtml($filePath) ?>" alt="Bukti Foto" class="max-h-96 mx-auto object-contain">
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-slate-500"><?= escapeHtml($report['evidence_file']) ?></span>
                            <a href="<?= escapeHtml($filePath) ?>" download class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh Foto Bukti</span>
                            </a>
                        </div>
                    </div>

                <?php elseif ($report['evidence_type'] === 'video' && !empty($report['evidence_file'])): ?>
                    <?php $filePath = '../uploads/evidence/' . $report['evidence_file']; ?>
                    <div class="space-y-4">
                        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-black">
                            <video src="<?= escapeHtml($filePath) ?>" controls class="w-full max-h-96">
                                Browser Anda tidak mendukung pemutar video HTML5.
                            </video>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-xs text-slate-500"><?= escapeHtml($report['evidence_file']) ?></span>
                            <a href="<?= escapeHtml($filePath) ?>" download class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs border border-indigo-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh Video Bukti</span>
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="p-8 text-center rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-600">Tidak ada bukti berkas foto atau video yang diunggah.</p>
                        <p class="text-[11px] text-slate-400">Pelapor menyampaikan kejadian secara tertulis pada formulir kronologi.</p>
                    </div>
                <?php endif; ?>

            </div>

        </div>

        <!-- RIGHT COLUMN: MANAJEMEN STATUS & AUDIT LOG (5 COLS) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Form Pembaruan Status & Catatan Satgas -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">
                    Perbarui Status & Tindak Lanjut
                </h3>

                <form action="detail.php?id=<?= $report['id'] ?>" method="POST" class="space-y-4">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">

                    <div class="space-y-1.5">
                        <label for="status" class="block text-xs font-bold text-slate-700">Status Penanganan</label>
                        <select id="status" name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs bg-white font-medium" required>
                            <option value="menunggu" <?= $report['status'] === 'menunggu' ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                            <option value="diproses" <?= $report['status'] === 'diproses' ? 'selected' : '' ?>>Sedang Diproses (Investigasi & Mediasi)</option>
                            <option value="selesai" <?= $report['status'] === 'selesai' ? 'selected' : '' ?>>Selesai Ditangani</option>
                            <option value="ditolak" <?= $report['status'] === 'ditolak' ? 'selected' : '' ?>>Ditolak (Tidak Cukup Bukti / Palsu)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="satgas_notes" class="block text-xs font-bold text-slate-700">
                            Pemberitahuan Resmi Satgas
                            <span class="text-slate-400 font-normal ml-1">(Dapat dilihat oleh pelapor)</span>
                        </label>
                        <textarea id="satgas_notes" name="satgas_notes" rows="4" placeholder="Tuliskan catatan tindak lanjut yang dapat dilihat oleh pelapor saat mengecek status tiket..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs leading-relaxed"><?= escapeHtml($report['satgas_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="space-y-1.5">
                        <label for="log_notes" class="block text-xs font-bold text-slate-700">
                            Catatan Tindakan Internal Satgas
                            <span class="text-slate-400 font-normal ml-1">(Tersimpan ke log)</span>
                        </label>
                        <input type="text" id="log_notes" name="log_notes" placeholder="Contoh: Pemanggilan saksi dilakukan jam 10:00" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-xs">
                    </div>

                    <button type="submit" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/30 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Simpan Perubahan Penanganan</span>
                    </button>
                </form>
            </div>

            <!-- Riwayat Audit Log -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Tindakan & Audit</span>
                </h3>

                <?php if (empty($logs)): ?>
                    <p class="text-xs text-slate-400 italic">Belum ada riwayat aktivitas.</p>
                <?php else: ?>
                    <div class="relative pl-5 space-y-4 border-l-2 border-slate-200">
                        <?php foreach ($logs as $log): ?>
                            <div class="relative group text-xs">
                                <div class="absolute -left-[27px] top-1 w-3.5 h-3.5 rounded-full bg-indigo-600 border-2 border-white shadow-sm"></div>
                                
                                <div class="space-y-0.5">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-slate-800">
                                            <?= ucfirst(escapeHtml($log['status_after'])) ?>
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            <?= date('d M Y, H:i', strtotime($log['created_at'])) ?>
                                        </span>
                                    </div>
                                    <p class="text-slate-600 text-[11px] bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                        <?= escapeHtml($log['notes'] ?? '-') ?>
                                    </p>
                                    <span class="text-[10px] text-slate-400 block">
                                        Oleh: <?= $log['user_name'] ? escapeHtml($log['user_name']) : 'Sistem' ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . '/includes/satgas_footer.php'; ?>
