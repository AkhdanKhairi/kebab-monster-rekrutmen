<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Ambil role dari session dan ubah ke huruf kecil semua untuk mencegah error kapitalisasi
$role_user = strtolower(trim($_SESSION['role'] ?? ''));

// 2. Pastikan yang akses adalah admin atau hr
if (!isset($_SESSION['user_id']) || ($role_user != 'admin' && $role_user != 'hr')) {
    // Hapus sesi yang tidak valid lalu arahkan ke login
    session_unset();
    session_destroy();
    echo "<script>alert('Akses ditolak! Role Anda terdeteksi sebagai: " . ($role_user ? $role_user : 'Tidak ada (Belum Login)') . ". Silakan login kembali sebagai HR/Admin.'); window.location='../login.php';</script>";
    exit();
}

// Koneksi database db_kebab_monster
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn_admin_header = mysqli_connect($host, $user, $pass, $db);

$nama_admin = $_SESSION['nama'] ?? 'HR Kebab Monster';
$info_admin = 'Admin / HR';

if ($conn_admin_header) {
    $id_admin = intval($_SESSION['user_id']);
    $q_admin = mysqli_query($conn_admin_header, "SELECT * FROM admin WHERE id_admin = $id_admin");
    if ($q_admin && mysqli_num_rows($q_admin) > 0) {
        $d_admin = mysqli_fetch_assoc($q_admin);
        $nama_admin = $d_admin['nama'] ?? 'HR Kebab Monster';
        $info_admin = !empty($d_admin['jabatan']) ? $d_admin['jabatan'] : '@' . ($d_admin['username'] ?? 'admin');
    }
    mysqli_close($conn_admin_header);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal HR & Admin - Kebab Monster</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f8; color: #333; display: flex; flex-direction: column; min-height: 100vh; }

        /* Top Header Full Width */
        .dashboard-header {
            background-color: #ffffff; 
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            height: 75px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        .header-left-section {
            background-color: #1b5e20; 
            height: 100%;
            display: flex;
            align-items: center;
            padding-left: 25px;
            padding-right: 45px;
            width: 52%; 
            border-bottom-right-radius: 45px;
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        .header-logo-img {
            height: 48px; width: 48px; background: white; border-radius: 10px; padding: 4px; object-fit: contain;
        }
        .brand-text-container { display: flex; flex-direction: column; }
        .brand-title-top { font-size: 12.5px; color: #e0f2f1; font-weight: 600; }
        .brand-title-main { font-size: 16px; font-weight: 900; color: #ffb300; letter-spacing: 0.8px; }

        .header-right-section {
            background: linear-gradient(135heds, #ffa726 0%, #fb8c00 100%);
            background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
            height: 100%; flex: 1; display: flex; align-items: center; justify-content: flex-end;
            padding-right: 30px; padding-left: 35px; gap: 15px; color: white; border-top-left-radius: 45px;
        }
        .user-text-box { display: flex; flex-direction: column; text-align: right; }
        .header-username { font-size: 14px; font-weight: 800; color: #ffffff; }
        .header-email { font-size: 12px; color: #fffde7; }
        .header-user-icon {
            font-size: 20px; color: #ffffff; background: rgba(255, 255, 255, 0.2);
            width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
        }

        .main-layout { display: flex; flex: 1; }
        .content-area { flex: 1; padding: 30px; background-color: #f8f9fa; }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <div class="header-left-section">
            <a href="dashboard.php" class="header-brand">
                <img src="../assets/logo.png" alt="Logo" class="header-logo-img" onerror="this.style.display='none'">
                <div class="brand-text-container">
                    <span class="brand-title-top">Portal HR & Admin</span>
                    <span class="brand-title-main">KEBAB MONSTER</span>
                </div>
            </a>
        </div>
        <div class="header-right-section">
            <div class="user-text-box">
                <span class="header-username"><?php echo htmlspecialchars($nama_admin); ?></span>
                <span class="header-email"><?php echo htmlspecialchars($info_admin); ?></span>
            </div>
            <div class="header-user-icon">🛡️</div>
        </div>
    </header>

    <div class="main-layout">