<?php
$host = 'localhost';  // Ganti sesuai host Anda
$username = 'root';   // Ganti sesuai username database Anda
$password = '';       // Ganti sesuai password database Anda
$dbname = 'billing'; // Ganti sesuai nama database Anda

// Koneksi ke database
$koneksi = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>
