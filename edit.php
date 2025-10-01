<?php
include 'db.php';
session_start();

// Debug: Tampilkan semua session untuk troubleshooting
error_log("Session data: " . print_r($_SESSION, true));

// Deteksi tabel berdasarkan wilayah user
$table_name = 'pelanggan_jogja'; // default untuk jogja
if (isset($_SESSION['wilayah'])) {
    if ($_SESSION['wilayah'] === 'samiran') {
        $table_name = 'pelanggan_samiran';
    } elseif ($_SESSION['wilayah'] === 'godean') {
        $table_name = 'pelanggan_godean';
    }
}

// Debug: tampilkan session wilayah dan tabel yang dipilih
error_log("Edit Debug - Session wilayah: " . (isset($_SESSION['wilayah']) ? $_SESSION['wilayah'] : 'tidak ada') . ", Table: $table_name");

// ambil id dari URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
// apakah dibuka dalam modal (iframe)
$isModal = isset($_GET['modal']) && $_GET['modal'] == '1';

// Validasi ID
if ($id <= 0) {
    die("ID tidak valid");
}

// ambil data lama
$result = $koneksi->query("SELECT * FROM $table_name WHERE id=$id");
if (!$result) {
    die("Error mengambil data: " . $koneksi->error . " | Query: SELECT * FROM $table_name WHERE id=$id");
}

if ($result->num_rows === 0) {
    die("Data tidak ditemukan dengan ID: $id di tabel $table_name");
}

$data = $result->fetch_assoc();

// Cek apakah tabel punya kolom 'nomor_pelanggan' dan 'alamat'
$escaped_table = $koneksi->real_escape_string($table_name);
$col_check_sql_nomor = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$escaped_table' AND COLUMN_NAME = 'nomor_pelanggan' LIMIT 1";
$col_check_res_nomor = $koneksi->query($col_check_sql_nomor);
$has_nomor_pelanggan = ($col_check_res_nomor && $col_check_res_nomor->num_rows > 0);

$col_check_sql_alamat = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$escaped_table' AND COLUMN_NAME = 'alamat' LIMIT 1";
$col_check_res_alamat = $koneksi->query($col_check_sql_alamat);
$has_alamat = ($col_check_res_alamat && $col_check_res_alamat->num_rows > 0);

// Cek apakah tabel punya kolom 'langganan_aktif_hingga'
$col_check_sql_masa = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$escaped_table' AND COLUMN_NAME = 'langganan_aktif_hingga' LIMIT 1";
$col_check_res_masa = $koneksi->query($col_check_sql_masa);
$has_langganan_aktif_hingga = ($col_check_res_masa && $col_check_res_masa->num_rows > 0);

// Cek apakah tabel punya kolom 'langganan_selesai' dan 'durasi_langganan'
$col_check_sql_selesai = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$escaped_table' AND COLUMN_NAME = 'langganan_selesai' LIMIT 1";
$col_check_res_selesai = $koneksi->query($col_check_sql_selesai);
$has_langganan_selesai = ($col_check_res_selesai && $col_check_res_selesai->num_rows > 0);

$col_check_sql_durasi = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$escaped_table' AND COLUMN_NAME = 'durasi_langganan' LIMIT 1";
$col_check_res_durasi = $koneksi->query($col_check_sql_durasi);
$has_durasi_langganan = ($col_check_res_durasi && $col_check_res_durasi->num_rows > 0);

// kalau tombol update ditekan
if (isset($_POST['update'])) {
    $tagihan = $_POST['tagihan'];
    $status = $_POST['status_bayar'];
    // terima value masa aktif jika tersedia (hanya jika field dikirimkan)
    $langganan_aktif_hingga = array_key_exists('langganan_aktif_hingga', $_POST) ? $koneksi->real_escape_string($_POST['langganan_aktif_hingga']) : null;
    $nomor_pelanggan = isset($_POST['nomor_pelanggan']) ? $koneksi->real_escape_string($_POST['nomor_pelanggan']) : '';
    $alamat = isset($_POST['alamat']) ? $koneksi->real_escape_string($_POST['alamat']) : '';
    // terima langganan_selesai jika kolom tersedia
    $langganan_selesai = isset($_POST['langganan_selesai']) ? trim($_POST['langganan_selesai']) : null;
    
    // Debug: tampilkan nilai yang diterima
    error_log("Edit Debug - ID: $id, Table: $table_name, Tagihan: $tagihan, Status: $status, Nomor: $nomor_pelanggan");

    // --- LOGIKA YANG DIPERBARUI ---
    // Jika admin secara manual memilih status "Lunas", maka tagihan otomatis diatur menjadi 0.
    if ($status == 'Lunas') {
        $tagihan = 0;
    } else {
        // Jika tagihan diisi lebih dari 0, pastikan statusnya adalah "Belum Lunas".
        // Ini menjaga konsistensi data dan juga didukung oleh trigger database.
        $numeric_tagihan = floatval(str_replace(',', '.', $tagihan));
        if ($numeric_tagihan > 0) {
            $status = 'Belum Lunas';
        }
    }
    // --- AKHIR DARI LOGIKA YANG DIPERBARUI ---

    // Escape data untuk keamanan
    $tagihan_escaped = $koneksi->real_escape_string($tagihan);
    $status_escaped = $koneksi->real_escape_string($status);
    
    // Bangun assignment untuk UPDATE secara dinamis (hanya kolom yang ada)
    $assignments = [];
    $assignments[] = "tagihan='$tagihan_escaped'";
    $assignments[] = "status_bayar='$status_escaped'";
    if ($has_nomor_pelanggan) {
        $assignments[] = "nomor_pelanggan='$nomor_pelanggan'";
    }
    if ($has_alamat) {
        $assignments[] = "alamat='$alamat'";
    }
    if ($has_langganan_aktif_hingga) {
        // Jika field tidak dikirimkan atau dikosongkan, simpan NULL ke kolom DATE
        if ($langganan_aktif_hingga === null || $langganan_aktif_hingga === '') {
            $assignments[] = "langganan_aktif_hingga=NULL";
        } else {
            // simpan format YYYY-MM ke tipe DATE or VARCHAR sesuai skema; kita simpan sebagai awal bulan (YYYY-MM-01) jika kolom DATE
            if (preg_match('/^\d{4}-\d{2}$/', $langganan_aktif_hingga)) {
                $escaped_masa = $koneksi->real_escape_string($langganan_aktif_hingga);
                $assignments[] = "langganan_aktif_hingga='" . $escaped_masa . "-01'";
            } else {
                $escaped_masa = $koneksi->real_escape_string($langganan_aktif_hingga);
                $assignments[] = "langganan_aktif_hingga='" . $escaped_masa . "'";
            }
        }
    }
    // Jika ada kolom langganan_selesai / durasi_langganan, hitung durasi dalam bulan dari kolom 'waktu' sampai 'langganan_selesai'
    if ($has_langganan_selesai) {
        if ($langganan_selesai === null || $langganan_selesai === '') {
            $assignments[] = "langganan_selesai=NULL";
            if ($has_durasi_langganan) $assignments[] = "durasi_langganan=NULL";
        } else {
            // validasi format YYYY-MM-DD
            $raw_selesai = $koneksi->real_escape_string($langganan_selesai);
            $assignments[] = "langganan_selesai='" . $raw_selesai . "'";

            if ($has_durasi_langganan) {
                // hitung selisih bulan antara waktu (kolom 'waktu' di DB) dan langganan_selesai
                try {
                    $start = new DateTime($data['waktu']);
                    $end = new DateTime($langganan_selesai);
                    // Jika end < start, set durasi 0
                    if ($end < $start) {
                        $bulan = 0;
                    } else {
                        $y1 = (int)$start->format('Y'); $m1 = (int)$start->format('n');
                        $y2 = (int)$end->format('Y'); $m2 = (int)$end->format('n');
                        $bulan = ($y2 - $y1) * 12 + ($m2 - $m1);
                        // jika tanggal di end lebih kecil dari tanggal di start, jangan tambahkan sisa; kita hitung per bulan penuh
                        if ((int)$end->format('j') < (int)$start->format('j')) {
                            // tidak menambah
                        }
                        if ($bulan < 0) $bulan = 0;
                    }
                } catch (Exception $e) {
                    $bulan = 0;
                }
                $durasi_text = $bulan . ' bulan';
                $assignments[] = "durasi_langganan='" . $koneksi->real_escape_string($durasi_text) . "'";
            }
        }
    }

    $update_query = "UPDATE `$table_name` SET " . implode(', ', $assignments) . " WHERE id=$id";

    // Debug: tampilkan query yang akan dieksekusi
    error_log("Update Query: $update_query");

    if ($koneksi->query($update_query)) {
        // Update berhasil
        $success_message = "Data berhasil diperbarui";
        error_log("Update berhasil untuk ID: $id");
        
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
                    <h3><?= $success_message ?></h3>
                    <p>Menutup jendela...</p>
                </div>
                <script>
                    // beri waktu singkat agar user melihat pesan lalu beri tahu parent untuk menutup modal
                    setTimeout(function(){
                        try { window.parent.postMessage(JSON.stringify({ action: 'close', success: true }), '*'); } catch(e){}
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
    } else {
        // Update gagal
        $error_message = "Gagal memperbarui data: " . $koneksi->error;
        error_log("Update gagal untuk ID: $id - Error: " . $koneksi->error);
        
        // Tampilkan pesan error tanpa redirect agar user bisa mencoba lagi
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Tagihan - <?= htmlspecialchars($data['nama']); ?></title>
    <style>
        :root{ --bg:#f5f7fb; --card:#fff; --accent:#3498db; --muted:#6c757d; --danger:#e74c3c }
        html,body{height:100%;margin:0;font-family:Segoe UI,Arial;background:var(--bg);color:#222}
    /* make the card fill iframe vertically so modal appears "full in" inside iframe */
    html,body{height:100%;}
    .wrap{min-height:100%;display:flex;align-items:center;justify-content:center;padding:0}
    .card{width:100%;height:100%;background:var(--card);border-radius:0;box-shadow:none;overflow:auto}
    .card__body{padding:22px;height:100%;box-sizing:border-box;display:flex;flex-direction:column}
        .grid{display:flex;gap:12px;flex-wrap:wrap}
        .col{flex:1;min-width:160px}
        label.small{display:block;color:var(--muted);font-size:13px;margin-bottom:6px}
        
        /* Error message styling */
        .error-message {
            background-color: #ffeeee;
            color: var(--danger);
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            border-left: 4px solid var(--danger);
        }

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
        .field[readonly], .field[aria-readonly="true"] { 
            background: #fbfdff; 
            border: 1px solid #e6eef6;
            padding: 10px 12px;
            border-radius: 8px;
            min-height: 40px;
        }
        .actions{display:flex;gap:10px;justify-content:flex-end;padding:12px 20px;border-top:1px solid #f0f4f8;margin-top:auto}
        .btn{padding:10px 14px;border-radius:8px;border:0;cursor:pointer;font-weight:600;text-decoration:none;display:inline-block;text-align:center}
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
                <?php if (isset($error_message)): ?>
                    <div class="error-message">
                        <strong>Error:</strong> <?= $error_message ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="grid">
                        <div class="col">
                            <label class="small">Nama</label>
                            <div class="field" aria-readonly="true"><?= htmlspecialchars($data['nama']); ?></div>
                        </div>
                        <div class="col">
                            <label class="small">ID Pelanggan</label>
                            <div class="field" aria-readonly="true"><?= htmlspecialchars($data['id_pelanggan']); ?></div>
                        </div>
                    </div>

                    <div class="grid" style="margin-top:10px">
                        <div class="col">
                            <label class="small">Nomor Pelanggan</label>
                            <input type="text" name="nomor_pelanggan" value="<?= htmlspecialchars(isset($data['nomor_pelanggan']) ? $data['nomor_pelanggan'] : ''); ?>">
                        </div>
                        <?php if (isset($_SESSION['wilayah']) && $_SESSION['wilayah'] === 'godean' && $has_alamat): ?>
                        <div class="col">
                            <label class="small">Alamat</label>
                            <input type="text" name="alamat" value="<?= htmlspecialchars(isset($data['alamat']) ? $data['alamat'] : ''); ?>">
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="grid" style="margin-top:12px">
                        <div class="col">
                            <label class="small">Paket</label>
                            <div class="field" aria-readonly="true"><?= htmlspecialchars($data['paket']); ?></div>
                        </div>
                        <div class="col">
                            <label class="small">Bulan</label>
                            <div class="field" aria-readonly="true"><?= date("F Y", strtotime($data['waktu'])); ?></div>
                        </div>
                    </div>

                    <div class="grid" style="margin-top:12px">
                        <div class="col">
                            <label class="small">Tagihan</label>
                            <input type="number" name="tagihan" value="<?= htmlspecialchars($data['tagihan']); ?>" required step="0.01" min="0">
                        </div>
                        <div class="col">
                            <label class="small">Status Bayar</label>
                            <select name="status_bayar">
                                <option value="Lunas" <?= $data['status_bayar']=="Lunas"?"selected":"" ?>>Lunas</option>
                                <option value="Belum Lunas" <?= $data['status_bayar']=="Belum Lunas"?"selected":"" ?>>Belum Lunas</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid" style="margin-top:12px">
                        <!-- Masa Aktif input removed per request; using langganan_selesai instead -->

                        <?php if ($has_langganan_selesai):
                            $selesai_value = !empty($data['langganan_selesai']) ? $data['langganan_selesai'] : '';
                        ?>
                        <div class="col">
                            <label class="small">Langganan Selesai</label>
                            <input type="date" name="langganan_selesai" value="<?= htmlspecialchars($selesai_value); ?>" style="width:100%;padding:10px 12px;border-radius:8px;border:1px solid #e6eef6;background:#fff;box-sizing:border-box">
                            <div class="note" style="margin-top:6px">Biarkan kosong untuk mengosongkan masa aktif.</div>
                        </div>
                        <?php endif; ?>
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
        try { 
            if (window.parent) {
                window.parent.postMessage(JSON.stringify({ 
                    action: 'loaded',
                    table: '<?= $table_name ?>',
                    id: <?= $id ?>
                }), '*'); 
            }
        } catch(e){}
        
        // Logika untuk mengubah status otomatis berdasarkan tagihan
        (function(){
            var tagihanInput = document.querySelector('input[name="tagihan"]');
            var statusSelect = document.querySelector('select[name="status_bayar"]');
            if (tagihanInput && statusSelect) {
                tagihanInput.addEventListener('input', function() {
                    const tagihan = parseFloat(this.value) || 0;
                    if (tagihan > 0 && statusSelect.value === 'Lunas') {
                        statusSelect.value = 'Belum Lunas';
                    }
                });

                statusSelect.addEventListener('change', function() {
                    if (this.value === 'Lunas') {
                        tagihanInput.value = '0';
                    }
                });
            }

            // Month input support detection and fallback
            try {
                var monthInput = document.getElementById('masa-month');
                var fallback = document.getElementById('masa-fallback');
                var hiddenMasa = document.getElementById('masa-hidden');
                if (monthInput) {
                    var isSupported = (function(){
                        var input = document.createElement('input');
                        input.setAttribute('type','month');
                        return input.type === 'month';
                    })();

                    if (!isSupported) {
                        // tampilkan fallback selects dan sinkronkan
                        fallback.style.display = 'block';
                        monthInput.style.display = 'none';
                        var monthSel = document.getElementById('masa-month-select');
                        var yearSel = document.getElementById('masa-year-select');
                        // inisialisasi hidden dari selects
                        function syncHidden(){
                            var m = monthSel.value;
                            var y = yearSel.value;
                            if (y && m) hiddenMasa.value = y + '-' + m; else hiddenMasa.value = '';
                        }
                        monthSel.addEventListener('change', syncHidden);
                        yearSel.addEventListener('change', syncHidden);
                        // set initial
                        syncHidden();
                    } else {
                        // Jika didukung, pastikan hidden mencerminkan nilai bulan yang dipilih
                        if (hiddenMasa) {
                            monthInput.addEventListener('change', function(){
                                hiddenMasa.value = this.value;
                            });
                            // set awal
                            hiddenMasa.value = monthInput.value || hiddenMasa.value || '';
                        }
                    }
                }
            } catch(e){ console.warn('Masa aktif script error', e); }
        })();
    </script>
</body>
</html>