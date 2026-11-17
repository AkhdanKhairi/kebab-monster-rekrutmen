<?php
// Mendeteksi nama file halaman yang sedang aktif saat ini (misal: index.php atau career.php)
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebab Monster - Sistem Penerimaan Karyawan</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f8f9fa; color: #333; padding-top: 75px; }
        
        /* Navbar Sticky (Menempel di Atas Saat Scroll) */
        nav { 
            background: #ffffff; 
            padding: 15px 40px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #e0e0e0;
            width: 100%;
            position: fixed;   
            top: 0;            
            left: 0;
            z-index: 1000;     
            box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
        }

        .logo-container img { height: 40px; width: auto; display: block; }
        .nav-menu { display: flex; gap: 35px; align-items: center; }
        .nav-menu a { 
            text-decoration: none; 
            color: #004d40; /* Warna default menu */
            font-weight: 700; 
            font-size: 14px; 
            letter-spacing: 0.5px;
            transition: color 0.2s;
        }
        .nav-menu a:hover { color: #ff9800; }
        
        /* Kelas aktif untuk menu yang sedang dikunjungi */
        .nav-menu a.active { 
            color: #ffb300 !important; 
        }

        .nav-menu a.btn-login { 
            background-color: #004d40; 
            color: white !important; 
            padding: 8px 20px; 
            border-radius: 4px; 
        }
        .nav-menu a.btn-login:hover { background-color: #00332c; }
    </style>
</head>
<body>

    <!-- Navbar Atas -->
    <nav>
        <div class="logo-container">
            <a href="index.php">
                <img src="assets/logo.png" alt="Kebab Monster Logo" onerror="this.style.display='none'; document.getElementById('fallback-logo').style.display='block';">
                <span id="fallback-logo" style="display:none; font-weight:bold; color:#004d40; font-size:18px;">KEBAB MONSTER</span>
            </a>
        </div>
        <div class="nav-menu">
            <!-- Menambahkan pengecekan otomatis untuk kelas 'active' -->
            <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">HOMEPAGE</a>
            <a href="career.php" class="<?php echo ($current_page == 'career.php') ? 'active' : ''; ?>">CAREER</a>
            <a href="information.php" class="<?php echo ($current_page == 'information.php') ? 'active' : ''; ?>">INFORMATION</a>
            <a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">CONTACT US</a>
            <a href="login.php" class="btn-login">LOGIN/REGISTER</a>
        </div>
    </nav>