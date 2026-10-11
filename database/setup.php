<?php
/**
 * Setup Script Otomatis Database & Direktori - KawalAman
 * Jalankan via CLI: php database/setup.php
 * Atau via browser: http://localhost:8000/database/setup.php
 */

$isCli = (php_sapi_name() === 'cli');

function outputMsg($msg, $isSuccess = true) {
    global $isCli;
    if ($isCli) {
        $prefix = $isSuccess ? "[OK] " : "[ERROR] ";
        echo $prefix . $msg . PHP_EOL;
    } else {
        $color = $isSuccess ? 'color: green;' : 'color: red;';
        echo "<div style='margin: 6px 0; font-family: monospace; {$color}'>" . htmlspecialchars($msg) . "</div>";
    }
}

// Konfigurasi Database
$dbHost = '127.0.0.1';
$dbPort = '3306';
$dbName = 'db_kawalaman';
$dbUser = 'root';
$dbPass = 'KitfyaDev@142808';

if (!$isCli) {
    echo "<!DOCTYPE html><html><head><title>Setup KawalAman</title></head><body style='padding: 24px; font-family: sans-serif; background: #f8fafc;'>";
    echo "<h2>Memulai Inisialisasi Database KawalAman...</h2><hr>";
}

try {
    // 1. Hubungkan ke MySQL server (tanpa db name)
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    outputMsg("Berhasil terhubung ke MySQL Server pada {$dbHost}:{$dbPort}.");

    // 2. Buat database jika belum ada
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    outputMsg("Database `{$dbName}` berhasil disiapkan.");

    // 3. Gunakan database tersebut
    $pdo->exec("USE `{$dbName}`;");

    // 4. Baca file schema.sql dan jalankan
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("File schema.sql tidak ditemukan di: {$schemaFile}");
    }

    $sqlContent = file_get_contents($schemaFile);
    $pdo->exec($sqlContent);
    outputMsg("Tabel users, reports, dan report_logs berhasil dibuat & akun default disiapkan.");

    // 5. Pastikan folder uploads/evidence ada dan writable
    $uploadDir = dirname(__DIR__) . '/uploads/evidence';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        outputMsg("Folder penyimpanan upload bukti dibuat di: uploads/evidence");
    } else {
        outputMsg("Folder uploads/evidence sudah ada.");
    }

    // 6. Buat .htaccess proteksi di folder uploads
    $htaccessFile = dirname(__DIR__) . '/uploads/.htaccess';
    $htaccessContent = "# Mencegah eksekusi file script berbahaya di folder uploads\n";
    $htaccessContent .= "<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|phps|sh|pl|cgi)$\">\n";
    $htaccessContent .= "    Order Deny,Allow\n";
    $htaccessContent .= "    Deny from all\n";
    $htaccessContent .= "</FilesMatch>\n";
    file_put_contents($htaccessFile, $htaccessContent);
    outputMsg("Proteksi keamanan .htaccess di folder uploads berhasil dibuat.");

    // 7. Buat index.html kosong di uploads untuk pencegahan directory indexing
    file_put_contents(dirname(__DIR__) . '/uploads/index.html', '<!DOCTYPE html><html><head><title>Access Denied</title></head><body><h1>403 Forbidden</h1></body></html>');

    outputMsg("SEMUA SETUP SELESAI DENGAN SUKSES!");
    outputMsg("Akun default Satgas: username 'satgas', password 'satgas123'");
    outputMsg("Akun default Admin: username 'admin', password 'admin123'");

} catch (Exception $e) {
    outputMsg("Gagal melakukan setup: " . $e->getMessage(), false);
}

if (!$isCli) {
    echo "<br><a href='../index.php' style='display:inline-block; padding: 10px 16px; background:#4f46e5; color:white; text-decoration:none; border-radius:6px;'>Kunjungi Landing Page</a> ";
    echo "<a href='../satgas/login.php' style='display:inline-block; padding: 10px 16px; background:#0f172a; color:white; text-decoration:none; border-radius:6px;'>Ke Panel Satgas</a>";
    echo "</body></html>";
}
