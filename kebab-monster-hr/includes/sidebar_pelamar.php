<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_pelamar_sidebar = $_SESSION['user_id'] ?? 0;
$buka_ujian = false;
$pesan_lock = "Harap masukkan lamaran terlebih dahulu";

// Koneksi database untuk mengecek status lamaran
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn_sidebar = mysqli_connect($host, $user, $pass, $db);

if ($id_pelamar_sidebar > 0 && $conn_sidebar) {
    // Ambil data lamaran pelamar beserta status/tahapannya
    $q_sidebar_cek = mysqli_query($conn_sidebar, "SELECT * FROM lamaran WHERE id_pelamar = $id_pelamar_sidebar");
    if ($q_sidebar_cek && mysqli_num_rows($q_sidebar_cek) > 0) {
        $data_lamaran = mysqli_fetch_assoc($q_sidebar_cek);
        $status_tahap = strtolower($data_lamaran['status_lamaran'] ?? ''); // Contoh kolom status: 'Screening CV', 'Psikotes', dll.

        // Cek apakah status sudah masuk ke tahap psikotes atau ujian
        if ($status_tahap == 'psikotes' || $status_tahap == 'ujian' || $status_tahap == 'diterima_tes') {
            $buka_ujian = true;
        } else {
            $pesan_lock = "Lamaran masih dalam tahap Screening CV oleh HR";
        }
    } else {
        $pesan_lock = "Harap kirimkan lamaran terlebih dahulu";
    }
    mysqli_close($conn_sidebar);
}

// Mendeteksi nama file yang sedang aktif di browser saat ini
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Samping dengan Posisi Sticky (Diam saat Di-scroll) -->
<aside class="sidebar">
    <!-- Logo Kebab Monster di Paling Atas Sidebar -->
    <div class="sidebar-logo-container">
        <img src="../assets/img/logo-kebab.png" alt="Logo" class="sidebar-logo-img" onerror="this.style.display='none'">
    </div>

    <!-- 1. Profile -->
    <div class="menu-group">
        <a href="profile_view.php" class="menu-item <?php echo ($current_page == 'profile_view.php' || $current_page == 'profile_edit.php' || $current_page == 'change_password.php') ? 'active' : ''; ?>" title="Profile">
            <span class="menu-icon">🏠</span>
            <span class="menu-text">Profile</span>
        </a>
        <ul class="menu-children">
            <li><a href="profile_view.php">Lihat Profile</a></li>
            <li><a href="profile_edit.php">Update Profile</a></li>
            <li><a href="ganti_pw.php">Ganti Password</a></li>
        </ul>
    </div>

    <!-- 2. Karir -->
    <div class="menu-group">
        <a href="karir.php" class="menu-item <?php echo ($current_page == 'karir.php') ? 'active' : ''; ?>" title="Karir">
            <span class="menu-icon">💼</span>
            <span class="menu-text">Karir</span>
        </a>
    </div>

    <!-- 3. Ujian (Terkunci jika status belum Psikotes) -->
    <div class="menu-group">
        <?php if ($buka_ujian): ?>
            <a href="ujian.php" class="menu-item <?php echo ($current_page == 'ujian.php') ? 'active' : ''; ?>" title="Ujian">
                <span class="menu-icon">📄</span>
                <span class="menu-text">Ujian</span>
            </a>
            <ul class="menu-children">
                <li><a href="ujian.php">Kerjakan Ujian</a></li>
                <li><a href="hasil_ujian.php">Lihat Hasil Ujian</a></li>
            </ul>
        <?php else: ?>
            <a href="javascript:void(0);" class="menu-item menu-locked" title="<?php echo $pesan_lock; ?>">
                <span class="menu-icon">🔒</span>
                <span class="menu-text">Ujian (Terkunci)</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- 4. Informasi / Status Lamaran -->
    <div class="menu-group">
        <a href="status_lamaran.php" class="menu-item <?php echo ($current_page == 'status_lamaran.php' || $current_page == 'hasil_lamaran.php') ? 'active' : ''; ?>" title="Status Lamaran">
            <span class="menu-icon">ℹ️</span>
            <span class="menu-text">Status Lamaran</span>
        </a>
        <ul class="menu-children">
            <li><a href="status_lamaran.php">Progres Lamaran</a></li>
            <li><a href="hasil_lamaran.php">Hasil Lamaran</a></li>
        </ul>
    </div>

    <!-- Keluar -->
    <div class="menu-group menu-logout">
        <a href="../index.php" class="menu-item" title="Keluar">
            <span class="menu-icon">🚪</span>
            <span class="menu-text">Keluar</span>
        </a>
    </div>
</aside>

<style>
    .sidebar {
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
    .sidebar:hover { width: 270px; }
    .sidebar-logo-container { display: flex; justify-content: center; align-items: center; padding: 0 15px 15px 15px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 5px; }
    .sidebar-logo-img { width: 50px; height: 50px; background: white; border-radius: 12px; padding: 4px; object-fit: contain; }
    .menu-group { position: relative; }
    .menu-item { display: flex; align-items: center; gap: 15px; padding: 10px 22px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 700; transition: all 0.2s ease; white-space: nowrap; }
    .menu-icon { font-size: 22px; min-width: 30px; text-align: center; }
    .menu-text { opacity: 0; transition: opacity 0.2s ease; }
    .sidebar:hover .menu-text { opacity: 1; }
    .menu-item.active, .menu-item:hover { background: linear-gradient(90deg, #ffcc80 0%, #ffe0b2 100%); color: #1b5e20; border-top-left-radius: 30px; border-bottom-left-radius: 30px; margin-left: 10px; }
    .menu-item.active .menu-icon, .menu-item:hover .menu-icon { color: #1b5e20; }
    .menu-locked { opacity: 0.5; cursor: not-allowed !important; position: relative; }
    .menu-locked:hover { background: transparent !important; color: #ffffff !important; }
    .sidebar:hover .menu-locked:hover::after {
        content: attr(title);
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        margin-left: 10px;
        background: #333;
        color: #fff;
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
        white-space: nowrap;
        z-index: 1000;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        font-weight: 600;
    }
    .menu-children { list-style: none; padding-left: 65px; display: flex; flex-direction: column; gap: 4px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .sidebar:hover .menu-group:hover .menu-children { max-height: 150px; margin-top: 4px; margin-bottom: 4px; }
    .menu-children a { font-size: 13px; color: #c8e6c9; text-decoration: none; font-weight: 600; transition: color 0.2s; white-space: nowrap; }
    .menu-children a:hover { color: #ffffff; }
    .menu-logout { border-top: 1px solid rgba(255,255,255,0.1); margin-top: 5px; padding-top: 5px; }
    .menu-logout .menu-item:hover { background: rgba(255, 82, 82, 0.2); color: #ff5252; }
</style>