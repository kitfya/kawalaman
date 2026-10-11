<?php
/**
 * Cetak Berita Acara Penanganan Bullying - Panel Satgas KawalAman
 */

require_once __DIR__ . '/../includes/auth.php';
requireSatgasAuth();

$pdo = getDbConnection();
$reportId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT r.*, u.name as handler_name, u.role as handler_role 
    FROM reports r
    LEFT JOIN users u ON r.handled_by = u.id
    WHERE r.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $reportId]);
$report = $stmt->fetch();

if (!$report) {
    die("Laporan tidak ditemukan.");
}

$currentUser = getLoggedInSatgas();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara - <?= escapeHtml($report['ticket_code']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/custom.css">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-border { border: 1px solid #333 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 antialiased">

    <!-- Action Toolbar (Hidden during print) -->
    <div class="no-print max-w-4xl mx-auto mb-6 flex items-center justify-between bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <a href="detail.php?id=<?= $report['id'] ?>" class="text-xs font-bold text-slate-600 hover:text-slate-900 inline-flex items-center gap-1">
            &larr; Kembali ke Detail Kasus
        </a>
        <button onclick="window.print()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            <span>Cetak Dokumen Resmi</span>
        </button>
    </div>

    <!-- Official Document Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-10 sm:p-14 rounded-2xl shadow-lg border border-slate-200 space-y-8">
        
        <!-- Kop Dokumen -->
        <div class="text-center pb-6 border-b-2 border-slate-900 space-y-1">
            <h1 class="text-xl font-black uppercase tracking-wider text-slate-900">
                SATUAN TUGAS PENCEGAHAN & PENANGANAN KEKERASAN DAN PERUNDUNGAN
            </h1>
            <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700">
                LAYANAN PENGADUAN TERPADU <?= strtoupper(APP_NAME) ?>
            </h2>
            <p class="text-[11px] text-slate-500">
                Sekretariat Satgas Anti-Bullying &bull; Email: satgas.aman@instansi.sch.id &bull; Hotline: 129
            </p>
        </div>

        <!-- Judul Berita Acara -->
        <div class="text-center space-y-1">
            <h3 class="text-base font-extrabold uppercase underline tracking-wider text-slate-900">
                BERITA ACARA PENANGANAN LAPORAN PERUNDUNGAN
            </h3>
            <p class="text-xs font-mono font-bold text-slate-600">
                NOMOR TIKET: <?= escapeHtml($report['ticket_code']) ?>
            </p>
        </div>

        <!-- Data Laporan -->
        <div class="space-y-3 text-xs leading-relaxed">
            <p>
                Pada hari ini, <strong><?= formatDateIndonesia(date('Y-m-d H:i:s')) ?></strong>, telah dicatatkan dan ditindaklanjuti dokumen pengaduan kasus perundungan (bullying) dengan data sebagai berikut:
            </p>

            <table class="w-full border-collapse border border-slate-300 text-xs">
                <tbody>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 w-44 border-r border-slate-300">Kode Tiket</td>
                        <td class="p-2.5 font-mono font-bold text-indigo-900"><?= escapeHtml($report['ticket_code']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Waktu Masuk Laporan</td>
                        <td class="p-2.5"><?= formatDateIndonesia($report['created_at']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Nama Pelapor</td>
                        <td class="p-2.5">
                            <?= escapeHtml($report['reporter_name']) ?>
                            <?= $report['is_anonymous'] ? '<em>(Permohonan Perlindungan Anonim)</em>' : '' ?>
                        </td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Email Pelapor</td>
                        <td class="p-2.5"><?= escapeHtml($report['reporter_email']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Kategori Perundungan</td>
                        <td class="p-2.5 font-semibold uppercase"><?= escapeHtml($report['category']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Tanggal & Lokasi Kejadian</td>
                        <td class="p-2.5"><?= formatDateIndonesia($report['incident_date'], false) ?> &mdash; di <?= escapeHtml($report['incident_location']) ?></td>
                    </tr>
                    <tr class="border-b border-slate-300">
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Status Penanganan</td>
                        <td class="p-2.5 font-bold uppercase"><?= escapeHtml($report['status']) ?></td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-bold bg-slate-50 border-r border-slate-300">Bukti yang Dilampirkan</td>
                        <td class="p-2.5">
                            <?php if ($report['evidence_type'] !== 'none'): ?>
                                Terlampir berkas <?= strtoupper(escapeHtml($report['evidence_type'])) ?> (<?= escapeHtml($report['evidence_file']) ?>)
                            <?php else: ?>
                                Tidak ada berkas digital terlampir.
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Kronologi Kasus -->
        <div class="space-y-2 text-xs leading-relaxed">
            <h4 class="font-bold uppercase tracking-wider text-slate-800">Uraian / Kronologi Kejadian:</h4>
            <div class="p-4 border border-slate-300 rounded-xl bg-slate-50/50 whitespace-pre-line text-justify">
                <?= escapeHtml($report['incident_description']) ?>
            </div>
        </div>

        <!-- Catatan & Tindak Lanjut Satgas -->
        <div class="space-y-2 text-xs leading-relaxed">
            <h4 class="font-bold uppercase tracking-wider text-slate-800">Catatan Tindak Lanjut & Rekomendasi Satgas:</h4>
            <div class="p-4 border border-slate-300 rounded-xl bg-slate-50/50 whitespace-pre-line text-justify">
                <?= !empty($report['satgas_notes']) ? escapeHtml($report['satgas_notes']) : 'Belum ada catatan tindak lanjut khusus yang dicantumkan.' ?>
            </div>
        </div>

        <!-- Kolom Tanda Tangan -->
        <div class="pt-8 grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-slate-500 mb-16">Petugas Pemeriksa Laporan,</p>
                <p class="font-bold text-slate-900 underline"><?= escapeHtml($report['handler_name'] ?? $currentUser['name']) ?></p>
                <p class="text-[11px] text-slate-500">Anggota Tim Satgas</p>
            </div>

            <div>
                <p class="text-slate-500 mb-16">Mengetahui,<br>Ketua Satgas Anti-Perundungan</p>
                <p class="font-bold text-slate-900 underline">(......................................................)</p>
                <p class="text-[11px] text-slate-500">NIP / Identitas Resmi</p>
            </div>
        </div>

    </div>

</body>
</html>
