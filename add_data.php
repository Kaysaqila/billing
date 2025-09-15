<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Pastikan hanya user yang login yang bisa akses
if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

// Pastikan metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak valid.']);
    exit;
}

// Ambil dan bersihkan data dari form
$id_pelanggan = $koneksi->real_escape_string($_POST['id_pelanggan']);
$nama = $koneksi->real_escape_string($_POST['nama']);
$paket = $koneksi->real_escape_string($_POST['paket']);
$nomor_pelanggan = $koneksi->real_escape_string($_POST['nomor_pelanggan']);
$tagihan = (float)$_POST['tagihan'];
$waktu = date('Y-m-d H:i:s'); // Waktu saat ini

// Validasi sederhana
if (empty($nama) || empty($paket)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan Paket wajib diisi.']);
    exit;
}

// Status bayar akan diatur otomatis oleh trigger database
// berdasarkan nilai tagihan. Jadi kita tidak perlu set di sini.

$sql = "INSERT INTO pelanggan (id_pelanggan, nama, paket, waktu, tagihan, nomor_pelanggan) 
        VALUES ('$id_pelanggan', '$nama', '$paket', '$waktu', '$tagihan', '$nomor_pelanggan')";

if ($koneksi->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $koneksi->error]);
}

$koneksi->close();
?>