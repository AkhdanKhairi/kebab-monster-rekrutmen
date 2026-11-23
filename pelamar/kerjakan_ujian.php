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
    .ujian-page {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        display: flex;
        gap: 25px;
        font-family: 'Segoe UI', sans-serif;
    }
    .ujian-page *,
    .ujian-page *::before,
    .ujian-page *::after {
        box-sizing: border-box;
    }
    .ujian-sidebar {
        width: 280px;
        flex-shrink: 0;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        height: fit-content;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
    }
    .timer-circle {
        width: 110px;
        height: 110px;
        border: 4px solid #1b5e20;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px auto;
        font-size: 20px;
        font-weight: 800;
        color: #111;
        background: #f1f8e9;
    }
    .pelamar-info {
        font-size: 13px;
        color: #555;
        margin-bottom: 18px;
        border-bottom: 1px solid #eee;
        padding-bottom: 14px;
        overflow-wrap: anywhere;
    }
    .grid-nomor {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 6px;
    }
    .btn-nomor {
        aspect-ratio: 1;
        min-height: 36px;
        background: #e0e0e0;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
    }
    .btn-nomor.active { background: #c8e6c9; border-color: #1b5e20; color: #1b5e20; }
    .btn-nomor.answered { background: #2e7d32; color: #fff; border-color: #1b5e20; }

    .ujian-content {
        flex: 1;
        min-width: 0;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .progress-container { margin-bottom: 24px; }
    .progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 6px;
    }
    .progress-text { font-size: 13px; font-weight: 700; color: #333; }
    .autosave-status { font-size: 12px; font-weight: 600; color: #2e7d32; }
    .warning-banner {
        display: none;
        background: #ffebee;
        border: 1px solid #ef5350;
        color: #c62828;
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        margin-bottom: 14px;
    }
    .progress-bar-bg { width: 100%; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background: #2e7d32; width: 0%; transition: width 0.3s; }

    .soal-card-layout {
        display: flex;
        gap: 20px;
        margin-bottom: 24px;
        align-items: flex-start;
    }
    .soal-image-box {
        width: 45%;
        flex-shrink: 0;
        background: #e0e0e0;
        border-radius: 10px;
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .soal-image-box img { width: 100%; height: auto; object-fit: cover; display: block; }
    .soal-text-box {
        flex: 1;
        min-width: 0;
        font-size: 14.5px;
        color: #333;
        line-height: 1.65;
        overflow-wrap: anywhere;
    }

    .options-list label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        line-height: 1.45;
        margin-bottom: 12px;
        cursor: pointer;
        overflow-wrap: anywhere;
    }
    .options-list input[type="radio"] {
        margin-top: 3px;
        flex-shrink: 0;
    }
    .textarea-essay {
        width: 100%;
        min-height: 110px;
        padding: 12px;
        border: 1.5px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        margin-top: 8px;
        resize: vertical;
    }

    .nav-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        border-top: 1px solid #eee;
        padding-top: 20px;
        margin-top: 12px;
    }
    .btn-nav {
        background: #e0e0e0;
        color: #222;
        border: none;
        min-height: 40px;
        padding: 9px 20px;
        border-radius: 20px;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        white-space: nowrap;
    }
    .btn-nav:hover { background: #d0d0d0; }
    .btn-next { background: #ffe082; color: #5d4037; }
    .btn-next:hover { background: #ffd54f; }

    @media (max-width: 900px) {
        .ujian-page {
            flex-direction: column;
            margin: 12px auto;
            padding: 14px;
            gap: 16px;
        }
        .ujian-sidebar {
            width: 100%;
            padding: 16px;
        }
        .timer-circle {
            width: 92px;
            height: 92px;
            font-size: 18px;
            margin-bottom: 12px;
        }
        .ujian-content {
            padding: 20px 16px;
        }
        .soal-card-layout {
            flex-direction: column;
            gap: 14px;
        }
        .soal-image-box {
            width: 100%;
            min-height: 140px;
        }
    }

    @media (max-width: 600px) {
        .grid-nomor {
            grid-template-columns: repeat(6, 1fr);
            gap: 6px;
        }
        .nav-buttons {
            flex-wrap: wrap;
        }
        .btn-nav {
            flex: 1 1 45%;
            min-height: 44px;
            font-size: 13px;
            padding: 9px 14px;
        }
    }
</style>

<div class="ujian-page">
    <div class="ujian-sidebar">
        <div class="timer-circle" id="countdown">00:00</div>
        <div class="pelamar-info">
            <strong>Lembar Jawaban</strong><br>
            <span id="pelamarEmail">-</span>
        </div>
        <div class="grid-nomor" id="gridNomor"></div>
    </div>

    <div class="ujian-content">
        <form id="formUjian" onsubmit="return false;">
            <div class="warning-banner" id="warningBanner"></div>

            <div class="progress-container">
                <div class="progress-header">
                    <div class="progress-text" id="progressText">Soal Terjawab: 0/0</div>
                    <div class="autosave-status" id="autosaveStatus">Auto-save aktif</div>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBar" style="width: 0%;"></div>
                </div>
            </div>

            <div id="containerSoal">
                <div style="text-align:center; padding:30px 12px; color:#666;">
                    Memuat soal ujian...
                </div>
            </div>

            <div class="nav-buttons" id="navButtons" style="display:none;">
                <button type="button" class="btn-nav" id="btnSebelumnya" onclick="geserSoal(-1)" style="visibility: hidden;">← Sebelumnya</button>
                <button type="button" class="btn-nav btn-next" id="btnSelanjutnya" onclick="geserSoal(1)">Selanjutnya →</button>
                <button type="button" id="btnSubmit" class="btn-nav btn-next" style="display:none; background: #2e7d32; color: #fff;" onclick="submitUjianManual()">Selesai & Kirim</button>
            </div>
        </form>
    </div>
</div>

<script>
    const idModul = <?php echo intval($id_modul); ?>;
    let currentIndex = 0;
    let totalSoal = 0;
    let izinSubmit = false;
    let sudahKunci = false;
    let arrSoalId = [];
    let waktuTotal = 0;
    let timerInterval = null;
    let autosaveTimer = null;
    let draftKey = `ujian_draft_mod_${idModul}`;

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Kunci tombol Back / Undo browser
    history.pushState({ locked: true }, null, location.href);
    window.addEventListener('popstate', function () {
        if (!izinSubmit && !sudahKunci) {
            history.pushState({ locked: true }, null, location.href);
            kirimUjianKeApi(true, 'Anda tidak dapat kembali ke halaman sebelumnya saat ujian berlangsung. Ujian dikunci & disimpan.');
        }
    });

    function kumpulkanJawaban() {
        const jawaban = {};
        arrSoalId.forEach(sId => {
            const checkedRadio = document.querySelector(`input[name="jawaban[${sId}]"]:checked`);
            const txt = document.querySelector(`textarea[name="jawaban[${sId}]"]`);
            if (checkedRadio) {
                jawaban[sId] = checkedRadio.value;
            } else if (txt) {
                jawaban[sId] = txt.value;
            }
        });
        return jawaban;
    }

    function simpanLocalDraft() {
        try {
            const alasan = {};
            arrSoalId.forEach(sId => {
                const ta = document.querySelector(`textarea[name="alasan[${sId}]"]`);
                if (ta) alasan[sId] = ta.value;
            });
            sessionStorage.setItem(draftKey, JSON.stringify({
                jawaban: kumpulkanJawaban(),
                alasan: alasan
            }));
        } catch (e) {}
    }

    function terapkanJawabanTersimpan(jawabanDb) {
        const dbMap = (jawabanDb && typeof jawabanDb === 'object') ? jawabanDb : {};

        arrSoalId.forEach((sId, idx) => {
            const valDb = String(dbMap[String(sId)] ?? dbMap[sId] ?? '').trim();

            const radios = document.querySelectorAll(`input[name="jawaban[${sId}]"]`);
            const txt = document.querySelector(`textarea[name="jawaban[${sId}]"]`);

            radios.forEach(r => { r.checked = false; });
            if (txt) txt.value = '';

            const btnGrid = document.getElementById('btnGrid' + idx);
            if (btnGrid) btnGrid.classList.remove('answered');

            let adaIsi = false;
            if (radios.length > 0 && valDb !== '') {
                radios.forEach(r => {
                    if (r.value.toLowerCase() === valDb.toLowerCase()) {
                        r.checked = true;
                        adaIsi = true;
                    }
                });
            } else if (txt && valDb !== '') {
                txt.value = valDb;
                adaIsi = true;
            }

            if (adaIsi && btnGrid) {
                btnGrid.classList.add('answered');
            }
        });

        updateProgresBarTotal();
    }

    function jadwalkanAutosave() {
        simpanLocalDraft();
        const statusEl = document.getElementById('autosaveStatus');
        if (statusEl) statusEl.textContent = 'Menyimpan...';
        if (autosaveTimer) clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => {
            kirimUjianKeApi(false);
        }, 400);
    }

    // Refresh / tutup halaman langsung simpan & kunci ujian (action: 'submit')
    window.addEventListener('beforeunload', function () {
        simpanLocalDraft();
        if (!izinSubmit && !sudahKunci && idModul > 0 && totalSoal > 0) {
            sudahKunci = true;
            const payload = JSON.stringify({
                id_modul: idModul,
                action: 'submit',
                jawaban: kumpulkanJawaban()
            });
            const blob = new Blob([payload], { type: 'application/json' });
            navigator.sendBeacon('../api/pelamar/ujian_soal.php', blob);
        }
    });

    // Tanpa kesempatan 3x: sekali pindah tab/window atau keluar layar langsung simpan & kunci ujian
    function kunciKarenaKecurangan(sumber) {
        if (izinSubmit || sudahKunci || totalSoal === 0) return;
        sudahKunci = true;

        const banner = document.getElementById('warningBanner');
        if (banner) {
            banner.style.display = 'block';
            banner.textContent = `⚠️ Terdeteksi ${sumber}! Ujian otomatis disimpan dan dikunci.`;
        }

        alert(`Terdeteksi ${sumber}! Demi menjaga kejujuran ujian, jawaban Anda langsung disimpan dan ujian dikunci.`);
        kirimUjianKeApi(true, 'Ujian otomatis disimpan dan dikunci karena berpindah layar/tab.');
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden && !izinSubmit && !sudahKunci) {
            kunciKarenaKecurangan('pindah tab atau minimize layar');
        }
    });

    window.addEventListener('blur', function () {
        if (!izinSubmit && !sudahKunci) {
            kunciKarenaKecurangan('keluar dari window ujian / Alt+Tab');
        }
    });

    // Cegah klik kanan & shortcut umum buka window/tab baru sejauh diizinkan browser
    document.addEventListener('contextmenu', function (e) {
        if (!izinSubmit) e.preventDefault();
    });

    document.addEventListener('keydown', function (e) {
        if (izinSubmit) return;
        const key = (e.key || '').toLowerCase();
        if (
            (e.ctrlKey && ['t', 'n', 'w', 'u', 's', 'p'].includes(key)) ||
            (e.ctrlKey && e.shiftKey && ['n', 't', 'i', 'j'].includes(key)) ||
            key === 'f12'
        ) {
            e.preventDefault();
        }
    });

    function tampilSoal(index) {
        if (totalSoal <= 0) return;
        document.querySelectorAll('.soal-slide').forEach(el => el.style.display = 'none');
        const targetSlide = document.getElementById('slideSoal' + index);
        if (targetSlide) targetSlide.style.display = 'block';

        document.querySelectorAll('.btn-nomor').forEach(el => el.classList.remove('active'));
        const targetBtn = document.getElementById('btnGrid' + index);
        if (targetBtn) targetBtn.classList.add('active');

        currentIndex = index;

        const btnPrev = document.getElementById('btnSebelumnya');
        const btnNext = document.getElementById('btnSelanjutnya');
        const btnSub = document.getElementById('btnSubmit');

        if (btnPrev) btnPrev.style.visibility = (currentIndex === 0) ? 'hidden' : 'visible';
        if (btnNext && btnSub) {
            if (currentIndex === totalSoal - 1) {
                btnNext.style.display = 'none';
                btnSub.style.display = 'inline-flex';
            } else {
                btnNext.style.display = 'inline-flex';
                btnSub.style.display = 'none';
            }
        }
    }

    function geserSoal(arah) {
        const target = currentIndex + arah;
        if (target >= 0 && target < totalSoal) {
            tampilSoal(target);
        }
    }

    function hitungProgresDanTandai(index) {
        const btnGrid = document.getElementById('btnGrid' + index);
        if (btnGrid) btnGrid.classList.add('answered');
        updateProgresBarTotal();
        jadwalkanAutosave();
    }

    function hitungProgresEssay(index) {
        const sId = arrSoalId[index];
        const txt = document.querySelector(`textarea[name="jawaban[${sId}]"]`);
        const btnGrid = document.getElementById('btnGrid' + index);
        if (txt && txt.value.trim() !== '') {
            if (btnGrid) btnGrid.classList.add('answered');
        } else if (btnGrid) {
            btnGrid.classList.remove('answered');
        }
        updateProgresBarTotal();
        jadwalkanAutosave();
    }

    function updateProgresBarTotal() {
        let terjawab = 0;
        for (let i = 0; i < totalSoal; i++) {
            const sId = arrSoalId[i];
            const radios = document.querySelectorAll(`input[name="jawaban[${sId}]"]:checked`);
            const txt = document.querySelector(`textarea[name="jawaban[${sId}]"]`);
            if (radios.length > 0 || (txt && txt.value.trim() !== '')) {
                terjawab++;
            }
        }
        const progText = document.getElementById('progressText');
        const progBar = document.getElementById('progressBar');
        if (progText) progText.textContent = `Soal Terjawab: ${terjawab}/${totalSoal}`;
        if (progBar) {
            const pct = totalSoal > 0 ? (terjawab / totalSoal) * 100 : 0;
            progBar.style.width = `${pct}%`;
        }
    }

    function renderTimer() {
        const safeDetik = Math.max(0, waktuTotal);
        let menit = Math.floor(safeDetik / 60);
        let detik = safeDetik % 60;
        menit = menit < 10 ? '0' + menit : String(menit);
        detik = detik < 10 ? '0' + detik : String(detik);
        const el = document.getElementById('countdown');
        if (el) el.textContent = `${menit}:${detik}`;
    }

    async function kirimUjianKeApi(isFinal, customMsg = '') {
        simpanLocalDraft();
        if (isFinal) {
            izinSubmit = true;
            sudahKunci = true;
            if (timerInterval) clearInterval(timerInterval);
        }

        const btnSub = document.getElementById('btnSubmit');
        const statusEl = document.getElementById('autosaveStatus');
        if (btnSub && isFinal) {
            btnSub.disabled = true;
            btnSub.textContent = 'Menyimpan...';
        }

        try {
            const res = await fetch('../api/pelamar/ujian_soal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    id_modul: idModul,
                    action: isFinal ? 'submit' : 'autosave',
                    jawaban: kumpulkanJawaban()
                })
            });
            const result = await res.json();

            if (!isFinal && statusEl) {
                statusEl.textContent = res.ok ? 'Tersimpan otomatis' : 'Gagal auto-save';
            }

            if (isFinal) {
                try { sessionStorage.removeItem(draftKey); } catch (e) {}
                if (customMsg) {
                    alert(customMsg);
                } else {
                    alert(result.message || 'Ujian berhasil diselesaikan dan dikunci!');
                }
                window.location.href = 'ujian.php';
            }
        } catch (err) {
            if (!isFinal && statusEl) {
                statusEl.textContent = 'Offline / gagal simpan';
            }
            if (isFinal) {
                alert('Terjadi kesalahan koneksi saat mengirim jawaban.');
                if (btnSub) {
                    btnSub.disabled = false;
                    btnSub.textContent = 'Selesai & Kirim';
                }
            }
        }
    }

    function submitUjianManual() {
        if (!confirm('Yakin ingin menyelesaikan ujian ini?')) return;
        kirimUjianKeApi(true);
    }

    function renderSoalSlides(soalList, jenisModul) {
        const gridEl = document.getElementById('gridNomor');
        const contEl = document.getElementById('containerSoal');
        const navEl = document.getElementById('navButtons');

        totalSoal = soalList.length;
        arrSoalId = soalList.map(s => parseInt(s.id_soal, 10));
        updateProgresBarTotal();

        if (totalSoal === 0) {
            gridEl.innerHTML = '';
            contEl.innerHTML = `<div style="text-align:center; padding:30px 12px; color:#666;">Belum ada soal yang terdaftar pada modul ujian ini.</div>`;
            navEl.style.display = 'none';
            return;
        }

        gridEl.innerHTML = soalList.map((_, idx) => `
            <button type="button" class="btn-nomor ${idx === 0 ? 'active' : ''}" onclick="tampilSoal(${idx})" id="btnGrid${idx}">${idx + 1}</button>
        `).join('');

        contEl.innerHTML = soalList.map((soal, idx) => {
            const sId = parseInt(soal.id_soal, 10);
            const imgHtml = soal.gambar_soal
                ? `<div class="soal-image-box"><img src="../assets/img/soal/${escapeHtml(soal.gambar_soal)}" alt="Gambar Soal"></div>`
                : '';

            let optionsHtml = '';
            if (jenisModul === 'pilihan_ganda') {
                ['A', 'B', 'C', 'D'].forEach(huruf => {
                    const teksOpsi = soal['opsi_' + huruf.toLowerCase()];
                    if (teksOpsi) {
                        optionsHtml += `<label><input type="radio" name="jawaban[${sId}]" value="${huruf}" onchange="hitungProgresDanTandai(${idx})"> <span>${huruf}. ${escapeHtml(teksOpsi)}</span></label>`;
                    }
                });
            } else if (jenisModul === 'benar_salah') {
                optionsHtml += `<label><input type="radio" name="jawaban[${sId}]" value="Benar" onchange="hitungProgresDanTandai(${idx})"> <span>Benar</span></label>`;
                optionsHtml += `<label><input type="radio" name="jawaban[${sId}]" value="Salah" onchange="hitungProgresDanTandai(${idx})"> <span>Salah</span></label>`;
                if (parseInt(soal.pakai_alasan || 0, 10) === 1) {
                    optionsHtml += `
                        <div style="margin-top: 15px;">
                            <label style="font-weight: 700; font-size: 13px; color: #333; display: block; margin-bottom: 5px;">Tuliskan Alasan Anda: <span>*</span></label>
                            <textarea name="alasan[${sId}]" class="textarea-essay" placeholder="Berikan alasan mengapa Anda memilih jawaban tersebut..." oninput="jadwalkanAutosave()"></textarea>
                        </div>
                    `;
                }
            } else {
                optionsHtml = `<textarea name="jawaban[${sId}]" class="textarea-essay" placeholder="Tuliskan jawaban essay Anda di sini..." oninput="hitungProgresEssay(${idx})"></textarea>`;
            }

            return `
                <div class="soal-slide" id="slideSoal${idx}" style="display: ${idx === 0 ? 'block' : 'none'};">
                    <div style="font-weight: 800; font-size: 16px; margin-bottom: 15px; color: #1b5e20;">Soal Nomor ${idx + 1}</div>
                    <div class="soal-card-layout">
                        ${imgHtml}
                        <div class="soal-text-box">${escapeHtml(soal.pertanyaan).replace(/\r?\n/g, '<br>')}</div>
                    </div>
                    <div class="options-list">${optionsHtml}</div>
                </div>
            `;
        }).join('');

        navEl.style.display = 'flex';
        const btnNext = document.getElementById('btnSelanjutnya');
        const btnSub = document.getElementById('btnSubmit');
        if (totalSoal === 1) {
            if (btnNext) btnNext.style.display = 'none';
            if (btnSub) btnSub.style.display = 'inline-flex';
        }
    }

    async function initUjian() {
        if (idModul <= 0) {
            alert('Modul ujian tidak ditemukan!');
            window.location.href = 'ujian.php';
            return;
        }

        try {
            const res = await fetch(`../api/pelamar/ujian_soal.php?id=${idModul}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu!');
                window.location.href = '../login.php';
                return;
            }

            if (result.data && result.data.already_completed) {
                alert(result.message || 'Anda sudah menyelesaikan modul ini dan ujian telah dikunci!');
                window.location.href = 'ujian.php';
                return;
            }

            if (!res.ok || !result.success || !result.data) {
                alert(result.message || 'Modul ujian tidak ditemukan!');
                window.location.href = 'ujian.php';
                return;
            }

            const data = result.data;
            const pelamar = data.pelamar || {};
            const modul = data.modul || {};
            const soalList = Array.isArray(data.soal_list) ? data.soal_list : [];
            const jawabanTersimpan = data.jawaban_tersimpan || {};

            draftKey = `ujian_draft_u${pelamar.id_pelamar || 'x'}_m${idModul}`;
            try { sessionStorage.removeItem(`ujian_draft_${idModul}`); } catch (e) {}

            document.getElementById('pelamarEmail').textContent = pelamar.email || '-';
            renderSoalSlides(soalList, modul.jenis_modul || '');
            terapkanJawabanTersimpan(jawabanTersimpan);

            waktuTotal = parseInt(modul.durasi_menit || 0, 10) * 60;
            renderTimer();

            timerInterval = setInterval(function () {
                if (waktuTotal <= 0) {
                    clearInterval(timerInterval);
                    renderTimer();
                    alert("Waktu ujian telah habis! Sistem akan mengunci dan menyimpan jawaban Anda.");
                    kirimUjianKeApi(true);
                    return;
                }
                waktuTotal--;
                renderTimer();
            }, 1000);
        } catch (err) {
            document.getElementById('containerSoal').innerHTML = `<div style="text-align:center; padding:30px 12px; color:#c62828;">Terjadi kesalahan koneksi saat memuat soal ujian.</div>`;
        }
    }

    initUjian();
</script>

<?php
include '../includes/footer_pelamar.php';
?>