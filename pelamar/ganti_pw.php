<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
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
        box-sizing: border-box;
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

    .btn-submit:disabled {
        opacity: 0.7;
        cursor: wait;
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

        <div id="alert-container"></div>

        <form id="formGantiPassword" action="" method="POST">
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

            <button type="submit" id="btnSimpanPw" class="btn-submit">Simpan Perubahan Password</button>
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

    function showAlert(type, message) {
        const container = document.getElementById('alert-container');
        const safeMsg = String(message ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
        container.innerHTML = `<div class="alert ${type}">${safeMsg}</div>`;
    }

    document.getElementById('formGantiPassword').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('btnSimpanPw');
        const originalText = btn.textContent;
        const payload = {
            password_lama: document.getElementById('pw-lama').value,
            password_baru: document.getElementById('pw-baru').value,
            konfirmasi_password: document.getElementById('pw-konfirm').value
        };

        btn.disabled = true;
        btn.textContent = 'Menyimpan...';

        try {
            const res = await fetch('../api/pelamar/password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (!res.ok || !result.success) {
                showAlert('error', result.message || 'Gagal mengubah password.');
                btn.disabled = false;
                btn.textContent = originalText;
                return;
            }

            showAlert('success', result.message || 'Password akun berhasil diperbarui!');
            this.reset();
        } catch (err) {
            showAlert('error', 'Terjadi kesalahan koneksi ke server.');
        } finally {
            btn.disabled = false;
            btn.textContent = originalText;
        }
    });
</script>

<?php
include '../includes/footer_pelamar.php';
?>