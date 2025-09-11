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
        
        .welcome-section {
            background: linear-gradient(120deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .welcome-section h2 {
            font-size: 24px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .welcome-section p {
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
    /* Table Styles */
    .table-section {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            font-size: 20px;
            color: var(--secondary);
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: var(--light);
            padding: 8px 15px;
            border-radius: 4px;
            width: 300px;
        }
        
        .search-box input {
            border: none;
            background: transparent;
            padding: 5px;
            width: 100%;
            outline: none;
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        /* modern table look */
        .modern-table thead th {
            /* gunakan warna biru sebagai latar header (teks tidak diubah) */
            background: linear-gradient(90deg, rgba(52,152,219,0.18), rgba(52,152,219,0.08));
            color: var(--secondary);
            font-weight: 700;
            padding: 14px 18px;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: .4px;
            position: sticky;
            top: 0;
            border: none;
        }

        .modern-table tbody tr {
            background: white;
            box-shadow: 0 6px 18px rgba(23,35,48,0.04);
            /* remove hover transition/transform to disable 'lift' effect */
            border-radius: 8px;
            overflow: hidden;
        }

        /* Disable hover visual response - keep consistent appearance on hover/scroll */
        .modern-table tbody tr:hover {
            transform: none;
            box-shadow: 0 6px 18px rgba(23,35,48,0.04);
        }

        .modern-table td {
            padding: 14px 18px;
            border: none;
            vertical-align: middle;
            color: #333;
        }

        .id-pill {
            display: inline-block;
            padding: 6px 10px;
            background: linear-gradient(90deg, #f1f7ff, #f8fff6);
            border-radius: 999px;
            color: var(--secondary);
            font-weight: 600;
            font-size: 13px;
            box-shadow: inset 0 -2px 0 rgba(0,0,0,0.02);
        }

        .name-cell {
            font-weight: 600;
            color: var(--dark);
        }

        .muted {
            color: var(--gray);
            font-size: 13px;
            display: block;
            margin-top: 4px;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            color: white;
        }

        .badge-success { background: linear-gradient(90deg,#27ae60,#2ecc71); }
        .badge-warning { background: linear-gradient(90deg,#f39c12,#f1c40f); }
        .badge-danger  { background: linear-gradient(90deg,#e74c3c,#ff6b6b); }

        /* action buttons tweaks */
        .action-btn {
            margin-right: 8px;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 140ms ease, box-shadow 140ms ease;
            box-shadow: 0 6px 14px rgba(51,65,85,0.06);
        }

        .action-btn:hover { transform: translateY(-3px); box-shadow: 0 18px 40px rgba(51,65,85,0.08); }

        .btn-tagihan { background: linear-gradient(90deg,#27ae60,#18a058); color: #fff; }
        .btn-resi { background: linear-gradient(90deg,#f39c12,#e67e22); color: #fff; }
        .btn-edit { background: linear-gradient(90deg,#3498db,#2980b9); color: #fff; }

        /* card-like spacing for table rows */
        .modern-table tbody tr td:first-child { width: 60px; }
        
        .action-btn {
            margin-right: 8px;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-tagihan { 
            background-color: var(--success); 
            color: white; 
        }
        
        .btn-tagihan:hover { 
            background-color: #219653; 
        }
        
        .btn-resi { 
            background-color: var(--warning); 
            color: white; 
        }
        
        .btn-resi:hover { 
            background-color: #e67e22; 
        }
        
        .btn-edit { 
            background-color: var(--primary); 
            color: white; 
        }
        
        .btn-edit:hover { 
            background-color: #2980b9; 
        }
        
        /* Pagination Styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 30px;
        }
        
        .pagination button {
            padding: 10px 16px;
            background-color: white;
            color: var(--dark);
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s ease;
        }
        
        .pagination button:hover:not(:disabled) {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination button:disabled {
            background-color: var(--light-gray);
            color: var(--gray);
            cursor: not-allowed;
        }
        
        .pagination button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .page-info {
            margin: 0 15px;
            color: var(--gray);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--gray);
            font-size: 14px;
            margin-top: 40px;
            border-top: 1px solid var(--light-gray);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .container {
                padding: 20px;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .header-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
            }
            
            .logout-btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .table-header {
                flex-direction: column;
                gap: 15px;
            }
            
            .search-box {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
        /* Additional responsive tweaks for tablets and phones */
        @media (max-width: 992px) {
            .container { padding: 20px; }
            .modern-table thead th { font-size: 12px; padding: 10px 12px; }
            .modern-table td { padding: 10px 12px; font-size: 14px; }
            .action-btn { padding: 6px 10px; font-size: 13px; }
        }

        @media (max-width: 768px) {
            .table-section { padding: 12px; }
            .table-header h2 { font-size: 16px; }
            .search-box { width: 100%; }

            /* Make table rows appear as stacked cards for small screens */
            .modern-table thead { display: none; }
            .modern-table, .modern-table tbody, .modern-table tr, .modern-table td { display: block; width: 100%; }
            .modern-table tbody tr { margin-bottom: 12px; padding: 12px; border-radius: 10px; background: #fff; box-shadow: 0 6px 16px rgba(23,35,48,0.04); }
            .modern-table td { padding: 6px 0; border: none; display: flex; justify-content: space-between; align-items: center; }
            .modern-table td .name-cell { display: block; }
            .modern-table td .muted { display: block; font-size: 13px; color: var(--gray); }
            .modern-table td:nth-child(1), .modern-table td:nth-child(4), .modern-table td:nth-child(5) { display: none; }
            /* Keep important fields visible: ID, Name, Tagihan, Status, Aksi */

            /* Action buttons wrap nicely */
            .action-btn { padding: 6px 8px; font-size: 13px; margin: 4px 6px 4px 0; }
            td:last-child { display: flex; gap: 6px; flex-wrap: wrap; }

            /* Modal becomes fullscreen-ish on small devices */
            .modal-box { width: 100%; max-width: 100%; height: calc(100vh - 32px); border-radius: 8px; display: flex; flex-direction: column; }
            .modal-iframe { height: calc(100vh - 68px); }
            .modal-header { padding: 12px 14px; }
        }
        /* Modal (smooth, blurred backdrop, scale+fade) */
        #edit-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 280ms cubic-bezier(.2,.9,.2,1);
        }

        #edit-modal .backdrop {
            position: absolute;
            inset: 0;
            background: rgba(6,12,24,0.56);
            backdrop-filter: blur(6px);
            opacity: 0;
            transition: opacity 280ms cubic-bezier(.2,.9,.2,1);
        }

        #edit-modal .modal-box {
            position: relative;
            width: 90%;
            max-width: 900px;
            height: 80vh; /* make modal taller so iframe can fill */
            background: #fff;
            border-radius: 10px;
            transform: translateY(12px) scale(.98);
            opacity: 0;
            transition: transform 320ms cubic-bezier(.2,.9,.2,1), opacity 260ms ease;
            box-shadow: 0 30px 60px rgba(8,15,30,0.35);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        #edit-modal.open { pointer-events: auto; opacity: 1; }
        #edit-modal.open .backdrop { opacity: 1; }
        #edit-modal.open .modal-box { transform: translateY(0) scale(1); opacity: 1; }

    .modal-header { display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:linear-gradient(90deg,#f7f9fb,#ffffff);border-bottom:1px solid #eef3f6; }
    .modal-title { font-weight:700;color:#222; }
    .modal-close { background:transparent;border-radius:8px;border:none;width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:transform 180ms ease, background 180ms ease; }
    .modal-close:hover { transform: rotate(90deg) scale(1.06); background:#f0f4f8; }
    .modal-iframe { width:100%; flex:1 1 auto; border:0; display:block; height: calc(100% - 56px); }
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
        <div class="welcome-section">
            <h2><i class="fas fa-hand-wave"></i> Welkam, <?= htmlspecialchars($_SESSION['username']); ?> (*^▽^*)</h2>
            <p>setel playlist, ratakan gawean</p>
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
                <!-- Tabel akan di-load via AJAX -->
                <div style="padding: 40px; text-align: center; color: var(--gray);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 15px;"></i>
                    <p>Memuat data...</p>
                </div>
            </div>
        </div>

        <div class="pagination" id="pagination">
            <!-- Pagination buttons akan di-generate via JS -->
        </div>
    </div>

    <div class="footer">
        <p>© 2025 Dashboard Billing - Sistem Manajemen Tagihan</p>
    </div>

    <script>

        // Modal for edit (DOM + animations)
        function createEditModalIfNeeded() {
            if (document.getElementById('edit-modal')) return;
            const modal = document.createElement('div');
            modal.id = 'edit-modal';

            const backdrop = document.createElement('div');
            backdrop.className = 'backdrop';

            const box = document.createElement('div');
            box.className = 'modal-box';

            const header = document.createElement('div');
            header.className = 'modal-header';
            const title = document.createElement('div');
            title.className = 'modal-title';
            title.innerText = 'Edit Tagihan';
            const closeBtn = document.createElement('button');
            closeBtn.className = 'modal-close';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.onclick = closeEditModal;

            header.appendChild(title);
            header.appendChild(closeBtn);

            const iframe = document.createElement('iframe');
            iframe.id = 'edit-iframe';
            iframe.className = 'modal-iframe';

            box.appendChild(header);
            box.appendChild(iframe);
            modal.appendChild(backdrop);
            modal.appendChild(box);

            // click outside to close
            backdrop.addEventListener('click', closeEditModal);

            document.body.appendChild(modal);

            // close on ESC
            document.addEventListener('keydown', function onEsc(e){
                if (e.key === 'Escape') closeEditModal();
            });
        }

        function openEditModal(id) {
            createEditModalIfNeeded();
            const modal = document.getElementById('edit-modal');
            const iframe = document.getElementById('edit-iframe');
            iframe.src = `edit.php?id=${id}&modal=1`;
            // force reflow then add class for transition
            modal.offsetWidth; // reflow
            modal.classList.add('open');
        }

        function closeEditModal() {
            const modal = document.getElementById('edit-modal');
            if (!modal) return;
            modal.classList.remove('open');
            // wait animation end before cleaning iframe and refreshing
            const iframe = document.getElementById('edit-iframe');
            setTimeout(() => {
                if (iframe) iframe.src = 'about:blank';
                // refresh current table page after closing
                loadData(currentPage, currentSearch);
            }, 320);
        }

        // Listen to postMessage from edit iframe to close modal when update done
        window.addEventListener('message', function(e){
            // simple origin-agnostic check since same-origin in this app; accept { action: 'close' }
            try {
                const data = typeof e.data === 'string' ? JSON.parse(e.data) : e.data;
                if (data && data.action === 'close') {
                    closeEditModal();
                }
            } catch(err) {
                // ignore
            }
        });

        let currentPage = 1;
        const itemsPerPage = 10;
        let currentSearch = "";

        // Fungsi untuk load data via AJAX
        function loadData(page, search = "") {
            currentSearch = search;
            document.getElementById('table-container').innerHTML = `
                <div style="padding: 40px; text-align: center; color: var(--gray);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 15px;"></i>
                    <p>Memuat data...</p>
                </div>
            `;
            
            fetch(`get_data.php?page=${page}&limit=${itemsPerPage}&search=${encodeURIComponent(search)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('table-container').innerHTML = data.table;
                    renderPagination(data.totalPages, page);
                })
                .catch(err => {
                    console.error('Error:', err);
                    document.getElementById('table-container').innerHTML = `
                        <div style="padding: 40px; text-align: center; color: var(--danger);">
                            <i class="fas fa-exclamation-triangle" style="font-size: 24px; margin-bottom: 15px;"></i>
                            <p>Terjadi kesalahan saat memuat data.</p>
                            <button onclick="loadData(${page}, '${search}')" class="action-btn btn-edit">
                                <i class="fas fa-redo"></i> Coba Lagi
                            </button>
                        </div>
                    `;
                });
        }

        // Render tombol pagination
        function renderPagination(totalPages, currentPage) {
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = '';

            // Info halaman
            const pageInfo = document.createElement('span');
            pageInfo.className = 'page-info';
            pageInfo.innerText = `Halaman ${currentPage} dari ${totalPages}`;
            paginationDiv.appendChild(pageInfo);

            // Tombol Previous
            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Sebelumnya';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    loadData(currentPage, currentSearch);
                }
            };
            paginationDiv.appendChild(prevBtn);

            // Tombol nomor halaman (maksimal 5 tombol)
            const startPage = Math.max(1, currentPage - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.innerText = i;
                if (i === currentPage) pageBtn.classList.add('active');
                pageBtn.onclick = () => {
                    currentPage = i;
                    loadData(currentPage, currentSearch);
                };
                paginationDiv.appendChild(pageBtn);
            }

            // Tombol Next
            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = 'Selanjutnya <i class="fas fa-chevron-right"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    loadData(currentPage, currentSearch);
                }
            };
            paginationDiv.appendChild(nextBtn);
        }

        // Event listener untuk input pencarian
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = searchInput.value.trim();
                    // selalu reset ke halaman 1 saat melakukan pencarian
                    currentPage = 1;

                    // jika input kosong, kembali ke pagination biasa
                    if (query === '') {
                        loadData(currentPage);
                        const notFound = document.getElementById('not-found');
                        if (notFound) notFound.remove();
                        return;
                    }

                    // Lakukan pencarian server-side agar mencari di semua data, bukan hanya halaman sekarang
                    loadData(currentPage, query);

                }, 300); // debounce
            });
            loadData(currentPage);
        });
    </script>

</body>
</html>