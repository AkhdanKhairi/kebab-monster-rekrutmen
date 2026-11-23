<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$id_admin  = intval($_SESSION['user_id'] ?? 0);

if ($id_admin <= 0 || ($role_user !== 'admin' && $role_user !== 'hr')) {
    session_unset();
    session_destroy();
    $pesan_tolak = "Akses ditolak! Role Anda terdeteksi sebagai: " . ($role_user !== '' ? $role_user : 'Tidak ada (Belum Login)') . ". Silakan login kembali sebagai HR/Admin.";
    echo "<script>alert(" . json_encode($pesan_tolak) . "); window.location='../login.php';</script>";
    exit();
}

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn_admin_header = mysqli_connect($host, $user, $pass, $db);

$nama_admin = $_SESSION['nama'] ?? 'HR Kebab Monster';
$info_admin = 'Admin / HR';

if ($conn_admin_header) {
    $q_admin = mysqli_query($conn_admin_header, "SELECT nama, jabatan, username FROM admin WHERE id_admin = $id_admin LIMIT 1");
    if ($q_admin && mysqli_num_rows($q_admin) > 0) {
        $d_admin = mysqli_fetch_assoc($q_admin);
        $nama_admin = !empty($d_admin['nama']) ? $d_admin['nama'] : $nama_admin;
        $info_admin = !empty($d_admin['jabatan'])
            ? $d_admin['jabatan']
            : (!empty($d_admin['username']) ? '@' . $d_admin['username'] : 'Admin / HR');
    }
    mysqli_close($conn_admin_header);
}

$docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');
$baseUrl     = ($docRoot !== '' && strpos($projectRoot, $docRoot) === 0)
    ? substr($projectRoot, strlen($docRoot))
    : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal HR & Admin - Kebab Monster</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { max-width: 100%; overflow-x: clip; }
        body { background-color: #f4f6f8; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        img, video, iframe { max-width: 100%; }
        table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Top Header Full Width */
        .dashboard-header {
            background-color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            min-height: 75px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }

        /* Kotak Hijau di Kiri */
        .header-left-section {
            background-color: #1b5e20;
            min-height: 75px;
            display: flex;
            align-items: center;
            padding: 10px 45px 10px 25px;
            width: 52%;
            border-bottom-right-radius: 45px;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            min-width: 0;
        }
        .header-logo-img {
            height: 48px;
            width: 48px;
            flex-shrink: 0;
            background: white;
            border-radius: 10px;
            padding: 4px;
            object-fit: contain;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .brand-text-container { display: flex; flex-direction: column; min-width: 0; }
        .brand-title-top { font-size: 12.5px; color: #e0f2f1; font-weight: 600; }
        .brand-title-main { font-size: 16px; font-weight: 900; color: #ffb300; letter-spacing: 0.8px; }

        /* Kotak Oranye di Kanan */
        .header-right-section {
            background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
            min-height: 75px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 10px 30px 10px 35px;
            gap: 15px;
            color: white;
            border-top-left-radius: 45px;
            min-width: 0;
        }
        .user-text-box { display: flex; flex-direction: column; text-align: right; min-width: 0; }
        .header-username {
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header-email {
            font-size: 12px;
            color: #fffde7;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .header-user-icon {
            font-size: 20px;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* Tombol buka sidebar di HP */
        .mobile-sidebar-btn {
            display: none;
            background: #ffffff;
            color: #1b5e20;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            flex-shrink: 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.12);
        }

        /* Layout Utama */
        .main-layout {
            display: flex;
            align-items: stretch;
            flex: 1;
            padding-left: 80px;
            position: relative;
        }
        .content-area {
            flex: 1;
            min-width: 0;
            padding: 30px;
            background-color: #f8f9fa;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                align-items: stretch;
                min-height: auto;
            }
            .header-left-section {
                width: 100%;
                min-height: 58px;
                padding: 10px 16px;
                border-bottom-right-radius: 0;
            }
            .header-logo-img {
                height: 36px;
                width: 36px;
            }
            .brand-title-top { font-size: 11px; }
            .brand-title-main { font-size: 14px; }
            .header-right-section {
                width: 100%;
                min-height: 52px;
                padding: 8px 16px;
                border-top-left-radius: 0;
                justify-content: space-between;
                gap: 10px;
            }
            .mobile-sidebar-btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .user-text-box {
                text-align: left;
                flex: 1;
            }
            .header-user-icon {
                width: 34px;
                height: 34px;
                font-size: 16px;
            }
            .main-layout {
                padding-left: 0;
            }
            .content-area {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <div class="header-left-section">
            <a href="dashboard.php" class="header-brand">
                <img src="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES); ?>/assets/logo.png" alt="Logo Kebab Monster" class="header-logo-img" onerror="this.style.display='none'">
                <div class="brand-text-container">
                    <span class="brand-title-top">Portal HR & Admin</span>
                    <span class="brand-title-main">KEBAB MONSTER</span>
                </div>
            </a>
        </div>
        <div class="header-right-section">
            <button type="button" class="mobile-sidebar-btn" id="mobileSidebarToggle" aria-label="Buka menu navigasi">
                ☰ Menu
            </button>
            <div class="user-text-box">
                <span class="header-username"><?php echo htmlspecialchars($nama_admin); ?></span>
                <span class="header-email"><?php echo htmlspecialchars($info_admin); ?></span>
            </div>
            <div class="header-user-icon">🛡️</div>
        </div>
    </header>

    <div class="main-layout">