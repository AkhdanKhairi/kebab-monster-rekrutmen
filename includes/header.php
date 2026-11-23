<?php
// Mendeteksi nama file halaman yang sedang aktif saat ini (misal: index.php atau career.php)
$current_page = basename($_SERVER['PHP_SELF']);

$docRoot     = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$projectRoot = rtrim(str_replace('\\', '/', realpath(__DIR__ . '/..')), '/');

$baseUrl = ($docRoot !== '' && strpos($projectRoot, $docRoot) === 0)
    ? substr($projectRoot, strlen($docRoot))
    : '';

$mobileCssFile = $projectRoot . '/assets/css/mobile-global.css';
$mobileCssVer  = file_exists($mobileCssFile) ? filemtime($mobileCssFile) : time();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebab Monster - Sistem Penerimaan Karyawan</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars($baseUrl, ENT_QUOTES); ?>/assets/css/mobile-global.css?v=<?php echo $mobileCssVer; ?>">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        html, body { max-width: 100%; overflow-x: hidden; }
        body { background-color: #f8f9fa; color: #333; padding-top: 75px; }

        /* Jaring pengaman global untuk media & tabel */
        img, video, iframe { max-width: 100%; }
        table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
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
            color: #004d40;
            font-weight: 700; 
            font-size: 14px; 
            letter-spacing: 0.5px;
            transition: color 0.2s;
            white-space: nowrap;
        }
        .nav-menu a:hover { color: #ff9800; }
        
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

        /* Tombol hamburger (sembunyi di desktop) */
        .menu-toggle {
            display: none;
            background: none;
            border: 1px solid #d0d7de;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 20px;
            color: #004d40;
            cursor: pointer;
        }

        /* Aturan khusus layar HP / tablet kecil */
        @media (max-width: 900px) {
            nav {
                padding: 12px 18px;
                flex-wrap: wrap;
            }
            .menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .nav-menu {
                display: none;
                flex-direction: column;
                align-items: stretch;
                gap: 0;
                width: 100%;
                background: #ffffff;
                margin-top: 12px;
                padding-top: 8px;
                border-top: 1px solid #e0e0e0;
            }
            .nav-menu.show {
                display: flex;
            }
            .nav-menu a {
                padding: 12px 6px;
                border-bottom: 1px solid #f1f3f5;
            }
            .nav-menu a.btn-login {
                margin-top: 10px;
                text-align: center;
                padding: 10px 14px;
            }
            input, select, textarea, button {
                max-width: 100%;
            }
        }
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

        <button type="button" class="menu-toggle" id="menuToggle" aria-label="Buka atau tutup menu" aria-expanded="false">☰</button>

        <div class="nav-menu" id="navMenu">
            <a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">HOMEPAGE</a>
            <a href="career.php" class="<?php echo ($current_page == 'career.php') ? 'active' : ''; ?>">CAREER</a>
            <a href="information.php" class="<?php echo ($current_page == 'information.php') ? 'active' : ''; ?>">INFORMATION</a>
            <a href="contact.php" class="<?php echo ($current_page == 'contact.php') ? 'active' : ''; ?>">CONTACT US</a>
            <a href="login.php" class="btn-login">LOGIN/REGISTER</a>
        </div>
    </nav>

    <script>
        (function () {
            const toggleBtn = document.getElementById('menuToggle');
            const navMenu = document.getElementById('navMenu');
            if (toggleBtn && navMenu) {
                toggleBtn.addEventListener('click', function () {
                    const isOpen = navMenu.classList.toggle('show');
                    toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }
        })();
    </script>