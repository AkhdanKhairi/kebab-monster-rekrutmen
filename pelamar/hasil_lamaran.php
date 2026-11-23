<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$initial_id_lamaran = isset($_GET['id_lamaran']) ? intval($_GET['id_lamaran']) : 0;

include '../includes/header_pelamar.php';
?>

<style>
    .info-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }

    .profile-sidebar-card {
        width: 320px;
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
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 2px; }
    .profile-phone { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; }
    
    .lamaran-info-box { text-align: left; }
    .lamaran-info-box h4 { font-size: 14px; font-weight: 800; color: #1b5e20; margin-bottom: 10px; }
    .lamaran-switcher {
        width: 100%;
        padding: 7px 10px;
        margin-bottom: 12px;
        border: 1.5px solid #1b5e20;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #1b5e20;
        background: #f1f8e9;
        cursor: pointer;
    }
    .info-row { font-size: 12px; margin-bottom: 10px; color: #444; }
    .info-row span { font-weight: 700; color: #111; display: block; margin-top: 2px; }

    .result-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 35px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
    }
    .result-title { font-size: 22px; font-weight: 800; color: #111; margin-bottom: 25px; }

    /* Badge Status Akhir */
    .status-badge-container {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 30px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 25px;
        letter-spacing: 0.5px;
    }
    .badge-success { background-color: #c8e6c9; color: #1b5e20; border: 1.5px solid #2e7d32; }
    .badge-danger { background-color: #ffcdd2; color: #b71c1c; border: 1.5px solid #c62828; }
    .badge-pending { background-color: #fff9c4; color: #f57f17; border: 1.5px solid #fbc02d; }

    /* Kartu Pengumuman Hasil */
    .result-card-box {
        background-color: #f9fbe7;
        border: 1.5px solid #c5e1a5;
        border-radius: 12px;
        padding: 30px;
        text-align: left;
    }
    .result-card-box h3 { font-size: 18px; font-weight: 800; color: #1b5e20; margin-bottom: 12px; }
    .result-card-box p { font-size: 14px; color: #444; line-height: 1.7; margin: 0; }

    .btn-action-container {
        margin-top: 25px;
        text-align: center;
    }
    .btn-back-career {
        display: inline-block;
        background-color: #1b5e20;
        color: #fff;
        padding: 10px 24px;
        border-radius: 25px;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-back-career:hover {
        background-color: #2e7d32;
    }

    @media (max-width: 850px) {
        .info-page-container { flex-direction: column; padding: 14px; gap: 20px; }
        .profile-sidebar-card { width: 100%; box-sizing: border-box; }
        .result-main-content { padding: 24px 18px; }
    }
</style>

<div class="info-page-container">

    <!-- SIDEBAR PROFIL -->
    <div class="profile-sidebar-card">
        <div id="hasil-avatar" class="profile-avatar">👤</div>
        <div id="hasil-name" class="profile-name">Memuat...</div>
        <div id="hasil-email" class="profile-email">-</div>
        <div id="hasil-phone" class="profile-phone">-</div>

        <div class="lamaran-info-box">
            <h4>LAMARAN</h4>
            <select id="hasil-lamaran-switcher" class="lamaran-switcher" style="display: none;"></select>
            <div class="info-row">Posisi Dilamar: <span id="hasil-posisi">Belum Melamar Posisi Apapun</span></div>
            <div class="info-row">Tanggal Daftar: <span id="hasil-tgl-daftar">-</span></div>
            <div class="info-row">Status Tahap: <span id="hasil-status-tahap">Belum Melamar</span></div>
        </div>
    </div>

    <!-- KONTEN UTAMA HASIL LAMARAN -->
    <div class="result-main-content">
        <h2 class="result-title">PENGUMUMAN HASIL AKHIR SELEKSI</h2>

        <!-- Badge Indikator Status -->
        <div id="hasil-badge" class="status-badge-container badge-pending">
            MENUNGGU PROSES
        </div>

        <!-- Kotak Informasi Hasil -->
        <div class="result-card-box">
            <h3 id="hasil-judul">Evaluasi Seleksi Sedang Berlangsung</h3>
            <p id="hasil-pesan">Memuat pengumuman hasil seleksi...</p>
        </div>

        <div class="btn-action-container">
            <a href="karir.php" class="btn-back-career">← Kembali ke Menu Karir</a>
        </div>
    </div>

</div>

<script>
    const INITIAL_ID_LAMARAN = <?php echo json_encode($initial_id_lamaran); ?>;
    let cachedHasilLamaranList = [];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function nl2brSafe(str) {
        return escapeHtml(str).replace(/\r\n|\r|\n/g, '<br>');
    }

    function formatTanggalIndo(dtRaw, fallback = '-') {
        if (!dtRaw) return fallback;
        const clean = String(dtRaw).split(' ')[0];
        const parts = clean.split('-');
        if (parts.length !== 3) return clean;
        return `${parts[2].padStart(2, '0')}-${parts[1].padStart(2, '0')}-${parts[0]}`;
    }

    function renderHasilLamaran(dataLamaran) {
        const posisiDilamar  = dataLamaran?.posisi_pekerjaan || 'Belum Melamar Posisi Apapun';
        const tglDaftar      = formatTanggalIndo(dataLamaran?.tgl_melamar, '-');
        const statusLamaran  = dataLamaran?.status_lamaran || 'Belum Melamar';
        const hasilAdmin     = (dataLamaran?.hasil_akhir_efektif || '').trim();
        const pesanAdmin     = (dataLamaran?.pesan_hasil_akhir_efektif || '').trim();

        const statusEfektif       = hasilAdmin !== '' ? hasilAdmin : statusLamaran;
        const statusTampilSidebar = hasilAdmin !== '' ? hasilAdmin : statusLamaran;

        document.getElementById('hasil-posisi').textContent       = posisiDilamar;
        document.getElementById('hasil-tgl-daftar').textContent   = tglDaftar;
        document.getElementById('hasil-status-tahap').textContent = statusTampilSidebar;

        let badgeStatus = "MENUNGGU PROSES";
        let badgeClass  = "badge-pending";
        let judulHasil  = "Evaluasi Seleksi Sedang Berlangsung";
        let pesanHasil  = "Seluruh tahapan seleksi Anda sedang ditinjau oleh tim manajemen dan HR Kebab Monster. Mohon pantau halaman ini secara berkala untuk mengetahui pengumuman hasil akhir.";

        const lowerStatus = statusEfektif.toLowerCase();

        if (lowerStatus === 'diterima' || lowerStatus === 'selesai') {
            badgeStatus = "SELAMAT! ANDA DITERIMA";
            badgeClass  = "badge-success";
            judulHasil  = "Penawaran Kerja (Offer Letter)";

            if (pesanAdmin !== '') {
                pesanHasil = nl2brSafe(pesanAdmin);
            } else {
                pesanHasil = `Berdasarkan hasil keseluruhan seleksi mulai dari seleksi berkas, psikotes, hingga wawancara, kami dengan senang hati mengumumkan bahwa Anda dinyatakan <strong>LULUS dan DITERIMA</strong> di Kebab Monster untuk posisi <b>${escapeHtml(posisiDilamar)}</b>.<br><br>Tim HR kami akan segera mengirimkan email resmi berisi rincian kontrak kerja, jadwal onboarding, serta instruksi penempatan lebih lanjut.`;
            }
        } else if (
            lowerStatus === 'tidak diterima' ||
            lowerStatus === 'ditolak' ||
            lowerStatus === 'tidak lolos'
        ) {
            badgeStatus = "MOHON MAAF, BELUM BERUNTUNG";
            badgeClass  = "badge-danger";
            judulHasil  = "Hasil Seleksi Rekrutmen";

            if (pesanAdmin !== '') {
                pesanHasil = nl2brSafe(pesanAdmin);
            } else {
                pesanHasil = `Terima kasih banyak atas antusiasme dan waktu yang telah Anda luangkan untuk mengikuti proses seleksi di Kebab Monster.<br><br>Setelah melalui pertimbangan yang matang dari tim penilai, dengan berat hati kami menyampaikan bahwa saat ini kami belum dapat melanjutkan proses rekrutmen Anda untuk posisi <b>${escapeHtml(posisiDilamar)}</b>. Kami mendoakan kesuksesan yang terbaik untuk karier Anda di masa depan.`;
            }
        } else if (pesanAdmin !== '') {
            pesanHasil = nl2brSafe(pesanAdmin);
        }

        const badgeEl = document.getElementById('hasil-badge');
        badgeEl.className   = `status-badge-container ${badgeClass}`;
        badgeEl.textContent = badgeStatus;

        document.getElementById('hasil-judul').textContent = judulHasil;
        document.getElementById('hasil-pesan').innerHTML   = pesanHasil;
    }

    async function loadHasilLamaran() {
        try {
            const url = INITIAL_ID_LAMARAN > 0
                ? `../api/pelamar/lamaran.php?id_lamaran=${encodeURIComponent(INITIAL_ID_LAMARAN)}`
                : '../api/pelamar/lamaran.php';

            const res = await fetch(url, { credentials: 'same-origin' });
            const result = await res.json();

            if (!res.ok || !result.success) {
                if (res.status === 401) {
                    alert(result.message || 'Silakan login terlebih dahulu!');
                    window.location.href = '../login.php';
                    return;
                }
                alert(result.message || 'Gagal memuat hasil lamaran.');
                return;
            }

            const pelamar          = result.data?.pelamar || {};
            cachedHasilLamaranList = result.data?.lamaran_list || [];
            const selected         = result.data?.selected_lamaran || null;

            if (pelamar.pas_foto_url) {
                document.getElementById('hasil-avatar').innerHTML =
                    `<img src="${pelamar.pas_foto_url}" alt="Foto Profil">`;
            }
            document.getElementById('hasil-name').textContent  = pelamar.nama_lengkap || 'Nama Pelamar';
            document.getElementById('hasil-email').textContent = pelamar.email || '-';
            document.getElementById('hasil-phone').textContent = pelamar.no_telepon || '-';

            const switcher = document.getElementById('hasil-lamaran-switcher');
            if (cachedHasilLamaranList.length > 1) {
                switcher.innerHTML = cachedHasilLamaranList.map(item => {
                    const tgl = formatTanggalIndo(item.tgl_melamar, '');
                    const isSel = selected && Number(item.id_lamaran) === Number(selected.id_lamaran) ? 'selected' : '';
                    return `<option value="${item.id_lamaran}" ${isSel}>${item.posisi_pekerjaan} (${tgl})</option>`;
                }).join('');
                switcher.style.display = 'block';
                switcher.onchange = function() {
                    const chosen = cachedHasilLamaranList.find(x => Number(x.id_lamaran) === Number(this.value)) || null;
                    renderHasilLamaran(chosen);
                };
            }

            renderHasilLamaran(selected);
        } catch (err) {
            alert('Terjadi kesalahan saat memuat pengumuman hasil seleksi.');
        }
    }

    loadHasilLamaran();
</script>

<?php include '../includes/footer_pelamar.php'; ?>