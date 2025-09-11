<?php
include 'db.php';
session_start();
if (!isset($_SESSION['login'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}



$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Cari parameter (jika ada) dan bangun klausa WHERE
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = '';
if ($search !== '') {
    $esc = $koneksi->real_escape_string($search);
    $where = " WHERE id_pelanggan LIKE '%$esc%' OR nama LIKE '%$esc%' ";
}

// Hitung total data (dengan filter jika ada)
$totalQuery = "SELECT COUNT(*) as total FROM pelanggan" . $where;
$totalResult = $koneksi->query($totalQuery);
$totalRow = $totalResult->fetch_assoc();
$totalData = (int)$totalRow['total'];
$totalPages = (int)ceil($totalData / $limit);
if ($totalPages < 1) $totalPages = 1;

// Ambil data sesuai pagination dan filter
$sql = "SELECT * FROM pelanggan" . $where . " LIMIT $limit OFFSET $offset";
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
            "1) Bank Mandiri - No. rek 1xxxx1 atas nama xx\n".
            "2) Bank BCA - No. rek 8xxxx atas nama xx\n\n".
            "Konfirmasikan pembayaran ke nomor wa.me/6xxxxx;\n".
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

            <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_tagihan_encode ?>" target="_blank">
                <button class="action-btn btn-tagihan">Kirim Tagihan</button>
            </a>
            <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_resi_encode ?>" target="_blank">
                <button class="action-btn btn-resi">Kirim Resi</button>
            </a>
            <button class="action-btn btn-edit" onclick="openEditModal(<?= $row['id']; ?>)">Edit</button>
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