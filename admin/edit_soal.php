<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id_soal = intval($_GET['id_soal'] ?? 0);

$q_soal = mysqli_query($conn, "SELECT s.*, m.jenis_modul, m.nama_modul FROM soal_ujian s JOIN modul_ujian m ON s.id_modul = m.id_modul WHERE s.id_soal = $id_soal");
if (!$q_soal || mysqli_num_rows($q_soal) == 0) {
    echo "<script>alert('Soal ujian tidak ditemukan!'); window.location='master_ujian.php';</script>";
    exit();
}

$soal        = mysqli_fetch_assoc($q_soal);
$id_modul    = $soal['id_modul'];
$jenis_modul = $soal['jenis_modul'];
$pesan       = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_soal'])) {
    $pertanyaan      = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $pakai_alasan    = isset($_POST['pakai_alasan']) ? 1 : 0;
    $nama_gambar_sql = $soal['gambar_soal'];

    if (isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] == 0) {
        $nama_file   = $_FILES['gambar_soal']['name'];
        $tmp_file    = $_FILES['gambar_soal']['tmp_name'];
        $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ekstensi, $allowed_ext)) {
            $nama_baru   = "soal_" . $id_modul . "_" . time() . "." . $ekstensi;
            $path_tujuan = "../assets/img/soal/" . $nama_baru;
            
            if (!file_exists('../assets/img/soal/')) {
                mkdir('../assets/img/soal/', 0777, true);
            }
            
            if (move_uploaded_file($tmp_file, $path_tujuan)) {
                if (!empty($soal['gambar_soal']) && file_exists("../assets/img/soal/" . $soal['gambar_soal'])) {
                    unlink("../assets/img/soal/" . $soal['gambar_soal']);
                }
                $nama_gambar_sql = $nama_baru;
            }
        }
    }

    if ($jenis_modul == 'pilihan_ganda') {
        $opsi_a        = mysqli_real_escape_string($conn, $_POST['opsi_a']);
        $opsi_b        = mysqli_real_escape_string($conn, $_POST['opsi_b']);
        $opsi_c        = mysqli_real_escape_string($conn, $_POST['opsi_c']);
        $opsi_d        = mysqli_real_escape_string($conn, $_POST['opsi_d']);
        $kunci_jawaban = mysqli_real_escape_string($conn, $_POST['kunci_jawaban']);

        $query_update = "UPDATE soal_ujian SET pertanyaan = '$pertanyaan', gambar_soal = '$nama_gambar_sql', opsi_a = '$opsi_a', opsi_b = '$opsi_b', opsi_c = '$opsi_c', opsi_d = '$opsi_d', kunci_jawaban = '$kunci_jawaban', pakai_alasan = $pakai_alasan WHERE id_soal = $id_soal";
    } 
    elseif ($jenis_modul == 'benar_salah') {
        $kunci_jawaban = mysqli_real_escape_string($conn, $_POST['kunci_jawaban'] ?? '');
        
        $query_update = "UPDATE soal_ujian SET pertanyaan = '$pertanyaan', gambar_soal = '$nama_gambar_sql', kunci_jawaban = '$kunci_jawaban', pakai_alasan = $pakai_alasan WHERE id_soal = $id_soal";
    } 
    else {
        $query_update = "UPDATE soal_ujian SET pertanyaan = '$pertanyaan', gambar_soal = '$nama_gambar_sql', pakai_alasan = $pakai_alasan WHERE id_soal = $id_soal";
    }

    if (mysqli_query($conn, $query_update)) {
        echo "<script>alert('Soal berhasil diperbarui!'); window.location='tambah_soal.php?id_modul=$id_modul';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal memperbarui soal: " . mysqli_error($conn) . "</div>";
    }
}
?>

<style>
    .card-container { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e0e0e0; max-width: 900px; margin: 0 auto; }
    .page-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f1f3f5; padding-bottom: 15px; }
    .page-title { font-size: 19px; font-weight: 800; color: #004d40; display: flex; align-items: center; gap: 10px; }
    .modul-badge-wrapper { display: flex; gap: 10px; margin-bottom: 25px; }
    .modul-badge { background: #e8f5e9; color: #1b5e20; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .jenis-badge { background: #e3f2fd; color: #0d47a1; padding: 8px 15px; border-radius: 20px; font-size: 13px; font-weight: 700; }
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 13.5px; font-weight: 700; color: #333; margin-bottom: 7px; }
    .form-control { width: 100%; padding: 11px 14px; border: 1px solid #ccc; border-radius: 8px; font-size: 14px; outline: none; }
    .form-control:focus { border-color: #1b5e20; box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1); }
    .grid-opsi { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .checkbox-wrapper { display: flex; align-items: center; gap: 10px; background: #f1f8e9; padding: 12px 15px; border-radius: 8px; border: 1px solid #dcedc8; margin-bottom: 20px; }
    .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; accent-color: #1b5e20; cursor: pointer; }
    .checkbox-wrapper label { margin-bottom: 0; cursor: pointer; font-weight: 700; color: #2e7d32; font-size: 13.5px; }
    .btn-submit { background: #1b5e20; color: #fff; padding: 12px 25px; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 14.5px; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s; }
    .btn-submit:hover { background: #00332c; }
    .btn-back { background: #f1f3f5; color: #333; text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; font-size: 13.5px; display: inline-flex; align-items: center; gap: 5px; transition: background 0.2s; }
    .btn-back:hover { background: #e2e6ea; }
    .alert { padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title"><span>✏️</span> Edit Soal Ujian</div>
        <a href="tambah_soal.php?id_modul=<?php echo $id_modul; ?>" class="btn-back">← Kembali</a>
    </div>

    <div class="modul-badge-wrapper">
        <div class="modul-badge">Modul: <strong><?php echo htmlspecialchars($soal['nama_modul']); ?></strong></div>
        <div class="jenis-badge">Jenis Tes: <strong><?php echo ucwords(str_replace('_', ' ', $jenis_modul)); ?></strong></div>
    </div>

    <?php echo $pesan; ?>

    <form action="" method="POST" enctype="multipart/form-data" style="background: #f9fbe7; border: 1.5px solid #c5e1a5; padding: 25px; border-radius: 12px;">
        <input type="hidden" name="update_soal" value="1">

        <div class="form-group">
            <label>Pertanyaan / Soal</label>
            <textarea name="pertanyaan" class="form-control" rows="4" required><?php echo htmlspecialchars($soal['pertanyaan']); ?></textarea>
        </div>

        <div class="form-group">
            <label>Gambar Soal (Opsional)</label>
            <?php if (!empty($soal['gambar_soal'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../assets/img/soal/<?php echo htmlspecialchars($soal['gambar_soal']); ?>" alt="Gambar" style="max-height: 120px; border-radius: 6px; border: 1px solid #ccc;">
                    <p style="font-size: 12px; color: #666; margin-top: 4px;">Gambar saat ini</p>
                </div>
            <?php endif; ?>
            <input type="file" name="gambar_soal" class="form-control" accept="image/*" style="padding: 9px;">
        </div>

        <?php if ($jenis_modul == 'pilihan_ganda'): ?>
            <div class="grid-opsi">
                <div class="form-group"><label>Pilihan A</label><input type="text" name="opsi_a" class="form-control" value="<?php echo htmlspecialchars($soal['opsi_a']); ?>" required></div>
                <div class="form-group"><label>Pilihan B</label><input type="text" name="opsi_b" class="form-control" value="<?php echo htmlspecialchars($soal['opsi_b']); ?>" required></div>
                <div class="form-group"><label>Pilihan C</label><input type="text" name="opsi_c" class="form-control" value="<?php echo htmlspecialchars($soal['opsi_c']); ?>" required></div>
                <div class="form-group"><label>Pilihan D</label><input type="text" name="opsi_d" class="form-control" value="<?php echo htmlspecialchars($soal['opsi_d']); ?>" required></div>
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label>Kunci Jawaban Benar</label>
                <select name="kunci_jawaban" class="form-control" required style="max-width: 250px;">
                    <option value="A" <?php if($soal['kunci_jawaban'] == 'A') echo 'selected'; ?>>A</option>
                    <option value="B" <?php if($soal['kunci_jawaban'] == 'B') echo 'selected'; ?>>B</option>
                    <option value="C" <?php if($soal['kunci_jawaban'] == 'C') echo 'selected'; ?>>C</option>
                    <option value="D" <?php if($soal['kunci_jawaban'] == 'D') echo 'selected'; ?>>D</option>
                </select>
            </div>
        <?php elseif ($jenis_modul == 'benar_salah'): ?>
            <div class="form-group" style="max-width: 300px;">
                <label>Kunci Jawaban (Opsional)</label>
                <select name="kunci_jawaban" class="form-control">
                    <option value="">-- Tanpa Kunci / Opsional --</option>
                    <option value="Benar" <?php if($soal['kunci_jawaban'] == 'Benar') echo 'selected'; ?>>Benar</option>
                    <option value="Salah" <?php if($soal['kunci_jawaban'] == 'Salah') echo 'selected'; ?>>Salah</option>
                </select>
            </div>
        <?php else: ?>
            <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; color: #0d47a1; font-size: 13.5px;">
                💡 **Mode Soal Essay**: Pelamar akan menjawab dalam bentuk teks uraian panjang. Penilaian manual oleh admin.
            </div>
        <?php endif; ?>

        <div class="checkbox-wrapper" style="margin-top: 20px;">
            <input type="checkbox" id="pakai_alasan" name="pakai_alasan" value="1" <?php if(isset($soal['pakai_alasan']) && $soal['pakai_alasan'] == 1) echo 'checked'; ?>>
            <label for="pakai_alasan">Sertakan kolom alasan untuk pelamar</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-submit">💾 Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer_admin.php'; 
?>