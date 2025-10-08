<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing id']);
    exit;
}

$id = (int)$_GET['id'];
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid id']);
    exit;
}

// pilih tabel berdasarkan wilayah session
$table_name = 'pelanggan_jogja';
if (isset($_SESSION['wilayah'])) {
    if ($_SESSION['wilayah'] === 'samiran') $table_name = 'pelanggan_samiran';
    elseif ($_SESSION['wilayah'] === 'godean') $table_name = 'pelanggan_godean';
}

// prevent deleting everything by mistake: require id exists
$check = $koneksi->prepare("SELECT id FROM `$table_name` WHERE id = ? LIMIT 1");
$check->bind_param('i', $id);
$check->execute();
$res = $check->get_result();
if (!$res || $res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Record not found']);
    exit;
}
$check->close();

$del = $koneksi->prepare("DELETE FROM `$table_name` WHERE id = ? LIMIT 1");
$del->bind_param('i', $id);
if ($del->execute()) {
    echo json_encode(['success' => true, 'message' => 'Record deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $del->error]);
}
$del->close();
$koneksi->close();
exit;
?>