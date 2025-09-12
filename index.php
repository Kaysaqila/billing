<?php
session_start();
include 'db.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Billing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --light-gray: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7f9;
            color: #333;
            line-height: 1.6;
        }
        
        /* Header Styles */
        .header {
            background: linear-gradient(135deg, var(--secondary) 0%, #1a2530 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 15px;
            border-radius: 20px;
        }
        
        .logout-btn {
            background-color: var(--danger);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Container Styles */
        .container {
            padding: 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Stats Cards - MODIFIED */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer; /* Menandakan bisa diklik */
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        /* Style untuk filter yang aktif - BARU */
        .stat-card.active-filter {
            box-shadow: 0 0 0 3px var(--primary), 0 8px 16px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 32px;
            padding: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .icon.bg-primary { background: var(--primary); }
        .icon.bg-success { background: var(--success); }
        .icon.bg-danger { background: var(--danger); }
        
        .stat-card h3 {
            font-size: 28px;
            margin: 0;
            color: var(--secondary);
        }
        
        .stat-card p {
            color: var(--gray);
            font-weight: 500;
            margin: 0;
        }
        
        /* Table Styles - (No major changes) */
        .table-section {
            background: white;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .table-header h2 { font-size: 20px; color: var(--secondary); }
        
        .search-box {
            display: flex; align-items: center; background: var(--light); border: 1px solid var(--light-gray);
            padding: 8px 15px; border-radius: 25px; width: 300px;
        }
        
        .search-box input { border: none; background: transparent; padding: 5px; width: 100%; outline: none; }
        
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        
        .modern-table thead th {
            background: #f8f9fa; color: var(--secondary); font-weight: 600; padding: 14px 18px; text-align: left;
            text-transform: uppercase; font-size: 13px; letter-spacing: .4px; border-bottom: 2px solid var(--light-gray);
        }
        .modern-table th:nth-child(1) { width: 4%; }   /* No */
        .modern-table th:nth-child(2) { width: 15%; }  /* ID Pelanggan */
        .modern-table th:nth-child(3) { width: 15%; }  /* Nama Pelanggan */
        .modern-table th:nth-child(4),
        .modern-table th:nth-child(5),
        .modern-table th:nth-child(6) { width: 10%; } /* Paket, Bulan, Tagihan */
        .modern-table th:nth-child(7) { width: 10%; }   /* Status */
        .modern-table th:nth-child(8) { width: 27%; min-width: 240px;} /* Aksi, dengan lebar minimum */
        .modern-table td { padding: 14px 18px; border-bottom: 1px solid var(--light-gray); vertical-align: middle; color: #333; word-wrap: break-word; }
        .id-pill { display: inline-block; padding: 6px 10px; background: var(--light-gray); border-radius: 15px; color: var(--secondary); font-weight: 600; font-size: 13px; }
        .name-cell { font-weight: 600; color: var(--dark); }
        .muted { color: var(--gray); font-size: 13px; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 15px; font-size: 13px; font-weight: 700; color: white; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .badge:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .badge-success { background: var(--success); }
        .badge-danger { background: var(--danger); }
        .action-btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 140ms ease; color: #fff; text-decoration: none; margin-bottom: 4px; }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .btn-tagihan { background: var(--success); }
        .btn-resi { background: var(--warning); }
        .btn-edit { background: var(--primary); }

        /* Pagination & Modal Styles (Unchanged) */
        .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; }
        .pagination button { padding: 10px 16px; background-color: white; color: var(--dark); border: 1px solid var(--light-gray); border-radius: 6px; cursor: pointer; transition: all 0.2s ease; }
        .pagination button:hover:not(:disabled) { background-color: var(--primary); color: white; border-color: var(--primary); }
        .pagination button:disabled { opacity: 0.6; cursor: not-allowed; }
        .pagination button.active { background-color: var(--primary); color: white; border-color: var(--primary); }
        #edit-modal { position: fixed; inset: 0; display: flex; align-items: center; justify-content: center; z-index: 2000; pointer-events: none; opacity: 0; transition: opacity 280ms cubic-bezier(.2,.9,.2,1); }
        #edit-modal .backdrop { position: absolute; inset: 0; background: rgba(6,12,24,0.56); backdrop-filter: blur(6px); opacity: 0; transition: opacity 280ms cubic-bezier(.2,.9,.2,1); }
        #edit-modal .modal-box { position: relative; width: 90%; max-width: 900px; height: 80vh; background: #fff; border-radius: 10px; transform: translateY(12px) scale(.98); opacity: 0; transition: transform 320ms cubic-bezier(.2,.9,.2,1), opacity 260ms ease; box-shadow: 0 30px 60px rgba(8,15,30,0.35); overflow: hidden; display: flex; flex-direction: column; }
        #edit-modal.open { pointer-events: auto; opacity: 1; }
        #edit-modal.open .backdrop { opacity: 1; }
        #edit-modal.open .modal-box { transform: translateY(0) scale(1); opacity: 1; }
        .modal-header { display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:#f7f9fb;border-bottom:1px solid #eef3f6; }
        .modal-title { font-weight:700;color:#222; }
        .modal-close { background:transparent;border-radius:8px;border:none;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform 180ms ease, background 180ms ease; }
        .modal-close:hover { transform: rotate(90deg) scale(1.06); background:#f0f4f8; }
        .modal-iframe { width:100%; flex:1 1 auto; border:0; display:block; }
    </style>
</head>
<body>

    <div class="header">
        <h1><i class="fas fa-file-invoice-dollar"></i> Dashboard Billing</h1>
        <div class="header-actions">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="container">

        <div class="stats-cards">
            <div class="stat-card" data-filter="all" onclick="filterByStatus('all')">
                <div class="icon bg-primary"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <h3 id="total-pelanggan">...</h3>
                    <p>Total Pelanggan</p>
                </div>
            </div>
            <div class="stat-card" data-filter="lunas" onclick="filterByStatus('lunas')">
                <div class="icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3 id="tagihan-lunas">...</h3>
                    <p>Lunas Bulan Ini</p>
                </div>
            </div>
            <div class="stat-card" data-filter="belum lunas" onclick="filterByStatus('belum lunas')">
                <div class="icon bg-danger"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3 id="tagihan-belum-lunas">...</h3>
                    <p>Belum Lunas Bulan Ini</p>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Billing Pelanggan</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" placeholder="Cari pelanggan...">
                </div>
            </div>
            
            <div id="table-container">
                </div>
        </div>

        <div class="pagination" id="pagination">
            </div>
    </div>

    <script>
        // Modal logic (unchanged)
        function createEditModalIfNeeded() { if (document.getElementById('edit-modal')) return; const modal = document.createElement('div'); modal.id = 'edit-modal'; modal.innerHTML = `<div class="backdrop"></div><div class="modal-box"><div class="modal-header"><div class="modal-title">Edit Tagihan</div><button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button></div><iframe id="edit-iframe" class="modal-iframe" src="about:blank"></iframe></div>`; modal.querySelector('.backdrop').addEventListener('click', closeEditModal); document.body.appendChild(modal); document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeEditModal(); }); }
        function openEditModal(id) { createEditModalIfNeeded(); const modal = document.getElementById('edit-modal'); const iframe = document.getElementById('edit-iframe'); iframe.src = `edit.php?id=${id}&modal=1`; modal.classList.add('open'); }
        function closeEditModal() { const modal = document.getElementById('edit-modal'); if (!modal) return; modal.classList.remove('open'); setTimeout(() => { document.getElementById('edit-iframe').src = 'about:blank'; loadData(currentPage, currentSearch, currentFilter); loadStats(); }, 320); }
        window.addEventListener('message', (e) => { try { const data = JSON.parse(e.data); if (data && data.action === 'close') closeEditModal(); } catch (err) {} });
        
        // --- SCRIPT UTAMA DIMODIFIKASI ---
        let currentPage = 1;
        const itemsPerPage = 10;
        let currentSearch = "";
        let currentFilter = "all"; // Variabel baru untuk menyimpan filter aktif

        // Fungsi BARU untuk menangani filter
        function filterByStatus(status) {
            currentFilter = status;

            // Perbarui tampilan visual kartu yang aktif
            document.querySelectorAll('.stat-card').forEach(card => {
                card.classList.remove('active-filter');
            });
            document.querySelector(`.stat-card[data-filter="${status}"]`).classList.add('active-filter');

            currentPage = 1; // Selalu kembali ke halaman 1 saat filter diubah
            loadData(currentPage, currentSearch, currentFilter);
        }

        function loadStats() { fetch('get_stats.php').then(response => response.json()).then(data => { document.getElementById('total-pelanggan').innerText = data.total_pelanggan; document.getElementById('tagihan-lunas').innerText = data.lunas_bulan_ini; document.getElementById('tagihan-belum-lunas').innerText = data.belum_lunas_bulan_ini; }).catch(err => console.error('Error loading stats:', err)); }

        // Fungsi loadData DIMODIFIKASI untuk menyertakan parameter filter
        function loadData(page, search = "", filter = "all") {
            currentSearch = search;
            currentFilter = filter;
            document.getElementById('table-container').innerHTML = `<div style="padding: 40px; text-align: center; color: var(--gray);"><i class="fas fa-spinner fa-spin fa-2x"></i></div>`;
            
            // Tambahkan parameter &filter= ke URL fetch
            const fetchUrl = `get_data.php?page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(search)}&filter=${encodeURIComponent(filter)}`;

            fetch(fetchUrl)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('table-container').innerHTML = data.table;
                    renderPagination(data.totalPages, page);
                })
                .catch(err => {
                    document.getElementById('table-container').innerHTML = `<div style="padding: 40px; text-align: center; color: var(--danger);">Error memuat data.</div>`;
                });
        }
        
        function updateStatus(id, currentStatus) {
            const newStatus = currentStatus === 'Lunas' ? 'Belum Lunas' : 'Lunas';
            Swal.fire({
                title: 'Konfirmasi Perubahan Status', text: `Anda yakin ingin mengubah status menjadi "${newStatus}"?`, icon: 'warning', showCancelButton: true,
                confirmButtonColor: '#3085d6', cancelButtonColor: '#d33', confirmButtonText: 'Ya, ubah!', cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('update_status.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: id, status: newStatus }) })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Berhasil!', 'Status telah berhasil diperbarui.', 'success');
                            loadData(currentPage, currentSearch, currentFilter);
                            loadStats();
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui status.', 'error');
                        }
                    }).catch(err => { Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error'); });
                }
            });
        }

        // Fungsi renderPagination DIMODIFIKASI agar onclick memanggil loadData dengan filter
        function renderPagination(totalPages, currentPage) {
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = '';
            if (totalPages <= 1) return;
            const createButton = (text, page, isDisabled = false, isActive = false) => {
                const btn = document.createElement('button');
                btn.innerHTML = text;
                btn.disabled = isDisabled;
                if (isActive) btn.classList.add('active');
                btn.onclick = () => { loadData(page, currentSearch, currentFilter); };
                return btn;
            };
            paginationDiv.appendChild(createButton('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1));
            for (let i = 1; i <= totalPages; i++) { paginationDiv.appendChild(createButton(i, i, false, i === currentPage)); }
            paginationDiv.appendChild(createButton('<i class="fas fa-chevron-right"></i>', currentPage + 1, currentPage === totalPages));
        }

        function sendReceipt(id, tujuan, encodedMessage) {
            Swal.fire({
                title: 'Konfirmasi Pengiriman Resi',
                text: "Mengirim resi akan mengubah status tagihan ini menjadi LUNAS. Lanjutkan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#27ae60',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim & LUNASKAN!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Langkah 1: Ubah status menjadi Lunas via AJAX
                    fetch('update_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: id, status: 'Lunas' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Langkah 2: Jika berhasil, buka link WhatsApp
                            const whatsappUrl = `https://wa.me/${tujuan}?text=${encodedMessage}`;
                            window.open(whatsappUrl, '_blank');

                            Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Status telah diubah menjadi Lunas.', timer: 2000, showConfirmButton: false });
                            
                            // Langkah 3: Muat ulang data di tabel dan statistik
                            loadData(currentPage, currentSearch, currentFilter);
                            loadStats();
                        } else {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat memperbarui status.', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Error!', 'Tidak dapat terhubung ke server.', 'error');
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentPage = 1;
                    loadData(currentPage, searchInput.value.trim(), currentFilter);
                }, 300);
            });
            // Muat data dan statistik awal, lalu set filter default secara visual
            loadData(currentPage, currentSearch, currentFilter);
            loadStats();
            document.querySelector('.stat-card[data-filter="all"]').classList.add('active-filter');
        });
    </script>

</body>
</html>