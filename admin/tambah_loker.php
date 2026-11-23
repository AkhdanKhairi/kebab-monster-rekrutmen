<?php
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
    .karir-page-wrap {
        max-width: 950px;
        margin: 0 auto;
        padding-bottom: 36px;
    }
    .karir-container {
        max-width: 100%;
        margin: 0 auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        overflow: hidden;
        border: 1px solid #e0e0e0;
    }
    .karir-header-bar {
        background: #1b5e20;
        color: #ffffff;
        padding: 15px 20px;
        font-weight: 700;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        margin: 16px 16px 0 16px;
    }
    .karir-form-body {
        padding: 26px 32px;
    }
    .page-main-title {
        text-align: center;
        font-size: 23px;
        font-weight: 800;
        color: #111;
        margin-bottom: 22px;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px 26px;
        margin-bottom: 16px;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }
    .form-group.full-width {
        grid-column: span 2;
    }

    .form-group label {
        font-size: 13.5px;
        font-weight: 700;
        color: #2c3e50;
    }
    .form-group label span {
        color: #d32f2f;
    }

    .form-control {
        width: 100%;
        min-height: 40px;
        padding: 10px 14px;
        border: 1px solid #b0bec5;
        border-radius: 8px;
        font-size: 13.5px;
        outline: none;
        background: #fff;
        color: #333;
        transition: all 0.2s;
        line-height: 1.5;
    }
    .form-control:focus {
        border-color: #1b5e20;
        box-shadow: 0 0 0 3px rgba(27,94,32,0.12);
    }

    .helper-text {
        font-size: 11.5px;
        color: #d32f2f;
        margin-top: 2px;
        font-style: italic;
    }

    .note-editor.note-frame {
        border: 1px solid #b0bec5;
        border-radius: 8px;
        overflow: hidden;
    }
    .note-editor.note-frame .note-toolbar {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px 8px;
    }

    .btn-wrapper {
        display: flex;
        justify-content: flex-end;
        margin-top: 28px;
        border-top: 1px solid #f1f3f5;
        padding-top: 20px;
    }
    .btn-save {
        background: #1b5e20;
        color: #fff;
        min-height: 44px;
        padding: 12px 38px;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14.5px;
        cursor: pointer;
        transition: background 0.2s;
        box-shadow: 0 4px 12px rgba(27,94,32,0.22);
    }
    .btn-save:hover {
        background: #00332c;
    }

    .btn-back {
        background: #f1f3f5;
        color: #333;
        text-decoration: none;
        min-height: 38px;
        padding: 9px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 16px;
        transition: background 0.2s;
    }
    .btn-back:hover {
        background: #e2e6ea;
    }

    .alert.error {
        display: none;
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
        padding: 12px 14px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        margin-bottom: 18px;
    }

    @media (max-width: 768px) {
        .karir-page-wrap {
            padding-bottom: 24px;
        }
        .btn-back {
            width: 100%;
            justify-content: center;
            min-height: 42px;
            margin-bottom: 14px;
        }
        .page-main-title {
            font-size: 19px;
            margin-bottom: 16px;
        }
        .karir-container {
            border-radius: 12px;
        }
        .karir-header-bar {
            margin: 12px 12px 0 12px;
            padding: 12px 14px;
            font-size: 14px;
            border-radius: 10px;
        }
        .karir-form-body {
            padding: 16px 14px;
        }
        .form-grid-2 {
            grid-template-columns: 1fr;
            gap: 14px;
        }
        .form-group.full-width {
            grid-column: span 1;
        }
        .form-control {
            min-height: 42px;
            padding: 10px 12px;
            font-size: 16px;
        }
        .note-editor.note-frame .note-editable {
            font-size: 16px;
            padding: 10px 12px;
        }
        .btn-wrapper {
            margin-top: 20px;
            padding-top: 16px;
        }
        .btn-save {
            width: 100%;
            padding: 12px 20px;
            font-size: 15px;
        }
    }
</style>

<div class="karir-page-wrap">
    <a href="master_loker.php" class="btn-back">← Kembali ke Master Karir</a>

    <h1 class="page-main-title">Tambah Data Karir</h1>

    <div class="karir-container">
        <div class="karir-header-bar">
            <span>🏢</span> Formulir Input Lowongan Pekerjaan
        </div>

        <div class="karir-form-body">
            <div class="alert error" id="tambahLokerError"></div>

            <form id="formTambahLoker">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="divisi">Divisi <span>*</span></label>
                        <select id="divisi" name="divisi" class="form-control" required>
                            <option value="">-- Pilih Divisi Pekerjaan --</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Finance">Finance</option>
                            <option value="SCM">SCM</option>
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
                        <input id="tgl_dibuat" type="date" name="tgl_dibuat" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
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
                        <input id="jurusan_akademik" type="text" name="jurusan_akademik" class="form-control" placeholder="Contoh: Semua Jurusan / Tata Boga / Informatika" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="lokasi_pekerjaan">Lokasi Penempatan <span>*</span></label>
                        <input id="lokasi_pekerjaan" type="text" name="lokasi_pekerjaan" class="form-control" maxlength="80" placeholder="Jl. Pangeran Sogiri No. 404 Tanah Baru" required>
                        <span class="helper-text">Maksimal 80 Karakter*</span>
                    </div>

                    <div class="form-group full-width">
                        <label for="nip">NIP HRD Pembuat <span>*</span></label>
                        <input id="nip" type="text" name="nip" class="form-control" value="1005" placeholder="Contoh: 1005" required>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-benefit">Benefit <span>*</span></label>
                        <textarea name="benefit" id="summernote-benefit" class="form-control"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-deskripsi">Deskripsi Pekerjaan <span>*</span></label>
                        <textarea name="deskripsi_pekerjaan" id="summernote-deskripsi" class="form-control"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-tugas">Tugas & Tanggung Jawab <span>*</span></label>
                        <textarea name="tugas_tanggung_jawab" id="summernote-tugas" class="form-control"></textarea>
                    </div>

                    <div class="form-group full-width">
                        <label for="summernote-kualifikasi">Kualifikasi <span>*</span></label>
                        <textarea name="kualifikasi" id="summernote-kualifikasi" class="form-control"></textarea>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <button type="submit" class="btn-save" id="btnSimpanLoker">💾 Simpan Lowongan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        const editorConfig = {
            placeholder: 'Tuliskan detail di sini...',
            tabsize: 2,
            height: isMobile ? 130 : 155,
            toolbar: isMobile
                ? [
                    ['font', ['bold', 'italic', 'underline']],
                    ['para', ['ul', 'ol']],
                    ['insert', ['link']]
                ]
                : [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link']],
                    ['view', ['undo', 'redo']]
                ]
        };

        const editors = [
            { selector: '#summernote-benefit', label: 'Benefit' },
            { selector: '#summernote-deskripsi', label: 'Deskripsi Pekerjaan' },
            { selector: '#summernote-tugas', label: 'Tugas & Tanggung Jawab' },
            { selector: '#summernote-kualifikasi', label: 'Kualifikasi' }
        ];

        editors.forEach(function(item) {
            $(item.selector).summernote(editorConfig);
        });

        const errBox = document.getElementById('tambahLokerError');
        const btnSave = document.getElementById('btnSimpanLoker');

        function showErr(msg) {
            errBox.style.display = 'block';
            errBox.textContent = '❌ ' + msg;
        }

        $('#formTambahLoker').on('submit', async function(e) {
            e.preventDefault();
            errBox.style.display = 'none';

            for (let i = 0; i < editors.length; i++) {
                const ed = editors[i];
                const htmlVal = $(ed.selector).summernote('code') || '';
                const plainText = $('<div>').html(htmlVal).text().replace(/\u00a0/g, '').trim();
                if (plainText === '') {
                    showErr('Kolom ' + ed.label + ' wajib diisi.');
                    $(ed.selector).summernote('focus');
                    return false;
                }
            }

            btnSave.disabled = true;
            const payload = {
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
                    alert(result.message || 'Data lowongan berhasil disimpan!');
                    window.location.href = 'master_loker.php';
                } else {
                    showErr(result.message || 'Gagal menyimpan data.');
                    btnSave.disabled = false;
                }
            } catch (err) {
                showErr('Terjadi kesalahan koneksi saat menyimpan lowongan.');
                btnSave.disabled = false;
            }
        });
    });
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>