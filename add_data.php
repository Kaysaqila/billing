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

// Deteksi tabel berdasarkan wilayah user
$table_name = 'pelanggan_jogja'; // default untuk jogja
if (isset($_SESSION['wilayah'])) {
    if ($_SESSION['wilayah'] === 'samiran') {
        $table_name = 'pelanggan_samiran';
    } elseif ($_SESSION['wilayah'] === 'godean') {
        $table_name = 'pelanggan_godean';
    }
}

// Ambil data dari POST dengan pemeriksaan isset
$id_pelanggan = isset($_POST['id_pelanggan']) ? trim($_POST['id_pelanggan']) : null;
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$paket = isset($_POST['paket']) ? trim($_POST['paket']) : '';
$nomor_pelanggan = isset($_POST['nomor_pelanggan']) ? trim($_POST['nomor_pelanggan']) : null;
$tagihan = isset($_POST['tagihan']) ? (float)$_POST['tagihan'] : 0;
$waktu = date('Y-m-d H:i:s'); // Waktu saat ini

// Validasi sederhana
if (empty($nama) || empty($paket)) {
    echo json_encode(['success' => false, 'message' => 'Nama dan Paket wajib diisi.']);
    exit;
}

// Periksa kolom yang tersedia di tabel (nomor_pelanggan, alamat)
$colsQuery = $koneksi->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?");
$dbName = $koneksi->real_escape_string($koneksi->query("SELECT DATABASE() as db")->fetch_object()->db);
$colsQuery->bind_param('ss', $dbName, $table_name);
$colsQuery->execute();
$resultCols = $colsQuery->get_result();
$availableCols = [];
while ($row = $resultCols->fetch_assoc()) {
    $availableCols[] = $row['COLUMN_NAME'];
}
$colsQuery->close();

$has_nomor = in_array('nomor_pelanggan', $availableCols);
$has_alamat = in_array('alamat', $availableCols);

// Build INSERT statement dynamically depending on available columns
$columns = ['nama', 'paket', 'waktu', 'tagihan'];
$placeholders = ['?', '?', '?', '?'];
$types = 'sssd';
$values = [$nama, $paket, $waktu, $tagihan];

if ($id_pelanggan !== null && $id_pelanggan !== '') {
    array_unshift($columns, 'id_pelanggan');
    array_unshift($placeholders, '?');
    $types = 's' . $types;
    array_unshift($values, $id_pelanggan);
}

if ($has_nomor) {
    $columns[] = 'nomor_pelanggan';
    $placeholders[] = '?';
    $types .= 's';
    $values[] = $nomor_pelanggan;
}

if ($has_alamat && isset($_POST['alamat'])) {
    $alamat = trim($_POST['alamat']);
    $columns[] = 'alamat';
    $placeholders[] = '?';
    $types .= 's';
    $values[] = $alamat;
}

$colList = implode(', ', $columns);
$phList = implode(', ', $placeholders);

$sql = "INSERT INTO $table_name ($colList) VALUES ($phList)";

$stmt = $koneksi->prepare($sql);
if (!$stmt) {
    error_log('Prepare failed: ' . $koneksi->error);
    echo json_encode(['success' => false, 'message' => 'Gagal menyiapkan query: ' . $koneksi->error]);
    exit;
}

// bind params dynamically
$bind_names[] = $types;
for ($i = 0; $i < count($values); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $values[$i];
    $bind_names[] = &$$bind_name;
}

call_user_func_array([$stmt, 'bind_param'], $bind_names);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Data berhasil ditambahkan.']);
} else {
    error_log('Execute failed: ' . $stmt->error . ' SQL: ' . $sql);
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $stmt->error]);
}

$stmt->close();
$koneksi->close();
exit;
?>