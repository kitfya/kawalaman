<?php
/**
 * Helper & Utility Functions - KawalAman
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

// Memulai session jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape teks untuk mencegah Cross-Site Scripting (XSS)
 */
function escapeHtml($str) {
    if ($str === null) return '';
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate atau ambil CSRF Token dari session
 */
function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render input hidden CSRF Token
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

/**
 * Verifikasi CSRF Token
 */
function verifyCsrfToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate Kode Tiket unik: KWL-YYYYMMDD-XXXX (contoh: KWL-20261011-8F3B)
 */
function generateTicketCode() {
    $datePart = date('Ymd');
    $randPart = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    return "KWL-{$datePart}-{$randPart}";
}

/**
 * Handle upload berkas bukti foto / video dengan validasi keamanan
 * 
 * @param array $fileData Array $_FILES['evidence']
 * @return array ['success' => bool, 'filename' => string|null, 'type' => 'image'|'video'|'none', 'error' => string|null]
 */
function handleEvidenceUpload($fileData) {
    // Jika tidak ada berkas yang diunggah
    if (!isset($fileData) || $fileData['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'success' => true,
            'filename' => null,
            'type' => 'none',
            'error' => null
        ];
    }

    // Periksa error umum upload
    if ($fileData['error'] !== UPLOAD_ERR_OK) {
        return [
            'success' => false,
            'filename' => null,
            'type' => 'none',
            'error' => 'Terjadi kesalahan saat mengunggah berkas (Kode error: ' . $fileData['error'] . ').'
        ];
    }

    // Validasi batas ukuran berkas
    if ($fileData['size'] > MAX_FILE_SIZE) {
        return [
            'success' => false,
            'filename' => null,
            'type' => 'none',
            'error' => 'Ukuran berkas melebihi batas maksimal 30MB.'
        ];
    }

    $originalName = $fileData['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Validasi ekstensi
    $isImage = in_array($ext, ALLOWED_IMAGE_EXT);
    $isVideo = in_array($ext, ALLOWED_VIDEO_EXT);

    if (!$isImage && !$isVideo) {
        return [
            'success' => false,
            'filename' => null,
            'type' => 'none',
            'error' => 'Format berkas tidak didukung. Harap unggah foto (JPG, PNG, WEBP) atau video (MP4, WEBM, MOV).'
        ];
    }

    // Validasi MIME Type secara riil di file sementara
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $fileData['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, ALLOWED_MIME_TYPES)) {
        return [
            'success' => false,
            'filename' => null,
            'type' => 'none',
            'error' => 'Tipe berkas tidak valid atau berbahaya (MIME: ' . $mimeType . ').'
        ];
    }

    // Tentukan tipe
    $evidenceType = $isImage ? 'image' : 'video';

    // Buat nama berkas acak yang aman
    $targetDir = UPLOADS_PATH;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $newFilename = 'bukti_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $newFilename;

    if (!move_uploaded_file($fileData['tmp_name'], $targetPath)) {
        return [
            'success' => false,
            'filename' => null,
            'type' => 'none',
            'error' => 'Gagal memindahkan berkas yang diunggah ke direktori server.'
        ];
    }

    return [
        'success' => true,
        'filename' => $newFilename,
        'type' => $evidenceType,
        'error' => null
    ];
}

/**
 * Format tanggal ke Bahasa Indonesia (Contoh: 11 Oktober 2026, 09:30 WIB)
 */
function formatDateIndonesia($datetimeStr, $withTime = true) {
    if (empty($datetimeStr)) return '-';
    $timestamp = strtotime($datetimeStr);
    if (!$timestamp) return $datetimeStr;

    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $hari = date('d', $timestamp);
    $bulan = $bulanIndo[(int)date('m', $timestamp)];
    $tahun = date('Y', $timestamp);

    if ($withTime) {
        $jam = date('H:i', $timestamp);
        return "{$hari} {$bulan} {$tahun}, {$jam} WIB";
    }
    return "{$hari} {$bulan} {$tahun}";
}

/**
 * Render badge HTML status laporan
 */
function renderStatusBadge($status) {
    switch (strtolower($status)) {
        case 'menunggu':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                Menunggu Verifikasi
            </span>';
        case 'diproses':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Sedang Diproses
            </span>';
        case 'selesai':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Selesai Ditangani
            </span>';
        case 'ditolak':
            return '<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-300">
                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                Ditolak
            </span>';
        default:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-300">' . escapeHtml(ucfirst($status)) . '</span>';
    }
}

/**
 * Render badge HTML kategori bullying
 */
function renderCategoryBadge($category) {
    switch (strtolower($category)) {
        case 'verbal':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 border border-purple-200">Perundungan Verbal</span>';
        case 'fisik':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">Perundungan Fisik</span>';
        case 'relasional':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200">Relasional / Pengucilan</span>';
        case 'cyber':
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-cyan-100 text-cyan-800 border border-cyan-200">Cyberbullying</span>';
        default:
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">' . escapeHtml(ucfirst($category)) . '</span>';
    }
}

/**
 * Flash Message Notification Helper
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function getFlashMessage() {
    if (!empty($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}
