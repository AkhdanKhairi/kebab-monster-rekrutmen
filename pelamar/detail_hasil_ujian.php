<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_modul = intval($_GET['id'] ?? ($_GET['id_modul'] ?? 0));

include '../includes/header_pelamar.php';
?>

<style>
    .hasil-container {
        max-width: 1050px;
        margin: 20px auto 50px auto;
        padding: 0 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .hasil-container *,
    .hasil-container *::before,
    .hasil-container *::after {
        box-sizing: border-box;
    }

    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .btn-back-ujian {
        background: #f1f5f9;
        color: #334155;
        text-decoration: none;
        padding: 9px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid #cbd5e1;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-back-ujian:hover { background: #e2e8f0; }

    .summary-hero {
        background: linear-gradient(135deg, #1b5e20, #2e7d32);
        color: #ffffff;
        border-radius: 16px;
        padding: 24px 28px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        box-shadow: 0 5px 15px rgba(27, 94, 32, 0.2);
    }
    .summary-hero.hero-merah {
        background: linear-gradient(135deg, #991b1b, #dc2626);
        box-shadow: 0 5px 15px rgba(185, 28, 28, 0.25);
    }
    .hero-left-info {
        flex: 1;
        min-width: 0;
    }
    .kandidat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        opacity: 0.88;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .kandidat-nama-besar {
        margin: 0 0 8px 0;
        font-size: 25px;
        font-weight: 800;
        color: #ffffff;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }
    .kandidat-meta-info {
        font-size: 13.5px;
        opacity: 0.95;
        display: flex;
        flex-wrap: wrap;
        gap: 8px 12px;
        align-items: center;
        overflow-wrap: anywhere;
    }
    .badge-posisi-hero {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
    }
    .catatan-hrd-box {
        margin-top: 12px;
        font-size: 13.5px;
        line-height: 1.5;
        background: rgba(0, 0, 0, 0.2);
        padding: 10px 14px;
        border-radius: 10px;
        overflow-wrap: anywhere;
    }
    .score-pill-box {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.35);
        padding: 14px 24px;
        border-radius: 14px;
        text-align: center;
        min-width: 170px;
        flex-shrink: 0;
    }
    .score-pill-box span {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        opacity: 0.92;
        font-weight: 700;
    }
    .score-pill-box strong {
        font-size: 28px;
        font-weight: 900;
    }

    .pending-notice {
        background: #fffbeb;
        border: 1.5px solid #fcd34d;
        color: #92400e;
        padding: 16px 22px;
        border-radius: 14px;
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.6;
    }

    .rincian-card-wrapper {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 24px 26px;
    }
    .rincian-title {
        margin: 0 0 18px 0;
        font-size: 18px;
        font-weight: 800;
        color: #111;
        text-align: center;
        line-height: 1.35;
    }

    .modul-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .modul-row-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 12px;
        padding: 18px 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
    }
    .modul-row-card.selected {
        background: #f0fdf4;
        border-width: 2px;
    }
    .modul-row-info {
        flex: 1;
        min-width: 0;
    }
    .modul-row-title {
        font-size: 15.5px;
        font-weight: 800;
        color: #111;
        margin-bottom: 4px;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    .modul-row-sub {
        font-size: 13px;
        color: #475569;
        line-height: 1.45;
    }
    .modul-row-action {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        flex-shrink: 0;
    }

    .badge-status-ok { background: #dcfce7; color: #166534; padding: 6px 13px; border-radius: 20px; font-size: 12px; font-weight: 800; }
    .badge-status-wait { background: #fef3c7; color: #92400e; padding: 6px 13px; border-radius: 20px; font-size: 12px; font-weight: 800; }
    .badge-status-no { background: #f1f5f9; color: #64748b; padding: 6px 13px; border-radius: 20px; font-size: 12px; font-weight: 700; }
    .link-kerjakan {
        font-size: 12.5px;
        font-weight: 700;
        color: #1b5e20;
        background: #e8f5e9;
        border: 1px solid #c8e6c9;
        padding: 6px 14px;
        border-radius: 20px;
        text-decoration: none;
        white-space: nowrap;
    }
    .link-kerjakan:hover {
        background: #c8e6c9;
    }

    @media (max-width: 768px) {
        .hasil-container {
            margin: 12px auto 36px auto;
            padding: 0 14px;
        }
        .btn-back-ujian {
            width: 100%;
            justify-content: center;
            min-height: 42px;
        }
        .summary-hero {
            flex-direction: column;
            align-items: stretch;
            padding: 20px 18px;
            gap: 14px;
        }
        .kandidat-nama-besar {
            font-size: 20px;
        }
        .kandidat-meta-info {
            font-size: 12.5px;
        }
        .score-pill-box {
            width: 100%;
            min-width: 0;
            padding: 12px 16px;
        }
        .score-pill-box strong {
            font-size: 24px;
        }
        .pending-notice {
            padding: 14px 16px;
            font-size: 13px;
        }
        .rincian-card-wrapper {
            padding: 18px 14px;
        }
        .rincian-title {
            font-size: 16px;
            margin-bottom: 14px;
        }
        .modul-row-card {
            flex-direction: column;
            align-items: stretch;
            padding: 16px 14px;
            gap: 12px;
        }
        .modul-row-title {
            font-size: 14.5px;
        }
        .modul-row-sub {
            font-size: 12.5px;
        }
        .modul-row-action {
            width: 100%;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px dashed #e2e8f0;
        }
        .badge-status-ok,
        .badge-status-wait {
            width: 100%;
            text-align: center;
            padding: 8px 12px;
        }
    }
</style>

<div class="hasil-container">
    <div class="top-bar">
        <a href="ujian.php" class="btn-back-ujian">← Kembali ke Daftar Modul Ujian</a>
    </div>

    <div id="detailHasilContent">
        <div class="rincian-card-wrapper" style="text-align:center; color:#64748b;">
            Memuat rincian hasil ujian...
        </div>
    </div>
</div>

<script>
    const idModulParam = <?php echo intval($id_modul); ?>;

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    async function loadDetailHasil() {
        const wrap = document.getElementById('detailHasilContent');
        const query = idModulParam > 0 ? `?id=${idModulParam}` : '';

        try {
            const res = await fetch(`../api/pelamar/ujian_hasil.php${query}`, {
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
                wrap.innerHTML = `<div class="rincian-card-wrapper" style="text-align:center; color:#c62828;">${escapeHtml(result.message || 'Gagal memuat hasil ujian.')}</div>`;
                return;
            }

            const data = result.data;
            const profile = data.pelamar || {};
            const posisiDilamar = data.posisi_dilamar || '';
            const hasilPsikotes = data.hasil_psikotes || null;
            const isPublished = parseInt(data.is_published || 0, 10) === 1;
            const isTidakLulus = Boolean(data.is_tidak_lulus);
            const daftarModul = Array.isArray(data.daftar_modul) ? data.daftar_modul : [];
            const selectedId = parseInt(data.selected_id_modul || 0, 10);

            const posisiBadge = posisiDilamar
                ? `<span class="badge-posisi-hero">💼 Posisi: ${escapeHtml(posisiDilamar)}</span>`
                : '';

            const catatanHtml = (isPublished && hasilPsikotes && hasilPsikotes.catatan_hrd)
                ? `<div class="catatan-hrd-box">💬 <strong>Catatan HRD:</strong> ${escapeHtml(hasilPsikotes.catatan_hrd)}</div>`
                : '';

            const scoreBoxHtml = (isPublished && hasilPsikotes)
                ? `<span>Total Skor Akhir (${escapeHtml(hasilPsikotes.status_lulus || 'Selesai')})</span><strong>${Number(hasilPsikotes.skor_akhir || 0).toFixed(1)}</strong>`
                : `<span>Status Penilaian</span><strong style="font-size: 17px; display:block; margin-top:4px;">⏳ Diproses HRD</strong>`;

            const pendingHtml = !isPublished
                ? `<div class="pending-notice"><strong>⏳ Hasil Penilaian Resmi Sedang Diproses Tim HRD</strong><br>Jawaban ujian yang sudah Anda kirimkan telah tersimpan di sistem. Nilai akhir akan tampil secara resmi di kotak banner di atas setelah tim HRD selesai melakukan verifikasi dan mempublikasikan hasil tes.</div>`
                : '';

            let modulListHtml = '';
            if (daftarModul.length > 0) {
                const cards = daftarModul.map((m, idx) => {
                    const no = parseInt(m.nomor_urut || (idx + 1), 10);
                    const isSelected = parseInt(m.id_modul, 10) === selectedId;
                    const selesaiWaktu = (m.waktu_selesai && m.waktu_selesai !== '-')
                        ? `<br>Selesai pada: ${escapeHtml(m.waktu_selesai)}`
                        : '';

                    let badgeHtml = '';
                    if (m.status_kerja === 'Selesai') {
                        badgeHtml = isPublished
                            ? `<span class="badge-status-ok">Nilai Modul: ${escapeHtml(m.skor_modul)} / 100</span>`
                            : `<span class="badge-status-wait">✅ Terkirim (Menunggu Hasil HRD)</span>`;
                    } else {
                        badgeHtml = `<span class="badge-status-no">Belum Dikerjakan</span><a href="ujian.php" class="link-kerjakan">Kerjakan ➔</a>`;
                    }

                    return `
                        <div class="modul-row-card ${isSelected ? 'selected' : ''}">
                            <div class="modul-row-info">
                                <div class="modul-row-title">Psikotes Module ${no}: ${escapeHtml(m.nama_modul_tampil)}</div>
                                <div class="modul-row-sub">Status: <strong>${escapeHtml(m.status_kerja)}</strong>${selesaiWaktu}</div>
                            </div>
                            <div class="modul-row-action">${badgeHtml}</div>
                        </div>
                    `;
                }).join('');
                modulListHtml = `<div class="modul-grid">${cards}</div>`;
            } else {
                modulListHtml = `<div style="text-align:center; padding:30px 14px; color:#64748b;">Belum ada modul psikotes yang sesuai dengan kategori posisi Anda saat ini.</div>`;
            }

            wrap.innerHTML = `
                <div class="summary-hero ${isTidakLulus ? 'hero-merah' : ''}">
                    <div class="hero-left-info">
                        <div class="kandidat-label">Kandidat Peserta Psikotes</div>
                        <h1 class="kandidat-nama-besar">${escapeHtml(profile.nama_lengkap || '-')}</h1>
                        <div class="kandidat-meta-info">
                            <span>✉️ ${escapeHtml(profile.email || '-')}</span>
                            ${posisiBadge}
                        </div>
                        ${catatanHtml}
                    </div>
                    <div class="score-pill-box">${scoreBoxHtml}</div>
                </div>
                ${pendingHtml}
                <div class="rincian-card-wrapper">
                    <h2 class="rincian-title">RINCIAN STATUS & HASIL PER MODUL PSIKOTES</h2>
                    ${modulListHtml}
                </div>
            `;
        } catch (err) {
            wrap.innerHTML = `<div class="rincian-card-wrapper" style="text-align:center; color:#c62828;">Terjadi kesalahan koneksi saat memuat rincian hasil ujian.</div>`;
        }
    }

    loadDetailHasil();
</script>

<?php
include '../includes/footer_pelamar.php';
?>