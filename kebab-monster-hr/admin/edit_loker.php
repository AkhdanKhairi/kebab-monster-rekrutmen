<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id_lowongan = intval($_GET['id'] ?? 0);

// Ambil data dari tabel 'lowongan'
$q_loker = mysqli_query($conn, "SELECT * FROM lowongan WHERE id_lowongan = $id_lowongan");
if (!$q_loker || mysqli_num_rows($q_loker) == 0) {
    echo "<script>alert('Data lowongan tidak ditemukan!'); window.location='master_loker.php';</script>";
    exit();
}
$loker = mysqli_fetch_assoc($q_loker);
$pesan = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_loker'])) {
    $divisi               = mysqli_real_escape_string($conn, $_POST['divisi']);
    $posisi_pekerjaan     = mysqli_real_escape_string($conn, $_POST['posisi_pekerjaan']);
    $tgl_dibuat           = mysqli_real_escape_string($conn, $_POST['tgl_dibuat']);
    $shift                = mysqli_real_escape_string($conn, $_POST['shift']);
    $jurusan_akademik     = mysqli_real_escape_string($conn, $_POST['jurusan_akademik']);
    $lokasi_pekerjaan     = mysqli_real_escape_string($conn, $_POST['lokasi_pekerjaan']);
    $nip                  = mysqli_real_escape_string($conn, $_POST['nip']);
    
    // Data teks panjang dari Summernote
    $benefit              = mysqli_real_escape_string($conn, $_POST['benefit']);
    $deskripsi_pekerjaan  = mysqli_real_escape_string($conn, $_POST['deskripsi_pekerjaan']);
    $tugas_tanggung_jawab = mysqli_real_escape_string($conn, $_POST['tugas_tanggung_jawab']);
    $kualifikasi          = mysqli_real_escape_string($conn, $_POST['kualifikasi']);

    $query_update = "UPDATE lowongan SET 
                     divisi = '$divisi', 
                     posisi_pekerjaan = '$posisi_pekerjaan', 
                     tgl_dibuat = '$tgl_dibuat', 
                     shift = '$shift', 
                     jurusan_akademik = '$jurusan_akademik', 
                     lokasi_pekerjaan = '$lokasi_pekerjaan', 
                     deskripsi_pekerjaan = '$deskripsi_pekerjaan', 
                     tugas_tanggung_jawab = '$tugas_tanggung_jawab', 
                     kualifikasi = '$kualifikasi', 
                     nip = '$nip', 
                     benefit = '$benefit' 
                     WHERE id_lowongan = $id_lowongan";

    if (mysqli_query($conn, $query_update)) {
        echo "<script>alert('Data lowongan berhasil diperbarui!'); window.location='master_loker.php';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal memperbarui data: " . mysqli_error($conn) . "</div>";
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

    <h1 class="page-main-title">Edit Data Karir</h1>

    <div class="karir-container">
        <div class="karir-header-bar">
            <span>🏢</span> Edit Lowongan Pekerjaan #<?php echo $loker['id_lowongan']; ?>
        </div>

        <div class="karir-form-body">
            <?php echo $pesan; ?>

            <form action="" method="POST">
                <input type="hidden" name="update_loker" value="1">

                <div class="form-grid-2">
                    <!-- Divisi Dropdown -->
                    <div class="form-group">
                        <label>Divisi <span>*</span></label>
                        <select name="divisi" class="form-control" required>
                            <option value="">-- Pilih Divisi Pekerjaan --</option>
                            <option value="Operasional" <?php if($loker['divisi'] == 'Operasional') echo 'selected'; ?>>Operasional</option>
                            <option value="Finance" <?php if($loker['divisi'] == 'Finance') echo 'selected'; ?>>Finance</option>
                            <option value="HRDGA" <?php if($loker['divisi'] == 'HRDGA') echo 'selected'; ?>>HRDGA</option>
                            <option value="BOD" <?php if($loker['divisi'] == 'BOD') echo 'selected'; ?>>BOD</option>
                            <option value="Internal Audit" <?php if($loker['divisi'] == 'Internal Audit') echo 'selected'; ?>>Internal Audit</option>
                        </select>
                    </div>

                    <!-- Posisi Pekerjaan Text Input -->
                    <div class="form-group">
                        <label>Posisi Pekerjaan <span>*</span></label>
                        <input type="text" name="posisi_pekerjaan" class="form-control" value="<?php echo htmlspecialchars($loker['posisi_pekerjaan']); ?>" placeholder="Contoh: SMC Manager / Crew Outlet / Business Intelligent" required>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Dibuat <span>*</span></label>
                        <input type="date" name="tgl_dibuat" class="form-control" value="<?php echo htmlspecialchars($loker['tgl_dibuat']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Shift <span>*</span></label>
                        <select name="shift" class="form-control" required>
                            <option value="">-- Pilih Shift Kerja --</option>
                            <option value="Full Time" <?php if($loker['shift'] == 'Full Time') echo 'selected'; ?>>Full Time</option>
                            <option value="Shift Pagi" <?php if($loker['shift'] == 'Shift Pagi') echo 'selected'; ?>>Shift Pagi</option>
                            <option value="Shift Malam" <?php if($loker['shift'] == 'Shift Malam') echo 'selected'; ?>>Shift Malam</option>
                            <option value="Part Time" <?php if($loker['shift'] == 'Part Time') echo 'selected'; ?>>Part Time</option>
                        </select>
                    </div>

                    <div class="form-group full-width">
                        <label>Jurusan Akademik <span>*</span></label>
                        <input type="text" name="jurusan_akademik" class="form-control" value="<?php echo htmlspecialchars($loker['jurusan_akademik']); ?>" required>
                    </div>

                    <div class="form-group full-width">
                        <label>Lokasi Penempatan <span>*</span></label>
                        <input type="text" name="lokasi_pekerjaan" class="form-control" maxlength="80" value="<?php echo htmlspecialchars($loker['lokasi_pekerjaan']); ?>" required>
                        <span class="helper-text">Maksimal 80 Karakter*</span>
                    </div>

                    <div class="form-group full-width">
                        <label>NIP HRD Pembuat <span>*</span></label>
                        <input type="text" name="nip" class="form-control" value="<?php echo htmlspecialchars($loker['nip']); ?>" required>
                    </div>

                    <!-- Benefit dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Benefit <span>*</span></label>
                        <textarea name="benefit" id="summernote-benefit" class="form-control" required><?php echo $loker['benefit']; ?></textarea>
                    </div>

                    <!-- Deskripsi dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Deskripsi Pekerjaan <span>*</span></label>
                        <textarea name="deskripsi_pekerjaan" id="summernote-deskripsi" class="form-control" required><?php echo $loker['deskripsi_pekerjaan']; ?></textarea>
                    </div>

                    <!-- Tugas dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Tugas & Tanggung Jawab <span>*</span></label>
                        <textarea name="tugas_tanggung_jawab" id="summernote-tugas" class="form-control" required><?php echo $loker['tugas_tanggung_jawab']; ?></textarea>
                    </div>

                    <!-- Kualifikasi dengan Summernote -->
                    <div class="form-group full-width">
                        <label>Kualifikasi <span>*</span></label>
                        <textarea name="kualifikasi" id="summernote-kualifikasi" class="form-control" required><?php echo $loker['kualifikasi']; ?></textarea>
                    </div>
                </div>

                <div class="btn-wrapper">
                    <button type="submit" class="btn-save">💾 Simpan Perubahan</button>
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