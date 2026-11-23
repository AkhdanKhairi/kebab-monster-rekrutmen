<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';
?>

<style>
    .dashboard-wrapper {
        display: flex;
        flex-direction: column;
        gap: 25px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 15px;
    }
    .summary-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        border-radius: 12px;
        transition: transform 0.16s ease, box-shadow 0.16s ease, background-color 0.16s ease;
    }
    .summary-card-link:hover {
        transform: translateY(-3px);
    }
    .summary-card-link:hover .summary-card {
        background: #f4fbf5;
        box-shadow: 0 8px 16px rgba(27, 94, 32, 0.12);
    }
    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        border: 2px solid #1b5e20;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        height: 100%;
        box-sizing: border-box;
        transition: background-color 0.16s ease, box-shadow 0.16s ease;
    }
    .summary-card .card-label {
        font-size: 12px;
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    .summary-card .card-value {
        font-size: 30px;
        font-weight: 900;
        color: #1b5e20;
        margin-bottom: 4px;
    }
    .summary-card .card-desc {
        font-size: 11.5px;
        color: #666;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 6px;
    }
    .summary-card .card-arrow {
        color: #1b5e20;
        font-weight: 800;
        font-size: 13px;
    }

    .lower-split-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 960px) {
        .lower-split-grid {
            grid-template-columns: 1fr;
        }
    }

    .section-card-box {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        border: 1px solid #e0e0e0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        min-width: 0;
    }
    .section-title {
        font-size: 17px;
        font-weight: 800;
        color: #004d40;
        margin-bottom: 18px;
        letter-spacing: 0.2px;
    }
    .job-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid #eee;
        font-size: 14.5px;
        font-weight: 600;
        color: #333;
    }
    .job-list-item:last-child {
        border-bottom: none;
    }
    .job-pendaftar {
        font-size: 13px;
        color: #1b5e20;
        font-weight: 700;
        background: #e8f5e9;
        padding: 5px 12px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .chart-canvas-wrap {
        position: relative;
        height: 290px;
        width: 100%;
    }

    .css-bar-fallback {
        display: none;
        flex-direction: column;
        gap: 12px;
    }
    .css-bar-row {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .css-bar-meta {
        display: flex;
        justify-content: space-between;
        font-size: 12.5px;
        font-weight: 700;
        color: #222;
    }
    .css-bar-track {
        width: 100%;
        height: 18px;
        background: #eef3ee;
        border-radius: 999px;
        overflow: hidden;
    }
    .css-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #1b5e20, #2e7d32);
        border-radius: 999px;
        transition: width 0.35s ease;
    }
</style>

<div class="dashboard-wrapper">

    <!-- RINGKASAN OPERASIONAL HARIAN -->
    <div class="summary-grid">
        <a href="daftar_pelamar.php" class="summary-card-link" title="Buka Daftar Pelamar">
            <div class="summary-card">
                <div class="card-label">Berkas Masuk (Screening)</div>
                <div class="card-value" id="jmlScreening">0</div>
                <div class="card-desc">
                    <span>Perlu Screening Berkas</span>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>

        <a href="jadwal_seleksi.php" class="summary-card-link" title="Buka Jadwal Psikotes">
            <div class="summary-card">
                <div class="card-label">Jadwal Psikotes (Antre)</div>
                <div class="card-value" id="jmlPsikotes">0</div>
                <div class="card-desc">
                    <span>Antre Penjadwalan</span>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>

        <a href="koreksi_hasil.php" class="summary-card-link" title="Buka Koreksi Hasil Psikotes">
            <div class="summary-card">
                <div class="card-label">Hasil Psikotes (Dinilai)</div>
                <div class="card-value" id="jmlHasilPsikotes">0</div>
                <div class="card-desc">
                    <span>Perlu Screening Berkas</span>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>

        <a href="jadwal_seleksi.php" class="summary-card-link" title="Buka Jadwal Interview">
            <div class="summary-card">
                <div class="card-label">Jadwal Interview (Antre)</div>
                <div class="card-value" id="jmlInterview">0</div>
                <div class="card-desc">
                    <span>Siap Dijadwalkan</span>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>

        <a href="hasil_akhir.php" class="summary-card-link" title="Buka Hasil Akhir / Keputusan">
            <div class="summary-card">
                <div class="card-label">Perlu Keputusan</div>
                <div class="card-value" id="jmlKeputusan">0</div>
                <div class="card-desc">
                    <span>Keputusan Lamaran</span>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>
    </div>

    <!-- LOWONGAN AKTIF & GRAFIK BATANG PENDAFTAR -->
    <div class="lower-split-grid">
        <div class="section-card-box">
            <div class="section-title">LOWONGAN AKTIF & STATISTIK PENDAFTAR</div>
            <div id="lowonganListContainer">
                <div style="text-align: center; padding: 20px; color: #777;">Memuat data lowongan...</div>
            </div>
        </div>

        <div class="section-card-box">
            <div class="section-title">GRAFIK BATANG PENDAFTAR PER LOWONGAN</div>
            <div id="chartAreaContainer">
                <div style="text-align: center; padding: 40px 20px; color: #777;">Memuat grafik statistik...</div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function() {
    let chartInstance = null;

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderLowongan(list) {
        const container = document.getElementById('lowonganListContainer');
        if (!container) return;

        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = `<div style="text-align: center; padding: 20px; color: #777;">Belum ada data lowongan di database Anda.</div>`;
            return;
        }

        container.innerHTML = list.map(job => `
            <div class="job-list-item">
                <span>${escapeHtml(job.nama)}</span>
                <span class="job-pendaftar">${parseInt(job.total || 0, 10)} Pendaftar</span>
            </div>
        `).join('');
    }

    function renderChart(list, labels, values, maxPendaftar) {
        const container = document.getElementById('chartAreaContainer');
        if (!container) return;

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (!Array.isArray(list) || list.length === 0) {
            container.innerHTML = `<div style="text-align: center; padding: 40px 20px; color: #777;">Belum ada data statistik untuk ditampilkan dalam grafik.</div>`;
            return;
        }

        const fallbackRows = list.map(job => {
            const total = parseInt(job.total || 0, 10);
            const pct = maxPendaftar > 0 ? Math.round((total / maxPendaftar) * 100) : 0;
            return `
                <div class="css-bar-row">
                    <div class="css-bar-meta">
                        <span>${escapeHtml(job.nama)}</span>
                        <span>${total}</span>
                    </div>
                    <div class="css-bar-track">
                        <div class="css-bar-fill" style="width: ${pct}%;"></div>
                    </div>
                </div>
            `;
        }).join('');

        container.innerHTML = `
            <div class="chart-canvas-wrap" id="chartCanvasWrap">
                <canvas id="grafikPendaftarLowongan"></canvas>
            </div>
            <div class="css-bar-fallback" id="cssBarFallback">${fallbackRows}</div>
        `;

        const canvas = document.getElementById('grafikPendaftarLowongan');
        const wrap = document.getElementById('chartCanvasWrap');
        const fallback = document.getElementById('cssBarFallback');

        if (typeof Chart === 'undefined' || !canvas) {
            if (wrap) wrap.style.display = 'none';
            if (fallback) fallback.style.display = 'flex';
            return;
        }

        const palette = [
            'rgba(27, 94, 32, 0.85)',
            'rgba(46, 125, 50, 0.85)',
            'rgba(0, 77, 64, 0.85)',
            'rgba(56, 142, 60, 0.85)',
            'rgba(104, 159, 56, 0.85)'
        ];
        const borderPalette = [
            '#1b5e20',
            '#2e7d32',
            '#004d40',
            '#388e3c',
            '#689f38'
        ];

        chartInstance = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: values,
                    backgroundColor: labels.map((_, idx) => palette[idx % palette.length]),
                    borderColor: labels.map((_, idx) => borderPalette[idx % borderPalette.length]),
                    borderWidth: 1.5,
                    borderRadius: 6,
                    maxBarThickness: 48
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        ticks: { color: '#222', font: { size: 11.5, weight: '600' } },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0, color: '#444', font: { size: 11.5 } },
                        grid: { color: '#eef2ee' }
                    }
                }
            }
        });
    }

    async function loadDashboard() {
        try {
            const res = await fetch('../api/admin/dashboard.php', {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu sebagai HR/Admin!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Gagal memuat dashboard');
            }

            const r = result.data.ringkasan || {};
            document.getElementById('jmlScreening').textContent = parseInt(r.jml_screening || 0, 10);
            document.getElementById('jmlPsikotes').textContent = parseInt(r.jml_psikotes || 0, 10);
            document.getElementById('jmlHasilPsikotes').textContent = parseInt(r.jml_hasil_psikotes || 0, 10);
            document.getElementById('jmlInterview').textContent = parseInt(r.jml_interview || 0, 10);
            document.getElementById('jmlKeputusan').textContent = parseInt(r.jml_keputusan || 0, 10);

            const list = result.data.lowongan_list || [];
            const labels = result.data.chart_labels || [];
            const values = result.data.chart_values || [];
            const maxVal = parseInt(result.data.max_pendaftar || 0, 10);

            renderLowongan(list);
            renderChart(list, labels, values, maxVal);
        } catch (err) {
            const lowEl = document.getElementById('lowonganListContainer');
            const chEl = document.getElementById('chartAreaContainer');
            if (lowEl) lowEl.innerHTML = `<div style="text-align: center; padding: 20px; color: #c62828;">Gagal memuat data dashboard.</div>`;
            if (chEl) chEl.innerHTML = `<div style="text-align: center; padding: 20px; color: #c62828;">Gagal memuat grafik.</div>`;
        }
    }

    loadDashboard();
})();
</script>

        </main>
    </div>
</body>
</html>

<?php
include '../includes/footer_admin.php';
?>