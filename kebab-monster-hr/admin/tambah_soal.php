<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$id_modul = intval($_GET['id_modul'] ?? 0);

// Ambil data modul
$q_modul = mysqli_query($conn, "SELECT * FROM modul_ujian WHERE id_modul = $id_modul");
if (!$q_modul || mysqli_num_rows($q_modul) == 0) {
    echo "<script>alert('Modul ujian tidak ditemukan!'); window.location='master_ujian.php';</script>";
    exit();
}
$modul = mysqli_fetch_assoc($q_modul);
$jenis_modul = $modul['jenis_modul'];
$pesan = "";

// PROSES SIMPAN SOAL BARU
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_soal'])) {
    $pertanyaan      = mysqli_real_escape_string($conn, $_POST['pertanyaan']);
    $pakai_alasan    = isset($_POST['pakai_alasan']) ? 1 : 0;
    $nama_gambar_sql = "";

    if (isset($_FILES['gambar_soal']) && $_FILES['gambar_soal']['error'] == 0) {
        $nama_file   = $_FILES['gambar_soal']['name'];
        $tmp_file    = $_FILES['gambar_soal']['tmp_name'];
        $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ekstensi, $allowed_ext)) {
            $nama_baru = "soal_" . $id_modul . "_" . time() . "." . $ekstensi;
            $path_tujuan = "../assets/img/soal/" . $nama_baru;
            
            if (!file_exists('../assets/img/soal/')) {
                mkdir('../assets/img/soal/', 0777, true);
            }
            
            if (move_uploaded_file($tmp_file, $path_tujuan)) {
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

        $query_insert = "INSERT INTO soal_ujian (id_modul, pertanyaan, gambar_soal, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, pakai_alasan) 
                         VALUES ($id_modul, '$pertanyaan', '$nama_gambar_sql', '$opsi_a', '$opsi_b', '$opsi_c', '$opsi_d', '$kunci_jawaban', $pakai_alasan)";
    } 
    elseif ($jenis_modul == 'benar_salah') {
        $kunci_jawaban = mysqli_real_escape_string($conn, $_POST['kunci_jawaban'] ?? '');
        
        $query_insert = "INSERT INTO soal_ujian (id_modul, pertanyaan, gambar_soal, kunci_jawaban, pakai_alasan) 
                         VALUES ($id_modul, '$pertanyaan', '$nama_gambar_sql', '$kunci_jawaban', $pakai_alasan)";
    } 
    else { // Essay
        $query_insert = "INSERT INTO soal_ujian (id_modul, pertanyaan, gambar_soal, pakai_alasan) 
                         VALUES ($id_modul, '$pertanyaan', '$nama_gambar_sql', $pakai_alasan)";
    }

    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Soal berhasil ditambahkan!'); window.location='tambah_soal.php?id_modul=$id_modul';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal menyimpan soal: " . mysqli_error($conn) . "</div>";
    }
}

// PROSES HAPUS SOAL
if (isset($_GET['hapus'])) {
    $id_soal = intval($_GET['hapus']);
    
    $q_img = mysqli_query($conn, "SELECT gambar_soal FROM soal_ujian WHERE id_soal = $id_soal");
    if ($d_img = mysqli_fetch_assoc($q_img)) {
        if (!empty($d_img['gambar_soal']) && file_exists("../assets/img/soal/" . $d_img['gambar_soal'])) {
            unlink("../assets/img/soal/" . $d_img['gambar_soal']);
        }
    }

    if (mysqli_query($conn, "DELETE FROM soal_ujian WHERE id_soal = $id_soal")) {
        echo "<script>alert('Soal berhasil dihapus!'); window.location='tambah_soal.php?id_modul=$id_modul';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menghapus soal.'); window.location='tambah_soal.php?id_modul=$id_modul';</script>";
    }
}

$query_daftar_soal = mysqli_query($conn, "SELECT * FROM soal_ujian WHERE id_modul = $id_modul ORDER BY id_soal ASC");
?>

<style>
    .card-container { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e0e0e0; max-width: 950px; margin: 0 auto; }
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
    .table-soal { width: 100%; border-collapse: collapse; margin-top: 35px; font-size: 13.5px; }
    .table-soal th { background-color: #f8f9fa; color: #333; padding: 12px 14px; border-bottom: 2px solid #ddd; text-align: left; }
    .table-soal td { padding: 12px 14px; border-bottom: 1px solid #eee; color: #444; vertical-align: top; }
    .btn-edit-soal { background: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; margin-bottom: 4px; }
    .btn-edit-soal:hover { background: #bbdefb; }
    .btn-delete-soal { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-block; }
    .btn-delete-soal:hover { background: #ffcdd2; }
    .alert { padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title"><span>📝</span> Kelola Soal & Pertanyaan Ujian</div>
        <a href="master_ujian.php" class="btn-back">← Kembali ke Master Ujian</a>
    </div>

    <div class="modul-badge-wrapper">
        <div class="modul-badge">Modul: <strong><?php echo htmlspecialchars($modul['nama_modul']); ?></strong></div>
        <div class="jenis-badge">Jenis Tes: <strong><?php echo ucwords(str_replace('_', ' ', $jenis_modul)); ?></strong></div>
    </div>

    <?php echo $pesan; ?>

    <!-- FORM TAMBAH SOAL -->
    <form action="" method="POST" enctype="multipart/form-data" style="background: #f9fbe7; border: 1.5px solid #c5e1a5; padding: 25px; border-radius: 12px;">
        <input type="hidden" name="simpan_soal" value="1">

        <div class="form-group">
            <label>Pertanyaan / Soal</label>
            <textarea name="pertanyaan" class="form-control" rows="4" placeholder="Tuliskan bunyi pertanyaan di sini..." required></textarea>
        </div>

        <div class="form-group">
            <label>Gambar Soal (Opsional)</label>
            <input type="file" name="gambar_soal" class="form-control" accept="image/*" style="padding: 9px;">
        </div>

        <?php if ($jenis_modul == 'pilihan_ganda'): ?>
            <div class="grid-opsi">
                <div class="form-group"><label>Pilihan A</label><input type="text" name="opsi_a" class="form-control" placeholder="Jawaban A" required></div>
                <div class="form-group"><label>Pilihan B</label><input type="text" name="opsi_b" class="form-control" placeholder="Jawaban B" required></div>
                <div class="form-group"><label>Pilihan C</label><input type="text" name="opsi_c" class="form-control" placeholder="Jawaban C" required></div>
                <div class="form-group"><label>Pilihan D</label><input type="text" name="opsi_d" class="form-control" placeholder="Jawaban D" required></div>
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label>Kunci Jawaban Benar</label>
                <select name="kunci_jawaban" class="form-control" required style="max-width: 250px;">
                    <option value="">-- Pilih Kunci Jawaban --</option>
                    <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
                </select>
            </div>
        <?php elseif ($jenis_modul == 'benar_salah'): ?>
            <div class="form-group" style="max-width: 300px;">
                <label>Kunci Jawaban (Opsional)</label>
                <select name="kunci_jawaban" class="form-control">
                    <option value="">-- Tanpa Kunci / Opsional --</option>
                    <option value="Benar">Benar</option>
                    <option value="Salah">Salah</option>
                </select>
            </div>
        <?php else: ?>
            <div style="background: #e3f2fd; border: 1px solid #bbdefb; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; color: #0d47a1; font-size: 13.5px;">
                💡 **Mode Soal Essay**: Pelamar akan menjawab dalam bentuk teks uraian panjang. Penilaian untuk modul ini bersifat manual oleh admin.
            </div>
        <?php endif; ?>

        <div class="checkbox-wrapper" style="margin-top: 20px;">
            <input type="checkbox" id="pakai_alasan" name="pakai_alasan" value="1">
            <label for="pakai_alasan">Sertakan kolom alasan untuk pelamar (Pelamar akan melihat kolom teks alasan saat ujian)</label>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn-submit">➕ Tambahkan Soal Ini</button>
        </div>
    </form>

    <!-- TABEL DAFTAR SOAL -->
    <h3 style="margin-top: 35px; font-size: 16px; font-weight: 800; color: #004d40;">Daftar Soal pada Modul Ini</h3>
    
    <div style="overflow-x: auto;">
        <table class="table-soal">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Pertanyaan & Gambar</th>
                    <?php if ($jenis_modul == 'pilihan_ganda' || $jenis_modul == 'benar_salah'): ?>
                        <th style="width: 100px;">Kunci</th>
                    <?php endif; ?>
                    <th style="width: 110px; text-align: center;">Pakai Alasan?</th>
                    <th style="width: 100px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                if ($query_daftar_soal && mysqli_num_rows($query_daftar_soal) > 0) {
                    while ($row = mysqli_fetch_assoc($query_daftar_soal)) {
                ?>
                <tr>
                    <td><strong><?php echo $no++; ?></strong></td>
                    <td>
                        <?php echo nl2br(htmlspecialchars($row['pertanyaan'])); ?>
                        <?php if (!empty($row['gambar_soal'])): ?>
                            <div style="margin-top: 5px;"><img src="../assets/img/soal/<?php echo htmlspecialchars($row['gambar_soal']); ?>" style="max-height: 60px; border-radius: 4px; border: 1px solid #ddd;"></div>
                        <?php endif; ?>
                    </td>
                    <?php if ($jenis_modul == 'pilihan_ganda' || $jenis_modul == 'benar_salah'): ?>
                        <td><strong><span style="color: #1b5e20;"><?php echo htmlspecialchars($row['kunci_jawaban'] ?? '-'); ?></span></strong></td>
                    <?php endif; ?>
                    <td style="text-align: center;">
                        <?php if (isset($row['pakai_alasan']) && $row['pakai_alasan'] == 1): ?>
                            <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700;">Ya</span>
                        <?php else: ?>
                            <span style="background: #f5f5f5; color: #777; padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700;">Tidak</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: center;">
                        <?php 
                            // Deteksi otomatis kolom ID (id_soal atau id)
                            $id_unik = isset($row['id_soal']) ? $row['id_soal'] : (isset($row['id']) ? $row['id'] : 0); 
                        ?>
                        <a href="edit_soal.php?id_soal=<?php echo $id_unik; ?>" class="btn-edit-soal">Edit</a><br>
                        <a href="tambah_soal.php?id_modul=<?php echo $id_modul; ?>&hapus=<?php echo $id_unik; ?>" class="btn-delete-soal" onclick="return confirm('Yakin ingin menghapus soal ini?')">Hapus</a>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align: center; padding: 25px; color: #777;'>Belum ada soal pada modul ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer_admin.php'; 
?>