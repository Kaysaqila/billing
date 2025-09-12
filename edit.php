<?php
include 'db.php';

// ambil id dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// apakah dibuka dalam modal (iframe)
$isModal = isset($_GET['modal']) && $_GET['modal'] == '1';

// ambil data lama
$result = $koneksi->query("SELECT * FROM pelanggan WHERE id=$id");
$data = $result->fetch_assoc();

// kalau tombol update ditekan
if (isset($_POST['update'])) {
    $tagihan = $_POST['tagihan'];
    $status = $_POST['status_bayar'];
    $nomor_pelanggan = isset($_POST['nomor_pelanggan']) ? $koneksi->real_escape_string($_POST['nomor_pelanggan']) : '';

    // pastikan status otomatis: 'Belum Lunas' jika tagihan > 0, sebaliknya 'Lunas'
    // cast/normalize tagihan sebagai angka terlebih dulu
    $numeric_tagihan = floatval(str_replace(',', '.', $tagihan));
    if ($numeric_tagihan > 0) {
        $status = 'Belum Lunas';
    } else {
        $status = 'Lunas';
    }

    $koneksi->query("UPDATE pelanggan SET tagihan='$tagihan', status_bayar='$status', nomor_pelanggan='$nomor_pelanggan' WHERE id=$id");

    if ($isModal) {
        // jika di-iframe/modal, tampilkan halaman kecil yang memberitahu parent untuk menutup modal
        ?>
        <!doctype html>
        <html>
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Berhasil</title>
            <style>
                html,body{height:100%;margin:0;padding:0;font-family:Segoe UI,Arial;background:transparent;display:flex;align-items:center;justify-content:center}
                .msg{background:#fff;padding:22px;border-radius:10px;box-shadow:0 18px 40px rgba(0,0,0,0.08);text-align:center}
                .msg h3{margin:0 0 8px;font-size:18px}
                .msg p{color:#666;margin:0 0 12px}
            </style>
        </head>
        <body>
            <div class="msg">
                <h3>Data berhasil diperbarui</h3>
                <p>Menutup jendela...</p>
            </div>
            <script>
                // beri waktu singkat agar user melihat pesan lalu beri tahu parent untuk menutup modal
                setTimeout(function(){
                    try { window.parent.postMessage(JSON.stringify({ action: 'close' }), '*'); } catch(e){}
                }, 600);
            </script>
        </body>
        </html>
        <?php
        exit;
    } else {
        header("Location: index.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Tagihan</title>
    <style>
        :root{ --bg:#f5f7fb; --card:#fff; --accent:#3498db; --muted:#6c757d }
        html,body{height:100%;margin:0;font-family:Segoe UI,Arial;background:var(--bg);color:#222}
    /* make the card fill iframe vertically so modal appears "full in" inside iframe */
    html,body{height:100%;}
    .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:0}
    .card{width:100%;height:100%;background:var(--card);border-radius:0;box-shadow:none;overflow:hidden}
    .card__body{padding:22px;height:100%;box-sizing:border-box;display:flex;flex-direction:column}
        .grid{display:flex;gap:12px;flex-wrap:wrap}
        .col{flex:1;min-width:160px}
        label.small{display:block;color:var(--muted);font-size:13px;margin-bottom:6px}

        /* konsistenkan tampilan kolom: div .field, input text/number, dan select
           semua memiliki lebar penuh, padding dan box-sizing yang sama */
        .field,
        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #e6eef6;
            background: #ffffff;
            font-size: 14px;
            box-sizing: border-box;
        }

        /* tampilan sedikit berbeda untuk elemen .field yang bersifat read-only/display */
        .field[readonly], .field[aria-readonly="true"] { background: #fbfdff; }
        .actions{display:flex;gap:10px;justify-content:flex-end;padding:12px 20px;border-top:1px solid #f0f4f8}
        .btn{padding:10px 14px;border-radius:8px;border:0;cursor:pointer;font-weight:600}
        .btn-primary{background:linear-gradient(90deg,var(--accent),#2980b9);color:#fff}
        .btn-muted{background:#f1f5f9;color:#213}
        .note{font-size:13px;color:var(--muted);margin-top:8px}
    @media (max-width:520px){ .grid{flex-direction:column} .actions{flex-direction:column} .btn{width:100%} .card{border-radius:8px} }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card" role="dialog" aria-label="Edit Tagihan">
            <div class="card__body">
                <form method="POST">
                    <div class="grid">
                        <div class="col">
                            <label class="small">Nama</label>
                            <div class="field"><?= htmlspecialchars($data['nama']); ?></div>
                        </div>
                        <div class="col">
                            <label class="small">ID Pelanggan</label>
                            <div class="field"><?= htmlspecialchars($data['id_pelanggan']); ?></div>
                        </div>
                    </div>

                    <div class="grid" style="margin-top:10px">
                        <div class="col">
                            <label class="small">Nomor Pelanggan</label>
                            <input type="text" name="nomor_pelanggan" class="field" value="<?= htmlspecialchars(isset($data['nomor_pelanggan']) ? $data['nomor_pelanggan'] : ''); ?>">
                        </div>
                    </div>

                    <div class="grid" style="margin-top:12px">
                        <div class="col">
                            <label class="small">Paket</label>
                            <div class="field"><?= htmlspecialchars($data['paket']); ?></div>
                        </div>
                        <div class="col">
                            <label class="small">Bulan</label>
                            <div class="field"><?= date("F Y", strtotime($data['waktu'])); ?></div>
                        </div>
                    </div>

                    <div class="grid" style="margin-top:12px">
                        <div class="col">
                            <label class="small">Tagihan</label>
                            <input type="number" name="tagihan" value="<?= htmlspecialchars($data['tagihan']); ?>" required>
                        </div>
                        <div class="col">
                            <label class="small">Status Bayar</label>
                            <select name="status_bayar">
                                <option value="Lunas" <?= $data['status_bayar']=="Lunas"?"selected":"" ?>>Lunas</option>
                                <option value="Belum Lunas" <?= $data['status_bayar']=="Belum Lunas"?"selected":"" ?>>Belum Lunas</option>
                            </select>
                        </div>
                    </div>

                    <div class="note">Ubah nilai tagihan dan status, lalu klik Update untuk menyimpan.</div>

                    <div class="actions">
                        <?php if ($isModal): ?>
                            <button type="button" class="btn btn-muted" onclick="if(window.parent){window.parent.postMessage(JSON.stringify({ action: 'close' }), '*');}">Batal</button>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-muted">Batal</a>
                        <?php endif; ?>
                        <button type="submit" name="update" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // optional: notify parent when loaded so parent can hide spinner
        try { if (window.parent) window.parent.postMessage(JSON.stringify({ action: 'loaded' }), '*'); } catch(e){}
    </script>
</body>
</html>
