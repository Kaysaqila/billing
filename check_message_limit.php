<?php
include 'db.php';
session_start();

if (!isset($_SESSION['login'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Dapatkan wilayah dari session
$wilayah = $_SESSION['wilayah'] ?? 'jogja';
$method = $_GET['method'] ?? '';

if ($method === 'check') {
    // Check apakah sudah mencapai batas 40
    $query = "SELECT COUNT(*) as count FROM message_send_log WHERE wilayah = '$wilayah' AND DATE(created_at) = CURDATE()";
    $result = $koneksi->query($query);
    $data = $result->fetch_assoc();
    $count = (int)$data['count'];
    
    echo json_encode([
        'count' => $count,
        'limit' => 40,
        'reached_limit' => $count >= 40
    ]);
} elseif ($method === 'increment') {
    // Increment counter
    $id_pelanggan = isset($_POST['id_pelanggan']) ? $koneksi->real_escape_string($_POST['id_pelanggan']) : '';
    
    $query = "INSERT INTO message_send_log (wilayah, id_pelanggan, created_at) 
              VALUES ('$wilayah', '$id_pelanggan', NOW())";
    
    if ($koneksi->query($query)) {
        // Check lagi apakah sudah mencapai 40
        $checkQuery = "SELECT COUNT(*) as count FROM message_send_log WHERE wilayah = '$wilayah' AND DATE(created_at) = CURDATE()";
        $checkResult = $koneksi->query($checkQuery);
        $checkData = $checkResult->fetch_assoc();
        $newCount = (int)$checkData['count'];
        
        echo json_encode([
            'success' => true,
            'new_count' => $newCount,
            'reached_limit' => $newCount >= 40
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $koneksi->error]);
    }
}
?>
