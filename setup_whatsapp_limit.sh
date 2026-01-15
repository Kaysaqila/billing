#!/bin/bash

# ============================================================================
# SETUP SCRIPT - Sistem Pembatasan Pengiriman Pesan WhatsApp
# ============================================================================

echo "======================================"
echo "  Setup Sistem Pembatasan WhatsApp"
echo "======================================"
echo ""

# Database configuration
DB_HOST="localhost"
DB_USER="root"
DB_PASS="123"
DB_NAME="billing_otw"

echo "1. Creating message_send_log table..."

mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" << EOF
CREATE TABLE IF NOT EXISTS message_send_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wilayah VARCHAR(50) NOT NULL,
    id_pelanggan VARCHAR(100),
    message_type VARCHAR(50) DEFAULT 'tagihan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wilayah_date (wilayah, created_at)
);

INSERT INTO message_send_log (wilayah, id_pelanggan, message_type) 
VALUES ('jogja', 'TEST001', 'tagihan');

SELECT COUNT(*) as total_records FROM message_send_log;
EOF

echo ""
echo "✓ Setup selesai!"
echo ""
echo "======================================"
echo "  File-File yang Sudah Disetup:"
echo "======================================"
echo ""
echo "✓ check_message_limit.php"
echo "  - API untuk check & increment counter"
echo ""
echo "✓ get_data.php"
echo "  - Modifikasi tombol 'Kirim Tagihan'"
echo ""
echo "✓ dashboard_jogja.php"
echo "  - Modal pesan + JavaScript functions"
echo ""
echo "✓ dashboard_samiran.php"
echo "  - Modal pesan + JavaScript functions"
echo ""
echo "✓ dashboard_godean.php"
echo "  - Modal pesan + JavaScript functions"
echo ""
echo "======================================"
echo "  Fitur Utama:"
echo "======================================"
echo ""
echo "1. Pengiriman Otomatis: Pesan 1-40"
echo "   → Langsung buka WhatsApp Web"
echo ""
echo "2. Pengiriman Manual: Pesan 41+"
echo "   → Tampilkan modal dengan pesan"
echo "   → User salin & paste manual"
echo ""
echo "======================================"
echo ""
echo "Siap digunakan!"
