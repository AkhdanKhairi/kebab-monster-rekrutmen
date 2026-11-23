<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

$id_lowongan = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_lowongan <= 0) {
    echo "<script>alert('Lowongan tidak ditemukan!'); window.location='karir.php';</script>";
    exit();
}

include __DIR__ . '/../includes/header_pelamar.php';
?>

<style>
    .detail-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background-color: #f1f8e9;
        color: #1b5e20;
        border: 1px solid #c8e6c9;
        min-height: 38px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        margin-top: 14px;
        transition: background 0.2s, transform 0.1s;
    }
    .btn-back:hover {
        background-color: #dcedc8;
        transform: translateX(-2px);
    }

    .alert-nonaktif {
        background-color: #f1f5f9;
        border: 1.5px solid #cbd5e1;
        color: #475569;
        border-radius: 12px;
        padding: 15px 18px;
        margin-bottom: 20px;
        font-size: 13.5px;
        line-height: 1.6;
    }
    .alert-nonaktif strong {
        color: #1e293b;
    }

    .alert-belum-lengkap {
        background-color: #fff8e1;
        border: 1.5px solid #ffb300;
        color: #6d4c00;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 22px;
        font-size: 13.5px;
        line-height: 1.6;
    }
    .alert-belum-lengkap strong { color: #4e342e; }
    .btn-lengkapi-profil {
        display: inline-block;
        margin-top: 10px;
        background-color: #1b5e20;
        color: #ffffff;
        text-decoration: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
    }
    .btn-lengkapi-profil:hover { background-color: #2e7d32; }

    .job-header-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 25px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
        margin-bottom: 25px;
    }
    .job-header-card.is-nonaktif {
        border-color: #cbd5e1;
        background: #fafafa;
    }
    .job-header-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    .job-title-row {
        display: flex;
        align-items: baseline;
        gap: 12px;
        flex-wrap: wrap;
    }
    .job-title {
        font-size: 22px;
        font-weight: 800;
        color: #111;
    }
    .job-dept {
        font-size: 14px;
        font-weight: 600;
        color: #666;
    }
    .job-company {
        font-size: 14px;
        font-weight: 700;
        color: #222;
    }
    .job-location {
        font-size: 13px;
        color: #555;
        word-break: break-word;
    }

    .job-header-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }
    .job-meta-badges {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    .time-badge {
        background-color: #c8e6c9;
        color: #1b5e20;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .date-badge {
        background-color: #ffe0b2;
        color: #e65100;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .status-badge-nonaktif {
        background-color: #e2e8f0;
        color: #475569;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .btn-apply {
        background-color: #ffe082;
        color: #5d4037;
        border: none;
        min-height: 40px;
        padding: 10px 22px;
        border-radius: 25px;
        font-size: 13.5px;
        font-weight: 800;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .btn-apply:hover {
        background-color: #ffd54f;
        transform: translateY(-1px);
    }
    .btn-apply:disabled {
        opacity: 0.7;
        cursor: wait;
    }

    .btn-apply-disabled {
        background-color: #e2e8f0;
        color: #64748b;
        border: 1px solid #cbd5e1;
        min-height: 40px;
        padding: 10px 22px;
        border-radius: 25px;
        font-size: 13.5px;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: not-allowed;
        pointer-events: none;
        user-select: none;
        box-shadow: none;
    }

    .detail-grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        align-items: start;
    }

    .card-box {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 25px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.04);
        min-width: 0;
    }

    .section-title {
        font-size: 16px;
        font-weight: 800;
        color: #111;
        margin-bottom: 12px;
        margin-top: 18px;
    }
    .section-title:first-child {
        margin-top: 0;
    }

    .content-text {
        font-size: 14px;
        color: #444;
        line-height: 1.65;
        margin-bottom: 15px;
        word-break: break-word;
    }

    .benefit-title {
        font-size: 18px;
        font-weight: 800;
        color: #1b5e20;
        margin-bottom: 15px;
        text-align: center;
        border-bottom: 2px solid #e8f5e9;
        padding-bottom: 10px;
    }

    @media (max-width: 900px) {
        .job-header-card {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        .job-header-right {
            align-items: flex-start;
            width: 100%;
            justify-content: space-between;
            flex-direction: row;
            flex-wrap: wrap;
        }
        .detail-grid-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .detail-container {
            padding: 12px;
        }
        .job-header-card {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 16px;
        }
        .job-title {
            font-size: 18px;
        }
        .job-header-right {
            flex-direction: column;
            align-items: stretch;
            gap: 10px;
        }
        .btn-apply,
        .btn-apply-disabled {
            width: 100%;
            min-height: 44px;
            font-size: 13.5px;
        }
        .card-box {
            padding: 16px;
            border-radius: 12px;
        }
        .btn-back {
            width: 100%;
            min-height: 42px;
        }
    }
</style>

<div class="detail-container">

    <div id="alert-nonaktif" class="alert-nonaktif" style="display: none;">
        <strong>Lowongan Sedang Nonaktif / Ditutup:</strong><br>
        Pendaftaran untuk lowongan ini saat ini sedang ditutup oleh HR/Admin, sehingga lamaran tidak dapat dikirim. Anda tetap dapat melihat detail informasi lowongan di bawah ini.
    </div>

    <div id="alert-belum-lengkap" class="alert-belum-lengkap" style="display: none;">
        <strong>Profil & Berkas Wajib Belum Lengkap:</strong><br>
        Lengkapi bagian berikut terlebih dahulu sebelum mengirim lamaran:
        <strong id="missing-fields-text">-</strong>.<br>
        <a href="profile_edit.php" class="btn-lengkapi-profil">Lengkapi Profil Sekarang ➔</a>
    </div>

    <!-- HEADER KARTU LOWONGAN -->
    <div id="job-header-card" class="job-header-card">
        <div class="job-header-info">
            <div class="job-title-row">
                <span id="job-title" class="job-title">Memuat detail lowongan...</span>
                <span id="job-dept" class="job-dept"></span>
            </div>
            <div class="job-company">PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster</div>
            <div id="job-location" class="job-location">📍 -</div>
        </div>
        <div class="job-header-right">
            <div id="job-meta-badges" class="job-meta-badges">
                <span id="job-shift" class="time-badge">Penuh Waktu</span>
                <span id="job-date" class="date-badge">-</span>
            </div>
            <div id="job-apply-action"></div>
        </div>
    </div>

    <!-- KONTEN UTAMA (GRID 2 KOLOM) -->
    <div class="detail-grid-layout">

        <!-- KOLOM KIRI: Deskripsi, Tugas, Kualifikasi -->
        <div class="card-box">
            <div class="section-title">Deskripsi pekerjaan</div>
            <div id="job-deskripsi" class="content-text">Memuat...</div>

            <div class="section-title">Tugas & Tanggung Jawab</div>
            <div id="job-tugas" class="content-text">Memuat...</div>

            <div class="section-title">Kualifikasi</div>
            <div id="job-kualifikasi" class="content-text">Memuat...</div>
        </div>

        <!-- KOLOM KANAN: Benefit -->
        <div class="card-box">
            <div class="benefit-title">Benefit</div>
            <div id="job-benefit" class="content-text">Memuat...</div>
            <a href="karir.php" class="btn-back">
                ← Kembali ke Daftar Karir
            </a>
        </div>

    </div>

</div>

<script>
    const ID_LOWONGAN = <?php echo json_encode($id_lowongan); ?>;
    const bulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

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

    function formatTanggal(tglRaw) {
        if (!tglRaw) return '-';
        const parts = String(tglRaw).split('-');
        if (parts.length !== 3) return tglRaw;
        const tahun = parts[0];
        const bulanIdx = parseInt(parts[1], 10) - 1;
        const hari = parts[2].padStart(2, '0');
        const namaBulan = bulanSingkat[bulanIdx] || parts[1];
        return `${hari}-${namaBulan}-${tahun}`;
    }

    async function kirimLamaranApi(btn) {
        if (!confirm('Yakin ingin mengirim lamaran untuk posisi ini?')) {
            return;
        }

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = 'Mengirim lamaran...';

        try {
            const res = await fetch('../api/pelamar/lamar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ id_lowongan: ID_LOWONGAN })
            });
            const result = await res.json();

            if (!res.ok || !result.success) {
                alert(result.message || 'Gagal mengirim lamaran.');
                btn.disabled = false;
                btn.innerHTML = originalText;
                return;
            }

            alert(result.message || 'Lamaran berhasil dikirim!');
            window.location.href = 'karir.php';
        } catch (err) {
            alert('Terjadi kesalahan koneksi saat mengirim lamaran.');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    async function loadDetailLowongan() {
        try {
            const [resDetail, resLamaran] = await Promise.all([
                fetch(`../api/lowongan/detail.php?id=${encodeURIComponent(ID_LOWONGAN)}`, {
                    credentials: 'same-origin'
                }),
                fetch(`../api/pelamar/lamaran.php?id_lowongan=${encodeURIComponent(ID_LOWONGAN)}`, {
                    credentials: 'same-origin'
                })
            ]);

            const resultDetail  = await resDetail.json();
            const resultLamaran = await resLamaran.json();

            if (!resDetail.ok || !resultDetail.success || !resultDetail.data?.lowongan) {
                alert(resultDetail.message || 'Lowongan tidak ditemukan!');
                window.location.href = 'karir.php';
                return;
            }

            const job             = resultDetail.data.lowongan;
            const isAktif         = Boolean(resultDetail.data.is_aktif);
            const isLengkap       = Boolean(resultLamaran.data?.kelengkapan?.is_lengkap);
            const missingFields   = resultLamaran.data?.kelengkapan?.missing_fields || [];
            const existingForJob  = resultLamaran.data?.existing_for_job || null;

            document.getElementById('job-title').textContent = job.posisi_pekerjaan || '-';
            document.getElementById('job-dept').textContent = job.divisi || '-';
            document.getElementById('job-location').textContent = '📍 ' + (job.lokasi_pekerjaan || '-');
            document.getElementById('job-shift').textContent = job.shift || 'Penuh Waktu';
            document.getElementById('job-date').textContent = formatTanggal(job.tgl_dibuat);

            document.getElementById('job-deskripsi').innerHTML = nl2brSafe(
                job.deskripsi_pekerjaan || 'Kesempatan kerja terbuka untuk kamu yang siap berkembang di dunia F&B bareng Kebab Monster!'
            );
            document.getElementById('job-tugas').innerHTML = nl2brSafe(job.tugas_tanggung_jawab || '-');
            document.getElementById('job-kualifikasi').innerHTML = nl2brSafe(job.kualifikasi || '-');
            document.getElementById('job-benefit').innerHTML = nl2brSafe(job.benefit || '-');

            const headerCard = document.getElementById('job-header-card');
            const badgesWrap = document.getElementById('job-meta-badges');
            const actionWrap = document.getElementById('job-apply-action');
            const alertNonaktif = document.getElementById('alert-nonaktif');
            const alertBelumLengkap = document.getElementById('alert-belum-lengkap');

            if (!isAktif) {
                headerCard.classList.add('is-nonaktif');
                alertNonaktif.style.display = 'block';
                badgesWrap.insertAdjacentHTML('beforeend', '<span class="status-badge-nonaktif">🔒 Nonaktif</span>');
                actionWrap.innerHTML = `
                    <button type="button" class="btn-apply-disabled" disabled aria-disabled="true">
                        Lamaran Ditutup (Nonaktif)
                    </button>
                `;
            } else if (existingForJob) {
                actionWrap.innerHTML = `
                    <a href="status_lamaran.php?id_lamaran=${parseInt(existingForJob.id_lamaran, 10)}" class="btn-apply" style="background-color:#c8e6c9; color:#1b5e20;">
                        Sudah Melamar (Lihat Status) ➔
                    </a>
                `;
            } else if (!isLengkap) {
                document.getElementById('missing-fields-text').textContent = missingFields.join(', ');
                alertBelumLengkap.style.display = 'block';
                actionWrap.innerHTML = `
                    <a href="profile_edit.php" class="btn-apply" onclick="alert('Data wajib Anda belum lengkap. Anda akan diarahkan ke halaman Update Profile.');">
                        Lengkapi Data Dulu ➔
                    </a>
                `;
            } else {
                actionWrap.innerHTML = `
                    <button type="button" id="btnKirimLamaran" class="btn-apply">
                        Kirim Lamar Sekarang ➔
                    </button>
                `;
                document.getElementById('btnKirimLamaran').addEventListener('click', function() {
                    kirimLamaranApi(this);
                });
            }
        } catch (err) {
            alert('Gagal memuat detail lowongan dari server.');
        }
    }

    loadDetailLowongan();
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_pelamar.php')) {
    include __DIR__ . '/../includes/footer_pelamar.php';
} else {
    echo "</body></html>";
}
?>