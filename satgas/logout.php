<?php
/**
 * Logout Petugas Satgas - KawalAman
 */

require_once __DIR__ . '/../includes/auth.php';

logoutSatgas();
header('Location: login.php');
exit;
