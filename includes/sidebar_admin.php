<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

$is_dashboard_page = in_array($current_page, ['dashboard.php']);
$is_profile_page   = in_array($current_page, ['profile_admin.php', 'profile_admin_edit.php', 'ubah_password_admin.php']);
$is_pelamar_page   = in_array($current_page, ['daftar_pelamar.php', 'jadwal_seleksi.php', 'hasil_akhir.php', 'detail_pelamar.php']);
$is_ujian_page     = in_array($current_page, ['master_ujian.php', 'tambah_soal.php', 'edit_soal.php', 'lihat_soal.php', 'koreksi_hasil.php']);
$is_karir_page     = in_array($current_page, ['master_loker.php', 'tambah_loker.php', 'edit_loker.php']);
?>

<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<aside class="sidebar-admin-panel" id="adminSidebar">
    <!-- 1. Dashboard HR -->
    <div class="menu-group">
        <a href="dashboard.php" class="menu-item <?php echo $is_dashboard_page ? 'active' : ''; ?>" title="Dashboard HR">
            <span class="menu-icon">📊</span>
            <span class="menu-text">Dashboard HR</span>
        </a>
    </div>

    <!-- 2. Profile HR -->
    <div class="menu-group">
        <a href="profile_admin.php" class="menu-item <?php echo $is_profile_page ? 'active' : ''; ?>" title="Profile HR">
            <span class="menu-icon">👤</span>
            <span class="menu-text">Profile HR</span>
        </a>
        <ul class="menu-children">
            <li><a href="profile_admin.php" class="<?php echo ($current_page === 'profile_admin.php') ? 'sub-active' : ''; ?>">Detail Profile</a></li>
            <li><a href="profile_admin_edit.php" class="<?php echo ($current_page === 'profile_admin_edit.php') ? 'sub-active' : ''; ?>">Edit Profile</a></li>
            <li><a href="ubah_password_admin.php" class="<?php echo ($current_page === 'ubah_password_admin.php') ? 'sub-active' : ''; ?>">Ubah Password</a></li>
        </ul>
    </div>

    <!-- 3. Kelola Pelamar -->
    <div class="menu-group">
        <a href="daftar_pelamar.php" class="menu-item <?php echo $is_pelamar_page ? 'active' : ''; ?>" title="Kelola Pelamar">
            <span class="menu-icon">👥</span>
            <span class="menu-text">Kelola Pelamar</span>
        </a>
        <ul class="menu-children">
            <li><a href="daftar_pelamar.php" class="<?php echo ($current_page === 'daftar_pelamar.php') ? 'sub-active' : ''; ?>">Daftar Pelamar</a></li>
            <li><a href="jadwal_seleksi.php" class="<?php echo ($current_page === 'jadwal_seleksi.php') ? 'sub-active' : ''; ?>">Jadwal Seleksi</a></li>
            <li><a href="hasil_akhir.php" class="<?php echo ($current_page === 'hasil_akhir.php') ? 'sub-active' : ''; ?>">Hasil Akhir</a></li>
        </ul>
    </div>

    <!-- 4. Master TES -->
    <div class="menu-group">
        <a href="master_ujian.php" class="menu-item <?php echo $is_ujian_page ? 'active' : ''; ?>" title="Master TES">
            <span class="menu-icon">📝</span>
            <span class="menu-text">Master TES</span>
        </a>
        <ul class="menu-children">
            <li><a href="master_ujian.php" class="<?php echo ($current_page === 'master_ujian.php') ? 'sub-active' : ''; ?>">Daftar Modul TES</a></li>
            <li><a href="koreksi_hasil.php" class="<?php echo ($current_page === 'koreksi_hasil.php') ? 'sub-active' : ''; ?>">Koreksi Hasil TES</a></li>
        </ul>
    </div>

    <!-- 5. Master Karir -->
    <div class="menu-group">
        <a href="master_loker.php" class="menu-item <?php echo $is_karir_page ? 'active' : ''; ?>" title="Master Karir">
            <span class="menu-icon">💼</span>
            <span class="menu-text">Master Karir</span>
        </a>
        <ul class="menu-children">
            <li><a href="master_loker.php" class="<?php echo ($current_page === 'master_loker.php') ? 'sub-active' : ''; ?>">Daftar Loker</a></li>
            <li><a href="tambah_loker.php" class="<?php echo ($current_page === 'tambah_loker.php') ? 'sub-active' : ''; ?>">Tambah Loker Baru</a></li>
        </ul>
    </div>

    <!-- Keluar -->
    <div class="menu-group menu-logout">
        <a href="../login.php" class="menu-item" title="Keluar" onclick="return confirm('Yakin ingin keluar dari akun admin?')">
            <span class="menu-icon">🚪</span>
            <span class="menu-text">Keluar</span>
        </a>
    </div>
</aside>

<style>
    .sidebar-admin-panel {
        position: fixed;
        top: 75px;
        left: 0;
        bottom: 0;
        height: auto;
        max-height: none;
        width: 80px;
        min-width: 80px;
        background-color: #1b5e20;
        padding: 20px 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
        flex-shrink: 0;
        overflow-x: hidden;
        overflow-y: auto;
        transition: width 0.28s ease, transform 0.28s ease;
        z-index: 999;
        box-shadow: 2px 0 10px rgba(0,0,0,0.12);
    }
    .sidebar-admin-panel:hover { width: 270px; }

    .menu-group { position: relative; }
    .menu-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 11px 22px;
        color: #ffffff;
        text-decoration: none;
        font-size: 15.5px;
        font-weight: 700;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    .menu-icon {
        font-size: 20px;
        min-width: 30px;
        text-align: center;
        line-height: 1;
    }
    .menu-text {
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    .sidebar-admin-panel:hover .menu-text { opacity: 1; }

    .menu-item.active,
    .menu-item:hover {
        background: linear-gradient(90deg, #ffcc80 0%, #ffe0b2 100%);
        color: #1b5e20;
        border-top-left-radius: 30px;
        border-bottom-left-radius: 30px;
        margin-left: 10px;
    }
    .menu-item.active .menu-icon,
    .menu-item:hover .menu-icon { color: #1b5e20; }

    .menu-children {
        list-style: none;
        padding-left: 65px;
        padding-right: 16px;
        display: flex;
        flex-direction: column;
        gap: 5px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.28s ease;
    }
    .sidebar-admin-panel:hover .menu-group:hover .menu-children {
        max-height: 190px;
        margin-top: 5px;
        margin-bottom: 6px;
    }
    .menu-children a {
        font-size: 13.5px;
        color: #c8e6c9;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s ease, transform 0.2s ease;
        white-space: nowrap;
        display: inline-block;
        padding: 2px 0;
    }
    .menu-children a:hover,
    .menu-children a.sub-active {
        color: #ffe0b2;
        font-weight: 700;
        transform: translateX(3px);
    }

    .menu-logout {
        border-top: 1px solid rgba(255,255,255,0.12);
        margin-top: 8px;
        padding-top: 8px;
    }
    .menu-logout .menu-item:hover {
        background: rgba(255, 82, 82, 0.2);
        color: #ff8a80;
    }

    .admin-sidebar-backdrop { display: none; }

    @media (max-width: 768px) {
        .sidebar-admin-panel {
            top: 0;
            bottom: 0;
            height: 100vh;
            width: 265px;
            min-width: 265px;
            justify-content: center;
            padding: 28px 0;
            transform: translateX(-100%);
            z-index: 1200;
        }
        .sidebar-admin-panel:hover { width: 265px; }
        .sidebar-admin-panel.mobile-open { transform: translateX(0); }
        .sidebar-admin-panel .menu-text { opacity: 1; }
        .sidebar-admin-panel .menu-children {
            max-height: 190px;
            margin-top: 5px;
            margin-bottom: 6px;
        }
        .admin-sidebar-backdrop.show {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            z-index: 1150;
        }
    }
</style>

<script>
    (function () {
        const btnOpen = document.getElementById('mobileSidebarToggle');
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('adminSidebarBackdrop');

        if (!btnOpen || !sidebar || !backdrop) return;

        function setSidebarState(open) {
            sidebar.classList.toggle('mobile-open', open);
            backdrop.classList.toggle('show', open);
        }

        btnOpen.addEventListener('click', function () {
            const willOpen = !sidebar.classList.contains('mobile-open');
            setSidebarState(willOpen);
        });

        backdrop.addEventListener('click', function () {
            setSidebarState(false);
        });
    })();
</script>

<main class="content-area">