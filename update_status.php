<?php
header('Content-Type: application/json');
include 'db.php';
session_start();

if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents('php://input'), true);

    $id = isset($data['id']) ? (int)$data['id'] : 0;
    $status = isset($data['status']) ? $koneksi->real_escape_string($data['status']) : '';

    if ($id > 0 && ($status == 'Lunas' || $status == 'Belum Lunas')) {
        
        $stmt = null;
        // --- LOGIKA BARU DIMULAI DI SINI ---
        if ($status == 'Lunas') {
            // Jika status diubah menjadi Lunas, perbarui status DAN atur tagihan menjadi 0
            $sql = "UPDATE $table_name SET status_bayar=?, tagihan=0 WHERE id=?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("si", $status, $id);
        } else {
            // Jika status diubah menjadi Belum Lunas, hanya perbarui statusnya
            $sql = "UPDATE $table_name SET status_bayar=? WHERE id=?";
            $stmt = $koneksi->prepare($sql);
            $stmt->bind_param("si", $status, $id);
        }
        // --- AKHIR DARI LOGIKA BARU ---
        
        if ($stmt && $stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed.']);
        }
        
        if ($stmt) {
            $stmt->close();
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$koneksi->close();
?>