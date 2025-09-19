<?php
session_start();
include 'db.php';

// Jika belum login, arahkan ke halaman login
if (empty($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

// Jika sudah login, arahkan pengguna sesuai wilayah ke dashboard yang sesuai
$wilayah = $_SESSION['wilayah'] ?? '';
if ($wilayah === 'samiran') {
    header('Location: dashboard_samiran.php');
    exit;
} elseif ($wilayah === 'godean') {
    header('Location: dashboard_godean.php');
    exit;
} else {
    // default ke jogja
    header('Location: dashboard_jogja.php');
    exit;
}

?>