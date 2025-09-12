<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Content-Type: application/json; charset=utf-8', true, 401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$koneksi = new mysqli("localhost", "root", "123", "billing");

if ($koneksi->connect_error) {
    header('Content-Type: application/json; charset=utf-8', true, 500);
    echo json_encode(['error' => 'Koneksi gagal: ' . $koneksi->connect_error]);
    exit;
}

$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Terima beberapa nama param (search, q, keyword)
$search_raw = trim(
    $_REQUEST['search'] ?? 
    $_REQUEST['q'] ?? 
    $_REQUEST['keyword'] ?? 
    ''
);
$search_param = '%' . $search_raw . '%';

// Hitung total data (dengan filter search) menggunakan prepared statement
$totalStmt = $koneksi->prepare(
    "SELECT COUNT(*) as total FROM pelanggan 
     WHERE nama LIKE ? OR id_pelanggan LIKE ? OR paket LIKE ?"
);
$totalStmt->bind_param('sss', $search_param, $search_param, $search_param);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalData = $totalRow['total'];
$totalPages = $limit > 0 ? ceil($totalData / $limit) : 0;

// Ambil data sesuai pagination + filter (LIMIT dan OFFSET sudah ter-cast jadi aman)
$sql = "SELECT * FROM pelanggan 
        WHERE nama LIKE ? OR id_pelanggan LIKE ? OR paket LIKE ?
        LIMIT $limit OFFSET $offset";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('sss', $search_param, $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();

header('Content-Type: application/json; charset=utf-8');

$nomor_admin = "6xxxxxx"; 

ob_start();
?>
<table border="1" cellpadding="8">
    <tr>
        <th>No</th>
        <th>ID Pelanggan</th>
        <th>Nama Pelanggan</th>
        <th>Paket</th>
        <th>Bulan</th>
        <th>Tagihan</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']); ?></td>
            <td><?= htmlspecialchars($row['id_pelanggan']); ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td><?= htmlspecialchars($row['paket']); ?></td>
            <td><?= date("F Y", strtotime($row['waktu'])); ?></td>
            <td>Rp<?= number_format($row['tagihan'],0,',','.'); ?></td>
            <td><?= htmlspecialchars($row['status_bayar']); ?></td>
            <td>
                <?php
                $tujuan = !empty($row['nomor_pelanggan']) ? $row['nomor_pelanggan'] : $nomor_admin;

                $nama         = $row['nama'];
                $id_pelanggan = $row['id_pelanggan'];
                $paket        = $row['paket'];
                $tagihan      = number_format($row['tagihan'], 0, ',', '.');
                $awal_bulan   = date("1 F Y", strtotime($row['waktu']));
                $akhir_bulan  = date("t F Y", strtotime($row['waktu']));

                $pesan_tagihan = "Pelanggan Yth.\n".
                "Bapak/Ibu/Sdr : $nama\n".
                "--------------------------------------------\n".
                "Informasi Pembayaran Layanan CLEON\n".
                "Nomor Pelanggan : $id_pelanggan\n".
                "Layanan : $paket\n".
                "Periode Berjalan : $awal_bulan - $akhir_bulan\n".
                "Jatuh Tempo : $akhir_bulan\n\n".
                "TAGIHAN : Rp$tagihan,00\n".
                "--------------------------------------------\n".
                "Untuk transfer bisa melalui:\n".
                "1) Bank Mandiri - No. rek atas nama E\n".
                "2) Bank BCA - No. rek atas nama E\n\n".
                "Konfirmasikan pembayaran ke nomor wa.me/6;\n".
                "Abaikan informasi ini jika anda telah melakukan pembayaran, Terima Kasih.";

                $pesan_tagihan_encode = urlencode($pesan_tagihan);

                $tanggal_bayar = date("j F Y");
                $pesan_resi = "Pelanggan CLEON Yth,\n".
                "Bapak/Ibu/Sdr : $nama\n".
                "Terima kasih telah memilih CLEON.\n".
                "Pembayaran di bawah ini sudah terkonfirmasi :\n".
                "------------------------------------------------\n".
                "No Pelanggan : $id_pelanggan\n".
                "Periode : $awal_bulan - $akhir_bulan\n".
                "Total : Rp$tagihan,00\n".
                "Tgl Bayar : $tanggal_bayar\n".
                "Layanan : $paket\n\n".
                "--------------------------------------------\n".
                "Terimakasih sudah berlangganan CLEON.";

                $pesan_resi_encode = urlencode($pesan_resi);
                ?>
                <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_tagihan_encode ?>" target="_blank">
                    <button class="action-btn btn-tagihan">Kirim Tagihan</button>
                </a>
                <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_resi_encode ?>" target="_blank">
                    <button class="action-btn btn-resi">Kirim Resi</button>
                </a>
                <a href="edit.php?id=<?= $row['id']; ?>">
                    <button class="action-btn btn-edit">Edit</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" style="text-align:center; color:gray;">Data tidak ditemukan</td>
        </tr>
    <?php endif; ?>
</table>
<?php
$tableHTML = ob_get_clean();

echo json_encode([
    'table' => $tableHTML,
    'totalPages' => $totalPages
]);
?>
