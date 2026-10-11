<?php
/**
 * Konfigurasi Database PDO - KawalAman
 */

if (!defined('APP_INIT')) {
    define('APP_INIT', true);
}

// Konfigurasi Database
$dbHost = 'sql211.ezyro.com';
$dbPort = '3306';
$dbName = 'ezyro_43142953_kawalaman';
$dbUser = 'ezyro_43142953';
$dbPass = '09a218ea';

$pdo = null;

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Jika database belum dibuat, kita simpan pesan dan jangan die langsung agar setup.php bisa berjalan
    $dbError = $e->getMessage();
}

/**
 * Helper untuk mendapatkan instance koneksi PDO
 */
function getDbConnection() {
    global $pdo, $dbError;
    if ($pdo === null) {
        die("<h3>Koneksi Database Gagal</h3><p>Pastikan Anda telah menjalankan <code>database/setup.php</code> atau membuat database <code>db_kawalaman</code>.</p><p>Pesan error: " . htmlspecialchars($dbError ?? 'Tidak diketahui') . "</p>");
    }
    return $pdo;
}
