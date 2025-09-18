<?php
header('Content-Type: application/json');
include 'db.php';
session_start();
if (!isset($_SESSION['login'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Deteksi tabel berdasarkan wilayah user
$table_name = 'pelanggan_jogja'; // default untuk jogja
if (isset($_SESSION['wilayah'])) {
    if ($_SESSION['wilayah'] === 'samiran') {
        $table_name = 'pelanggan_samiran';
    } elseif ($_SESSION['wilayah'] === 'godean') {
        $table_name = 'pelanggan_godean';
    }
}

// Total Pelanggan
$total_pelanggan_result = $koneksi->query("SELECT COUNT(*) as total FROM $table_name");
$total_pelanggan = $total_pelanggan_result->fetch_assoc()['total'];

// Statistik bulan ini
$bulan_ini = date('Y-m-01');
$query_bulan_ini = "SELECT status_bayar, COUNT(*) as jumlah FROM $table_name WHERE waktu >= '$bulan_ini' GROUP BY status_bayar";
$result_bulan_ini = $koneksi->query($query_bulan_ini);

$lunas_bulan_ini = 0;
$belum_lunas_bulan_ini = 0;

while($row = $result_bulan_ini->fetch_assoc()) {
    if (strtolower($row['status_bayar']) == 'lunas') {
        $lunas_bulan_ini = $row['jumlah'];
    } else {
        $belum_lunas_bulan_ini += $row['jumlah'];
    }
}

echo json_encode([
    'total_pelanggan' => $total_pelanggan,
    'lunas_bulan_ini' => $lunas_bulan_ini,
    'belum_lunas_bulan_ini' => $belum_lunas_bulan_ini
]);

?>