<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Mendeteksi halaman aktif untuk menandai menu mana yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);

// Kategori halaman admin
$is_dashboard_page = in_array($current_page, ['dashboard.php']);
$is_pelamar_page   = in_array($current_page, ['pelamar.php', 'detail_pelamar.php']);
$is_ujian_page     = in_array($current_page, ['master_ujian.php', 'tambah_soal.php', 'edit_soal.php', 'lihat_soal.php']);
// Tambahan deteksi halaman untuk Master Karir
$is_karir_page     = in_array($current_page, ['master_loker.php', 'tambah_loker.php', 'edit_loker.php']);
?>
<!-- Sidebar Samping Admin dengan Posisi Sticky & Efek Hover Expand -->
<aside class="sidebar-admin-panel">
    <!-- Logo Kebab Monster di Paling Atas Sidebar -->
    <div class="sidebar-logo-container">
        <img src="../assets/img/logo-kebab.png" alt="Logo" class="sidebar-logo-img" onerror="this.style.display='none'">
    </div>

    <!-- 1. Dashboard HR -->
    <div class="menu-group">
        <a href="dashboard.php" class="menu-item <?php echo $is_dashboard_page ? 'active' : ''; ?>" title="Dashboard HR">
            <span class="menu-icon">📊</span>
            <span class="menu-text">Dashboard HR</span>
        </a>
        <ul class="menu-children">
            <li><a href="dashboard.php">Ringkasan Statistik</a></li>
        </ul>
    </div>

    <!-- 2. Kelola Pelamar -->
    <div class="menu-group">
        <a href="pelamar.php" class="menu-item <?php echo $is_pelamar_page ? 'active' : ''; ?>" title="Kelola Pelamar">
            <span class="menu-icon">👥</span>
            <span class="menu-text">Kelola Pelamar</span>
        </a>
        <ul class="menu-children">
            <li><a href="pelamar.php">Daftar Pelamar</a></li>
        </ul>
    </div>

    <!-- 3. Master Ujian & Modul -->
    <div class="menu-group">
        <a href="master_ujian.php" class="menu-item <?php echo $is_ujian_page ? 'active' : ''; ?>" title="Master Ujian">
            <span class="menu-icon">📝</span>
            <span class="menu-text">Master Ujian</span>
        </a>
        <ul class="menu-children">
            <li><a href="master_ujian.php">Daftar Modul Ujian</a></li>
        </ul>
    </div>

    <!-- 4. Master Karir (Dengan 3 Sub-Bab) -->
    <div class="menu-group">
        <a href="master_loker.php" class="menu-item <?php echo $is_karir_page ? 'active' : ''; ?>" title="Master Karir">
            <span class="menu-icon">💼</span>
            <span class="menu-text">Master Karir</span>
        </a>
        <ul class="menu-children">
            <li><a href="master_loker.php">Daftar Loker</a></li>
            <li><a href="tambah_loker.php">Tambah Loker Baru</a></li>
        </ul>
    </div>

    <!-- Keluar / Logout -->
    <div class="menu-group menu-logout">
        <a href="../login.php" class="menu-item" title="Keluar" onclick="return confirm('Yakin ingin keluar dari akun admin?')">
            <span class="menu-icon">🚪</span>
            <span class="menu-text">Keluar</span>
        </a>
    </div>
</aside>

<style>
    .sidebar-admin-panel {
        width: 80px;
        min-width: 80px;
        background-color: #1b5e20;
        padding: 20px 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        flex-shrink: 0;
        overflow-x: hidden;
        overflow-y: auto;
        transition: width 0.3s ease;
        z-index: 999;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 75px;
        height: calc(100vh - 75px);
    }
    .sidebar-admin-panel:hover { width: 270px; }
    
    .sidebar-logo-container { 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        padding: 0 15px 15px 15px; 
        border-bottom: 1px solid rgba(255,255,255,0.1); 
        margin-bottom: 5px; 
    }
    .sidebar-logo-img { 
        width: 50px; 
        height: 50px; 
        background: white; 
        border-radius: 12px; 
        padding: 4px; 
        object-fit: contain; 
    }
    
    .menu-group { position: relative; }
    .menu-item { 
        display: flex; 
        align-items: center; 
        gap: 15px; 
        padding: 10px 22px; 
        color: #ffffff; 
        text-decoration: none; 
        font-size: 16px; 
        font-weight: 700; 
        transition: all 0.2s ease; 
        white-space: nowrap; 
    }
    .menu-icon { font-size: 22px; min-width: 30px; text-align: center; }
    .menu-text { opacity: 0; transition: opacity 0.2s ease; }
    .sidebar-admin-panel:hover .menu-text { opacity: 1; }
    
    .menu-item.active, .menu-item:hover { 
        background: linear-gradient(90deg, #ffcc80 0%, #ffe0b2 100%); 
        color: #1b5e20; 
        border-top-left-radius: 30px; 
        border-bottom-left-radius: 30px; 
        margin-left: 10px; 
    }
    .menu-item.active .menu-icon, .menu-item:hover .menu-icon { color: #1b5e20; }
    
    .menu-children { 
        list-style: none; 
        padding-left: 65px; 
        display: flex; 
        flex-direction: column; 
        gap: 4px; 
        max-height: 0; 
        overflow: hidden; 
        transition: max-height 0.3s ease; 
    }
    .sidebar-admin-panel:hover .menu-group:hover .menu-children { 
        max-height: 180px; /* Ditinggikan sedikit agar muat 3 sub-menu */
        margin-top: 4px; 
        margin-bottom: 4px; 
    }
    .menu-children a { 
        font-size: 13px; 
        color: #c8e6c9; 
        text-decoration: none; 
        font-weight: 600; 
        transition: color 0.2s; 
        white-space: nowrap; 
    }
    .menu-children a:hover { color: #ffffff; }
    
    .menu-logout { border-top: 1px solid rgba(255,255,255,0.1); margin-top: 5px; padding-top: 5px; }
    .menu-logout .menu-item:hover { background: rgba(255, 82, 82, 0.2); color: #ff5252; }
</style>
<main class="content-area">