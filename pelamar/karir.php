<?php
session_start();
include '../includes/header_pelamar.php';
?>

<style>
    .career-page-container {
        max-width: 1100px;
        margin: 0 auto;
        padding: 24px 18px 48px 18px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        overflow-x: clip;
    }
    .career-page-container *,
    .career-page-container *::before,
    .career-page-container *::after {
        box-sizing: border-box;
    }

    /* Bagian Header Teks */
    .career-hero {
        text-align: center;
        margin-bottom: 26px;
    }
    .career-hero h1 {
        font-size: clamp(24px, 5vw, 32px);
        font-weight: 800;
        color: #222;
        margin: 0 0 12px 0;
        line-height: 1.25;
    }
    .career-hero h1 span {
        color: #e65100;
    }
    .career-hero p {
        font-size: 14.5px;
        color: #555;
        max-width: 750px;
        margin: 0 auto 20px auto;
        line-height: 1.55;
    }

    /* Badge Info (Lokasi & Tanpa Biaya) */
    .badge-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .info-badge {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #c8e6c9;
        padding: 6px 15px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Kotak Pencarian */
    .search-wrapper {
        max-width: 750px;
        margin: 0 auto 30px auto;
    }
    .search-bar-form {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 2px solid #1b5e20;
        border-radius: 35px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        padding: 4px;
        gap: 6px;
    }
    .search-input {
        flex: 1;
        min-width: 0;
        min-height: 40px;
        border: none;
        padding: 10px 16px;
        font-size: 14px;
        outline: none;
        color: #333;
        background: transparent;
    }
    .search-btn {
        background-color: #1b5e20;
        color: white;
        border: none;
        min-height: 40px;
        padding: 10px 24px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .search-btn:hover { background-color: #00332c; }

    .search-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 10px;
        padding: 0 8px;
        font-size: 12.5px;
        color: #555;
    }
    .search-reset-link {
        color: #1b5e20;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }
    .search-reset-link:hover {
        text-decoration: underline;
    }

    /* Kartu Lowongan Pekerjaan */
    .job-card-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .job-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 14px;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .job-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    }
    .job-info {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
        flex: 1;
    }
    .job-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px 10px;
    }
    .job-title {
        font-size: 18px;
        font-weight: 800;
        color: #111;
        letter-spacing: 0.2px;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }
    .job-dept {
        font-size: 12px;
        font-weight: 700;
        color: #1b5e20;
        background: #f1f8e9;
        border: 1px solid #dcedc8;
        padding: 3px 10px;
        border-radius: 999px;
    }
    .job-company {
        font-size: 13px;
        font-weight: 700;
        color: #222;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }
    .job-location {
        font-size: 12.5px;
        color: #555;
        display: flex;
        align-items: flex-start;
        gap: 6px;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    /* Bagian Kanan Kartu */
    .job-card-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }
    .job-meta-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
    }
    .time-badge {
        background-color: #c8e6c9;
        color: #1b5e20;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .date-badge {
        background-color: #ffe0b2;
        color: #e65100;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }
    .btn-detail {
        background-color: #ffe082;
        color: #5d4037;
        border: none;
        min-height: 40px;
        padding: 9px 18px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-detail:hover {
        background-color: #ffd54f;
    }
    .no-data {
        text-align: center;
        padding: 32px 20px;
        color: #666;
        font-style: italic;
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
    }

    @media (max-width: 820px) {
        .job-card {
            flex-direction: column;
            align-items: stretch;
            padding: 18px;
            gap: 14px;
        }
        .job-card-right {
            align-items: center;
            width: 100%;
            justify-content: space-between;
            flex-direction: row;
            flex-wrap: wrap;
            padding-top: 12px;
            border-top: 1px solid #eef2ee;
            gap: 10px;
        }
        .job-meta-badges {
            justify-content: flex-start;
        }
    }

    @media (max-width: 560px) {
        .career-page-container {
            padding: 18px 14px 36px 14px;
        }
        .career-hero p {
            font-size: 13.5px;
        }
        .search-bar-form {
            border-radius: 16px;
            padding: 5px;
            gap: 6px;
        }
        .search-input {
            padding: 9px 12px;
            font-size: 13.5px;
        }
        .search-btn {
            padding: 9px 16px;
            border-radius: 12px;
            font-size: 13px;
        }
        .job-title {
            font-size: 16.5px;
        }
        .job-card-right {
            flex-direction: column;
            align-items: stretch;
        }
        .btn-detail {
            width: 100%;
            min-height: 44px;
        }
    }
</style>

<div class="career-page-container">

    <!-- HERO SECTION -->
    <div class="career-hero">
        <h1>Karier di <span>Kebab Monster</span></h1>
        <p>Di Kebab Monster, kami percaya setiap individu punya potensi luar biasa untuk berkembang bersama. Temukan posisi yang cocok dan raih karier cemerlang bersama kami.</p>
        
        <div class="badge-container">
            <div class="info-badge">📍 Bogor, Jawa Barat</div>
            <div class="info-badge">🛡️ Tanpa Biaya Rekrutmen</div>
        </div>
    </div>

    <!-- PENCARIAN -->
    <div class="search-wrapper">
        <form id="search-form" class="search-bar-form">
            <input type="text" id="search-input" name="search" class="search-input" placeholder="Cari posisi, divisi, atau lokasi...">
            <button type="submit" class="search-btn">Cari</button>
        </form>
        <div id="search-meta" class="search-meta" style="display: none;">
            <span>Hasil pencarian untuk: <strong id="search-keyword-label"></strong></span>
            <a href="karir.php" id="search-reset-btn" class="search-reset-link">Tampilkan semua lowongan</a>
        </div>
    </div>

    <!-- DAFTAR LOWONGAN (Dimuat via API ../api/lowongan/index.php) -->
    <div id="job-card-list" class="job-card-list">
        <div class="no-data">Memuat daftar lowongan pekerjaan...</div>
    </div>

</div>

<script>
    const bulanIndoSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTanggal(tglRaw) {
        if (!tglRaw) return '-';
        const parts = String(tglRaw).split('-');
        if (parts.length !== 3) return tglRaw;
        const tahun = parts[0];
        const bulanIdx = parseInt(parts[1], 10) - 1;
        const hari = parts[2].padStart(2, '0');
        const namaBulan = bulanIndoSingkat[bulanIdx] || parts[1];
        return `${hari}-${namaBulan}-${tahun}`;
    }

    async function loadLowongan(keyword = '') {
        const listEl = document.getElementById('job-card-list');
        const metaEl = document.getElementById('search-meta');
        const keywordLabel = document.getElementById('search-keyword-label');

        listEl.innerHTML = '<div class="no-data">Memuat daftar lowongan pekerjaan...</div>';

        const params = new URLSearchParams();
        if (keyword !== '') {
            params.set('q', keyword);
            keywordLabel.textContent = `"${keyword}"`;
            metaEl.style.display = 'flex';
        } else {
            metaEl.style.display = 'none';
        }

        const url = '../api/lowongan/index.php' + (params.toString() ? `?${params.toString()}` : '');

        try {
            const res = await fetch(url, { credentials: 'same-origin' });
            const result = await res.json();

            if (!res.ok || !result.success) {
                listEl.innerHTML = `<div class="no-data">${escapeHtml(result.message || 'Gagal memuat data lowongan.')}</div>`;
                return;
            }

            const items = result.data?.lowongan || [];
            if (items.length === 0) {
                listEl.innerHTML = '<div class="no-data">Belum ada lowongan pekerjaan yang tersedia atau kata kunci pencarian tidak ditemukan.</div>';
                return;
            }

            listEl.innerHTML = items.map(row => {
                const posisi = escapeHtml(row.posisi_pekerjaan || '');
                const divisi = row.divisi ? `<span class="job-dept">${escapeHtml(row.divisi)}</span>` : '';
                const lokasi = escapeHtml(row.lokasi_pekerjaan || '-');
                const shift = escapeHtml(row.shift || 'Penuh Waktu');
                const tgl = escapeHtml(formatTanggal(row.tgl_dibuat));
                const idLowongan = parseInt(row.id_lowongan, 10) || 0;

                return `
                    <div class="job-card">
                        <div class="job-info">
                            <div class="job-title-row">
                                <span class="job-title">${posisi}</span>
                                ${divisi}
                            </div>
                            <div class="job-company">PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster</div>
                            <div class="job-location">📍 <span>${lokasi}</span></div>
                        </div>
                        <div class="job-card-right">
                            <div class="job-meta-badges">
                                <span class="time-badge">${shift}</span>
                                <span class="date-badge">${tgl}</span>
                            </div>
                            <a href="detail_karir.php?id=${idLowongan}" class="btn-detail">Lihat Detail Lamaran ➔</a>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (err) {
            listEl.innerHTML = '<div class="no-data">Gagal menghubungi server API lowongan.</div>';
        }
    }

    document.getElementById('search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const kw = document.getElementById('search-input').value.trim();
        loadLowongan(kw);
    });

    document.getElementById('search-reset-btn').addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('search-input').value = '';
        loadLowongan('');
    });

    // Muat awal saat halaman dibuka (mendukung ?search=... jika ada di URL)
    const initialSearch = new URLSearchParams(window.location.search).get('search') || '';
    document.getElementById('search-input').value = initialSearch;
    loadLowongan(initialSearch.trim());
</script>

<?php include '../includes/footer_pelamar.php'; ?>