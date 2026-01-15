-- Tabel untuk mencatat setiap pengiriman pesan WhatsApp
CREATE TABLE IF NOT EXISTS message_send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wilayah VARCHAR(50) NOT NULL,
    id_pelanggan VARCHAR(100),
    message_type VARCHAR(50) DEFAULT 'tagihan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wilayah_date (wilayah, created_at)
);
