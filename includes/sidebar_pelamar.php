<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_pelamar_sidebar = $_SESSION['user_id'] ?? 0;
$buka_ujian = false;
$pesan_lock = "Harap masukkan lamaran terlebih dahulu";
$id_modul_default_sidebar = 0;

$ada_notif_progres = false;
$judul_notif_progres = "Ada progres lamaran terbaru";

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn_sidebar = mysqli_connect($host, $user, $pass, $db);

if ($id_pelamar_sidebar > 0 && $conn_sidebar) {
    $q_sidebar_cek = mysqli_query($conn_sidebar, "SELECT * FROM lamaran WHERE id_pelamar = $id_pelamar_sidebar ORDER BY id_lamaran DESC LIMIT 1");
    if ($q_sidebar_cek && mysqli_num_rows($q_sidebar_cek) > 0) {
        $data_lamaran = mysqli_fetch_assoc($q_sidebar_cek);
        $raw_status   = $data_lamaran['status_lamaran'] ?? ($data_lamaran['status'] ?? '');
        $status_tahap = strtolower(trim($raw_status));

        $tahap_unlock = [
            'psikotes',
            'ujian',
            'hasil',
            'interview',
            'wawancara',
            'hasil akhir',
            'pengumuman',
            'hasil_akhir',
            'ditolak',
            'lolos',
            'selesai'
        ];

        foreach ($tahap_unlock as $kata) {
            if (strpos($status_tahap, $kata) !== false) {
                $buka_ujian = true;
                break;
            }
        }

        if (!$buka_ujian) {
            $tampil_st  = !empty($raw_status) ? $raw_status : 'Screening CV';
            $pesan_lock = "Terkunci: Lamaran masih dalam tahap $tampil_st";
        }

        $profil_lengkap_sb = false;
        $q_prof_sb = mysqli_query($conn_sidebar, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar_sidebar LIMIT 1");
        if ($q_prof_sb && $prof_sb = mysqli_fetch_assoc($q_prof_sb)) {
            if (
                !empty($prof_sb['nama_lengkap']) &&
                !empty($prof_sb['email']) &&
                !empty($prof_sb['no_telepon']) &&
                !empty($prof_sb['jenis_kelamin']) &&
                !empty($prof_sb['tanggal_lahir']) &&
                !empty($prof_sb['alamat']) &&
                !empty($prof_sb['no_ktp']) &&
                !empty($prof_sb['pas_foto'])
            ) {
                $profil_lengkap_sb = true;
            }
        }

        $progres_terkini = implode('|', [
            $data_lamaran['id_lamaran'] ?? '',
            $raw_status,
            $data_lamaran['tgl_verifikasi'] ?? '',
            $data_lamaran['tgl_screening'] ?? '',
            $data_lamaran['tgl_psikotes'] ?? '',
            $data_lamaran['tgl_interview'] ?? '',
            $data_lamaran['tgl_hasil'] ?? '',
            $profil_lengkap_sb ? '1' : '0'
        ]);

        $last_seen_progres = $_SESSION['last_seen_lamaran_progres'] ?? '';
        $current_page_cek  = basename($_SERVER['PHP_SELF']);

        if (!$profil_lengkap_sb) {
            $ada_notif_progres = true;
            $judul_notif_progres = "Lengkapi data profil untuk melanjutkan verifikasi lamaran";
        } elseif ($progres_terkini !== '' && $progres_terkini !== $last_seen_progres) {
            if ($current_page_cek === 'status_lamaran.php' || $current_page_cek === 'hasil_lamaran.php') {
                $_SESSION['last_seen_lamaran_progres'] = $progres_terkini;
                $ada_notif_progres = false;
            } else {
                $ada_notif_progres = true;
                $judul_notif_progres = "Progres lamaran terbaru: " . ($raw_status ?: 'Diperbarui');
            }
        }
    } else {
        $pesan_lock = "Harap kirimkan lamaran terlebih dahulu";
    }

    $q_mod_riw = mysqli_query($conn_sidebar, "SELECT id_modul FROM riwayat_ujian_pelamar WHERE id_pelamar = $id_pelamar_sidebar ORDER BY id_modul ASC LIMIT 1");
    if ($q_mod_riw && mysqli_num_rows($q_mod_riw) > 0) {
        $id_modul_default_sidebar = intval(mysqli_fetch_assoc($q_mod_riw)['id_modul']);
    } else {
        $q_mod_first = mysqli_query($conn_sidebar, "SELECT id_modul FROM modul_ujian ORDER BY urutan ASC, id_modul ASC LIMIT 1");
        if ($q_mod_first && mysqli_num_rows($q_mod_first) > 0) {
            $id_modul_default_sidebar = intval(mysqli_fetch_assoc($q_mod_first)['id_modul']);
        }
    }

    mysqli_close($conn_sidebar);
}

$link_hasil_ujian = ($id_modul_default_sidebar > 0)
    ? "detail_hasil_ujian.php?id=" . $id_modul_default_sidebar
    : "detail_hasil_ujian.php";

$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<!-- Sidebar Samping -->
<aside class="sidebar" id="pelamarSidebar">
    <!-- 1. Profile -->
    <div class="menu-group">
        <a href="profile_view.php" class="menu-item <?php echo ($current_page == 'profile_view.php' || $current_page == 'profile_edit.php' || $current_page == 'change_password.php' || $current_page == 'ganti_pw.php') ? 'active' : ''; ?>" title="Profile">
            <span class="menu-icon">👤</span>
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

    <!-- 3. Ujian -->
    <div class="menu-group">
        <?php if ($buka_ujian): ?>
            <a href="ujian.php" class="menu-item <?php echo ($current_page == 'ujian.php' || $current_page == 'detail_hasil_ujian.php' || $current_page == 'hasil_ujian.php') ? 'active' : ''; ?>" title="Ujian">
                <span class="menu-icon">📝</span>
                <span class="menu-text">TES</span>
            </a>
            <ul class="menu-children">
                <li><a href="ujian.php">Kerjakan TES</a></li>
                <li><a href="<?php echo $link_hasil_ujian; ?>">Lihat Hasil TES</a></li>
            </ul>
        <?php else: ?>
            <a href="javascript:void(0);" class="menu-item menu-locked" title="<?php echo htmlspecialchars($pesan_lock); ?>">
                <span class="menu-icon">🔒</span>
                <span class="menu-text">Ujian (Terkunci)</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- 4. Informasi / Status Lamaran -->
    <div class="menu-group">
        <a href="status_lamaran.php"
           class="menu-item <?php echo ($current_page == 'status_lamaran.php' || $current_page == 'hasil_lamaran.php') ? 'active' : ''; ?>"
           title="<?php echo htmlspecialchars($ada_notif_progres ? $judul_notif_progres : 'Status Lamaran'); ?>">
            <span class="menu-icon menu-icon-with-badge">
                📋
                <?php if ($ada_notif_progres): ?>
                    <span class="notif-bang-badge" aria-label="Notifikasi Progres Baru">!</span>
                <?php endif; ?>
            </span>
            <span class="menu-text">
                Status Lamaran
                <?php if ($ada_notif_progres): ?>
                    <span class="notif-dot-text">!</span>
                <?php endif; ?>
            </span>
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
        gap: 10px;
        flex-shrink: 0;
        overflow-x: hidden;
        overflow-y: auto;
        transition: width 0.3s ease, transform 0.3s ease;
        z-index: 999;
        box-shadow: 2px 0 8px rgba(0,0,0,0.1);
    }
    .sidebar:hover { width: 270px; }
    .menu-group { position: relative; }
    .menu-item { display: flex; align-items: center; gap: 15px; padding: 12px 22px; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 700; transition: all 0.2s ease; white-space: nowrap; }
    .menu-icon { font-size: 20px; min-width: 30px; text-align: center; }
    .menu-icon-with-badge { position: relative; display: inline-flex; align-items: center; justify-content: center; }

    .notif-bang-badge {
        position: absolute;
        top: -5px;
        right: -3px;
        width: 17px;
        height: 17px;
        border-radius: 50%;
        background-color: #e53935;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #1b5e20;
        box-shadow: 0 2px 6px rgba(229, 57, 53, 0.55);
    }
    .notif-dot-text {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        margin-left: 6px;
        border-radius: 50%;
        background-color: #e53935;
        color: #ffffff;
        font-size: 11px;
        font-weight: 900;
        vertical-align: middle;
    }

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
    .menu-children { list-style: none; padding-left: 65px; display: flex; flex-direction: column; gap: 6px; max-height: 0; overflow: hidden; transition: max-height 0.3s ease; }
    .sidebar:hover .menu-group:hover .menu-children { max-height: 150px; margin-top: 4px; margin-bottom: 6px; }
    .menu-children a { font-size: 14px; color: #c8e6c9; text-decoration: none; font-weight: 600; transition: color 0.2s; white-space: nowrap; display: inline-block; padding: 2px 0; }
    .menu-children a:hover { color: #ffffff; }
    .menu-logout { border-top: 1px solid rgba(255,255,255,0.1); margin-top: 5px; padding-top: 5px; }
    .menu-logout .menu-item:hover { background: rgba(255, 82, 82, 0.2); color: #ff5252; }

    .sidebar-backdrop {
        display: none;
    }

    @media (min-width: 769px) {
        .sidebar {
            position: fixed !important;
            top: 75px !important;
            left: 0 !important;
            bottom: 0 !important;
            height: auto !important;
            max-height: none !important;
        }
    }

    /* Mode HP: drawer penuh dari atas ke bawah, menu terpusat vertikal */
    @media (max-width: 768px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 265px;
            min-width: 265px;
            height: 100vh;
            max-height: 100vh;
            justify-content: center;
            padding: 28px 0;
            transform: translateX(-100%);
            z-index: 1200;
        }
        .sidebar:hover {
            width: 265px;
        }
        .sidebar.mobile-open {
            transform: translateX(0);
        }
        .sidebar .menu-text {
            opacity: 1;
        }
        .sidebar .menu-children {
            max-height: 160px;
            margin-top: 4px;
            margin-bottom: 6px;
        }
        .sidebar-backdrop.show {
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
        const sidebar = document.getElementById('pelamarSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

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