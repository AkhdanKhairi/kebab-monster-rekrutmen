<?php
session_start();

// 1. Cek apakah pelamar sudah login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];
$pesan = "";

// 2. Koneksi ke Database XAMPP
$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 3. Proses ketika form Ganti Password disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_lama       = md5($_POST['password_lama']); // Sesuai dengan enkripsi MD5 di login
    $password_baru       = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Ambil password lama dari database untuk dicocokkan
    $query_cek = mysqli_query($conn, "SELECT password FROM pelamar WHERE id_pelamar = $id_pelamar");
    $data_pelamar = mysqli_fetch_assoc($query_cek);

    if ($data_pelamar['password'] !== $password_lama) {
        $pesan = "<div class='alert error'>Password lama yang Anda masukkan salah!</div>";
    } elseif ($password_baru !== $konfirmasi_password) {
        $pesan = "<div class='alert error'>Konfirmasi password baru tidak cocok!</div>";
    } elseif (strlen($password_baru) < 6) {
        $pesan = "<div class='alert error'>Password baru minimal harus 6 karakter!</div>";
    } else {
        // Update password baru ke database (dienkripsi dengan MD5)
        $password_baru_md5 = md5($password_baru);
        $query_update = mysqli_query($conn, "UPDATE pelamar SET password = '$password_baru_md5' WHERE id_pelamar = $id_pelamar");

        if ($query_update) {
            $pesan = "<div class='alert success'>Password akun berhasil diperbarui! Silakan gunakan password baru untuk login selanjutnya.</div>";
        } else {
            $pesan = "<div class='alert error'>Terjadi kesalahan sistem, gagal mengubah password.</div>";
        }
    }
}

include '../includes/header_pelamar.php';
?>

<style>
    .password-page-container {
        max-width: 600px;
        margin: 30px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .card-box {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 30px 35px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .card-box h2 {
        color: #1b5e20;
        font-weight: 800;
        font-size: 24px;
        margin-bottom: 8px;
        text-align: center;
    }

    .card-box p.subtitle {
        text-align: center;
        color: #666;
        font-size: 13.5px;
        margin-bottom: 25px;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 13.5px;
        font-weight: 700;
        color: #333;
        margin-bottom: 6px;
    }

    .password-container {
        position: relative;
    }

    .form-control {
        width: 100%;
        padding: 12px 40px 12px 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    .form-control:focus {
        border-color: #1b5e20;
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

    .btn-submit {
        background-color: #1b5e20;
        color: white;
        border: none;
        width: 100%;
        padding: 13px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 10px;
    }

    .btn-submit:hover {
        background-color: #00332c;
    }

    .alert {
        padding: 12px 15px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
    }
    .alert.error { background: #ffebee; color: #c62828; }
    .alert.success { background: #e8f5e9; color: #2e7d32; }
</style>

<div class="password-page-container">
    <div class="card-box">
        <h2>Ganti Password Akun</h2>
        <p class="subtitle">Amankan akun Anda dengan menggunakan kombinasi password yang kuat.</p>

        <?php echo $pesan; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Password Lama</label>
                <div class="password-container">
                    <input type="password" name="password_lama" id="pw-lama" class="form-control" placeholder="Masukkan password lama" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('pw-lama', this)">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label>Password Baru</label>
                <div class="password-container">
                    <input type="password" name="password_baru" id="pw-baru" class="form-control" placeholder="Masukkan password baru (minimal 6 karakter)" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('pw-baru', this)">👁️</button>
                </div>
            </div>

            <div class="form-group">
                <label>Konfirmasi Password Baru</label>
                <div class="password-container">
                    <input type="password" name="konfirmasi_password" id="pw-konfirm" class="form-control" placeholder="Ulangi password baru" required>
                    <button type="button" class="toggle-password" onclick="togglePassword('pw-konfirm', this)">👁️</button>
                </div>
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan Password</button>
        </form>
    </div>
</div>

<script>
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

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>