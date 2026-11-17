<?php
// Sertakan header dan sidebar admin
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

// Koneksi database db_kebab_monster
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
$pesan = "";

// Proses Simpan Tata Cara & Gambar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tata_cara = mysqli_real_escape_string($conn, $_POST['tata_cara']);
    $gambar_sql = "";
    
    if (isset($_FILES['gambar_petunjuk']) && $_FILES['gambar_petunjuk']['error'] == 0) {
        $nama_file   = $_FILES['gambar_petunjuk']['name'];
        $tmp_file    = $_FILES['gambar_petunjuk']['tmp_name'];
        $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ekstensi, $allowed_ext)) {
            $nama_baru = "tatacara_" . $id_modul . "_" . time() . "." . $ekstensi;
            $path_tujuan = "../assets/img/tatacara/" . $nama_baru;
            
            if (!file_exists('../assets/img/tatacara/')) {
                mkdir('../assets/img/tatacara/', 0777, true);
            }
            
            if (move_uploaded_file($tmp_file, $path_tujuan)) {
                $gambar_sql = ", gambar_petunjuk = '$nama_baru'";
            }
        }
    }

    $query_update = "UPDATE modul_ujian SET tata_cara = '$tata_cara' $gambar_sql WHERE id_modul = $id_modul";
    
    if (mysqli_query($conn, $query_update)) {
        // Diarahkan kembali ke master_ujian.php setelah disimpan
        echo "<script>alert('Tata cara ujian berhasil disimpan!'); window.location='master_ujian.php';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal menyimpan tata cara: " . mysqli_error($conn) . "</div>";
    }
}
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
    .alert { padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
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
        Modul: <strong><?php echo htmlspecialchars($modul['nama_modul']); ?></strong>
    </div>

    <?php echo $pesan; ?>

    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Tulis Tata Cara / Petunjuk Pengerjaan Ujian</label>
            <textarea name="tata_cara" class="form-control" rows="8" placeholder="Tuliskan petunjuk atau ketentuan ujian di sini..."><?php echo htmlspecialchars($modul['tata_cara'] ?? ''); ?></textarea>
            <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">*Kotak teks bersih tanpa tombol editor yang rumit.</small>
        </div>

        <div class="form-group">
            <label>Upload Gambar Pendukung (Opsional)</label>
            <input type="file" name="gambar_petunjuk" class="form-control" accept="image/*" style="padding: 9px;">
            
            <?php if (!empty($modul['gambar_petunjuk'])): ?>
                <div>
                    <p style="font-size: 12px; font-weight: 700; margin-top: 10px;">Gambar Saat Ini:</p>
                    <img src="../assets/img/tatacara/<?php echo $modul['gambar_petunjuk']; ?>" class="preview-img" alt="Tata Cara">
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; align-items: center;">
            <button type="submit" class="btn-submit">💾 Simpan</button>
        </div>
    </form>
</div>

<?>
<?php 
mysqli_close($conn);
?>
        </main>
    </div>
</body>
</html>