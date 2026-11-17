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

$pesan = "";

// Proses Simpan Data Modul Baru (Urutan Otomatis)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_modul      = mysqli_real_escape_string($conn, $_POST['nama_modul']);
    $kategori_posisi = mysqli_real_escape_string($conn, $_POST['kategori_posisi']);
    $durasi_menit    = intval($_POST['durasi_menit']);
    $jenis_modul     = mysqli_real_escape_string($conn, $_POST['jenis_modul']);

    // Cari nomor urut terakhir secara otomatis, lalu tambah 1
    $q_urutan = mysqli_query($conn, "SELECT IFNULL(MAX(urutan), 0) + 1 AS next_urutan FROM modul_ujian");
    $d_urutan = mysqli_fetch_assoc($q_urutan);
    $urutan   = intval($d_urutan['next_urutan']);

    $query_insert = "INSERT INTO modul_ujian (nama_modul, kategori_posisi, durasi_menit, jenis_modul, urutan) 
                     VALUES ('$nama_modul', '$kategori_posisi', $durasi_menit, '$jenis_modul', $urutan)";
    
    if (mysqli_query($conn, $query_insert)) {
        echo "<script>alert('Modul ujian baru berhasil ditambahkan dengan urutan otomatis ke-$urutan!'); window.location='master_ujian.php';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal menambahkan modul: " . mysqli_error($conn) . "</div>";
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
        max-width: 700px;
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
        font-size: 20px;
        font-weight: 800;
        color: #004d40;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block; 
        font-size: 14px; 
        font-weight: 700; 
        color: #333; 
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%; 
        padding: 12px 15px; 
        border: 1px solid #ccc; 
        border-radius: 8px; 
        font-size: 14px; 
        outline: none;
        transition: border-color 0.2s;
    }
    .form-control:focus { 
        border-color: #1b5e20; 
        box-shadow: 0 0 0 3px rgba(27, 94, 32, 0.1);
    }
    .btn-submit {
        background: #1b5e20; 
        color: #fff; 
        padding: 12px 25px; 
        border: none; 
        border-radius: 8px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 15px;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover { 
        background: #00332c; 
    }
    .btn-back {
        background: #f1f3f5;
        color: #333;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 13.5px;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .btn-back:hover {
        background: #e2e6ea;
    }
    .alert { padding: 12px; border-radius: 8px; font-size: 13.5px; margin-bottom: 20px; }
    .alert.error { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title">
            <span>📝</span> Tambah Modul Ujian Baru
        </div>
        <a href="master_ujian.php" class="btn-back">← Kembali</a>
    </div>

    <?php echo $pesan; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Nama Modul Ujian</label>
            <input type="text" name="nama_modul" class="form-control" placeholder="Contoh: Tes Logika & Penalaran Dasar" required>
        </div>

        <div class="form-group">
            <label>Kategori Posisi Target</label>
            <select name="kategori_posisi" class="form-control" required>
                <option value="Semua">Semua Posisi (Umum)</option>
                <option value="Staff">Staff</option>
                <option value="Crew">Crew / Store</option>
            </select>
        </div>

        <div class="form-group">
            <label>Durasi Pengerjaan (Menit)</label>
            <input type="number" name="durasi_menit" class="form-control" placeholder="Contoh: 20" min="1" required>
        </div>

        <div class="form-group">
            <label>Jenis Modul / Soal</label>
            <select name="jenis_modul" class="form-control" required>
                <option value="pilihan_ganda">Pilihan Ganda</option>
                <option value="benar_salah">Benar / Salah (Ya / Tidak)</option>
                <option value="essay">Essay / Uraian</option>
            </select>
        </div>

        <!-- Kolom nomor urut sudah dihapus karena kini diatur otomatis oleh sistem -->

        <div style="margin-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
            <a href="master_ujian.php" class="btn-back">Batal</a>
            <button type="submit" class="btn-submit">💾 Simpan Modul</button>
        </div>
    </form>
</div>

<?php 
mysqli_close($conn);
?>
        </main>
    </div>
</body>
</html>