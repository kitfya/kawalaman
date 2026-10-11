<?php
/**
 * Konstanta Aplikasi - KawalAman
 */

if (!defined('APP_NAME')) {
    define('APP_NAME', 'KawalAman');
}
if (!defined('APP_TAGLINE')) {
    define('APP_TAGLINE', 'Ruang Aman untuk Bersuara, Lawan Bullying Bersama');
}

// Path direktori
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . '/uploads/evidence');
define('UPLOADS_URL', 'uploads/evidence');

// Batasan Upload Berkas Bukti
define('MAX_FILE_SIZE', 30 * 1024 * 1024); // Maksimal 30 Megabytes (cukup untuk video pendek & foto resolusi tinggi)
define('ALLOWED_IMAGE_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_VIDEO_EXT', ['mp4', 'webm', 'mov', 'mkv']);
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp',
    'video/mp4',
    'video/webm',
    'video/quicktime',
    'video/x-matroska'
]);
