<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$id_lowongan = intval($_GET['id'] ?? 0);
?>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
    .karir-container { max-width: 950px; margin: 0 auto; background: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #e0e0e0; }
    .karir-header-bar { background: #1b5e20; color: #ffffff; padding: 16px 22px; font-weight: 700; font-size: 15px; display: flex; align-items: center; gap: 8px; border-top-left-radius: 12px; border-top-right-radius: 12px; margin: 15px 15px 0 15px; }
    .karir-form-body { padding: 30px 35px; }
    .page-main-title { text-align: center; font-size: 24px; font-weight: 800; color: #111; margin-bottom: 25px; }

    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px 30px; margin-bottom: 20px; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group.full-width { grid-column: span 2; }

    .form-group label { font-size: 14px; font-weight: 700; color: #2c3e50; }
    .form-group label span { color: #d32f2f; }

    .form-control { width: 100%; padding: 12px 16px; border: 1px solid #b0bec5; border-radius: 8px; font-size: 14px; outline: none; background: #fff; color: #333; transition: all 0.2s; line-height: 1.5; }
    .form-control:focus { border-color: #1b5e20; box-shadow: 0 0 0 3px rgba(27,94,32,0.12); }

    .helper-text { font-size: 12px; color: #d32f2f; margin-top: 4px; font-style: italic; }

    .btn-wrapper { display: flex; justify-content: flex-end; margin-top: 35px; border-top: 1px solid #f1f3f5; padding-top: 25px; }
    .btn-save { background: #1b5e20; color: #fff; padding: 13px 45px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(27,94,32,0.25); }
    .btn-save:hover { background: #00332c; }

    .btn-back { background: #f1f3f5; color: #333; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 20px; transition: background 0.2s; }
    .btn-back:hover { background: #e2e6ea; }

    .alert.error { display: none; background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 14px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
</style>

<div style="max-width: 950px; margin: 0 auto; padding-bottom: 40px;">
    <a href="master_loker.php" class="btn-back">← Kembali ke Master Karir</a>

    <h1 class="page-main-title">Edit Data Karir</h1>

    <div class="karir-container">
        <div class="karir-header-bar">
            <span>🏢</span> Edit Lowongan Pekerjaan #<span id="lokerIdLabel"><?php echo intval($id_lowongan); ?></span>
        </div>

        <div class="karir-form-body">
            <div class="alert error" id="editLokerError"></div>

            <form id="formEditLoker">
                <input type="hidden" name="id_lowongan" id="id_lowongan" value="<?php echo intval($id_lowongan); ?>">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="divisi">Divisi <span>*</span></label>
                        <select id="divisi" name="divisi" class="form-control" required>
                            <option value="">-- Pilih Divisi Pekerjaan --</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Finance">Finance</option>
                            <option value="HRDGA">HRDGA</option>
                            <option value="BOD">BOD</option>
                            <option value="Internal Audit">Internal Audit</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="posisi_pekerjaan">Posisi Pekerjaan <span>*</span></label>
                        <input id="posisi_pekerjaan" type="text" name="posisi_pekerjaan" class="form-control" placeholder="Contoh: SMC Manager / Crew Outlet / Business Intelligent" required>
                    </div>

                    <div class="form-group">
                        <label for="tgl_dibuat">Tanggal Dibuat <span>*</span></label>
                        <input id="tgl_dibuat" type="date" name="tgl_dibuat" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="shift">Shift <span>*</span></label>
                        <select id="shift" name="shift" class="form-control" required>
                            <option value="">-- Pilih Shift Kerja --</option>
                            <option value="Full Time">Full Time</option>
                            <option value="Shift Pagi">Shift Pagi</option>
                            <option value="Shift Malam">Shift Malam</option>
                            <option value="Part Time">Part Time</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label for="jurusan_akademik">Jurusan Akademik <span>*</span></label>
                        <input id="jurusan_akademik" type="text" name="jurusan_akademik" class="form-control" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="lokasi_pekerjaan">Lokasi Penempatan <span>*</span></label>
                        <input id="lokasi_pekerjaan" type="text" name="lokasi_pekerjaan" class="form-control" maxlength="80" required>
                        <span class="helper-text">Maksimal 80 Karakter*</span>
                    </div>

                    <div class="form-group full-width">
                        <label for="nip">NIP HRD Pembuat <span>*</span></label>
                        <input id="nip" type="text" name="nip" class="form-control" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-benefit">Benefit <span>*</span></label>
                        <textarea name="benefit" id="summernote-benefit" class="form-control" required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-deskripsi">Deskripsi Pekerjaan <span>*</span></label>
                        <textarea name="deskripsi_pekerjaan" id="summernote-deskripsi" class="form-control" required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-tugas">Tugas & Tanggung Jawab <span>*</span></label>
                        <textarea name="tugas_tanggung_jawab" id="summernote-tugas" class="form-control" required></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-kualifikasi">Kualifikasi <span>*</span></label>
                        <textarea name="kualifikasi" id="summernote-kualifikasi" class="form-control" required></textarea>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <button type="submit" class="btn-save" id="btnUpdateLoker">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const idLowongan = <?php echo intval($id_lowongan); ?>;
        const editorConfig = {
            placeholder: 'Tuliskan detail di sini...',
            tabsize: 2,
            height: 150,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['link']],
                ['view', ['undo', 'redo']]
            ]
        };

        $('#summernote-benefit').summernote(editorConfig);
        $('#summernote-deskripsi').summernote(editorConfig);
        $('#summernote-tugas').summernote(editorConfig);
        $('#summernote-kualifikasi').summernote(editorConfig);

        const errBox = document.getElementById('editLokerError');
        const btnSave = document.getElementById('btnUpdateLoker');

        function showErr(msg) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ ' + msg;
        }

        async function loadDetailLoker() {
            if (idLowongan <= 0) {
                alert('Data lowongan tidak ditemukan!');
                window.location.href = 'master_loker.php';
                return;
            }

            try {
                const res = await fetch(`../api/admin/lowongan.php?id=${idLowongan}`, {
                    method: 'GET',
                    credentials: 'same-origin'
                });
                const result = await res.json();

                if (!res.ok || !result.success || !result.data || !result.data.lowongan) {
                    alert(result.message || 'Data lowongan tidak ditemukan!');
                    window.location.href = 'master_loker.php';
                    return;
                }

                const l = result.data.lowongan;
                $('#lokerIdLabel').text(l.id_lowongan);
                $('#divisi').val(l.divisi || '');
                $('#posisi_pekerjaan').val(l.posisi_pekerjaan || '');
                $('#tgl_dibuat').val(l.tgl_dibuat || '');
                $('#shift').val(l.shift || '');
                $('#jurusan_akademik').val(l.jurusan_akademik || '');
                $('#lokasi_pekerjaan').val(l.lokasi_pekerjaan || '');
                $('#nip').val(l.nip || '');
                $('#summernote-benefit').summernote('code', l.benefit || '');
                $('#summernote-deskripsi').summernote('code', l.deskripsi_pekerjaan || '');
                $('#summernote-tugas').summernote('code', l.tugas_tanggung_jawab || '');
                $('#summernote-kualifikasi').summernote('code', l.kualifikasi || '');
            } catch (err) {
                alert('Gagal memuat data lowongan.');
                window.location.href = 'master_loker.php';
            }
        }

        $('#formEditLoker').on('submit', async function(e) {
            e.preventDefault();
            errBox.style.display = 'none';
            btnSave.disabled = true;

            const payload = {
                id_lowongan: idLowongan,
                divisi: $('#divisi').val().trim(),
                posisi_pekerjaan: $('#posisi_pekerjaan').val().trim(),
                tgl_dibuat: $('#tgl_dibuat').val().trim(),
                shift: $('#shift').val().trim(),
                jurusan_akademik: $('#jurusan_akademik').val().trim(),
                lokasi_pekerjaan: $('#lokasi_pekerjaan').val().trim(),
                nip: $('#nip').val().trim(),
                benefit: $('#summernote-benefit').summernote('code'),
                deskripsi_pekerjaan: $('#summernote-deskripsi').summernote('code'),
                tugas_tanggung_jawab: $('#summernote-tugas').summernote('code'),
                kualifikasi: $('#summernote-kualifikasi').summernote('code')
            };

            try {
                const res = await fetch('../api/admin/lowongan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                const result = await res.json();

                if (res.ok && result.success) {
                    alert(result.message || 'Data lowongan berhasil diperbarui!');
                    window.location.href = 'master_loker.php';
                } else {
                    showErr(result.message || 'Gagal memperbarui data.');
                    btnSave.disabled = false;
                }
            } catch (err) {
                showErr('Terjadi kesalahan koneksi saat memperbarui data.');
                btnSave.disabled = false;
            }
        });

        loadDetailLoker();
    });
</script>

<?php
include '../includes/footer_admin.php';
?>