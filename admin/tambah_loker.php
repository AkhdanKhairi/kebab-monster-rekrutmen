<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_loker'])) {
    $divisi               = mysqli_real_escape_string($conn, $_POST['divisi']);
    $posisi_pekerjaan     = mysqli_real_escape_string($conn, $_POST['posisi_pekerjaan']);
    $tgl_dibuat           = mysqli_real_escape_string($conn, $_POST['tgl_dibuat']);
    $shift                = mysqli_real_escape_string($conn, $_POST['shift']);
    $jurusan_akademik     = mysqli_real_escape_string($conn, $_POST['jurusan_akademik']);
    $lokasi_pekerjaan     = mysqli_real_escape_string($conn, $_POST['lokasi_pekerjaan']);
    $nip                  = mysqli_real_escape_string($conn, $_POST['nip']);
    
    // Kolom teks panjang dengan Rich Text Editor
    $benefit              = mysqli_real_escape_string($conn, $_POST['benefit']);
    $deskripsi_pekerjaan  = mysqli_real_escape_string($conn, $_POST['deskripsi_pekerjaan']);
    $tugas_tanggung_jawab = mysqli_real_escape_string($conn, $_POST['tugas_tanggung_jawab']);
    $kualifikasi          = mysqli_real_escape_string($conn, $_POST['kualifikasi']);
    $view                 = 0;

    $query_insert = "INSERT INTO lowongan (posisi_pekerjaan, divisi, shift, tgl_dibuat, jurusan_akademik, lokasi_pekerjaan, deskripsi_pekerjaan, tugas_tanggung_jawab, kualifikasi, nip, benefit, view) 
                     VALUES ('$posisi_pekerjaan', '$divisi', '$shift', '$tgl_dibuat', '$jurusan_akademik', '$lokasi_pekerjaan', '$deskripsi_pekerjaan', '$tugas_tanggung_jawab', '$kualifikasi', '$nip', '$benefit', $view)";

    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Data lowongan berhasil disimpan!'); window.location='master_loker.php';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal menyimpan data: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!-- CDN CSS & JS untuk Summernote Rich Text Editor -->
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
    
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 14px; border-radius: 8px; font-size: 14px; margin-bottom: 20px; }
</style>

<div style="max-width: 950px; margin: 0 auto; padding-bottom: 40px;">
    <a href="master_loker.php" class="btn-back">← Kembali ke Master Karir</a>

    <h1 class="page-main-title">Tambah Data Karir</h1>

    <div class="karir-container">
        <div class="karir-header-bar">
            <span>🏢</span> Formulir Input Lowongan Pekerjaan
        </div>

        <div class="karir-form-body">
            <?php echo $pesan; ?>

            <form action="" method="POST">
                <input type="hidden" name="simpan_loker" value="1">

                <div class="form-grid-2">
                    <!-- Divisi sekarang menjadi Dropdown -->
                    <div class="form-group">
                        <label>Divisi <span>*</span></label>
                        <select name="divisi" class="form-control" required>
                            <option value="">-- Pilih Divisi Pekerjaan --</option>
                            <option value="Operasional">Operasional</option>
                            <option value="Finance">Finance</option>
                            <option value="SCM">SCM</option>
                            <option value="HRDGA">HRDGA</option>
                            <option value="BOD">BOD</option>
                            <option value="Internal Audit">Internal Audit</option>
                        </select>
                    </div>

                    <!-- Posisi Pekerjaan sekarang menjadi Text Input Biasa -->
                    <div class="form-group">
                        <label>Posisi Pekerjaan <span>*</span></label>
                        <input type="text" name="posisi_pekerjaan" class="form-control" placeholder="Contoh: SMC Manager / Crew Outlet / Business Intelligent" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Dibuat <span>*</span></label>
                        <input type="date" name="tgl_dibuat" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Shift <span>*</span></label>
                        <select name="shift" class="form-control" required>
                            <option value="">-- Pilih Shift Kerja --</option>
                            <option value="Full Time">Full Time</option>
                            <option value="Shift Pagi">Shift Pagi</option>
                            <option value="Shift Malam">Shift Malam</option>
                            <option value="Part Time">Part Time</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Jurusan Akademik <span>*</span></label>
                        <input type="text" name="jurusan_akademik" class="form-control" placeholder="Contoh: Semua Jurusan / Tata Boga / Informatika" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Lokasi Penempatan <span>*</span></label>
                        <input type="text" name="lokasi_pekerjaan" class="form-control" maxlength="80" placeholder="Jl. Pangeran Sogiri No. 404 Tanah Baru" required>
                        <span class="helper-text">Maksimal 80 Karakter*</span>
                    </div>

                    <div class="form-group full-width">
                        <label>NIP HRD Pembuat <span>*</span></label>
                        <input type="text" name="nip" class="form-control" value="1005" placeholder="Contoh: 1005" required>
                    </div>

                    <!-- Benefit dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Benefit <span>*</span></label>
                        <textarea name="benefit" id="summernote-benefit" class="form-control" required></textarea>
                    </div>

                    <!-- Deskripsi dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Deskripsi Pekerjaan <span>*</span></label>
                        <textarea name="deskripsi_pekerjaan" id="summernote-deskripsi" class="form-control" required></textarea>
                    </div>

                    <!-- Tugas dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Tugas & Tanggung Jawab <span>*</span></label>
                        <textarea name="tugas_tanggung_jawab" id="summernote-tugas" class="form-control" required></textarea>
                    </div>

                    <!-- Kualifikasi dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Kualifikasi <span>*</span></label>
                        <textarea name="kualifikasi" id="summernote-kualifikasi" class="form-control" required></textarea>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <button type="submit" class="btn-save">💾 Simpan Lowongan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script Summernote -->
<script>
    $(document).ready(function() {
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
    });
</script>

<?php 
mysqli_close($conn);
include '../includes/footer_admin.php'; 
?>