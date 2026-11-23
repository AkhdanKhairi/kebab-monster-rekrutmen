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
    .hasil-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .profile-sidebar-card {
        width: 320px; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px;
        padding: 25px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); text-align: center; height: fit-content;
    }
    .profile-avatar {
        width: 100px; height: 100px; background: #ffe0b2; border-radius: 50%;
        margin: 0 auto 15px auto; display: flex; align-items: center; justify-content: center;
        font-size: 40px; overflow: hidden; border: 2px solid #1b5e20;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; }
    
    .hasil-main-content {
        flex: 1; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px;
        padding: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); text-align: center;
        display: flex; flex-direction: column; justify-content: space-between; align-items: center;
    }

    .waiting-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex: 1;
        width: 100%;
        padding: 40px 0;
    }
    .waiting-title {
        font-size: 24px;
        font-weight: 800;
        color: #111;
        line-height: 1.4;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-rekap {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        margin-bottom: 30px;
    }
    .table-rekap th, .table-rekap td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
        font-size: 14px;
    }
    .table-rekap th {
        background-color: #1b5e20;
        color: white;
        font-weight: 700;
    }
    .table-rekap tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .badge-nilai {
        background: #c8e6c9;
        color: #1b5e20;
        padding: 5px 12px;
        border-radius: 12px;
        font-weight: 800;
    }

    .btn-kembali {
        background-color: #1b5e20; color: #fff; text-decoration: none; padding: 10px 25px;
        border-radius: 20px; font-weight: 700; font-size: 14px; transition: background 0.2s;
        display: inline-block;
    }
    .btn-kembali:hover { background-color: #2e7d32; }
</style>

<div class="hasil-page-container">
    <div class="profile-sidebar-card">
        <div class="profile-avatar" id="hasilAvatar">👤</div>
        <div class="profile-name" id="hasilName">Memuat...</div>
        <div class="profile-email" id="hasilEmail">-</div>

        <ul class="modul-sidebar-list" style="list-style: none; padding: 0; margin-top: 20px; text-align: left;">
            <li id="hasilStatusSidebar" style="font-size: 13px; font-weight: 600; padding: 6px 0; border-bottom: 1px dashed #eee;">
                Status: Memuat...
            </li>
        </ul>
    </div>

    <div class="hasil-main-content">
        <div id="hasilMainBody" style="width: 100%;">
            <div class="waiting-wrapper">
                <div class="waiting-title">Memuat rekapitulasi hasil psikotes...</div>
            </div>
        </div>

        <div style="align-self: flex-start; width: 100%; text-align: left; border-top: 1px solid #eee; padding-top: 15px;">
            <a href="ujian.php" class="btn-kembali">← Kembali ke Daftar Modul</a>
        </div>
    </div>
</div>

<script>
    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    async function loadRekapHasil() {
        const bodyEl = document.getElementById('hasilMainBody');
        const sidebarStatus = document.getElementById('hasilStatusSidebar');

        try {
            const res = await fetch('../api/pelamar/ujian_hasil.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success || !result.data) {
                bodyEl.innerHTML = `<div class="waiting-wrapper"><div class="waiting-title">${escapeHtml(result.message || 'Gagal memuat data hasil.')}</div></div>`;
                return;
            }

            const data = result.data;
            const profile = data.pelamar || {};
            const belumDikonfirmasi = Boolean(data.belum_dikonfirmasi);
            const rekapModul = Array.isArray(data.rekap_modul) ? data.rekap_modul : [];

            const avatarEl = document.getElementById('hasilAvatar');
            if (profile.pas_foto_url) {
                avatarEl.innerHTML = `<img src="${escapeHtml(profile.pas_foto_url)}" alt="Foto Profil">`;
            } else {
                avatarEl.textContent = '👤';
            }
            document.getElementById('hasilName').textContent = profile.nama_lengkap || 'Nama Pelamar';
            document.getElementById('hasilEmail').textContent = profile.email || '-';

            sidebarStatus.textContent = belumDikonfirmasi
                ? 'Status: ⏳ Menunggu Konfirmasi HR'
                : 'Status: ✅ Selesai Divalidasi';

            if (belumDikonfirmasi) {
                bodyEl.innerHTML = `
                    <div class="waiting-wrapper">
                        <div class="waiting-title">
                            JAWABAN ANDA SEDANG DI KOREKSI OLEH TIM HR,<br>
                            HARAP TUNGGU UNTUK MELIHAT HASILNYA
                        </div>
                    </div>
                `;
                return;
            }

            const rowsHtml = rekapModul.map((rm, idx) => `
                <tr>
                    <td style="text-align: center; font-weight: 700;">${idx + 1}</td>
                    <td>${escapeHtml(rm.nama_modul)}</td>
                    <td style="text-align: center;"><span class="badge-nilai">${escapeHtml(rm.skor_akhir)} / 100</span></td>
                </tr>
            `).join('');

            bodyEl.innerHTML = `
                <h2 style="font-size: 22px; font-weight: 800; color: #111; margin-bottom: 10px;">
                    REKAPITULASI HASIL PSIKOTES
                </h2>
                <p style="font-size: 13.5px; color: #666; margin-bottom: 20px;">
                    Seluruh modul ujian Anda telah selesai divalidasi dan dikonfirmasi oleh tim HR Kebab Monster.
                </p>
                <table class="table-rekap">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">No</th>
                            <th style="width: 65%;">Nama Modul Psikotes</th>
                            <th style="width: 25%; text-align: center;">Hasil / Skor</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            `;
        } catch (err) {
            bodyEl.innerHTML = `<div class="waiting-wrapper"><div class="waiting-title">Terjadi kesalahan koneksi ke server.</div></div>`;
        }
    }

    loadRekapHasil();
</script>

<?php
include '../includes/footer_pelamar.php';
?>