<?php
$host = 'localhost';  // Ganti sesuai host Anda
$username = 'root';   // Ganti sesuai username database Anda
$password = '123';       // Ganti sesuai password database Anda
$dbname = 'billing_otw'; // Ganti sesuai nama database Anda

// Koneksi ke database
$koneksi = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
