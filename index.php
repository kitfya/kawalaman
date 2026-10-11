<?php
/**
 * Landing Page & Form Pelaporan Bullying - KawalAman
 */

define('PAGE_TITLE', 'Pusat Edukasi & Pelaporan Bullying');
require_once __DIR__ . '/includes/header.php';

$errors = [];
$old = [];

// Proses Submit Laporan Bullying
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_report') {
    // 1. Verifikasi CSRF Token
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Sesi pengiriman formulir kedaluwarsa atau tidak valid. Silakan coba lagi.';
    }

    // 2. Ambil & Sanitasi Input
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $reporterName = trim($_POST['reporter_name'] ?? '');
    if ($isAnonymous) {
        $reporterName = 'Hamba Allah (Anonim)';
    }

    $reporterEmail = trim($_POST['reporter_email'] ?? '');
    $category = trim($_POST['category'] ?? 'verbal');
    $incidentDate = trim($_POST['incident_date'] ?? '');
    $incidentLocation = trim($_POST['incident_location'] ?? '');
    $incidentDescription = trim($_POST['incident_description'] ?? '');

    $old = [
        'reporter_name' => $reporterName,
        'is_anonymous' => $isAnonymous,
        'reporter_email' => $reporterEmail,
        'category' => $category,
        'incident_date' => $incidentDate,
        'incident_location' => $incidentLocation,
        'incident_description' => $incidentDescription,
    ];

    // 3. Validasi
    if (empty($reporterName)) {
        $errors[] = 'Nama pelapor wajib diisi (atau centang opsi Anonim).';
    }
    if (empty($reporterEmail) || !filter_var($reporterEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email pelapor wajib diisi dengan format email yang valid.';
    }
    if (!in_array($category, ['verbal', 'fisik', 'relasional', 'cyber', 'lainnya'])) {
        $errors[] = 'Kategori perundungan yang dipilih tidak valid.';
    }
    if (empty($incidentDate)) {
        $errors[] = 'Tanggal kejadian wajib diisi.';
    }
    if (empty($incidentLocation)) {
        $errors[] = 'Lokasi / media tempat kejadian wajib diisi.';
    }
    if (empty($incidentDescription) || strlen($incidentDescription) < 15) {
        $errors[] = 'Deskripsi kronologi kejadian wajib diisi minimal 15 karakter secara jelas.';
    }

    // 4. Proses Upload Bukti (Foto / Video)
    $uploadResult = ['success' => true, 'filename' => null, 'type' => 'none'];
    if (empty($errors) && isset($_FILES['evidence_file']) && $_FILES['evidence_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = handleEvidenceUpload($_FILES['evidence_file']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['error'];
        }
    }

    // 5. Simpan ke Database jika tidak ada error
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $ticketCode = generateTicketCode();

            $stmt = $pdo->prepare("
                INSERT INTO reports (
                    ticket_code, reporter_name, is_anonymous, reporter_email, 
                    category, incident_date, incident_location, incident_description, 
                    evidence_file, evidence_type, status
                ) VALUES (
                    :ticket_code, :reporter_name, :is_anonymous, :reporter_email, 
                    :category, :incident_date, :incident_location, :incident_description, 
                    :evidence_file, :evidence_type, 'menunggu'
                )
            ");

            $stmt->execute([
                ':ticket_code'          => $ticketCode,
                ':reporter_name'        => $reporterName,
                ':is_anonymous'         => $isAnonymous,
                ':reporter_email'       => $reporterEmail,
                ':category'             => $category,
                ':incident_date'        => $incidentDate,
                ':incident_location'    => $incidentLocation,
                ':incident_description' => $incidentDescription,
                ':evidence_file'        => $uploadResult['filename'],
                ':evidence_type'        => $uploadResult['type'],
            ]);

            $newReportId = $pdo->lastInsertId();

            // Catat log awal
            $logStmt = $pdo->prepare("
                INSERT INTO report_logs (report_id, user_id, status_before, status_after, notes)
                VALUES (:report_id, NULL, NULL, 'menunggu', 'Laporan baru berhasil dikirimkan oleh pelapor.')
            ");
            $logStmt->execute([':report_id' => $newReportId]);

            // Simpan detail sukses ke session untuk modal konfirmasi
            $_SESSION['report_success'] = [
                'ticket_code' => $ticketCode,
                'reporter_name' => $reporterName,
                'is_anonymous' => $isAnonymous,
                'created_at' => date('Y-m-d H:i:s'),
                'category' => $category
            ];

            // Redirect PRG pattern
            header("Location: index.php?lapor=sukses");
            exit;

        } catch (Exception $e) {
            $errors[] = 'Gagal menyimpan laporan ke database: ' . $e->getMessage();
        }
    }
}

// Cek apakah ada notifikasi sukses pelaporan
$reportSuccess = null;
if (isset($_GET['lapor']) && $_GET['lapor'] === 'sukses' && isset($_SESSION['report_success'])) {
    $reportSuccess = $_SESSION['report_success'];
    unset($_SESSION['report_success']); // Sekali tampil
}
?>

<!-- ============================================================== -->
<!-- HERO SECTION                                                   -->
<!-- ============================================================== -->
<section class="relative overflow-hidden bg-gradient-to-b from-indigo-950 via-slate-900 to-slate-900 text-white pt-20 pb-28">
    <!-- Decorative background glow -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 right-10 w-[350px] h-[350px] bg-rose-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
                <!-- Badge Hero -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-indigo-300 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span>Layanan Pengaduan & Perlindungan Terpadu</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight">
                    Jangan Diam. <br class="hidden sm:inline">
                    <span class="bg-gradient-to-r from-rose-400 via-pink-400 to-indigo-300 bg-clip-text text-transparent">
                        Hentikan Bullying Bersama.
                    </span>
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl leading-relaxed">
                    Setiap orang berhak merasa aman dan dihargai. KawalAman menyediakan ruang pelaporan yang rahasia, terpercaya, dan langsung ditindaklanjuti secara profesional oleh Satuan Tugas Anti-Perundungan.
                </p>

                <!-- Dual Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start pt-2">
                    <button type="button" class="btn-open-report w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-2xl text-base font-bold text-white bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-500 hover:to-rose-600 shadow-xl shadow-rose-600/30 hover:shadow-rose-600/40 hover:-translate-y-0.5 active:translate-y-0 transition-all cursor-pointer">
                        <svg class="w-5 h-5 text-rose-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Laporkan Pembullyan Sekarang
                    </button>

                    <a href="cek-status.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl text-base font-bold text-slate-200 bg-white/10 hover:bg-white/15 border border-white/20 transition-all">
                        <svg class="w-5 h-5 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Cek Status Laporan Saya
                    </a>
                </div>

                <!-- Trust Badges -->
                <div class="grid grid-cols-3 gap-4 pt-6 border-t border-slate-800/80 text-left">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-xs text-slate-300 font-medium">100% Rahasia & Anonim</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                        <span class="text-xs text-slate-300 font-medium">Bukti Foto / Video Aman</span>
                    </div>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <span class="text-xs text-slate-300 font-medium">Respon Cepat Satgas</span>
                    </div>
                </div>

            </div>

            <!-- Hero Illustration Card / Quick Tracker -->
            <div class="lg:col-span-5">
                <div class="glass-card rounded-3xl p-6 sm:p-8 border border-white/20 text-slate-800 shadow-2xl space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">Pantau Laporan Cepat</h3>
                                <p class="text-xs text-slate-500">Punya kode tiket dari laporan sebelumnya?</p>
                            </div>
                        </div>
                    </div>

                    <form action="cek-status.php" method="GET" class="space-y-3">
                        <label for="quick_ticket" class="block text-xs font-semibold text-slate-700">Masukkan Kode Tiket Pelaporan Anda:</label>
                        <div class="relative">
                            <input type="text" id="quick_ticket" name="kode" placeholder="Contoh: KWL-20261011-XXXX" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm uppercase tracking-wider font-mono font-semibold placeholder:normal-case placeholder:font-sans placeholder:font-normal" required>
                        </div>
                        <button type="submit" class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors flex items-center justify-center gap-2">
                            <span>Lacak Perkembangan Kasus</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>

                    <div class="bg-indigo-50/70 rounded-2xl p-4 border border-indigo-100 flex items-start gap-3">
                        <div class="mt-0.5 text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-xs text-indigo-900 leading-relaxed">
                            Laporan baru akan langsung masuk ke radar pantau <strong>Satuan Tugas</strong> dalam hitungan detik. Kami memprioritaskan keselamatan dan pemulihan mental korban.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================== -->
<!-- SECTION: EDUKASI APA ITU BULLYING                             -->
<!-- ============================================================== -->
<section id="edukasi" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-100 text-rose-700">Kenali & Pahami</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Apa Sebenarnya Perundungan (Bullying)?
            </h2>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                Bullying bukan sekadar candaan atau perselisihan biasa. Bullying adalah tindakan agresif yang disengaja dan berulang kali dilakukan oleh satu atau sekelompok orang yang memiliki kekuasaan atau kekuatan lebih, dengan tujuan menyakiti pihak yang lebih lemah.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 hover:shadow-xl hover:-translate-y-1 transition-all space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/30">
                    1
                </div>
                <h3 class="text-lg font-bold text-slate-900">Ketimpangan Kekuatan</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Pelaku memanfaatkan perbedaan fisik, popularitas, status sosial, senioritas, atau penguasaan teknologi untuk mengintimidasi atau menekan korban yang kesulitan membela diri.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 hover:shadow-xl hover:-translate-y-1 transition-all space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/30">
                    2
                </div>
                <h3 class="text-lg font-bold text-slate-900">Niat Jahat & Disengaja</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Perilaku dilakukan dengan kesadaran penuh untuk menimbulkan penderitaan, rasa takut, malu, sakit fisik, maupun keterasingan psikologis pada diri korban.
                </p>
            </div>

            <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-indigo-300 hover:shadow-xl hover:-translate-y-1 transition-all space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-indigo-600/30">
                    3
                </div>
                <h3 class="text-lg font-bold text-slate-900">Pola yang Berulang</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Tindakan ini cenderung terjadi lebih dari satu kali atau memiliki potensi tinggi untuk terus berulang seiring berjalannya waktu jika tidak ada intervensi tegas dari lingkungan sekitar.
                </p>
            </div>

        </div>

    </div>
</section>

<!-- ============================================================== -->
<!-- SECTION: 4 BENTUK PERUNDUNGAN (JENIS BULLYING)                -->
<!-- ============================================================== -->
<section id="jenis" class="py-24 bg-slate-50 border-t border-slate-200/80">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-100 text-indigo-700">Identifikasi Bentuk</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                4 Pilar Bentuk Bullying yang Sering Terjadi
            </h2>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                Penting untuk mengenali setiap jenis perundungan agar kita tidak menormalisasinya sebagai "sekadar candaan".
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- 1. Verbal -->
            <div class="bg-white rounded-3xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Perundungan Verbal</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Serangan menggunakan kata-kata kasar, sindiran menusuk, julukan merendahkan (labeling), fitnah, atau celaan fisik (*body shaming*).
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider block mb-1">Contoh Kasus:</span>
                        <ul class="text-xs text-slate-500 list-disc list-inside space-y-1">
                            <li>Memanggil dengan nama orang tua bernada mengejek</li>
                            <li>Mengolok bentuk tubuh atau pakaian</li>
                            <li>Menyebarkan gosip bohong di kelas</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2. Fisik -->
            <div class="bg-white rounded-3xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Perundungan Fisik</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Kekerasan yang melibatkan kontak fisik langsung atau perusakan barang pribadi korban yang menimbulkan cedera maupun kerugian materiil.
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider block mb-1">Contoh Kasus:</span>
                        <ul class="text-xs text-slate-500 list-disc list-inside space-y-1">
                            <li>Memukul, menendang, mencubit, atau menjegal</li>
                            <li>Memalak uang jajan / pemerasan</li>
                            <li>Merusak tas, buku, atau ponsel korban</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 3. Relasional / Sosial -->
            <div class="bg-white rounded-3xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Relasional / Pengucilan</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Upaya sistematis merusak reputasi sosial atau hubungan pertemanan korban dengan cara mengucilkan atau menghasut lingkungan.
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block mb-1">Contoh Kasus:</span>
                        <ul class="text-xs text-slate-500 list-disc list-inside space-y-1">
                            <li>Memboikot dan melarang orang lain berteman</li>
                            <li>Mengucilkan dari kelompok belajar</li>
                            <li>Membuat isyarat tubuh yang merendahkan di depan umum</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4. Cyberbullying -->
            <div class="bg-white rounded-3xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Cyberbullying</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Teror atau perundungan yang dilakukan di media sosial, grup percakapan digital, game online, atau forum internet.
                    </p>
                    <div class="pt-2">
                        <span class="text-[11px] font-semibold text-cyan-700 uppercase tracking-wider block mb-1">Contoh Kasus:</span>
                        <ul class="text-xs text-slate-500 list-disc list-inside space-y-1">
                            <li>Komentar kebencian atau pelecehan di postingan</li>
                            <li>Menyebarkan foto aib/privasi korban (*doxxing*)</li>
                            <li>Membuat akun palsu untuk menjatuhkan korban</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- ============================================================== -->
<!-- SECTION: ALUR PENANGANAN LAPORAN SATGAS                       -->
<!-- ============================================================== -->
<section id="alur" class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-3xl mx-auto space-y-4 mb-16">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-100 text-emerald-700">SOP & Transparansi</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                Bagaimana Laporan Anda Ditangani oleh Satgas?
            </h2>
            <p class="text-slate-600 text-sm sm:text-base leading-relaxed">
                Kami menerapkan alur kerja yang terukur dan berorientasi penuh pada perlindungan martabat serta keselamatan korban.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
            
            <!-- Step 1 -->
            <div class="space-y-4 text-center md:text-left">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-600 font-extrabold text-xl flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                    01
                </div>
                <h4 class="text-base font-bold text-slate-900">Pengiriman Laporan</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pelapor mengisi form pengaduan dan melampirkan bukti autentik (foto atau video). Sistem secara instan menerbitkan <strong>Kode Tiket</strong> pelacakan.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="space-y-4 text-center md:text-left">
                <div class="w-14 h-14 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 font-extrabold text-xl flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                    02
                </div>
                <h4 class="text-base font-bold text-slate-900">Verifikasi Tim Satgas</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Tim Satgas memeriksa validitas bukti, tingkat keparahan, dan menentukan langkah pengamanan darurat untuk korban dalam waktu maksimal 1x24 jam.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="space-y-4 text-center md:text-left">
                <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-200 text-blue-600 font-extrabold text-xl flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                    03
                </div>
                <h4 class="text-base font-bold text-slate-900">Investigasi & Mediasi</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Proses pendampingan konseling bagi korban, pemanggilan pihak terlapor/pelaku secara tertutup, serta koordinasi dengan pihak konselor atau wali.
                </p>
            </div>

            <!-- Step 4 -->
            <div class="space-y-4 text-center md:text-left">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 font-extrabold text-xl flex items-center justify-center mx-auto md:mx-0 shadow-sm">
                    04
                </div>
                <h4 class="text-base font-bold text-slate-900">Penyelesaian & Monitoring</h4>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Penerbitan sanksi edukatif bagi pelaku, jaminan pemulihan korban, penutupan status laporan, dan pemantauan berkala agar kasus tidak terulang.
                </p>
            </div>

        </div>

        <div class="mt-14 p-6 rounded-2xl bg-indigo-50/70 border border-indigo-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-indigo-950">Privasi Anda Terjaga Sepenuhnya</h5>
                    <p class="text-xs text-indigo-800">Identitas Anda tidak akan pernah dipublikasikan tanpa persetujuan tertulis dari Anda.</p>
                </div>
            </div>
            <button type="button" class="btn-open-report px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors whitespace-nowrap cursor-pointer">
                Buat Laporan Baru Sekarang
            </button>
        </div>

    </div>
</section>

<!-- ============================================================== -->
<!-- SECTION: KONTAK BANTUAN & HOTLINE DARURAT                      -->
<!-- ============================================================== -->
<section id="bantuan" class="py-20 bg-slate-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            
            <div class="lg:col-span-6 space-y-5">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">Butuh Bantuan Mendesak?</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Jika Sedang Dalam Bahaya Fisik Nyata, Jangan Ragu Hubungi Layanan Darurat
                </h2>
                <p class="text-sm text-slate-300 leading-relaxed">
                    KawalAman terhubung dengan jaringan penegak hukum dan konseling nasional. Layanan di bawah ini dapat dihubungi 24 jam sehari, 7 hari seminggu tanpa dipungut biaya pulsa.
                </p>
                <div class="pt-2">
                    <button type="button" class="btn-open-report inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg transition-all cursor-pointer">
                        Laporkan Lewat Formulir Satgas Kami
                    </button>
                </div>
            </div>

            <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition-all">
                    <span class="text-2xl font-black text-rose-400 block mb-1">129</span>
                    <h4 class="text-sm font-bold text-white mb-1">SAPA 129</h4>
                    <p class="text-xs text-slate-400 mb-3">Layanan Sahabat Perempuan dan Anak Kementerian PPPA.</p>
                    <span class="text-[11px] text-emerald-400 font-semibold">&bull; Bebas Pulsa 24 Jam</span>
                </div>

                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition-all">
                    <span class="text-2xl font-black text-blue-400 block mb-1">110</span>
                    <h4 class="text-sm font-bold text-white mb-1">Layanan Polisi 110</h4>
                    <p class="text-xs text-slate-400 mb-3">Panggilan tanggap darurat kepolisian di seluruh wilayah Republik Indonesia.</p>
                    <span class="text-[11px] text-emerald-400 font-semibold">&bull; Bebas Pulsa 24 Jam</span>
                </div>

                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition-all">
                    <span class="text-2xl font-black text-amber-400 block mb-1">119 ext. 8</span>
                    <h4 class="text-sm font-bold text-white mb-1">Sejiwa Kemenkes</h4>
                    <p class="text-xs text-slate-400 mb-3">Layanan pertolongan pertama kesehatan jiwa & pencegahan bunuh diri.</p>
                    <span class="text-[11px] text-emerald-400 font-semibold">&bull; Konseling Psikolog Resmi</span>
                </div>

                <div class="p-6 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition-all">
                    <span class="text-2xl font-black text-indigo-400 block mb-1">SATGAS</span>
                    <h4 class="text-sm font-bold text-white mb-1">Satgas Institusi</h4>
                    <p class="text-xs text-slate-400 mb-3">Konsultasi tatap muka langsung di ruang bimbingan konseling.</p>
                    <span class="text-[11px] text-indigo-300 font-semibold">&bull; Ruang Konseling Aman</span>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- ============================================================== -->
<!-- MODAL FORM: LAPORKAN PEMBULLYAN                                -->
<!-- ============================================================== -->
<div id="reportModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <!-- Backdrop Blur -->
    <div class="modal-backdrop fixed inset-0 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity"></div>

    <div class="flex min-h-full items-center justify-center p-4 sm:p-6 text-center">
        <div class="modal-content relative w-full max-w-2xl transform overflow-hidden rounded-3xl bg-white p-6 sm:p-8 text-left shadow-2xl opacity-0 scale-95 transition-all border border-slate-100">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Formulir Pelaporan Pembullyan</h3>
                        <p class="text-xs text-slate-500">Laporkan kejadian dengan jujur dan sertakan bukti yang Anda miliki</p>
                    </div>
                </div>
                <button type="button" class="btn-close-report p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Error Alerts (jika ada submit error) -->
            <?php if (!empty($errors)): ?>
                <div class="mt-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-rose-900">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        Mohon perbaiki kendala berikut:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        <?php foreach ($errors as $err): ?>
                            <li><?= escapeHtml($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="index.php" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="submit_report">

                <!-- Row 1: Nama & Opsi Anonim -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="reporter_name" class="block text-xs font-bold text-slate-700">Nama Pelapor <span class="text-rose-500">*</span></label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-indigo-600 cursor-pointer select-none">
                            <input type="checkbox" id="is_anonymous" name="is_anonymous" value="1" <?= !empty($old['is_anonymous']) ? 'checked' : '' ?> class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="font-semibold">Rahasiakan Nama Saya (Anonim)</span>
                        </label>
                    </div>
                    <input type="text" id="reporter_name" name="reporter_name" value="<?= escapeHtml($old['reporter_name'] ?? '') ?>" placeholder="Tuliskan nama lengkap Anda" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required>
                    <p class="text-[11px] text-slate-400">Jika mencentang opsi Anonim, nama Anda akan disamarkan sebagai Hamba Allah (Anonim).</p>
                </div>

                <!-- Row 2: Email Pelapor & Kategori -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="reporter_email" class="block text-xs font-bold text-slate-700">Email Pelapor <span class="text-rose-500">*</span></label>
                        <input type="email" id="reporter_email" name="reporter_email" value="<?= escapeHtml($old['reporter_email'] ?? '') ?>" placeholder="contoh@email.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required>
                        <p class="text-[11px] text-slate-400">Untuk konfirmasi status laporan dan respon resmi Satgas.</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="category" class="block text-xs font-bold text-slate-700">Jenis Perundungan <span class="text-rose-500">*</span></label>
                        <select id="category" name="category" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all bg-white" required>
                            <option value="verbal" <?= (!isset($old['category']) || $old['category'] === 'verbal') ? 'selected' : '' ?>>Perundungan Verbal (Ucapan/Hinaan)</option>
                            <option value="fisik" <?= (isset($old['category']) && $old['category'] === 'fisik') ? 'selected' : '' ?>>Perundungan Fisik (Kekerasan)</option>
                            <option value="relasional" <?= (isset($old['category']) && $old['category'] === 'relasional') ? 'selected' : '' ?>>Relasional / Pengucilan Sosial</option>
                            <option value="cyber" <?= (isset($old['category']) && $old['category'] === 'cyber') ? 'selected' : '' ?>>Cyberbullying (Media Online)</option>
                            <option value="lainnya" <?= (isset($old['category']) && $old['category'] === 'lainnya') ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                </div>

                <!-- Row 3: Tanggal & Lokasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="incident_date" class="block text-xs font-bold text-slate-700">Tanggal Kejadian <span class="text-rose-500">*</span></label>
                        <input type="date" id="incident_date" name="incident_date" value="<?= escapeHtml($old['incident_date'] ?? date('Y-m-d')) ?>" max="<?= date('Y-m-d') ?>" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required>
                    </div>

                    <div class="space-y-1.5">
                        <label for="incident_location" class="block text-xs font-bold text-slate-700">Lokasi / Platform Kejadian <span class="text-rose-500">*</span></label>
                        <input type="text" id="incident_location" name="incident_location" value="<?= escapeHtml($old['incident_location'] ?? '') ?>" placeholder="Misal: Kantin, Lapangan Olahraga, Grup WA" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all" required>
                    </div>
                </div>

                <!-- Row 4: Deskripsi Kronologi -->
                <div class="space-y-1.5">
                    <label for="incident_description" class="block text-xs font-bold text-slate-700">Kronologi Kejadian Secara Rinci <span class="text-rose-500">*</span></label>
                    <textarea id="incident_description" name="incident_description" rows="4" placeholder="Ceritakan apa yang terjadi, siapa saja pihak yang terlibat, bagaimana situasinya..." class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm transition-all leading-relaxed" required><?= escapeHtml($old['incident_description'] ?? '') ?></textarea>
                </div>

                <!-- Row 5: Unggah Bukti Pelaporan (Foto atau Video) -->
                <div class="space-y-2">
                    <label class="block text-xs font-bold text-slate-700">
                        Unggah Bukti Pelaporan (Foto atau Video)
                        <span class="font-normal text-slate-400 ml-1">(Opsional namun sangat disarankan)</span>
                    </label>

                    <!-- Dropzone -->
                    <div id="evidence_dropzone" class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-2xl p-6 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-indigo-50/20">
                        <input type="file" id="evidence_file" name="evidence_file" accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.mov" class="hidden">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            </div>
                            <div class="text-xs text-slate-600 font-medium">
                                <span class="font-bold text-indigo-600 hover:underline">Klik untuk memilih berkas</span> atau seret foto/video ke sini
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Format Foto: JPG, PNG, WEBP &bull; Video: MP4, WEBM, MOV (Maksimal 30 MB)
                            </p>
                        </div>
                    </div>

                    <!-- File Preview Box (Hidden by default) -->
                    <div id="file_preview_container" class="hidden p-4 rounded-2xl bg-indigo-50/60 border border-indigo-200 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <!-- Image Thumbnail Preview -->
                            <img id="preview_image" src="" alt="Preview Bukti" class="w-14 h-14 object-cover rounded-xl border border-indigo-200 hidden flex-shrink-0">
                            <!-- Video Preview Player -->
                            <video id="preview_video" src="" controls class="w-24 h-14 rounded-xl border border-indigo-200 hidden flex-shrink-0 bg-black"></video>
                            
                            <div class="truncate">
                                <span id="preview_filename" class="text-xs font-bold text-slate-800 block truncate">nama_file.jpg</span>
                                <span id="preview_filesize" class="text-[11px] text-slate-500">2.5 MB</span>
                            </div>
                        </div>
                        <button type="button" id="btn_remove_file" class="p-2 rounded-xl text-rose-500 hover:bg-rose-100 transition-colors flex-shrink-0" title="Hapus Berkas">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" class="btn-close-report px-5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold shadow-md shadow-rose-600/30 hover:shadow-lg transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Kirimkan Laporan Ini
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL SUKSES: MENAMPILKAN KODE TIKET                           -->
<!-- ============================================================== -->
<?php if ($reportSuccess): ?>
<div id="successModal" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

    <div class="flex min-h-full items-center justify-center p-4 text-center">
        <div class="relative w-full max-w-lg transform overflow-hidden rounded-3xl bg-white p-6 sm:p-8 text-center shadow-2xl border border-slate-100 space-y-6">
            
            <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-extrabold text-slate-900">Laporan Berhasil Terkirim!</h3>
                <p class="text-xs text-slate-600 leading-relaxed max-w-sm mx-auto">
                    Terima kasih atas keberanian Anda melapor. Tim Satgas telah menerima berkas pengaduan Anda dan akan segera memverifikasinya.
                </p>
            </div>

            <!-- Ticket Code Box -->
            <div class="bg-indigo-50/80 p-5 rounded-2xl border border-indigo-200 space-y-3">
                <span class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider block">KODE TIKET PELAPORAN ANDA</span>
                <div class="text-2xl sm:text-3xl font-black font-mono text-indigo-950 tracking-wider">
                    <?= escapeHtml($reportSuccess['ticket_code']) ?>
                </div>
                <p class="text-[11px] text-slate-500">
                    Simpan kode tiket ini untuk memantau status perkembangan penanganan kasus Anda.
                </p>
                <button type="button" onclick="copyTicketCode('<?= escapeHtml($reportSuccess['ticket_code']) ?>', this)" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-indigo-300 text-xs font-bold text-indigo-700 hover:bg-indigo-50 shadow-sm transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Salin Kode Tiket</span>
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                <a href="cek-status.php?kode=<?= urlencode($reportSuccess['ticket_code']) ?>" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-colors">
                    Lihat Status Laporan
                </a>
                <a href="index.php" class="w-full py-3 px-4 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors">
                    Kembali ke Beranda
                </a>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<!-- Script auto open modal jika ada error saat submit form -->
<?php if (!empty($errors)): ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('reportModal');
        if (modal) {
            modal.classList.remove('hidden');
            const backdrop = modal.querySelector('.modal-backdrop');
            const content = modal.querySelector('.modal-content');
            if (backdrop) backdrop.classList.remove('opacity-0');
            if (content) {
                content.classList.remove('opacity-0', 'scale-95');
                content.classList.add('opacity-100', 'scale-100');
            }
        }
    });
</script>
<?php endif; ?>

<!-- Script klik dropzone memicu input file -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('evidence_dropzone');
        const fileInput = document.getElementById('evidence_file');
        if (dropzone && fileInput) {
            dropzone.addEventListener('click', () => {
                fileInput.click();
            });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
