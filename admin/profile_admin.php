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
        padding: 30px 20px 45px 20px;
        text-align: center;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #eceef2;
    }
    .hr-avatar-circle {
        width: 155px;
        height: 155px;
        margin: 0 auto 20px auto;
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
        margin-bottom: 6px;
    }
    .hr-username {
        font-size: 12.5px;
        color: #666;
        word-break: break-all;
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
        background-color: #f9fafc;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 20px 22px;
    }
    .hr-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 25px;
    }
    .hr-field-row {
        display: grid;
        grid-template-columns: 115px 1fr;
        align-items: center;
        gap: 10px;
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
    .hr-value-input {
        background-color: #f4f5f7;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 7px 12px;
        font-size: 12.5px;
        color: #444;
        min-height: 18px;
        width: 100%;
        box-sizing: border-box;
    }
    .helper-max-char {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
        display: block;
    }
    .helper-max-char span {
        color: #e53935;
    }

    @media (max-width: 900px) {
        .hr-profile-grid { grid-template-columns: 1fr; }
        .hr-fields-grid { grid-template-columns: 1fr; }
        .hr-field-row.full-span { grid-column: span 1; }
    }
</style>

<div class="hr-profile-wrapper">
    <div class="page-heading">LIHAT DATA DIRI</div>

    <div class="hr-profile-grid">
        <div class="hr-left-card">
            <div class="hr-avatar-circle">
                <span style="font-size: 75px;">🧑🏻‍💼</span>
            </div>
            <div class="hr-fullname" id="hrFullname">Nama Tim HRD</div>
            <div class="hr-username" id="hrUsername">@username</div>
        </div>

        <div class="hr-right-card">
            <div class="hr-banner-header">
                <span>👤</span> Data Diri
            </div>

            <div class="hr-inner-form-box">
                <div class="hr-fields-grid">
                    <div class="hr-field-row">
                        <label>Nama Lengkap <span class="req">*</span></label>
                        <div class="hr-value-input" id="valNama">-</div>
                    </div>
                    <div class="hr-field-row">
                        <label>Jenis Kelamin <span class="req">*</span></label>
                        <div class="hr-value-input" id="valJekel">-</div>
                    </div>

                    <div class="hr-field-row">
                        <label>Tempat Lahir <span class="req">*</span></label>
                        <div class="hr-value-input" id="valTempatLhr">-</div>
                    </div>
                    <div class="hr-field-row">
                        <label>Tanggal Lahir <span class="req">*</span></label>
                        <div class="hr-value-input" id="valTglLhr">-</div>
                    </div>

                    <div class="hr-field-row">
                        <label>Agama <span class="req">*</span></label>
                        <div class="hr-value-input" id="valAgama">-</div>
                    </div>
                    <div class="hr-field-row">
                        <label>No Telepone <span class="req">*</span></label>
                        <div class="hr-value-input" id="valNoTlp">-</div>
                    </div>

                    <div class="hr-field-row">
                        <label>ID Admin / Username <span class="req">*</span></label>
                        <div class="hr-value-input" id="valUsername">-</div>
                    </div>
                    <div class="hr-field-row">
                        <label>Jabatan <span class="req">*</span></label>
                        <div class="hr-value-input" id="valJabatan">-</div>
                    </div>

                    <div class="hr-field-row full-span">
                        <label style="padding-top: 6px;">Alamat <span class="req">*</span></label>
                        <div>
                            <div class="hr-value-input" id="valAlamat">-</div>
                            <span class="helper-max-char">Max 80 Karakter<span>*</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function formatTanggalIndo(tglStr) {
        if (!tglStr) return '-';
        const parts = String(tglStr).split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        return tglStr;
    }

    async function loadProfilAdmin() {
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

            if (!res.ok || !result.success || !result.data || !result.data.profil) {
                return;
            }

            const p = result.data.profil;
            document.getElementById('hrFullname').textContent = p.nama || 'Nama Tim HRD';
            document.getElementById('hrUsername').textContent = '@' + (p.username || 'username');
            document.getElementById('valNama').textContent = p.nama || '-';
            document.getElementById('valJekel').textContent = p.jekel || '-';
            document.getElementById('valTempatLhr').textContent = p.tempat_lhr || '-';
            document.getElementById('valTglLhr').textContent = formatTanggalIndo(p.tgl_lhr);
            document.getElementById('valAgama').textContent = p.agama || '-';
            document.getElementById('valNoTlp').textContent = p.no_tlp || '-';
            document.getElementById('valUsername').textContent = p.username || '-';
            document.getElementById('valJabatan').textContent = p.jabatan || '-';
            document.getElementById('valAlamat').textContent = p.alamat || '-';
        } catch (err) {}
    }

    loadProfilAdmin();
})();
</script>

<?php
include '../includes/footer_admin.php';
?>