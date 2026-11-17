<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$email_pelamar = isset($_SESSION['email']) ? $_SESSION['email'] : 'emailpendaftar@gmail.com';
$username_tag  = '@' . explode('@', $email_pelamar)[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penerimaan Karyawan - Kebab Monster</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f4f6f8; color: #333; }

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

        /* Kotak Hijau di Kiri */
        .header-left-section {
            background-color: #1b5e20; /* Hijau tua */
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
            height: 48px;
            width: 48px;
            background: white;
            border-radius: 10px;
            padding: 4px;
            object-fit: contain;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .brand-text-container {
            display: flex;
            flex-direction: column;
        }
        .brand-title-top {
            font-size: 12.5px;
            color: #e0f2f1;
            font-weight: 600;
            letter-spacing: 0.4px;
        }
        .brand-title-main {
            font-size: 16px;
            font-weight: 900;
            color: #ffb300; /* Warna aksen kuning/oranye khas kebab */
            letter-spacing: 0.8px;
        }

        /* Kotak Oranye di Kanan */
        .header-right-section {
            background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
            height: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 30px;
            padding-left: 35px;
            gap: 15px;
            color: white;
            border-top-left-radius: 45px;
        }
        .user-text-box {
            display: flex;
            flex-direction: column;
            text-align: right;
        }
        .header-username {
            font-size: 14px;
            font-weight: 800;
            color: #ffffff;
        }
        .header-email {
            font-size: 12px;
            color: #fffde7;
        }
        .header-user-icon {
            font-size: 20px;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.2);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        /* Layout Utama (Sidebar + Konten) */
        .main-layout {
            display: flex;
            min-height: calc(100vh - 75px);
        }

        .content-area {
            flex: 1;
            padding: 30px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

    <!-- Header Full Width -->
    <header class="dashboard-header">
        <!-- Kotak Hijau di Kiri -->
        <div class="header-left-section">
            <a href="profile_view.php" class="header-brand">
                <img src="../assets/logo.png" alt="Logo Kebab Monster" class="header-logo-img" onerror="this.style.display='none'">
                <div class="brand-text-container">
                    <span class="brand-title-top">Sistem Penerimaan Karyawan</span>
                    <span class="brand-title-main">KEBAB MONSTER</span>
                </div>
            </a>
        </div>

        <!-- Kotak Oranye di Kanan -->
        <div class="header-right-section">
            <div class="user-text-box">
                <span class="header-username"><?php echo htmlspecialchars($username_tag); ?></span>
                <span class="header-email"><?php echo htmlspecialchars($email_pelamar); ?></span>
            </div>
            <div class="header-user-icon">👤</div>
        </div>
    </header>

    <div class="main-layout">
        <!-- Memanggil Sidebar Terpisah -->
        <?php include 'sidebar_pelamar.php'; ?>

        <main class="content-area">