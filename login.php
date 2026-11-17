<?php
session_start();
include 'includes/header.php';

$pesan_login    = "";
$pesan_register = "";
$active_tab     = "login"; // Default tab yang aktif

// Logika jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Koneksi menggunakan database: db_kebab_monster
    $host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
    $conn = mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    // Cek apakah yang dikirim adalah form Login
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $active_tab = "login";
        $identifier = trim($_POST['identifier']);
        $password_input = trim($_POST['password']); // Password mentah untuk admin (plain text di db)
        $password_md5   = md5($password_input);     // Password MD5 untuk pelamar

        // 1. Cek tabel ADMIN (menggunakan tabel 'admin' dan password plain-text)
        $query_admin = "SELECT * FROM admin WHERE username = '$identifier' AND password = '$password_input'";
        $result_admin = mysqli_query($conn, $query_admin);

        if ($result_admin && mysqli_num_rows($result_admin) == 1) {
            $row = mysqli_fetch_assoc($result_admin);
            $_SESSION['user_id'] = $row['id_admin'];
            $_SESSION['nama']    = $row['nama'];
            $_SESSION['email']   = $row['username'];
            $_SESSION['role']    = 'admin';

            echo "<script>alert('Login berhasil sebagai Admin/HR!'); window.location='admin/dashboard.php';</script>";
            exit();
        }

        // 2. Cek tabel PELAMAR (menggunakan password MD5)
        $query_pelamar = "SELECT * FROM pelamar WHERE email = '$identifier' AND password = '$password_md5'";
        $result_pelamar = mysqli_query($conn, $query_pelamar);

        if ($result_pelamar && mysqli_num_rows($result_pelamar) == 1) {
            $row = mysqli_fetch_assoc($result_pelamar);
            $_SESSION['user_id'] = $row['id_pelamar'];
            $_SESSION['nama']    = $row['nama_lengkap'];
            $_SESSION['email']   = $row['email'];
            $_SESSION['role']    = 'pelamar';

            echo "<script>alert('Login berhasil!'); window.location='pelamar/profile_view.php';</script>";
            exit();
        }

        $pesan_login = "<div class='alert error'>Email/Username atau Password salah!</div>";
    }

    // Cek apakah yang dikirim adalah form Register Pelamar
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $active_tab = "register";
        $nama     = trim($_POST['nama_lengkap']);
        $email    = trim($_POST['email']);
        $password = md5($_POST['password']);
        $telepon  = trim($_POST['no_telepon']);

        $cek = mysqli_query($conn, "SELECT * FROM pelamar WHERE email = '$email'");
        if (mysqli_num_rows($cek) > 0) {
            $pesan_register = "<div class='alert error'>Email sudah terdaftar, silakan gunakan email lain!</div>";
        } else {
            $query = "INSERT INTO pelamar (nama_lengkap, email, password, no_telepon) VALUES ('$nama', '$email', '$password', '$telepon')";
            if (mysqli_query($conn, $query)) {
                $pesan_register = "<div class='alert success'>Registrasi berhasil! Silakan pindah ke tab Login.</div>";
            } else {
                $pesan_register = "<div class='alert error'>Terjadi kesalahan sistem.</div>";
            }
        }
    }
    mysqli_close($conn);
}
?>

<style>
    .auth-wrapper {
        width: 100%;
        max-width: 480px;
        margin: 50px auto 80px auto;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .auth-switch-tabs {
        display: flex;
        background: #f1f3f5;
        border-bottom: 1px solid #e0e0e0;
    }
    .switch-btn {
        flex: 1;
        padding: 16px;
        text-align: center;
        font-weight: 700;
        font-size: 14px;
        color: #666;
        background: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .switch-btn.active {
        background: #fff;
        color: #004d40;
        border-bottom: 3px solid #ffb300;
    }

    .auth-form-content {
        padding: 30px 35px;
    }
    .form-section {
        display: none;
    }
    .form-section.active {
        display: block;
    }

    .form-section h2 {
        color: #004d40;
        font-weight: 800;
        font-size: 22px;
        margin-bottom: 20px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #333;
        margin-bottom: 6px;
    }
    
    .password-container {
        position: relative;
    }
    .form-control {
        width: 100%;
        padding: 11px 40px 11px 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        border-color: #004d40;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: #777;
    }

    .btn-auth {
        background-color: #004d40;
        color: white;
        border: none;
        width: 100%;
        padding: 12px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 10px;
    }
    .btn-auth:hover {
        background-color: #00332c;
    }

    .alert {
        padding: 10px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 15px;
        text-align: center;
    }
    .alert.error { background: #ffebee; color: #c62828; }
    .alert.success { background: #e8f5e9; color: #2e7d32; }
</style>

<div class="auth-wrapper">
    <div class="auth-switch-tabs">
        <button type="button" class="switch-btn <?php echo ($active_tab == 'login') ? 'active' : ''; ?>" onclick="switchTab('login')">MASUK (LOGIN)</button>
        <button type="button" class="switch-btn <?php echo ($active_tab == 'register') ? 'active' : ''; ?>" onclick="switchTab('register')">DAFTAR AKUN BARU</button>
    </div>

    <div class="auth-form-content">
        <!-- FORM LOGIN -->
        <div id="section-login" class="form-section <?php echo ($active_tab == 'login') ? 'active' : ''; ?>">
            <h2>Masuk ke Akun</h2>
            <?php echo $pesan_login; ?>
            <form action="" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>Email (Pelamar) / Username (HR/Admin)</label>
                    <input type="text" name="identifier" class="form-control" style="padding-right: 14px;" placeholder="Masukkan email atau username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="login-password" class="form-control" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('login-password', this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn-auth">Masuk Sekarang</button>
            </form>
        </div>

        <!-- FORM REGISTER -->
        <div id="section-register" class="form-section <?php echo ($active_tab == 'register') ? 'active' : ''; ?>">
            <h2>Registrasi Pelamar</h2>
            <?php echo $pesan_register; ?>
            <form action="" method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" style="padding-right: 14px;" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Alamat Email</label>
                    <input type="email" name="email" class="form-control" style="padding-right: 14px;" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_telepon" class="form-control" style="padding-right: 14px;" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="register-password" class="form-control" placeholder="Buat password akun" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('register-password', this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="btn-auth">Daftar Sebagai Pelamar</button>
            </form>
        </div>
    </div>
</div>

<script>
    function switchTab(tabName) {
        document.querySelectorAll('.switch-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.form-section').forEach(sec => sec.classList.remove('active'));

        if (tabName === 'login') {
            document.querySelectorAll('.switch-btn')[0].classList.add('active');
            document.getElementById('section-login').classList.add('active');
        } else {
            document.querySelectorAll('.switch-btn')[1].classList.add('active');
            document.getElementById('section-register').classList.add('active');
        }
    }

    function togglePassword(fieldId, btnElement) {
        const passwordField = document.getElementById(fieldId);
        if (passwordField.type === "password") {
            passwordField.type = "text";
            btnElement.textContent = "👁";
        } else {
            passwordField.type = "password";
            btnElement.textContent = "👁️";
        }
    }
</script>

<?php include 'includes/footer.php'; ?>