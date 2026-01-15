# Sistem Pembatasan Pengiriman Pesan WhatsApp

## Deskripsi
Fitur ini menerapkan pembatasan pada pengiriman pesan WhatsApp otomatis untuk melindungi nomor WhatsApp dari risiko banned oleh WhatsApp. Setelah 40 pesan terkirim dalam satu hari per dashboard/wilayah, sistem akan beralih dari pengiriman otomatis ke mode manual.

## Cara Kerja

### Fase 1: Pengiriman Otomatis (Pesan 1-40)
- Ketika admin mengklik tombol **"Kirim Tagihan"**, sistem akan mengecek berapa banyak pesan yang sudah dikirim hari ini
- Jika belum mencapai batas 40, pesan akan:
  1. Dihitung dalam database (tabel `message_send_log`)
  2. Secara otomatis membuka link WhatsApp Web dengan pesan sudah siap
  3. Admin tinggal klik "Kirim" di WhatsApp Web

### Fase 2: Pengiriman Manual (Pesan 41+)
- Setelah mencapai 40 pesan, klik tombol **"Kirim Tagihan"** akan membuka modal
- Modal berisi:
  - **Pesan yang sudah di-generate** - siap untuk di-copy-paste
  - **Tombol "Salin ke Clipboard"** - untuk menyalin pesan dengan mudah
  - **Tombol "Buka WhatsApp Web"** - untuk membuka WhatsApp Web secara langsung
- Admin hanya perlu:
  1. Salin pesan dari modal
  2. Paste di WhatsApp Web secara manual
  3. Kirim pesan

## File-File yang Ditambahkan/Dimodifikasi

### File Baru:
- `check_message_limit.php` - API endpoint untuk mengecek dan mencatat pengiriman pesan
- `create_message_log_table.sql` - Script untuk membuat tabel database

### File yang Dimodifikasi:
1. **get_data.php**
   - Mengubah tombol "Kirim Tagihan" dari link langsung menjadi handler function
   - Menambahkan atribut untuk menyimpan pesan dan nomor tujuan

2. **dashboard_jogja.php, dashboard_samiran.php, dashboard_godean.php**
   - Menambahkan Modal HTML untuk menampilkan pesan
   - Menambahkan CSS styling untuk modal
   - Menambahkan JavaScript functions:
     - `handleKirimTagihan()` - Handler utama tombol kirim
     - `sendMessageAndIncrement()` - Kirim pesan dan increment counter
     - `openMessageModal()` - Buka modal pesan
     - `closeMessageModal()` - Tutup modal
     - `copyMessageToClipboard()` - Copy pesan ke clipboard
     - `openWhatsAppManual()` - Buka WhatsApp Web manual

## Database Schema

Tabel `message_send_log`:
```sql
CREATE TABLE IF NOT EXISTS message_send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wilayah VARCHAR(50) NOT NULL,
    id_pelanggan VARCHAR(100),
    message_type VARCHAR(50) DEFAULT 'tagihan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wilayah_date (wilayah, created_at)
);
```

## Konfigurasi

Batas pengiriman pesan dapat diubah di:
- `check_message_limit.php` (line dengan `'limit' => 40`)
- `handleKirimTagihan()` function di dashboard files (kondisi `data.reached_limit`)
- `sendMessageAndIncrement()` function (kondisi `if (data.new_count >= 35)` untuk warning)

## Keamanan

1. **Session Check** - Hanya user yang sudah login yang dapat mengakses
2. **Wilayah Tracking** - Setiap wilayah memiliki counter tersendiri
3. **Daily Limit** - Counter direset otomatis setiap hari
4. **No WhatsApp Integration** - Hanya tracking pengiriman, tidak tergantung API WhatsApp

## Testing

Untuk test fitur ini:
1. Login ke masing-masing dashboard (Jogja, Samiran, Godean)
2. Klik tombol "Kirim Tagihan" beberapa kali
3. Setelah 40x klik, akan muncul modal dengan pesan
4. Coba fitur "Salin ke Clipboard" dan "Buka WhatsApp Web"

## Catatan

- Counter akan direset otomatis setiap tengah malam (CURDATE())
- Jika tombol "Buka WhatsApp Web" diklik, counter tetap terhitung
- Warning akan muncul ketika sudah mencapai 35 pesan (5 pesan sebelum limit)
- Pesan di modal menampilkan format yang sama seperti pesan otomatis
