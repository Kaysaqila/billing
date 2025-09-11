<?php
$koneksi = new mysqli("localhost", "root", "123", "billing");

// ambil id dari URL
$id = $_GET['id'];

// ambil data lama
$result = $koneksi->query("SELECT * FROM pelanggan WHERE id=$id");
$data = $result->fetch_assoc();

// kalau tombol update ditekan
if (isset($_POST['update'])) {
    $tagihan = $_POST['tagihan'];
    $status = $_POST['status_bayar'];

    $koneksi->query("UPDATE pelanggan SET tagihan='$tagihan', status_bayar='$status' WHERE id=$id");

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tagihan</title>
</head>
<body>
    <h2>Edit Data Tagihan</h2>
    <form method="POST">
        <label>Nama: <?= $data['nama']; ?></label><br>
        <label>ID Pelanggan: <?= $data['id_pelanggan']; ?></label><br>
        <label>Paket: <?= $data['paket']; ?></label><br>
        <label>Bulan: <?= date("F Y", strtotime($data['waktu'])); ?></label><br><br>

        <label>Tagihan:</label>
        <input type="number" name="tagihan" value="<?= $data['tagihan']; ?>"><br><br>

        <label>Status Bayar:</label>
        <select name="status_bayar">
            <option value="Lunas" <?= $data['status_bayar']=="Lunas"?"selected":"" ?>>Lunas</option>
            <option value="Belum Lunas" <?= $data['status_bayar']=="Belum Lunas"?"selected":"" ?>>Belum Lunas</option>
        </select><br><br>

        <button type="submit" name="update">Update</button>
        <a href="index.php"><button type="button">Cancel</button></a>
    </form>
</body>
</html>
