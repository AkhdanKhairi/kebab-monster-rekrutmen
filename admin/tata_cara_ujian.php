<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$id_modul = intval($_GET['id_modul'] ?? 0);
?>

<style>
    .card-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #e0e0e0;
        max-width: 850px;
        margin: 0 auto;
    }
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        border-bottom: 2px solid #f1f3f5;
        padding-bottom: 15px;
    }
    .page-title {
        font-size: 19px;
        font-weight: 800;
        color: #004d40;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .modul-badge {
        background: #e8f5e9;
        color: #1b5e20;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        display: inline-block;
        margin-bottom: 25px;
    }
    .form-group { margin-bottom: 22px; }
    .form-group label { display: block; font-size: 14px; font-weight: 700; color: #333; margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; resize: vertical; }
    .form-control:focus { border-color: #1b5e20; box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1); }
    .preview-img { max-width: 200px; border-radius: 8px; margin-top: 10px; border: 1px solid #ddd; padding: 4px; }
    .btn-submit {
        background: #1b5e20; color: #fff; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 15px;
        display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;
    }
    .btn-submit:hover { background: #00332c; }
    .btn-back {
        background: #f1f3f5; color: #333; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px;
        display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s;
    }
    .btn-back:hover { background: #e2e6ea; }
    .alert { display: none; padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; font-weight: 600; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title">
            <span>📋</span> Pengaturan Tata Cara & Instruksi Ujian
        </div>
        <a href="master_ujian.php" class="btn-back">← Kembali</a>
    </div>

    <div class="modul-badge">
        Modul: <strong id="tcNamaModul">Memuat...</strong>
    </div>

    <div class="alert error" id="tataCaraError"></div>

    <form id="formTataCara" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_tata_cara">
        <input type="hidden" name="id_modul" value="<?php echo intval($id_modul); ?>">

        <div class="form-group">
            <label for="tata_cara">Tulis Tata Cara / Petunjuk Pengerjaan Ujian</label>
            <textarea id="tata_cara" name="tata_cara" class="form-control" rows="8" placeholder="Tuliskan petunjuk atau ketentuan ujian di sini..."></textarea>
            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">*Kotak teks bersih tanpa tombol editor yang rumit.</small>
        </div>

        <div class="form-group">
            <label for="gambar_petunjuk">Upload Gambar Pendukung (Opsional)</label>
            <input type="file" id="gambar_petunjuk" name="gambar_petunjuk" class="form-control" accept="image/*" style="padding: 9px;">

            <div id="tcPreviewBox" style="display:none;">
                <p style="font-size: 12px; font-weight: 700; margin-top: 10px;">Gambar Saat Ini:</p>
                <img id="tcImgPreview" src="" class="preview-img" alt="Tata Cara">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center;">
            <button type="submit" class="btn-submit" id="btnSimpanTataCara">💾 Simpan</button>
        </div>
    </form>
</div>

<script>
(function() {
    const idModul = <?php echo intval($id_modul); ?>;
    const labelModul = document.getElementById('tcNamaModul');
    const textareaTc = document.getElementById('tata_cara');
    const previewBox = document.getElementById('tcPreviewBox');
    const imgPreview = document.getElementById('tcImgPreview');
    const formTc = document.getElementById('formTataCara');
    const errBox = document.getElementById('tataCaraError');
    const btnSave = document.getElementById('btnSimpanTataCara');

    async function loadTataCara() {
        if (idModul <= 0) {
            alert('Modul ujian tidak ditemukan!');
            window.location.href = 'master_ujian.php';
            return;
        }

        try {
            const res = await fetch(`../api/admin/modul_ujian.php?id_modul=${idModul}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (!res.ok || !result.success || !result.data || !result.data.modul) {
                alert(result.message || 'Modul ujian tidak ditemukan!');
                window.location.href = 'master_ujian.php';
                return;
            }

            const modul = result.data.modul;
            labelModul.textContent = modul.nama_modul || '';
            textareaTc.value = modul.tata_cara || '';

            if (modul.gambar_petunjuk) {
                imgPreview.src = `../assets/img/tatacara/${encodeURIComponent(modul.gambar_petunjuk)}`;
                previewBox.style.display = 'block';
            }
        } catch (err) {
            alert('Gagal memuat data tata cara ujian.');
            window.location.href = 'master_ujian.php';
        }
    }

    formTc.addEventListener('submit', async function(e) {
        e.preventDefault();
        errBox.style.display = 'none';
        btnSave.disabled = true;

        const formData = new FormData(formTc);
        formData.set('action', 'update_tata_cara');
        formData.set('id_modul', String(idModul));

        try {
            const res = await fetch('../api/admin/modul_ujian.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            const result = await res.json();

            if (res.ok && result.success) {
                alert(result.message || 'Tata cara ujian berhasil disimpan!');
                window.location.href = 'master_ujian.php';
            } else {
                errBox.style.display = 'block';
                errBox.textContent = '❌ ' + (result.message || 'Gagal menyimpan tata cara.');
                btnSave.disabled = false;
            }
        } catch (err) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ Terjadi kesalahan koneksi saat menyimpan tata cara.';
            btnSave.disabled = false;
        }
    });

    loadTataCara();
})();
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>