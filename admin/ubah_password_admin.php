<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';
?>

<style>
    .hr-profile-wrapper {
        max-width: 1150px;
        margin: 15px auto;
        padding: 0 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .page-heading {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin-bottom: 18px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .hr-profile-grid {
        display: grid;
        grid-template-columns: 270px 1fr;
        gap: 25px;
        align-items: start;
    }

    .hr-left-card {
        background-color: #f4f5f9;
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #eceef2;
    }
    .hr-avatar-circle {
        width: 145px;
        height: 145px;
        margin: 0 auto 18px auto;
        border-radius: 50%;
        background-color: #fce2d5;
        border: 3px solid #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .hr-fullname {
        font-size: 14px;
        font-weight: 700;
        color: #222;
        margin-bottom: 5px;
    }
    .hr-username {
        font-size: 12.5px;
        color: #666;
        margin-bottom: 20px;
    }
    .btn-simpan-hr {
        background-color: #0b6623;
        color: #ffffff;
        border: none;
        width: 100%;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.2s;
    }
    .btn-simpan-hr:hover {
        background-color: #084d1a;
    }
    .btn-kembali-hr {
        display: block;
        margin-top: 10px;
        background-color: #e2e8f0;
        color: #334155;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-kembali-hr:hover {
        background-color: #cbd5e1;
    }

    .hr-right-card {
        background-color: #f4f5f9;
        border-radius: 14px;
        padding: 15px 18px 20px 18px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }
    .hr-banner-header {
        background-color: #0b6623;
        color: #ffffff;
        border-radius: 25px;
        padding: 9px 20px;
        font-size: 13.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 15px;
    }
    .hr-inner-form-box {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 25px 28px;
    }
    .pwd-field-group {
        display: grid;
        grid-template-columns: 185px 1fr;
        align-items: center;
        gap: 15px;
        margin-bottom: 16px;
    }
    .pwd-field-group:last-child {
        margin-bottom: 0;
    }
    .pwd-field-group label {
        font-size: 13px;
        font-weight: 600;
        color: #333;
    }
    .pwd-field-group label span.req {
        color: #e53935;
    }
    .pwd-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }
    .hr-form-input {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 9px 38px 9px 12px;
        font-size: 13px;
        color: #222;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .hr-form-input:focus {
        outline: none;
        border-color: #0b6623;
        box-shadow: 0 0 0 2px rgba(11, 102, 35, 0.12);
    }
    .toggle-eye-btn {
        position: absolute;
        right: 10px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 15px;
        color: #64748b;
        padding: 0;
    }
    .alert-msg-success {
        display: none;
        background-color: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 15px;
    }
    .alert-msg-error {
        display: none;
        background-color: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    @media (max-width: 900px) {
        .hr-profile-grid { grid-template-columns: 1fr; }
        .pwd-field-group { grid-template-columns: 1fr; gap: 6px; }
    }
</style>

<div class="hr-profile-wrapper">
    <div class="page-heading">UBAH PASSWORD</div>

    <div class="alert-msg-success" id="pwdAlertSuccess"></div>
    <div class="alert-msg-error" id="pwdAlertError"></div>

    <form id="formUbahPasswordAdmin" style="display: contents;">
        <div class="hr-profile-grid">
            <div class="hr-left-card">
                <div class="hr-avatar-circle">
                    <span style="font-size: 70px;">🧑🏻‍💼</span>
                </div>
                <div class="hr-fullname" id="pwdFullname">Nama Tim HRD</div>
                <div class="hr-username" id="pwdUsername">@username</div>

                <button type="submit" class="btn-simpan-hr" id="btnUpdatePassword">
                    <span>🔒</span> Update Password
                </button>
                <a href="profile_admin.php" class="btn-kembali-hr">
                    ← Kembali ke Profil
                </a>
            </div>

            <div class="hr-right-card">
                <div class="hr-banner-header">
                    <span>🔑</span> Keamanan Akun & Ubah Password
                </div>

                <div class="hr-inner-form-box">
                    <div class="pwd-field-group">
                        <label>Password Lama <span class="req">*</span></label>
                        <div class="pwd-input-wrapper">
                            <input type="password" id="pwd_lama" name="password_lama" class="hr-form-input" placeholder="Masukkan password saat ini" required>
                            <button type="button" class="toggle-eye-btn" onclick="togglePassword('pwd_lama', this)">👁️</button>
                        </div>
                    </div>

                    <div class="pwd-field-group">
                        <label>Password Baru <span class="req">*</span></label>
                        <div class="pwd-input-wrapper">
                            <input type="password" id="pwd_baru" name="password_baru" class="hr-form-input" placeholder="Masukkan password baru (min. 5 karakter)" required>
                            <button type="button" class="toggle-eye-btn" onclick="togglePassword('pwd_baru', this)">👁️</button>
                        </div>
                    </div>

                    <div class="pwd-field-group">
                        <label>Konfirmasi Password Baru <span class="req">*</span></label>
                        <div class="pwd-input-wrapper">
                            <input type="password" id="pwd_konfirmasi" name="konfirmasi_password" class="hr-form-input" placeholder="Ulangi password baru" required>
                            <button type="button" class="toggle-eye-btn" onclick="togglePassword('pwd_konfirmasi', this)">👁️</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "👁";
    } else {
        input.type = "password";
        btn.textContent = "👁️";
    }
}

(function() {
    const form = document.getElementById('formUbahPasswordAdmin');
    const alertOk = document.getElementById('pwdAlertSuccess');
    const alertErr = document.getElementById('pwdAlertError');
    const btnSub = document.getElementById('btnUpdatePassword');

    function showSuccess(msg) {
        alertErr.style.display = 'none';
        alertOk.style.display = 'block';
        alertOk.textContent = '✅ ' + msg;
    }

    function showError(msg) {
        alertOk.style.display = 'none';
        alertErr.style.display = 'block';
        alertErr.textContent = '❌ ' + msg;
    }

    async function loadAdminInfo() {
        try {
            const res = await fetch('../api/admin/profil.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();
            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu sebagai HR/Admin!');
                window.location.href = '../login.php';
                return;
            }
            if (res.ok && result.success && result.data && result.data.profil) {
                const p = result.data.profil;
                document.getElementById('pwdFullname').textContent = p.nama || 'Nama Tim HRD';
                document.getElementById('pwdUsername').textContent = '@' + (p.username || 'username');
            }
        } catch (err) {}
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        btnSub.disabled = true;

        const payload = {
            password_lama: document.getElementById('pwd_lama').value,
            password_baru: document.getElementById('pwd_baru').value,
            konfirmasi_password: document.getElementById('pwd_konfirmasi').value
        };

        try {
            const res = await fetch('../api/admin/ubah_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok && result.success) {
                showSuccess(result.message || 'Password akun HR/Admin berhasil diperbarui!');
                form.reset();
            } else {
                showError(result.message || 'Gagal memperbarui password.');
            }
        } catch (err) {
            showError('Terjadi kesalahan koneksi saat memperbarui password.');
        } finally {
            btnSub.disabled = false;
        }
    });

    loadAdminInfo();
})();
</script>

<?php
include '../includes/footer_admin.php';
?>