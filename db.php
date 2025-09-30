<?php
$host = 'localhost';  // Ganti sesuai host Anda
$username = 'admin';   // Ganti sesuai username database Anda
$password = 's0t0kudus';       // Ganti sesuai password database Anda
$dbname = 'billing_2'; // Ganti sesuai nama database Anda

// Koneksi ke database
$koneksi = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
