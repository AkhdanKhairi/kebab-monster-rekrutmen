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
    .info-page-container *,
    .info-page-container *::before,
    .info-page-container *::after {
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
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 2px; overflow-wrap: anywhere; }
    .profile-phone { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; overflow-wrap: anywhere; }
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
    .info-row { font-size: 12px; margin-bottom: 10px; color: #444; line-height: 1.45; }
    .info-row span { font-weight: 700; color: #111; display: block; margin-top: 2px; overflow-wrap: anywhere; }

    .progress-main-content {
        flex: 1;
        min-width: 0;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .progress-title { text-align: center; font-size: 22px; font-weight: 800; color: #111; margin-bottom: 30px; line-height: 1.3; }

    .stepper-wrapper {
        position: relative;
        margin-bottom: 36px;
    }
    .stepper-container {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        position: relative;
        padding: 0 12px;
        gap: 6px;
    }
    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        flex: 1;
        min-width: 0;
    }
    .step-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    .step-circle.gray { background-color: #e0e0e0; color: #757575; border: 2px solid #bdbdbd; }
    .step-circle.yellow { background-color: #fff176; color: #f57f17; border: 3px solid #fbc02d; box-shadow: 0 0 10px rgba(253, 216, 53, 0.5); }
    .step-circle.green { background-color: #2e7d32; color: #ffffff; border: 3px solid #1b5e20; }
    .step-label {
        font-size: 12px;
        font-weight: 700;
        color: #333;
        margin-top: 8px;
        text-align: center;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .step-line {
        position: absolute;
        top: 30px;
        left: 10%;
        right: 10%;
        height: 4px;
        background-color: #e0e0e0;
        z-index: 1;
    }

    .detail-tahap-box {
        background-color: #fff9c4;
        border: 1px solid #fbc02d;
        border-radius: 12px;
        padding: 20px;
    }
    .detail-tahap-box h3 { font-size: 15px; font-weight: 800; color: #f57f17; margin: 0 0 10px 0; line-height: 1.35; }
    .detail-tahap-box p { font-size: 13.5px; color: #444; line-height: 1.65; margin: 0; overflow-wrap: anywhere; }

    /* Tablet & Layar Kecil */
    @media (max-width: 900px) {
        .info-page-container {
            flex-direction: column;
            margin: 12px auto;
            padding: 14px;
            gap: 18px;
        }
        .profile-sidebar-card {
            width: 100%;
            padding: 20px 16px;
        }
        .progress-main-content {
            padding: 20px 16px;
        }
    }

    /* Layar HP */
    @media (max-width: 768px) {
        .progress-title {
            font-size: 18px;
            margin-bottom: 22px;
        }
        .profile-avatar {
            width: 82px;
            height: 82px;
            font-size: 32px;
            margin-bottom: 12px;
        }
        .stepper-wrapper {
            margin-bottom: 26px;
        }
        .stepper-container {
            padding: 0;
            gap: 4px;
        }
        .step-circle {
            width: 42px;
            height: 42px;
            font-size: 16px;
        }
        .step-line {
            top: 21px;
            left: 10%;
            right: 10%;
            height: 3px;
        }
        .step-label {
            font-size: 10px;
            margin-top: 6px;
            line-height: 1.2;
        }
        .detail-tahap-box {
            padding: 16px 14px;
        }
        .detail-tahap-box h3 {
            font-size: 14px;
        }
        .detail-tahap-box p {
            font-size: 13px;
        }
    }
</style>

<div class="info-page-container">
    <div class="profile-sidebar-card">
        <div id="status-avatar" class="profile-avatar">👤</div>
        <div id="status-name" class="profile-name">Memuat...</div>
        <div id="status-email" class="profile-email">-</div>
        <div id="status-phone" class="profile-phone">-</div>

        <div class="lamaran-info-box">
            <h4>LAMARAN</h4>
            <select id="lamaran-switcher" class="lamaran-switcher" style="display: none;"></select>
            <div class="info-row">Posisi Dilamar: <span id="info-posisi">POSISI DILAMAR</span></div>
            <div class="info-row">Tanggal Daftar: <span id="info-tgl-daftar">-</span></div>
            <div class="info-row">Status Berkas (tanggal verifikasi): <span id="info-berkas">Memuat...</span></div>
            <div class="info-row">Screening CV (tanggal update): <span id="info-screening">Menunggu Jadwal</span></div>
            <div class="info-row">Status Psikotes: <span id="info-psikotes">Menunggu Jadwal</span></div>
            <div class="info-row">Status interview: <span id="info-interview">Menunggu Jadwal</span></div>
            <div class="info-row">Status hasil penerimaan: <span id="info-hasil">Menunggu Jadwal</span></div>
        </div>
    </div>

    <div class="progress-main-content">
        <h2 class="progress-title">STATUS LAMARAN ANDA</h2>
        <div class="stepper-wrapper">
            <div class="step-line"></div>
            <div class="stepper-container">
                <div class="step-item">
                    <div id="step-1" class="step-circle gray">✓</div>
                    <div class="step-label">Aplikasi Diterima</div>
                </div>
                <div class="step-item">
                    <div id="step-2" class="step-circle gray">📄</div>
                    <div class="step-label">Screening CV</div>
                </div>
                <div class="step-item">
                    <div id="step-3" class="step-circle gray">📝</div>
                    <div class="step-label">Psikotes</div>
                </div>
                <div class="step-item">
                    <div id="step-4" class="step-circle gray">👥</div>
                    <div class="step-label">Interview</div>
                </div>
                <div class="step-item">
                    <div id="step-5" class="step-circle gray">A+</div>
                    <div class="step-label">Hasil Penerimaan</div>
                </div>
            </div>
        </div>

        <div class="detail-tahap-box">
            <h3 id="detail-judul">INFORMASI STATUS LAMARAN</h3>
            <p id="detail-teks">Memuat status lamaran...</p>
        </div>
    </div>
</div>

<script>
    const INITIAL_ID_LAMARAN = <?php echo json_encode($initial_id_lamaran); ?>;
    let cachedLamaranList = [];
    let cachedIsProfilLengkap = false;

    function formatTanggalIndo(dtRaw, fallback = '-') {
        if (!dtRaw) return fallback;
        const clean = String(dtRaw).split(' ')[0];
        const parts = clean.split('-');
        if (parts.length !== 3) return clean;
        return `${parts[2].padStart(2, '0')}-${parts[1].padStart(2, '0')}-${parts[0]}`;
    }

    function setStepColor(stepNum, color) {
        const el = document.getElementById(`step-${stepNum}`);
        if (el) {
            el.className = `step-circle ${color}`;
        }
    }

    function renderSelectedLamaran(dataLamaran, isProfilLengkap) {
        let posisiDilamar    = 'POSISI DILAMAR';
        let tglDaftar        = '-';
        let statusBerkasTeks = 'Belum Melamar';
        let tglScreening     = 'Menunggu Jadwal';
        let tglPsikotes      = 'Menunggu Jadwal';
        let tglInterview     = 'Menunggu Jadwal';
        let tglHasil         = 'Menunggu Jadwal';
        let statusDb         = 'Belum Melamar';

        if (dataLamaran) {
            posisiDilamar = dataLamaran.posisi_pekerjaan || 'POSISI DILAMAR';
            tglDaftar     = formatTanggalIndo(dataLamaran.tgl_melamar, '-');
            statusDb      = dataLamaran.status_lamaran || 'Pending';

            const tglVerifikasi = formatTanggalIndo(dataLamaran.tgl_verifikasi, tglDaftar);
            tglScreening  = formatTanggalIndo(dataLamaran.tgl_screening, 'Menunggu Jadwal');
            tglPsikotes   = formatTanggalIndo(dataLamaran.tgl_psikotes, 'Menunggu Jadwal');
            tglInterview  = formatTanggalIndo(dataLamaran.tgl_interview, 'Menunggu Jadwal');
            tglHasil      = formatTanggalIndo(dataLamaran.tgl_hasil, 'Menunggu Jadwal');

            if (!isProfilLengkap) {
                statusBerkasTeks = 'Belum Lengkap (Pending)';
            } else {
                statusBerkasTeks = `Berkas Sudah Terverifikasi (${tglVerifikasi})`;
            }
        }

        document.getElementById('info-posisi').textContent      = posisiDilamar;
        document.getElementById('info-tgl-daftar').textContent  = tglDaftar;
        document.getElementById('info-berkas').textContent      = statusBerkasTeks;
        document.getElementById('info-screening').textContent   = tglScreening;
        document.getElementById('info-psikotes').textContent    = tglPsikotes;
        document.getElementById('info-interview').textContent   = tglInterview;
        document.getElementById('info-hasil').textContent       = tglHasil;

        let s1 = 'gray', s2 = 'gray', s3 = 'gray', s4 = 'gray', s5 = 'gray';
        let judulDetail = "INFORMASI STATUS LAMARAN";
        let teksDetail  = "Anda belum mengirimkan lamaran ke posisi apa pun. Silakan buka menu 'Karir' untuk memilih lowongan dan mengirimkan lamaran Anda.";

        if (statusDb === 'Belum Melamar') {
            // Semua abu-abu
        } else if (dataLamaran && !isProfilLengkap) {
            s1 = 'yellow';
            judulDetail = "PERHATIAN: LENGKAPI DATA DIRI ANDA";
            teksDetail  = "Formulir pendaftaran Anda sudah masuk ke sistem, namun <b>data diri atau profil akun Anda belum lengkap</b> di database. Sistem menahan proses verifikasi ini. Mohon segera melengkapi informasi profil Anda pada menu 'Profil' agar status berubah menjadi hijau dan dapat diproses ke tahap selanjutnya.";
        } else if (statusDb === 'Pending' || statusDb === 'Aplikasi Diterima') {
            s1 = 'yellow';
            judulDetail = "DETAIL TAHAP: 1. APLIKASI DITERIMA";
            teksDetail  = "Selamat! Formulir pendaftaran dan berkas lamaran Anda telah berhasil masuk dan tercatat di dalam sistem database rekrutmen Kebab Monster untuk diverifikasi secara administratif.<br><br>Saat ini lamaran Anda sedang berada dalam status menunggu pengecekan berkas awal oleh tim HR. Mohon pastikan nomor telepon dan alamat email Anda selalu aktif dan rutin diperiksa agar Anda tidak ketinggalan informasi untuk tahapan berikutnya.";
        } else if (statusDb === 'Screening') {
            s1 = 'green';
            s2 = 'yellow';
            judulDetail = "DETAIL TAHAP: 2. SCREENING CV";
            teksDetail  = "Tahap <i>Screening CV</i> adalah proses di mana tim HR dan manajemen Kebab Monster meninjau secara mendalam dan teliti riwayat pendidikan, latar belakang pengalaman kerja, serta keahlian yang tertera di dalam CV Anda guna disesuaikan dengan posisi yang dilamar.<br><br>Saat ini lamaran Anda sedang dalam antrean peninjauan oleh tim terkait, yang umumnya memakan waktu sekitar 1 hingga 3 hari kerja. Mohon bersabar menunggu hingga status Anda diperbarui secara berkala melalui sistem ini.";
        } else if (statusDb === 'Psikotes') {
            s1 = 'green';
            s2 = 'green';
            s3 = 'yellow';
            judulDetail = "DETAIL TAHAP: 3. PSIKOTES ONLINE";
            teksDetail  = "<i>Psikotes Online</i> adalah tahapan uji kompetensi kepribadian dan kemampuan dasar guna mengukur kesesuaian karakter serta potensi kerja Anda dengan standar perusahaan.<br><br>Jika Anda belum mengisi atau mengerjakan tes pada halaman \"Psikotes\", harap segera mengakses dan menyelesaikannya. Bagi yang sudah selesai mengerjakan, saat ini lembar jawaban Anda sedang dalam proses pemeriksaan oleh tim HR, jadi mohon tunggu hasilnya.";
        } else if (statusDb === 'Interview') {
            s1 = 'green';
            s2 = 'green';
            s3 = 'green';
            s4 = 'yellow';
            judulDetail = "DETAIL TAHAP: 4. INTERVIEW HR";
            teksDetail  = "<i>Interview HR</i> adalah sesi wawancara awal bersama tim Human Resources Kebab Monster, baik secara daring maupun tatap muka, untuk membahas latar belakang profesional, motivasi kerja, kecocokan budaya, serta ekspektasi gaji Anda.<br><br>Saat ini status Anda sedang menunggu penjadwalan wawancara dari tim kami. Undangan resmi yang memuat jadwal dan tautan pertemuan akan segera dikirimkan langsung melalui email Anda jika giliran Anda telah tiba.";
        } else if (statusDb === 'Hasil Akhir' || statusDb === 'Selesai') {
            s1 = 'green';
            s2 = 'green';
            s3 = 'green';
            s4 = 'green';
            s5 = 'green';
            judulDetail = "DETAIL TAHAP: 5. PENGUMUMAN AKHIR";
            teksDetail  = "Pengumuman Akhir merupakan babak penutup dari seluruh rangkaian proses seleksi, di mana tim manajemen dan HR telah merampungkan penilaian keseluruhan mulai dari seleksi berkas, psikotes, hingga hasil wawancara.<br><br>Saat ini hasil akhir evaluasi Anda sedang diproses oleh tim penilai. Pengumuman resmi akan segera dipublikasikan melalui sistem dan dikirimkan ke email Anda, lengkap beserta informasi penawaran kerja jika Anda dinyatakan lulus.";
        }

        setStepColor(1, s1);
        setStepColor(2, s2);
        setStepColor(3, s3);
        setStepColor(4, s4);
        setStepColor(5, s5);

        document.getElementById('detail-judul').textContent = judulDetail;
        document.getElementById('detail-teks').innerHTML   = teksDetail;
    }

    async function loadStatusLamaran() {
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
                alert(result.message || 'Gagal memuat status lamaran.');
                return;
            }

            const pelamar       = result.data?.pelamar || {};
            cachedIsProfilLengkap = Boolean(result.data?.kelengkapan?.is_lengkap);
            cachedLamaranList   = result.data?.lamaran_list || [];
            const selected      = result.data?.selected_lamaran || null;

            if (pelamar.pas_foto_url) {
                document.getElementById('status-avatar').innerHTML =
                    `<img src="${pelamar.pas_foto_url}" alt="Foto Profil">`;
            }
            document.getElementById('status-name').textContent  = pelamar.nama_lengkap || 'Nama Lengkap Pelamar';
            document.getElementById('status-email').textContent = pelamar.email || '-';
            document.getElementById('status-phone').textContent = pelamar.no_telepon || '-';

            // Jika melamar lebih dari 1 lowongan, tampilkan dropdown pemilih posisi
            const switcher = document.getElementById('lamaran-switcher');
            if (cachedLamaranList.length > 1) {
                switcher.innerHTML = cachedLamaranList.map(item => {
                    const tgl = formatTanggalIndo(item.tgl_melamar, '');
                    const isSel = selected && Number(item.id_lamaran) === Number(selected.id_lamaran) ? 'selected' : '';
                    return `<option value="${item.id_lamaran}" ${isSel}>${item.posisi_pekerjaan} (${tgl})</option>`;
                }).join('');
                switcher.style.display = 'block';
                switcher.onchange = function() {
                    const chosen = cachedLamaranList.find(x => Number(x.id_lamaran) === Number(this.value)) || null;
                    renderSelectedLamaran(chosen, cachedIsProfilLengkap);
                };
            }

            renderSelectedLamaran(selected, cachedIsProfilLengkap);
        } catch (err) {
            alert('Terjadi kesalahan saat memuat status lamaran dari server.');
        }
    }

    loadStatusLamaran();
</script>

<?php include '../includes/footer_pelamar.php'; ?>