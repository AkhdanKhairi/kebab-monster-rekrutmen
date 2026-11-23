<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_modul = isset($_GET['id']) ? intval($_GET['id']) : 0;

include '../includes/header_pelamar.php';
?>

<style>
    .cara-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .cara-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .cara-title {
        font-size: 24px;
        font-weight: 800;
        color: #111;
        margin-bottom: 10px;
    }
    .modul-subtitle {
        font-size: 18px;
        font-weight: 700;
        color: #1b5e20;
        margin-bottom: 20px;
    }
    .instruksi-box {
        font-size: 14.5px;
        color: #444;
        line-height: 1.8;
        background: #f9fbe7;
        border: 1.5px solid #c5e1a5;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    .instruksi-content ol {
        padding-left: 20px;
        margin-top: 10px;
    }
    .instruksi-content li {
        margin-bottom: 8px;
    }
    .instruksi-box img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .btn-mulai-tes {
        background-color: #ffe082;
        color: #5d4037;
        border: 1.5px solid #fbc02d;
        padding: 12px 28px;
        border-radius: 30px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        transition: background 0.2s, transform 0.1s;
        align-self: flex-end;
    }
    .btn-mulai-tes:hover {
        background-color: #ffd54f;
        transform: translateY(-1px);
    }
</style>

<div class="cara-page-container">
    <div class="cara-main-content">
        <div>
            <div class="cara-title">Tata Cara Mengerjakan Ujian</div>
            <div class="modul-subtitle" id="modulSubtitle">Memuat modul...</div>

            <div class="instruksi-box" id="instruksiBox">
                <p>Memuat tata cara ujian...</p>
            </div>
        </div>

        <div id="tombolMulaiContainer"></div>
    </div>
</div>

<script>
    const idModul = <?php echo intval($id_modul); ?>;

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatJenisTes(jenis) {
        return String(jenis ?? '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, ch => ch.toUpperCase());
    }

    function renderTataCara(rawText) {
        const trimmed = String(rawText ?? '').trim();
        if (!trimmed) {
            return '<p>Belum ada tata cara khusus yang diatur oleh HR untuk modul ini.</p>';
        }
        const lines = trimmed.split(/\r?\n/);
        if (lines.length > 1) {
            const items = lines
                .map(line => line.trim())
                .filter(line => line !== '')
                .map(line => line.replace(/^\d+[\.\)]\s*/, ''))
                .map(clean => `<li>${escapeHtml(clean)}</li>`)
                .join('');
            return `<ol>${items}</ol>`;
        }
        return `<p>${escapeHtml(trimmed).replace(/\r?\n/g, '<br>')}</p>`;
    }

    async function loadCaraUjian() {
        const subtitleEl = document.getElementById('modulSubtitle');
        const boxEl = document.getElementById('instruksiBox');
        const btnWrap = document.getElementById('tombolMulaiContainer');

        if (idModul <= 0) {
            alert('Modul ujian tidak ditemukan!');
            window.location.href = 'ujian.php';
            return;
        }

        try {
            const res = await fetch(`../api/pelamar/ujian_list.php?id=${idModul}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success || !(result.data && result.data.modul)) {
                alert(result.message || 'Modul ujian tidak ditemukan!');
                window.location.href = 'ujian.php';
                return;
            }

            const modul = result.data.modul;
            const namaModul = modul.nama_modul || '';
            const durasi = parseInt(modul.durasi_menit || 0, 10);
            const jenisLabel = formatJenisTes(modul.jenis_modul || '');

            subtitleEl.textContent = namaModul;

            let gambarHtml = '';
            if (modul.gambar_petunjuk) {
                gambarHtml = `
                    <div style="margin-top: 20px; text-align: center;">
                        <img src="../assets/img/tatacara/${escapeHtml(modul.gambar_petunjuk)}" alt="Petunjuk Gambar Tata Cara">
                    </div>
                `;
            }

            boxEl.innerHTML = `
                <p style="margin-bottom: 12px; font-weight: 600; color: #1b5e20;">
                    ⏱️ Durasi Pengerjaan: ${durasi} Menit &nbsp;|&nbsp;
                    📋 Jenis Tes: ${escapeHtml(jenisLabel)}
                </p>
                <hr style="border: 0; border-top: 1px solid #c5e1a5; margin-bottom: 18px;">
                <div class="instruksi-content">
                    ${renderTataCara(modul.tata_cara)}
                </div>
                ${gambarHtml}
            `;

            btnWrap.innerHTML = `
                <a href="kerjakan_ujian.php?id=${parseInt(modul.id_modul, 10)}" class="btn-mulai-tes">
                    Mulai Tes ${escapeHtml(namaModul)} ➔
                </a>
            `;
        } catch (err) {
            boxEl.innerHTML = '<p>Terjadi kesalahan koneksi saat memuat tata cara ujian.</p>';
        }
    }

    loadCaraUjian();
</script>

<?php
include '../includes/footer_pelamar.php';
?>