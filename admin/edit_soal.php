<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$id_soal = intval($_GET['id_soal'] ?? 0);
?>

<style>
    .card-container { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e0e0e0; max-width: 900px; margin: 0 auto; }
    .page-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f1f3f5; padding-bottom: 15px; }
    .page-title { font-size: 19px; font-weight: 800; color: #004d40; display: flex; align-items: center; gap: 10px; }
    .modul-badge-wrapper { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
    .modul-badge { background: #e8f5e9; color: #1b5e20; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .jenis-badge { background: #e3f2fd; color: #0d47a1; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13.5px; font-weight: 700; color: #333; margin-bottom: 7px; }
    .form-control { width: 100%; padding: 11px 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; background: #fff; }
    .form-control:focus { border-color: #1b5e20; box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1); }
    .grid-opsi { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .checkbox-wrapper { display: flex; align-items: center; gap: 10px; background: #f1f8e9; padding: 12px 15px; border-radius: 8px; border: 1px solid #dcedc8; margin-bottom: 20px; }
    .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; accent-color: #1b5e20; cursor: pointer; }
    .checkbox-wrapper label { margin-bottom: 0; cursor: pointer; font-weight: 700; color: #2e7d32; font-size: 13.5px; }
    .btn-submit { background: #1b5e20; color: #fff; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 14.5px; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .btn-submit:hover { background: #00332c; }
    .btn-back { background: #f1f3f5; color: #333; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s; }
    .btn-back:hover { background: #e2e6ea; }
    .alert { display: none; padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; font-weight: 600; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title"><span>✏️</span> Edit Soal Ujian</div>
        <a href="master_ujian.php" id="btnKembaliSoal" class="btn-back">← Kembali</a>
    </div>

    <div class="modul-badge-wrapper">
        <div class="modul-badge">Modul: <strong id="editLabelModul">Memuat...</strong></div>
        <div class="jenis-badge">Jenis Tes: <strong id="editLabelJenis">-</strong></div>
    </div>

    <div class="alert error" id="editSoalError"></div>

    <form id="formEditSoal" enctype="multipart/form-data" style="background: #f9fbe7; border: 1.5px solid #c5e1a5; padding: 25px; border-radius: 12px;">
        <input type="hidden" name="id_soal" value="<?php echo intval($id_soal); ?>">

        <div class="form-group">
            <label for="pertanyaan">Pertanyaan / Soal</label>
            <textarea id="pertanyaan" name="pertanyaan" class="form-control" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="gambar_soal">Gambar Soal (Opsional)</label>
            <div id="previewGambarLama" style="display:none; margin-bottom: 10px;">
                <img id="imgSoalLama" src="" alt="Gambar" style="max-height: 120px; border-radius: 6px; border: 1px solid #ccc;">
                <p style="font-size: 12px; color: #666; margin-top: 4px;">Gambar saat ini</p>
            </div>
            <input type="file" id="gambar_soal" name="gambar_soal" class="form-control" accept="image/*" style="padding: 9px;">
        </div>

        <div id="editDynamicFields"></div>

        <div class="checkbox-wrapper" style="margin-top: 20px;">
            <input type="checkbox" id="pakai_alasan" name="pakai_alasan" value="1">
            <label for="pakai_alasan">Sertakan kolom alasan untuk pelamar</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-submit" id="btnUpdateSoal">💾 Simpan Perubahan</button>
        </div>
    </form>
</div>

<script>
(function() {
    const idSoal = <?php echo intval($id_soal); ?>;
    const labelModul = document.getElementById('editLabelModul');
    const labelJenis = document.getElementById('editLabelJenis');
    const btnKembali = document.getElementById('btnKembaliSoal');
    const dynamicFields = document.getElementById('editDynamicFields');
    const previewBox = document.getElementById('previewGambarLama');
    const imgLama = document.getElementById('imgSoalLama');
    const formEdit = document.getElementById('formEditSoal');
    const errBox = document.getElementById('editSoalError');
    const btnSave = document.getElementById('btnUpdateSoal');

    let targetModulId = 0;

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatJenis(jenis) {
        return String(jenis ?? '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, ch => ch.toUpperCase());
    }

    function renderEditFields(soal) {
        const jenis = soal.jenis_modul || 'essay';
        const kunci = soal.kunci_jawaban || '';

        if (jenis === 'pilihan_ganda') {
            dynamicFields.innerHTML = `
                <div class="grid-opsi">
                    <div class="form-group"><label for="opsi_a">Pilihan A</label><input id="opsi_a" type="text" name="opsi_a" class="form-control" value="${escapeHtml(soal.opsi_a || '')}" required></div>
                    <div class="form-group"><label for="opsi_b">Pilihan B</label><input id="opsi_b" type="text" name="opsi_b" class="form-control" value="${escapeHtml(soal.opsi_b || '')}" required></div>
                    <div class="form-group"><label for="opsi_c">Pilihan C</label><input id="opsi_c" type="text" name="opsi_c" class="form-control" value="${escapeHtml(soal.opsi_c || '')}" required></div>
                    <div class="form-group"><label for="opsi_d">Pilihan D</label><input id="opsi_d" type="text" name="opsi_d" class="form-control" value="${escapeHtml(soal.opsi_d || '')}" required></div>
                </div>
                <div class="form-group" style="margin-top: 10px;">
                    <label for="kunci_jawaban">Kunci Jawaban Benar</label>
                    <select id="kunci_jawaban" name="kunci_jawaban" class="form-control" required style="max-width: 250px;">
                        <option value="A" ${kunci === 'A' ? 'selected' : ''}>A</option>
                        <option value="B" ${kunci === 'B' ? 'selected' : ''}>B</option>
                        <option value="C" ${kunci === 'C' ? 'selected' : ''}>C</option>
                        <option value="D" ${kunci === 'D' ? 'selected' : ''}>D</option>
                    </select>
                </div>
            `;
        } else if (jenis === 'benar_salah') {
            dynamicFields.innerHTML = `
                <div class="form-group" style="max-width: 300px;">
                    <label for="kunci_jawaban">Kunci Jawaban (Opsional)</label>
                    <select id="kunci_jawaban" name="kunci_jawaban" class="form-control">
                        <option value="">-- Tanpa Kunci / Opsional --</option>
                        <option value="Benar" ${kunci === 'Benar' ? 'selected' : ''}>Benar</option>
                        <option value="Salah" ${kunci === 'Salah' ? 'selected' : ''}>Salah</option>
                    </select>
                </div>
            `;
        } else {
            dynamicFields.innerHTML = `
                <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; color: #0d47a1; font-size: 13.5px;">
                    💡 <strong>Mode Soal Essay</strong>: Pelamar akan menjawab dalam bentuk teks uraian panjang. Penilaian manual oleh admin.
                </div>
            `;
        }
    }

    async function loadSoal() {
        if (idSoal <= 0) {
            alert('Soal Tes tidak ditemukan!');
            window.location.href = 'master_ujian.php';
            return;
        }

        try {
            const res = await fetch(`../api/admin/soal_ujian.php?id_soal=${idSoal}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (!res.ok || !result.success || !result.data || !result.data.soal) {
                alert(result.message || 'Soal Tes tidak ditemukan!');
                window.location.href = 'master_ujian.php';
                return;
            }

            const s = result.data.soal;
            targetModulId = parseInt(s.id_modul, 10) || 0;
            if (targetModulId > 0) {
                btnKembali.href = `tambah_soal.php?id_modul=${targetModulId}`;
            }

            labelModul.textContent = s.nama_modul || '';
            labelJenis.textContent = formatJenis(s.jenis_modul || 'essay');
            document.getElementById('pertanyaan').value = s.pertanyaan || '';
            document.getElementById('pakai_alasan').checked = parseInt(s.pakai_alasan, 10) === 1;

            if (s.gambar_soal) {
                imgLama.src = `../assets/img/soal/${encodeURIComponent(s.gambar_soal)}`;
                previewBox.style.display = 'block';
            }

            renderEditFields(s);
        } catch (err) {
            alert('Gagal memuat data soal.');
            window.location.href = 'master_ujian.php';
        }
    }

    formEdit.addEventListener('submit', async function(e) {
        e.preventDefault();
        errBox.style.display = 'none';
        btnSave.disabled = true;

        const formData = new FormData(formEdit);
        formData.set('id_soal', String(idSoal));

        try {
            const res = await fetch('../api/admin/soal_ujian.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            const result = await res.json();

            if (res.ok && result.success) {
                alert(result.message || 'Soal berhasil diperbarui!');
                window.location.href = `tambah_soal.php?id_modul=${result.data?.id_modul || targetModulId}`;
            } else {
                errBox.style.display = 'block';
                errBox.textContent = '❌ ' + (result.message || 'Gagal memperbarui soal.');
                btnSave.disabled = false;
            }
        } catch (err) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ Terjadi kesalahan koneksi saat memperbarui soal.';
            btnSave.disabled = false;
        }
    });

    loadSoal();
})();
</script>

<?php
include '../includes/footer_admin.php';
?>