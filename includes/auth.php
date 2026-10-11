<?php
/**
 * Helper Otentikasi & Sesi Satgas - KawalAman
 */

require_once __DIR__ . '/functions.php';

/**
 * Cek apakah user satgas sudah login
 */
function isLoggedInSatgas() {
    return isset($_SESSION['satgas_user']) && !empty($_SESSION['satgas_user']['id']);
}

/**
 * Mendapatkan data satgas yang sedang login
 */
function getLoggedInSatgas() {
    return $_SESSION['satgas_user'] ?? null;
}

/**
 * Wajibkan login satgas, redirect ke login.php jika belum login
 */
function requireSatgasAuth() {
    if (!isLoggedInSatgas()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        setFlashMessage('error', 'Silakan login terlebih dahulu untuk mengakses panel Satgas.');
        header('Location: login.php');
        exit;
    }
}

/**
 * Proses login Satgas
 * 
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function attemptSatgasLogin($username, $password) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Username atau password tidak ditemukan.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Username atau password tidak cocok.'];
    }

    // Regenerasi session ID untuk mencegah session fixation
    session_regenerate_id(true);

    $_SESSION['satgas_user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'role' => $user['role']
    ];

    return ['success' => true, 'message' => 'Login berhasil. Selamat bertugas!'];
}

/**
 * Logout Satgas
 */
function logoutSatgas() {
    unset($_SESSION['satgas_user']);
    // Hapus cookie sesi jika perlu
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
