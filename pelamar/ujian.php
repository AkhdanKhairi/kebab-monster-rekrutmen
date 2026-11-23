<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

include '../includes/header_pelamar.php';
?>

<style>
    .ujian-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .ujian-page-container *,
    .ujian-page-container *::before,
    .ujian-page-container *::after {
        box-sizing: border-box;
    }
    .profile-sidebar-card {
        width: 320px;
        flex-shrink: 0;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 25px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        height: fit-content;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: #ffe0b2;
        border-radius: 50%;
        margin: 0 auto 15px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        overflow: hidden;
        border: 2px solid #1b5e20;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; overflow-wrap: anywhere; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; overflow-wrap: anywhere; }
    
    .modul-sidebar-list { list-style: none; padding: 0; margin: 0; text-align: left; }
    .modul-sidebar-list li {
        font-size: 13px;
        font-weight: 600;
        padding: 7px 0;
        border-bottom: 1px dashed #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        color: #444;
    }

    .ujian-main-content {
        flex: 1;
        min-width: 0;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .ujian-title { text-align: center; font-size: 22px; font-weight: 800; color: #111; margin-bottom: 25px; }

    .modul-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .modul-info {
        min-width: 0;
        flex: 1;
    }
    .modul-info h3 { font-size: 16px; font-weight: 800; color: #111; margin: 0 0 4px 0; line-height: 1.35; overflow-wrap: anywhere; }
    .modul-info p { font-size: 13px; color: #555; margin: 0; line-height: 1.45; }

    .modul-action {
        flex-shrink: 0;
    }

    .btn-mulai {
        background-color: #c8e6c9;
        color: #1b5e20;
        border: 1.5px solid #2e7d32;
        padding: 9px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
        transition: background 0.2s;
    }
    .btn-mulai:hover { background-color: #a5d6a7; }

    .btn-hasil {
        background-color: #fff9c4;
        color: #f57f17;
        border: 1.5px solid #fbc02d;
        padding: 9px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }
    .btn-hasil:hover { background-color: #fff176; }

    .btn-locked {
        background-color: #e0e0e0;
        color: #757575;
        border: 1.5px solid #bdbdbd;
        padding: 9px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }

    .empty-modul {
        text-align: center;
        padding: 28px 16px;
        color: #666;
        font-size: 14px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
    }

    @media (max-width: 900px) {
        .ujian-page-container {
            flex-direction: column;
            margin: 12px auto;
            padding: 14px;
            gap: 18px;
        }
        .profile-sidebar-card {
            width: 100%;
            padding: 20px 16px;
        }
        .ujian-main-content {
            padding: 20px 16px;
        }
    }

    @media (max-width: 768px) {
        .ujian-title {
            font-size: 19px;
            margin-bottom: 18px;
        }
        .profile-avatar {
            width: 82px;
            height: 82px;
            font-size: 32px;
            margin-bottom: 12px;
        }
        .modul-card {
            flex-direction: column;
            align-items: stretch;
            padding: 16px;
            gap: 14px;
        }
        .modul-info h3 {
            font-size: 15px;
        }
        .modul-action {
            width: 100%;
        }
        .btn-mulai,
        .btn-hasil,
        .btn-locked {
            width: 100%;
            min-height: 42px;
        }
    }
</style>

<div class="ujian-page-container">
    <div class="profile-sidebar-card">
        <div class="profile-avatar" id="sidebarAvatar">👤</div>
        <div class="profile-name" id="sidebarName">Memuat...</div>
        <div class="profile-email" id="sidebarEmail">-</div>
        <ul class="modul-sidebar-list" id="sidebarModulList"></ul>
    </div>

    <div class="ujian-main-content">
        <h2 class="ujian-title">DAFTAR MODUL PSIKOTES</h2>
        <div id="daftarModulContainer">
            <div class="empty-modul">Memuat daftar modul psikotes...</div>
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

    async function loadUjianList() {
        const container = document.getElementById('daftarModulContainer');
        const sidebarList = document.getElementById('sidebarModulList');

        try {
            const res = await fetch('../api/pelamar/ujian_list.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success) {
                container.innerHTML = `<div class="empty-modul">${escapeHtml(result.message || 'Gagal memuat modul ujian.')}</div>`;
                return;
            }

            const data = result.data || {};
            const pelamar = data.pelamar || {};
            const modulList = Array.isArray(data.daftar_modul) ? data.daftar_modul : [];

            const avatarEl = document.getElementById('sidebarAvatar');
            if (pelamar.pas_foto_url) {
                avatarEl.innerHTML = `<img src="${escapeHtml(pelamar.pas_foto_url)}" alt="Foto Profil">`;
            } else {
                avatarEl.textContent = '👤';
            }
            document.getElementById('sidebarName').textContent = pelamar.nama_lengkap || 'Nama Pelamar';
            document.getElementById('sidebarEmail').textContent = pelamar.email || '-';

            sidebarList.innerHTML = modulList.map((m, idx) => {
                const icon = (m.status_pengerjaan === 'Selesai') ? '✅' : '🔒';
                return `<li><span>Psikotes Module ${idx + 1}</span><span>${icon}</span></li>`;
            }).join('');

            if (modulList.length === 0) {
                container.innerHTML = `<div class="empty-modul">Belum ada modul psikotes yang tersedia untuk kategori posisi Anda saat ini.</div>`;
                return;
            }

            container.innerHTML = modulList.map((modul, idx) => {
                const no = idx + 1;
                const status = modul.status_pengerjaan || 'Belum Mulai';
                const durasi = parseInt(modul.durasi_menit || 0, 10);
                const idModul = parseInt(modul.id_modul, 10);

                let actionHtml = '';
                if (status === 'Selesai') {
                    actionHtml = `<a href="detail_hasil_ujian.php?id=${idModul}" class="btn-hasil">Lihat Hasil Tes ➔</a>`;
                } else if (modul.terbuka) {
                    actionHtml = `<a href="cara_ujian.php?id=${idModul}" class="btn-mulai">Mulai ➔</a>`;
                } else {
                    actionHtml = `<button type="button" class="btn-locked" disabled>🔒 Terkunci</button>`;
                }

                return `
                    <div class="modul-card">
                        <div class="modul-info">
                            <h3>Psikotes Module ${no}: ${escapeHtml(modul.nama_modul)}</h3>
                            <p>Status: <strong>${escapeHtml(status)}</strong> (Durasi: ${durasi} Menit)</p>
                        </div>
                        <div class="modul-action">${actionHtml}</div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            container.innerHTML = `<div class="empty-modul">Terjadi kesalahan koneksi saat memuat daftar modul.</div>`;
        }
    }

    loadUjianList();
</script>

<?php
include '../includes/footer_pelamar.php';
?>