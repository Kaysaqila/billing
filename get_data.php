<?php
include 'db.php';
session_start();
if (!isset($_SESSION['login'])) {
    header('Content-Type: application/json; charset=utf-8', true, 401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}


if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $koneksi->real_escape_string($_GET['search']) : "";

// Hitung total data (dengan filter search)
$totalQuery = "SELECT COUNT(*) as total FROM pelanggan 
               WHERE nama LIKE '%$search%' 
                  OR id_pelanggan LIKE '%$search%' 
                  OR paket LIKE '%$search%'";
$totalResult = $koneksi->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalData = $totalRow['total'];
$totalPages = ceil($totalData / $limit);

// Ambil data sesuai pagination + filter
$sql = "SELECT * FROM pelanggan 
        WHERE nama LIKE '%$search%' 
           OR id_pelanggan LIKE '%$search%' 
           OR paket LIKE '%$search%' 
        LIMIT $limit OFFSET $offset";
$result = $koneksi->query($sql);

$nomor_admin = "62xxxxx";

ob_start(); // Mulai buffering output
?>

<table class="modern-table" cellpadding="0">
    <thead>
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
    </thead>
    <tbody>
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']); ?></td>
            <td><span class="id-pill"><?= htmlspecialchars($row['id_pelanggan']); ?></span></td>
            <td>
                <div class="name-cell"><?= htmlspecialchars($row['nama']); ?></div>
                <div class="muted"><?= htmlspecialchars($row['alamat'] ?? ''); ?></div>
            </td>
            <td><?= htmlspecialchars($row['paket']); ?></td>
            <td><?= date("F Y", strtotime($row['waktu'])); ?></td>
            <td><strong>Rp<?= number_format($row['tagihan'],0,',','.'); ?></strong></td>
            <td>
                <?php if (strtolower($row['status_bayar']) === 'lunas'): ?>
                    <span class="badge badge-success">Lunas</span>
                <?php elseif (strtolower($row['status_bayar']) === 'pending' || strtolower($row['status_bayar']) === 'belum'): ?>
                    <span class="badge badge-warning"><?= htmlspecialchars($row['status_bayar']); ?></span>
                <?php else: ?>
                    <span class="badge badge-danger"><?= htmlspecialchars($row['status_bayar']); ?></span>
                <?php endif; ?>
            </td>
            <td>
          <?php
            $tujuan = !empty($row['nomor_pelanggan']) ? $row['nomor_pelanggan'] : $nomor_admin;

            $nama         = $row['nama'];
            $id_pelanggan = $row['id_pelanggan'];
            $paket        = $row['paket'];
            $tagihan      = number_format($row['tagihan'], 0, ',', '.');
            $awal_bulan   = date("1 F Y", strtotime($row['waktu']));
            $akhir_bulan  = date("t F Y", strtotime($row['waktu']));

            // Pesan tagihan
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
            "1) Bank Mandiri - No. rek 1370011667371 atas nama Eksan Wahyu Nugroho\n".
            "2) Bank BCA - No. rek 8465356509 atas nama Eksan Wahyu Nugroho\n\n".
            "Konfirmasikan pembayaran ke nomor wa.me/6281314152347;\n".
            "Abaikan informasi ini jika anda telah melakukan pembayaran, Terima Kasih.";

            $pesan_tagihan_encode = urlencode($pesan_tagihan);

            // Pesan resi
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

             <div class="action-group">
                <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_tagihan_encode ?>" target="_blank">
                    <button class="action-btn btn-tagihan">Kirim Tagihan</button>
                </a>
                <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_resi_encode ?>" target="_blank">
                    <button class="action-btn btn-resi">Kirim Resi</button>
                </a>
                <button class="action-btn btn-edit" onclick="openEditModal(<?= $row['id']; ?>)">Edit</button>
            </div>
        </td>
    </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" style="text-align:center; padding:30px; color: #6c757d;">
                <i class="fas fa-search"></i> Tidak ada data yang cocok.
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<?php
$tableHTML = ob_get_clean();

echo json_encode([
    'table' => $tableHTML,
    'totalPages' => $totalPages
]);
?>