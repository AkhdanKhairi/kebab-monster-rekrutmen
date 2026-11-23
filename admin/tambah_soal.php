<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$id_modul = intval($_GET['id_modul'] ?? 0);
?>

<style>
    .card-container { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e0e0e0; max-width: 950px; margin: 0 auto; }
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
    .table-soal { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13.5px; }
    .table-soal th { background-color: #f8f9fa; color: #333; padding: 12px 14px; border-bottom: 2px solid #ddd; text-align: left; }
    .table-soal td { padding: 12px 14px; border-bottom: 1px solid #eee; color: #444; vertical-align: top; }
    .btn-edit-soal { background: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; margin-bottom: 4px; }
    .btn-edit-soal:hover { background: #bbdefb; }
    .btn-delete-soal { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; cursor: pointer; }
    .btn-delete-soal:hover { background: #ffcdd2; }
    .alert { display: none; padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; font-weight: 600; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title"><span>📝</span> Kelola Soal & Pertanyaan Ujian</div>
        <a href="master_ujian.php" class="btn-back">← Kembali ke Master Ujian</a>
    </div>

    <div class="modul-badge-wrapper">
        <div class="modul-badge">Modul: <strong id="labelNamaModul">Memuat...</strong></div>
        <div class="jenis-badge">Jenis Tes: <strong id="labelJenisModul">-</strong></div>
    </div>

    <div class="alert error" id="tambahSoalError"></div>

    <form id="formTambahSoal" enctype="multipart/form-data" style="background: #f9fbe7; border: 1.5px solid #c5e1a5; padding: 25px; border-radius: 12px;">
        <input type="hidden" name="id_modul" value="<?php echo intval($id_modul); ?>">

        <div class="form-group">
            <label for="pertanyaan">Pertanyaan / Soal</label>
            <textarea id="pertanyaan" name="pertanyaan" class="form-control" rows="4" placeholder="Tuliskan bunyi pertanyaan di sini..." required></textarea>
        </div>

        <div class="form-group">
            <label for="gambar_soal">Gambar Soal (Opsional)</label>
            <input type="file" id="gambar_soal" name="gambar_soal" class="form-control" accept="image/*" style="padding: 9px;">
        </div>

        <div id="dynamicSoalFields"></div>

        <div class="checkbox-wrapper" style="margin-top: 20px;">
            <input type="checkbox" id="pakai_alasan" name="pakai_alasan" value="1">
            <label for="pakai_alasan">Sertakan kolom alasan untuk pelamar (Pelamar akan melihat kolom teks alasan saat ujian)</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-submit" id="btnSimpanSoal">➕ Tambahkan Soal Ini</button>
        </div>
    </form>

    <h3 style="margin-top: 35px; font-size: 16px; font-weight: 800; color: #004d40;">Daftar Soal pada Modul Ini</h3>

    <div style="overflow-x: auto;">
        <table class="table-soal">
            <thead id="theadSoal">
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Pertanyaan & Gambar</th>
                    <th style="width: 110px; text-align: center;">Pakai Alasan?</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="tbodySoal">
                <tr>
                    <td colspan="4" style="text-align: center; padding: 25px; color: #777;">Memuat daftar soal...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
(function() {
    const idModul = <?php echo intval($id_modul); ?>;
    const labelNama = document.getElementById('labelNamaModul');
    const labelJenis = document.getElementById('labelJenisModul');
    const dynamicFields = document.getElementById('dynamicSoalFields');
    const theadSoal = document.getElementById('theadSoal');
    const tbodySoal = document.getElementById('tbodySoal');
    const formSoal = document.getElementById('formTambahSoal');
    const errBox = document.getElementById('tambahSoalError');
    const btnSave = document.getElementById('btnSimpanSoal');

    let jenisModul = 'essay';

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function nl2br(val) {
        return escapeHtml(val).replace(/\r\n|\r|\n/g, '<br>');
    }

    function formatJenis(jenis) {
        return String(jenis ?? '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, ch => ch.toUpperCase());
    }

    function renderDynamicFields(jenis) {
        if (jenis === 'pilihan_ganda') {
            dynamicFields.innerHTML = `
                <div class="grid-opsi">
                    <div class="form-group"><label for="opsi_a">Pilihan A</label><input id="opsi_a" type="text" name="opsi_a" class="form-control" placeholder="Jawaban A" required></div>
                    <div class="form-group"><label for="opsi_b">Pilihan B</label><input id="opsi_b" type="text" name="opsi_b" class="form-control" placeholder="Jawaban B" required></div>
                    <div class="form-group"><label for="opsi_c">Pilihan C</label><input id="opsi_c" type="text" name="opsi_c" class="form-control" placeholder="Jawaban C" required></div>
                    <div class="form-group"><label for="opsi_d">Pilihan D</label><input id="opsi_d" type="text" name="opsi_d" class="form-control" placeholder="Jawaban D" required></div>
                </div>
                <div class="form-group" style="margin-top: 10px;">
                    <label for="kunci_jawaban">Kunci Jawaban Benar</label>
                    <select id="kunci_jawaban" name="kunci_jawaban" class="form-control" required style="max-width: 250px;">
                        <option value="">-- Pilih Kunci Jawaban --</option>
                        <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
                    </select>
                </div>
            `;
        } else if (jenis === 'benar_salah') {
            dynamicFields.innerHTML = `
                <div class="form-group" style="max-width: 300px;">
                    <label for="kunci_jawaban">Kunci Jawaban (Opsional)</label>
                    <select id="kunci_jawaban" name="kunci_jawaban" class="form-control">
                        <option value="">-- Tanpa Kunci / Opsional --</option>
                        <option value="Benar">Benar</option>
                        <option value="Salah">Salah</option>
                    </select>
                </div>
            `;
        } else {
            dynamicFields.innerHTML = `
                <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; color: #0d47a1; font-size: 13.5px;">
                    💡 <strong>Mode Soal Essay</strong>: Pelamar akan menjawab dalam bentuk teks uraian panjang. Penilaian untuk modul ini bersifat manual oleh admin.
                </div>
            `;
        }
    }

    function renderDaftarSoal(soalList, jenis) {
        const hasKunci = (jenis === 'pilihan_ganda' || jenis === 'benar_salah');
        theadSoal.innerHTML = hasKunci
            ? `<tr>
                <th style="width: 50px;">No</th>
                <th>Pertanyaan & Gambar</th>
                <th style="width: 100px;">Kunci</th>
                <th style="width: 110px; text-align: center;">Pakai Alasan?</th>
                <th style="width: 100px; text-align: center;">Aksi</th>
               </tr>`
            : `<tr>
                <th style="width: 50px;">No</th>
                <th>Pertanyaan & Gambar</th>
                <th style="width: 110px; text-align: center;">Pakai Alasan?</th>
                <th style="width: 100px; text-align: center;">Aksi</th>
               </tr>`;

        const colCount = hasKunci ? 5 : 4;
        if (!Array.isArray(soalList) || soalList.length === 0) {
            tbodySoal.innerHTML = `<tr><td colspan="${colCount}" style="text-align: center; padding: 25px; color: #777;">Belum ada soal pada modul ini.</td></tr>`;
            return;
        }

        tbodySoal.innerHTML = soalList.map((row, index) => {
            const idSoal = parseInt(row.id_soal, 10);
            const imgHtml = row.gambar_soal
                ? `<div style="margin-top: 5px;"><img src="../assets/img/soal/${escapeHtml(row.gambar_soal)}" style="max-height: 60px; border-radius: 4px; border: 1px solid #ddd;" alt="Gambar Soal"></div>`
                : '';
            const kunciTd = hasKunci
                ? `<td><strong><span style="color: #1b5e20;">${escapeHtml(row.kunci_jawaban || '-')}</span></strong></td>`
                : '';
            const alasanBadge = parseInt(row.pakai_alasan, 10) === 1
                ? `<span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700;">Ya</span>`
                : `<span style="background: #f5f5f5; color: #777; padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700;">Tidak</span>`;

            return `
                <tr>
                    <td><strong>${index + 1}</strong></td>
                    <td>${nl2br(row.pertanyaan || '')}${imgHtml}</td>
                    ${kunciTd}
                    <td style="text-align: center;">${alasanBadge}</td>
                    <td style="text-align: center;">
                        <a href="edit_soal.php?id_soal=${idSoal}" class="btn-edit-soal">Edit</a><br>
                        <button type="button" class="btn-delete-soal" data-delete-soal="${idSoal}">Hapus</button>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadModulDanSoal() {
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
            jenisModul = modul.jenis_modul || 'essay';
            labelNama.textContent = modul.nama_modul || '';
            labelJenis.textContent = formatJenis(jenisModul);

            renderDynamicFields(jenisModul);
            renderDaftarSoal(result.data.soal_list || [], jenisModul);
        } catch (err) {
            alert('Gagal memuat data modul dan soal.');
            window.location.href = 'master_ujian.php';
        }
    }

    formSoal.addEventListener('submit', async function(e) {
        e.preventDefault();
        errBox.style.display = 'none';
        btnSave.disabled = true;

        const formData = new FormData(formSoal);
        formData.set('id_modul', String(idModul));

        try {
            const res = await fetch('../api/admin/soal_ujian.php', {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });
            const result = await res.json();

            if (res.ok && result.success) {
                alert(result.message || 'Soal berhasil ditambahkan!');
                formSoal.reset();
                renderDynamicFields(jenisModul);
                await loadModulDanSoal();
            } else {
                errBox.style.display = 'block';
                errBox.textContent = '❌ ' + (result.message || 'Gagal menyimpan soal.');
            }
        } catch (err) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ Terjadi kesalahan koneksi saat menyimpan soal.';
        } finally {
            btnSave.disabled = false;
        }
    });

    tbodySoal.addEventListener('click', async function(e) {
        const delBtn = e.target.closest('[data-delete-soal]');
        if (!delBtn) return;
        const idSoal = parseInt(delBtn.getAttribute('data-delete-soal'), 10);
        if (!confirm('Yakin ingin menghapus soal ini?')) return;

        try {
            const res = await fetch('../api/admin/soal_ujian.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ action: 'delete', id_soal: idSoal })
            });
            const result = await res.json();
            if (res.ok && result.success) {
                alert(result.message || 'Soal berhasil dihapus!');
                await loadModulDanSoal();
            } else {
                errBox.style.display = 'block';
                errBox.textContent = '❌ ' + (result.message || 'Gagal menghapus soal.');
            }
        } catch (err) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ Terjadi kesalahan koneksi saat menghapus soal.';
        }
    });

    loadModulDanSoal();
})();
</script>

<?php
include '../includes/footer_admin.php';
?>