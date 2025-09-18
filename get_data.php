<?php
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

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// --- LOGIKA FILTER DAN PENCARIAN ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

$conditions = [];

// Tambahkan kondisi untuk pencarian
if ($search !== '') {
    $esc_search = $koneksi->real_escape_string($search);
    $conditions[] = "(nama LIKE '%$esc_search%' OR id_pelanggan LIKE '%$esc_search%')";
}

// Tambahkan kondisi untuk filter status di bulan ini
if ($filter === 'lunas') {
    $bulan_ini = date('Y-m-01');
    $conditions[] = "(status_bayar = 'Lunas' AND waktu >= '$bulan_ini')";
} elseif ($filter === 'belum lunas') {
    $bulan_ini = date('Y-m-01');
    $conditions[] = "(LOWER(status_bayar) != 'lunas' AND waktu >= '$bulan_ini')";
}

// Bangun klausa WHERE dari semua kondisi
$where = '';
if (!empty($conditions)) {
    $where = ' WHERE ' . implode(' AND ', $conditions);
}
// --- AKHIR DARI LOGIKA ---


$totalQuery = "SELECT COUNT(*) as total FROM $table_name" . $where;
$totalResult = $koneksi->query($totalQuery);
$totalData = (int)($totalResult->fetch_assoc()['total'] ?? 0);
$totalPages = (int)ceil($totalData / $limit) ?: 1;

$sql = "SELECT * FROM $table_name" . $where . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $koneksi->query($sql);

$nomor_admin = "6285174328821"; // Ganti dengan nomor Admin Anda

ob_start();
?>
<table class="modern-table">
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
        <?php $nomor_urut = $offset + 1; ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $nomor_urut++; ?></td>
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
                    <span class="badge badge-success" onclick="updateStatus(<?= $row['id']; ?>, 'Lunas')" title="Klik untuk ubah status">Lunas</span>
                <?php else: ?>
                    <span class="badge badge-danger" onclick="updateStatus(<?= $row['id']; ?>, 'Belum Lunas')" title="Klik untuk ubah status">Belum Lunas</span>
                <?php endif; ?>
            </td>
            <td>
                <?php
                // --- KODE FORMAT PESAN WHATSAPP ASLI DIKEMBALIKAN ---
                $tujuan = !empty($row['nomor_pelanggan']) ? $row['nomor_pelanggan'] : $nomor_admin;

                $nama         = $row['nama'];
                $id_pelanggan = $row['id_pelanggan'];
                $paket        = $row['paket'];
                $tagihan      = number_format($row['tagihan'], 0, ',', '.');
                $awal_bulan   = date("1 F Y", strtotime($row['waktu']));
                $akhir_bulan  = date("t F Y", strtotime($row['waktu']));

                // Pesan tagihan (Format Asli yang Detail)
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

                // Pesan resi (Format Asli yang Detail)
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
                <a href="https://wa.me/<?= $tujuan ?>?text=<?= $pesan_tagihan_encode ?>" target="_blank"><button class="action-btn btn-tagihan">Kirim Tagihan</button></a>
                <button class="action-btn btn-resi" onclick="sendReceipt(<?= $row['id']; ?>, '<?= $tujuan; ?>', '<?= $pesan_resi_encode; ?>')">Kirim Resi</button>
                <button class="action-btn btn-edit" onclick="openEditModal(<?= $row['id']; ?>)">Edit</button>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="8" style="text-align:center; padding:30px; color: #6c757d;">Tidak ada data ditemukan.</td></tr>
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