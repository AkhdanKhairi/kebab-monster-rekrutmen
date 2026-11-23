<?php
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .hr-profile-wrapper {
        max-width: 1150px;
        margin: 12px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .page-heading {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin-bottom: 16px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .hr-profile-grid {
        display: grid;
        grid-template-columns: 270px 1fr;
        gap: 22px;
        align-items: start;
    }

    .hr-left-card {
        background-color: #f4f5f9;
        border-radius: 10px;
        padding: 26px 18px;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #eceef2;
    }
    .hr-avatar-circle {
        width: 130px;
        height: 130px;
        margin: 0 auto 16px auto;
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
        font-size: 14.5px;
        font-weight: 700;
        color: #222;
        margin-bottom: 4px;
        word-break: break-word;
    }
    .hr-username {
        font-size: 12.5px;
        color: #666;
        margin-bottom: 18px;
        word-break: break-all;
    }
    .btn-simpan-hr {
        background-color: #0b6623;
        color: #ffffff;
        border: none;
        width: 100%;
        min-height: 42px;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13.5px;
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
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        margin-top: 10px;
        background-color: #e2e8f0;
        color: #334155;
        text-decoration: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        transition: background 0.2s;
    }
    .btn-kembali-hr:hover {
        background-color: #cbd5e1;
    }

    .hr-right-card {
        background-color: #f4f5f9;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }
    .hr-banner-header {
        background-color: #0b6623;
        color: #ffffff;
        border-radius: 25px;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 14px;
    }
    .hr-inner-form-box {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .hr-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 24px;
    }
    .hr-field-row {
        display: grid;
        grid-template-columns: 120px 1fr;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .hr-field-row > div {
        min-width: 0;
    }
    .hr-field-row.full-span {
        grid-column: span 2;
        align-items: start;
    }
    .hr-field-row label {
        font-size: 12.5px;
        font-weight: 600;
        color: #333;
    }
    .hr-field-row label span.req {
        color: #e53935;
    }
    .hr-form-input {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 12px;
        font-size: 13px;
        color: #222;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .hr-form-input:focus {
        outline: none;
        border-color: #0b6623;
        box-shadow: 0 0 0 2px rgba(11, 102, 35, 0.12);
    }
    .helper-max-char {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
        display: block;
    }
    .helper-max-char span {
        color: #e53935;
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
        margin-bottom: 14px;
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
        margin-bottom: 14px;
    }

    @media (max-width: 900px) {
        .hr-profile-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        .hr-fields-grid {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .hr-field-row.full-span {
            grid-column: span 1;
        }
    }

    @media (max-width: 768px) {
        .hr-profile-wrapper {
            margin: 6px auto;
            padding: 0 8px;
        }
        .page-heading {
            font-size: 16px;
            margin-bottom: 12px;
        }
        .hr-left-card {
            padding: 20px 14px;
        }
        .hr-avatar-circle {
            width: 105px;
            height: 105px;
            margin-bottom: 12px;
        }
        .hr-avatar-circle span {
            font-size: 52px !important;
        }
        .hr-right-card {
            padding: 12px;
            border-radius: 12px;
        }
        .hr-inner-form-box {
            padding: 14px;
            border-radius: 10px;
        }
        .hr-field-row,
        .hr-field-row.full-span {
            grid-template-columns: 1fr;
            gap: 5px;
            align-items: stretch;
        }
        .hr-field-row label {
            font-size: 13px;
            padding-top: 0 !important;
        }
        .hr-form-input {
            min-height: 42px;
            font-size: 16px;
            padding: 9px 12px;
        }
        .btn-simpan-hr,
        .btn-kembali-hr {
            min-height: 44px;
            font-size: 14px;
        }
    }
</style>

<div class="hr-profile-wrapper">
    <div class="page-heading">EDIT DATA DIRI</div>

    <div class="alert-msg-success" id="alertSuccess"></div>
    <div class="alert-msg-error" id="alertError"></div>

    <form id="formEditProfilAdmin" style="display: contents;">
        <div class="hr-profile-grid">
            <div class="hr-left-card">
                <div class="hr-avatar-circle">
                    <span style="font-size: 66px;">🧑🏻‍💼</span>
                </div>
                <div class="hr-fullname" id="sideFullname">Nama Tim HRD</div>
                <div class="hr-username" id="sideUsername">@username</div>

                <button type="submit" class="btn-simpan-hr" id="btnSimpanProfil">
                    <span>💾</span> Simpan Perubahan
                </button>
                <a href="profile_admin.php" class="btn-kembali-hr">
                    ← Kembali ke Profil
                </a>
            </div>

            <div class="hr-right-card">
                <div class="hr-banner-header">
                    <span>✏️</span> Edit Data Diri
                </div>

                <div class="hr-inner-form-box">
                    <div class="hr-fields-grid">
                        <div class="hr-field-row">
                            <label for="nama">Nama Lengkap <span class="req">*</span></label>
                            <input id="nama" type="text" name="nama" class="hr-form-input" required>
                        </div>
                        <div class="hr-field-row">
                            <label for="jekel">Jenis Kelamin <span class="req">*</span></label>
                            <select id="jekel" name="jekel" class="hr-form-input" required>
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-Laki">Laki-Laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="hr-field-row">
                            <label for="tempat_lhr">Tempat Lahir <span class="req">*</span></label>
                            <input id="tempat_lhr" type="text" name="tempat_lhr" class="hr-form-input" required>
                        </div>
                        <div class="hr-field-row">
                            <label for="tgl_lhr">Tanggal Lahir <span class="req">*</span></label>
                            <input id="tgl_lhr" type="date" name="tgl_lhr" class="hr-form-input" required>
                        </div>

                        <div class="hr-field-row">
                            <label for="agama">Agama <span class="req">*</span></label>
                            <select id="agama" name="agama" class="hr-form-input" required>
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </select>
                        </div>
                        <div class="hr-field-row">
                            <label for="no_tlp">No Telepone <span class="req">*</span></label>
                            <input id="no_tlp" type="tel" name="no_tlp" class="hr-form-input" required>
                        </div>

                        <div class="hr-field-row">
                            <label for="username">Username <span class="req">*</span></label>
                            <input id="username" type="text" name="username" class="hr-form-input" required>
                        </div>
                        <div class="hr-field-row">
                            <label for="jabatan">Jabatan <span class="req">*</span></label>
                            <input id="jabatan" type="text" name="jabatan" class="hr-form-input" required>
                        </div>

                        <div class="hr-field-row full-span">
                            <label for="alamat" style="padding-top: 6px;">Alamat <span class="req">*</span></label>
                            <div>
                                <input id="alamat" type="text" name="alamat" maxlength="80" class="hr-form-input" required>
                                <span class="helper-max-char">Max 80 Karakter<span>*</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function() {
    const form = document.getElementById('formEditProfilAdmin');
    const alertOk = document.getElementById('alertSuccess');
    const alertErr = document.getElementById('alertError');
    const btnSimpan = document.getElementById('btnSimpanProfil');

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

    function isiForm(p) {
        if (!p) return;
        document.getElementById('sideFullname').textContent = p.nama || 'Nama Tim HRD';
        document.getElementById('sideUsername').textContent = '@' + (p.username || 'username');
        document.getElementById('nama').value = p.nama || '';

        const jk = String(p.jekel || '');
        const jkSelect = document.getElementById('jekel');
        if (['laki-laki', 'laki-lakI'].includes(jk.toLowerCase())) {
            jkSelect.value = 'Laki-Laki';
        } else {
            jkSelect.value = jk;
        }

        document.getElementById('tempat_lhr').value = p.tempat_lhr || '';
        document.getElementById('tgl_lhr').value = p.tgl_lhr || '';
        document.getElementById('agama').value = p.agama || '';
        document.getElementById('no_tlp').value = p.no_tlp || '';
        document.getElementById('username').value = p.username || '';
        document.getElementById('jabatan').value = p.jabatan || '';
        document.getElementById('alamat').value = p.alamat || '';
    }

    async function loadProfil() {
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
                isiForm(result.data.profil);
            }
        } catch (err) {}
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        btnSimpan.disabled = true;

        const payload = {
            nama: document.getElementById('nama').value.trim(),
            jekel: document.getElementById('jekel').value.trim(),
            tempat_lhr: document.getElementById('tempat_lhr').value.trim(),
            tgl_lhr: document.getElementById('tgl_lhr').value.trim(),
            agama: document.getElementById('agama').value.trim(),
            no_tlp: document.getElementById('no_tlp').value.trim(),
            username: document.getElementById('username').value.trim(),
            jabatan: document.getElementById('jabatan').value.trim(),
            alamat: document.getElementById('alamat').value.trim()
        };

        try {
            const res = await fetch('../api/admin/profil.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            const result = await res.json();

            if (res.ok && result.success) {
                showSuccess(result.message || 'Data profil HR/Admin berhasil diperbarui!');
                if (result.data && result.data.profil) {
                    isiForm(result.data.profil);
                }
            } else {
                showError(result.message || 'Gagal memperbarui data.');
            }
        } catch (err) {
            showError('Terjadi kesalahan koneksi saat menyimpan profil.');
        } finally {
            btnSimpan.disabled = false;
        }
    });

    loadProfil();
})();
</script>

<?php
include __DIR__ . '/../includes/footer_admin.php';
?>