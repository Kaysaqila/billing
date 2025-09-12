<?php
session_start();
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
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--light-gray);
        }
        
        th {
            background-color: #f8f9fa;
            color: var(--secondary);
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
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
            <p>buset</p>
        </div>

        <div class="table-section">
            <div class="table-header">
                <h2><i class="fas fa-list"></i> Daftar Billing Pelanggan</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input id="search-input" type="text" placeholder="Cari pelanggan..." aria-label="Cari pelanggan">
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
        let currentPage = 1;
        const itemsPerPage = 10;
        let currentSearch = '';

        // Debounce helper
        function debounce(fn, delay = 300) {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn(...args), delay);
            };
        }

        // Fungsi untuk load data via AJAX (kirim cookie/session dan search)
        function loadData(page = 1, search = '') {
            currentPage = page;
            currentSearch = search;

            console.log('Loading data - Page:', page, 'Search:', search); // Debug log

            document.getElementById('table-container').innerHTML = `
                <div style="padding: 40px; text-align: center; color: var(--gray);">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; margin-bottom: 15px;"></i>
                    <p>Memuat data...</p>
                </div>
            `;

            const url = `get_data.php?page=${page}&limit=${itemsPerPage}` + (search ? `&search=${encodeURIComponent(search)}` : '');
            console.log('Fetching URL:', url); // Debug log

            fetch(url, {
                method: 'GET',
                credentials: 'same-origin', // pastikan cookie/session dikirim
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.status === 401) {
                    // session expired / unauthorized -> redirect ke login
                    window.location.href = 'login.php';
                    throw new Error('Unauthorized');
                }
                return response.json();
            })
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
                        <button onclick="loadData(${page}, '${encodeURIComponent(search)}')" class="action-btn btn-edit">
                            <i class="fas fa-redo"></i> Coba Lagi
                        </button>
                    </div>
                `;
            });
        }

        // Render tombol pagination (menggunakan currentSearch agar filter tetap aktif)
        function renderPagination(totalPages, currentPageLocal) {
            const paginationDiv = document.getElementById('pagination');
            paginationDiv.innerHTML = '';

            const pageInfo = document.createElement('span');
            pageInfo.className = 'page-info';
            pageInfo.innerText = `Halaman ${currentPageLocal} dari ${totalPages}`;
            paginationDiv.appendChild(pageInfo);

            const prevBtn = document.createElement('button');
            prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i> Sebelumnya';
            prevBtn.disabled = currentPageLocal === 1;
            prevBtn.onclick = () => {
                if (currentPageLocal > 1) {
                    loadData(currentPageLocal - 1, currentSearch);
                }
            };
            paginationDiv.appendChild(prevBtn);

            const startPage = Math.max(1, currentPageLocal - 2);
            const endPage = Math.min(totalPages, startPage + 4);
            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.innerText = i;
                if (i === currentPageLocal) pageBtn.classList.add('active');
                pageBtn.onclick = () => loadData(i, currentSearch);
                paginationDiv.appendChild(pageBtn);
            }

            const nextBtn = document.createElement('button');
            nextBtn.innerHTML = 'Selanjutnya <i class="fas fa-chevron-right"></i>';
            nextBtn.disabled = currentPageLocal === totalPages;
            nextBtn.onclick = () => {
                if (currentPageLocal < totalPages) {
                    loadData(currentPageLocal + 1, currentSearch);
                }
            };
            paginationDiv.appendChild(nextBtn);
        }

        // Hubungkan input search dengan debounce
        const searchInput = document.getElementById('search-input');
        const onSearch = debounce(function () {
            const q = this.value.trim();
            console.log('Search query:', q); // Debug log
            loadData(1, q);
        }, 300);
        searchInput.addEventListener('input', onSearch);

        // Load data pertama kali (jika ada query awal, dapat diisi di future)
        window.onload = () => loadData(currentPage, currentSearch);
    </script>

</body>
</html>