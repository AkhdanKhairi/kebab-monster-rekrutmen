<?php
session_start();
include 'includes/header.php';
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
    .btn-auth:hover:not(:disabled) {
        background-color: #00332c;
    }
    .btn-auth:disabled {
        opacity: 0.7;
        cursor: not-allowed;
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
        <button type="button" class="switch-btn active" onclick="switchTab('login')">MASUK (LOGIN)</button>
        <button type="button" class="switch-btn" onclick="switchTab('register')">DAFTAR AKUN BARU</button>
    </div>

    <div class="auth-form-content">
        <!-- FORM LOGIN -->
        <div id="section-login" class="form-section active">
            <h2>Masuk ke Akun</h2>
            <div id="login-alert"></div>
            <form id="form-login">
                <div class="form-group">
                    <label>Email (Pelamar) / Username (HR/Admin)</label>
                    <input type="text" name="identifier" id="login-identifier" class="form-control" style="padding-right: 14px;" placeholder="Masukkan email atau username" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="login-password" class="form-control" placeholder="Masukkan password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('login-password', this)">👁️</button>
                    </div>
                </div>
                <button type="submit" id="btn-login" class="btn-auth">Masuk Sekarang</button>
            </form>
        </div>

        <!-- FORM REGISTER -->
        <div id="section-register" class="form-section">
            <h2>Registrasi Pelamar</h2>
            <div id="register-alert"></div>
            <form id="form-register">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="reg-nama" class="form-control" style="padding-right: 14px;" placeholder="Masukkan nama lengkap" required>
                </div>
                <div class="form-group">
                    <label>Alamat Email</label>
                    <input type="email" name="email" id="reg-email" class="form-control" style="padding-right: 14px;" placeholder="nama@email.com" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon / WhatsApp</label>
                    <input type="text" name="no_telepon" id="reg-telepon" class="form-control" style="padding-right: 14px;" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-container">
                        <input type="password" name="password" id="register-password" class="form-control" placeholder="Buat password akun" required>
                        <button type="button" class="toggle-password" onclick="togglePassword('register-password', this)">👁️</button>
                    </div>
                </div>
                <button type="submit" id="btn-register" class="btn-auth">Daftar Sebagai Pelamar</button>
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

    function showAlert(containerId, type, message) {
        const box = document.getElementById(containerId);
        box.innerHTML = `<div class="alert ${type}">${message}</div>`;
    }

    // Kirim data login ke api/auth/login.php
    document.getElementById('form-login').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-login');
        const identifier = document.getElementById('login-identifier').value.trim();
        const password = document.getElementById('login-password').value;

        btn.disabled = true;
        btn.textContent = 'Memproses...';
        document.getElementById('login-alert').innerHTML = '';

        try {
            const res = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ identifier, password })
            });

            const result = await res.json();

            if (res.ok && result.success) {
                showAlert('login-alert', 'success', result.message);
                const role = result.data?.user?.role;
                setTimeout(() => {
                    if (role === 'admin') {
                        window.location.href = 'admin/dashboard.php';
                    } else {
                        window.location.href = 'pelamar/profile_view.php';
                    }
                }, 500);
            } else {
                showAlert('login-alert', 'error', result.message || 'Login gagal.');
                btn.disabled = false;
                btn.textContent = 'Masuk Sekarang';
            }
        } catch (err) {
            showAlert('login-alert', 'error', 'Gagal menghubungi server API.');
            btn.disabled = false;
            btn.textContent = 'Masuk Sekarang';
        }
    });

    // Kirim data registrasi ke api/auth/register.php
    document.getElementById('form-register').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btn-register');
        const nama_lengkap = document.getElementById('reg-nama').value.trim();
        const email = document.getElementById('reg-email').value.trim();
        const no_telepon = document.getElementById('reg-telepon').value.trim();
        const password = document.getElementById('register-password').value;

        btn.disabled = true;
        btn.textContent = 'Mendaftarkan...';
        document.getElementById('register-alert').innerHTML = '';

        try {
            const res = await fetch('api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ nama_lengkap, email, no_telepon, password })
            });

            const result = await res.json();

            if (res.ok && result.success) {
                showAlert('register-alert', 'success', result.message);
                document.getElementById('form-register').reset();
                setTimeout(() => {
                    switchTab('login');
                    showAlert('login-alert', 'success', result.message);
                }, 1200);
            } else {
                showAlert('register-alert', 'error', result.message || 'Registrasi gagal.');
            }
        } catch (err) {
            showAlert('register-alert', 'error', 'Gagal menghubungi server API.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Daftar Sebagai Pelamar';
        }
    });
</script>

<?php include 'includes/footer.php'; ?>