<?php
session_start();
include 'db.php';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Batasi akses: hanya untuk wilayah Samiran. User wilayah lain diarahkan ke dashboard masing-masing
if (!isset($_SESSION['wilayah']) || $_SESSION['wilayah'] !== 'samiran') {
    if (isset($_SESSION['wilayah']) && $_SESSION['wilayah'] === 'godean') {
        header('Location: dashboard_godean.php');
        exit;
    }
    header('Location: dashboard_jogja.php');
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
    <link rel="icon" href="logo.png" type="image/png">
    <link rel="shortcut icon" href="logo.png" type="image/png">
    <link rel="apple-touch-icon" sizes="180x180" href="logo.png">
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
            background: var(--primary); /* Diubah menjadi warna biru utama */
            color: white; /* Diubah menjadi warna putih */
            font-weight: 600;
            padding: 14px 18px;
            text-align: left;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: .4px;
            /* Ganti border agar serasi dengan background biru */
            border-bottom: 2px solid #2980b9; 
        }
    .modern-table th:nth-child(1) { width: 5%; }   /* No */
    .modern-table th:nth-child(2) { width: 12%; }  /* ID Pelanggan */
    .modern-table th:nth-child(3) { width: 14%; }  /* Nama Pelanggan */
    .modern-table th:nth-child(4) { width: 12%; }  /* Paket */
    .modern-table th:nth-child(5) { width: 12%; }  /* Masa Aktif */
    .modern-table th:nth-child(6) { width: 10%; }  /* Bulan */
    .modern-table th:nth-child(7) { width: 10%; }   /* Tagihan */
    .modern-table th:nth-child(8) { width: 10%; }   /* Status */
    .modern-table th:nth-child(9) { width: 25%; min-width: 240px;} /* Aksi, dengan lebar minimum */
        .modern-table td { padding: 14px 18px; border-bottom: 1px solid var(--light-gray); vertical-align: middle; color: #333; word-wrap: break-word; }
        .id-pill { display: inline-block; padding: 6px 10px; background: var(--light-gray); border-radius: 15px; color: var(--secondary); font-weight: 600; font-size: 13px; }
        .name-cell { font-weight: 600; color: var(--dark); }
        .muted { color: var(--gray); font-size: 13px; }
        .badge { display: inline-block; padding: 6px 12px; border-radius: 15px; font-size: 13px; font-weight: 700; color: white; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .badge:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .badge-success { background: var(--success); }
        .badge-danger { background: var(--danger); }
    .action-btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 140ms ease; color: #fff; text-decoration: none; margin: 6px 4px; }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .btn-tagihan { background: var(--success); }
        .btn-resi { background: var(--warning); }
        .btn-edit { background: var(--primary); }

        /* Pagination & Modal Styles (Unchanged) */
    .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; margin-bottom: 40px; }
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

        .table-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modern-table td.actions-cell {
            display: flex;
            flex-direction: row;
            justify-content: flex-start;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
        }
        .modern-table td.actions-cell .action-row { display: inline-flex; gap: 8px; }

        .btn-create {
            background: var(--primary); /* Warna biru agar serasi dengan tombol Edit */
        }

        /* -- Styles untuk Add Modal (BARU) -- */
    #add-modal {
        position: fixed; 
        inset: 0; 
        display: none; /* Awalnya disembunyikan */
        align-items: center; 
        justify-content: center; 
        z-index: 2000; 
        pointer-events: none; 
        opacity: 0; 
        transition: opacity 280ms cubic-bezier(.2,.9,.2,1);
    }
    #add-modal.open {
        pointer-events: auto; 
        opacity: 1;
    }
    #add-modal .backdrop { 
        position: absolute; 
        inset: 0; 
        background: rgba(6,12,24,0.56); 
        backdrop-filter: blur(6px); 
        opacity: 0; 
        transition: opacity 280ms cubic-bezier(.2,.9,.2,1); 
    }
    #add-modal.open .backdrop { 
        opacity: 1; 
    }
    #add-modal .modal-box {
        position: relative; 
        width: 90%; 
        max-width: 700px; /* Lebar modal bisa disesuaikan */
        background: #fff; 
        border-radius: 10px; 
        transform: translateY(12px) scale(.98); 
        opacity: 0; 
        transition: transform 320ms cubic-bezier(.2,.9,.2,1), opacity 260ms ease; 
        box-shadow: 0 30px 60px rgba(8,15,30,0.35); 
        overflow: hidden;
    }
    #add-modal.open .modal-box { 
        transform: translateY(0) scale(1); 
        opacity: 1; 
    }
    /* Style tambahan untuk form di dalam modal (opsional, tapi disarankan) */
    #add-modal .grid { display: flex; gap: 12px; }
    #add-modal .col { flex: 1; }
    #add-modal label.small { display: block; color: #6c757d; font-size: 13px; margin-bottom: 6px; }
    #add-modal input[type="text"],
    #add-modal input[type="number"] {
        width: 100%;
        padding: 10px 12px;
        border-radius: 8px;
        border: 1px solid #e6eef6;
        font-size: 14px;
        box-sizing: border-box;
    }
    #add-modal .note { font-size: 13px; color: #6c757d; margin-top: 12px; }
    #add-modal .actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; }
    #add-modal .btn { padding: 10px 14px; border-radius: 8px; border: 0; cursor: pointer; font-weight: 600; }
    #add-modal .btn-primary { background: #f1f5f9; (90deg,var(--accent),#2980b9); color: #213; }
    #add-modal .btn-muted { background: #f1f5f9; color: #213; }

    /* --- STYLES UNTUK RESPONSIVE --- */
/* Tambahkan kode ini di bagian paling bawah tag <style> Anda */

/* Untuk Tablet & Perangkat Lebih Kecil (di bawah 820px) */
@media (max-width: 820px) {
    .container {
        padding: 20px 15px; /* Kurangi padding di layar kecil */
    }

    .header h1 {
        font-size: 20px; /* Perkecil judul header */
    }

    .table-header {
        flex-direction: column; /* Susun judul dan aksi secara vertikal */
        align-items: flex-start; /* Rata kiri */
        gap: 20px;
    }

    .table-actions {
        width: 100%; /* Lebarkan grup aksi */
        justify-content: space-between;
    }
    
    .search-box {
        flex-grow: 1; /* Biarkan search box memanjang */
    }

    /* --- Mengubah Tabel Menjadi Tampilan "Kartu" --- */
    .modern-table thead {
        display: none; /* Sembunyikan header tabel asli di mobile */
    }

    .modern-table tr {
        display: block; /* Ubah baris menjadi blok */
        margin-bottom: 15px; /* Jarak antar "kartu" */
        border: 1px solid var(--light-gray);
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    }

    .modern-table td {
        display: block; /* Ubah sel menjadi blok */
        width: 100%;
        padding: 12px 15px;
        padding-left: 45%; /* Sediakan ruang kiri untuk label */
        position: relative;
        text-align: right; /* Rata kanan untuk isi data */
        border-bottom: 1px solid var(--light-gray);
    }

    .modern-table td:last-child {
        border-bottom: none;
    }
    
    /* Membuat label dari header tabel menggunakan pseudo-element ::before */
    .modern-table td::before {
        content: attr(data-label); /* Ini akan mengambil teks dari atribut data-label */
        position: absolute;
        left: 15px;
        width: 40%;
        text-align: left; /* Rata kiri untuk label */
        font-weight: 600;
        color: var(--secondary);
    }

    /* Menambahkan data-label secara dinamis via CSS */
    .modern-table td:nth-of-type(1)::before { content: "No"; }
    .modern-table td:nth-of-type(2)::before { content: "ID Pelanggan"; }
    .modern-table td:nth-of-type(3)::before { content: "Nama Pelanggan"; }
    .modern-table td:nth-of-type(4)::before { content: "Paket"; }
    .modern-table td:nth-of-type(5)::before { content: "Masa Aktif"; }
    .modern-table td:nth-of-type(6)::before { content: "Bulan"; }
    .modern-table td:nth-of-type(7)::before { content: "Tagihan"; }
    .modern-table td:nth-of-type(8)::before { content: "Status"; }
    .modern-table td:nth-of-type(9)::before { content: "Aksi"; }

    /* Penyesuaian untuk sel yang kontennya kompleks */
    .modern-table td .id-pill,
    .modern-table td .badge {
        float: right; /* Pastikan elemen ini tetap di kanan */
    }
    .modern-table td .name-cell,
    .modern-table td .muted {
        text-align: right;
    }
    .name-cell { display: block; word-break: break-word; overflow-wrap: anywhere; text-align: left; padding: 6px 0; border-bottom: none; }
    .modern-table td:nth-of-type(9) .action-btn {
       margin-bottom: 5px;
    }
    
    /* Membuat form di dalam modal menjadi responsif */
    #add-modal .grid,
    #edit-modal .grid { /* Target grid di kedua modal */
        flex-direction: column;
    }
    }

    /* Untuk Ponsel dengan Layar Sangat Kecil (di bawah 480px) */
    @media (max-width: 480px) {
        .header {
            flex-direction: column;
            gap: 15px;
            padding: 15px;
        }

        .user-info {
            width: 100%;
            justify-content: center;
        }
        
        .table-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .btn-create {
            justify-content: center; /* Tombol tambah jadi rata tengah */
        }

        .modern-table td {
            padding-left: 15px;
            text-align: left; /* Di layar sangat kecil, semua rata kiri */
        }

        .modern-table td::before {
            position: static;
            display: block;
            width: 100%;
            margin-bottom: 5px;
            font-size: 12px;
            color: var(--gray);
        }
        
        .modern-table td .id-pill,
        .modern-table td .badge {
            float: none;
        }
        
        .modern-table td .name-cell,
        .modern-table td .muted {
            text-align: left;
        }
        .name-cell { word-break: break-word; overflow-wrap: anywhere; }
    }
    </style>
</head>
<body>

    <div class="header">
        <h1><i class="fas fa-file-invoice-dollar"></i> Dashboard Billing Samiran</h1>
        <div class="header-actions">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($_SESSION['username']); ?></span>
            </div>
            <a href="#" onclick="confirmLogout(event)" class="logout-btn">
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

       <div class="table-header">
    <h2><i class="fas fa-list"></i> Daftar Billing Pelanggan</h2>
    
        <div class="table-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search" placeholder="Cari pelanggan...">
                </div>
                <button class="action-btn btn-create" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Tambah Pelanggan
                </button>
            </div>
        </div>
            <div id="add-modal" style="display:none;">
    <div class="backdrop" onclick="closeAddModal()"></div>
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title">Tambah Pelanggan Baru</div>
            <button class="modal-close" onclick="closeAddModal()"><i class="fas fa-times"></i></button>
        </div>
                    <div style="padding: 20px;">
                        <form id="add-customer-form">
                            <div class="grid">
                                <div class="col">
                                    <label class="small">ID Pelanggan</label>
                                    <input type="text" name="id_pelanggan">
                                </div>
                                <div class="col">
                                    <label class="small">Nama Lengkap</label>
                                    <input type="text" name="nama" required>
                                </div>
                            </div>
                            <div class="grid" style="margin-top:12px">
                                <div class="col">
                                    <label class="small">Paket</label>
                                    <input type="text" name="paket" required>
                                </div>
                                <div class="col">
                                    <label class="small">Nomor WhatsApp</label>
                                    <input type="text" name="nomor_pelanggan" placeholder="Contoh: 628123456789">
                                </div>
                            </div>
                            <div class="grid" style="margin-top:12px">
                                <div class="col">
                                    <label class="small">Tagihan Awal</label>
                                    <input type="number" name="tagihan" value="0" required>
                                </div>
                            </div>
                            <div class="note">Status akan otomatis menjadi "Belum Lunas" jika tagihan > 0.</div>

                            <div class="actions">
                                <button type="button" class="btn btn-muted" onclick="closeAddModal()">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
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

            // tombol prev
            paginationDiv.appendChild(createButton('<i class="fas fa-chevron-left"></i>', currentPage - 1, currentPage === 1));

            let maxVisible = 5; // jumlah angka yang ditampilkan di sekitar currentPage
            let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(totalPages, start + maxVisible - 1);

            if (start > 1) {
                paginationDiv.appendChild(createButton(1, 1));
                if (start > 2) paginationDiv.appendChild(createButton('...', null, true));
            }

            for (let i = start; i <= end; i++) {
                paginationDiv.appendChild(createButton(i, i, false, i === currentPage));
            }

            if (end < totalPages) {
                if (end < totalPages - 1) paginationDiv.appendChild(createButton('...', null, true));
                paginationDiv.appendChild(createButton(totalPages, totalPages));
            }

            // tombol next
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

        function confirmLogout(event) {
            event.preventDefault(); // Mencegah link berpindah halaman secara langsung

            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Anda yakin ingin keluar?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna menekan "Ya", arahkan ke halaman logout
                    window.location.href = 'logout.php';
                }
            });
        }

                // Fungsi untuk membuka dan menutup modal tambah data
        function openAddModal() {
            // Reset form setiap kali dibuka
            document.getElementById('add-customer-form').reset();
            const modal = document.getElementById('add-modal');
            modal.style.display = 'flex';
            // Gunakan timeout agar transisi CSS berjalan
            setTimeout(() => modal.classList.add('open'), 10);
        }

        function closeAddModal() {
            const modal = document.getElementById('add-modal');
            modal.classList.remove('open');
            // Sembunyikan elemen setelah transisi selesai
            setTimeout(() => modal.style.display = 'none', 320);
        }

        // Event listener untuk menangani submit form tambah pelanggan
        document.getElementById('add-customer-form').addEventListener('submit', function(event) {
            event.preventDefault(); // Mencegah form submit cara biasa

            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = 'Menyimpan...';

            fetch('add_data.php', {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const text = await response.text();
                let data = null;
                try { data = JSON.parse(text); } catch(e) { data = null; }
                if (!response.ok) {
                    const msg = data && data.message ? data.message : (text || 'Terjadi kesalahan server');
                    throw new Error(msg);
                }
                return data;
            })
            .then(data => {
                if (data && data.success) {
                    Swal.fire('Berhasil!', data.message || 'Data pelanggan baru telah ditambahkan.', 'success');
                    closeAddModal();
                    loadData(1, '', currentFilter); // Muat ulang data ke halaman pertama
                    loadStats(); // Muat ulang statistik
                } else {
                    Swal.fire('Gagal!', data && data.message ? data.message : 'Terjadi kesalahan.', 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', error.message || 'Tidak dapat terhubung ke server.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Simpan';
            });
        });
    </script>

</body>
</html>