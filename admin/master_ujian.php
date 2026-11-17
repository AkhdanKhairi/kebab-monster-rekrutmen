<?php
// Sertakan header admin
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

// Koneksi database db_kebab_monster
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan = "";

// 1. PROSES EDIT / UPDATE MODUL & GESER URUTAN OTOMATIS
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_modul'])) {
    $id_modul        = intval($_POST['id_modul']);
    $nama_modul      = mysqli_real_escape_string($conn, $_POST['nama_modul']);
    $kategori_posisi = mysqli_real_escape_string($conn, $_POST['kategori_posisi']);
    $durasi_menit    = intval($_POST['durasi_menit']);
    $jenis_modul     = mysqli_real_escape_string($conn, $_POST['jenis_modul']);
    $new_urutan      = intval($_POST['urutan']);

    // Ambil urutan lama modul ini
    $q_old = mysqli_query($conn, "SELECT urutan FROM modul_ujian WHERE id_modul = $id_modul");
    $d_old = mysqli_fetch_assoc($q_old);
    $old_urutan = intval($d_old['urutan']);

    // Cek total maksimum urutan yang ada
    $q_max = mysqli_query($conn, "SELECT MAX(urutan) as max_u FROM modul_ujian");
    $d_max = mysqli_fetch_assoc($q_max);
    $max_urutan = intval($d_max['max_u']);

    // Batasi agar urutan baru tidak keluar jalur
    if ($new_urutan < 1) $new_urutan = 1;
    if ($new_urutan > $max_urutan) $new_urutan = $max_urutan;

    // Logika Pergeseran Posisi Otomatis
    if ($new_urutan != $old_urutan) {
        if ($new_urutan > $old_urutan) {
            // Jika posisi diturunkan (misal 2 ke 4): item di antara old dan new digeser ke atas (-1)
            mysqli_query($conn, "UPDATE modul_ujian SET urutan = urutan - 1 WHERE urutan > $old_urutan AND urutan <= $new_urutan AND id_modul != $id_modul");
        } else {
            // Jika posisi dinaikkan (misal 4 ke 2): item di antara new dan old digeser ke bawah (+1)
            mysqli_query($conn, "UPDATE modul_ujian SET urutan = urutan + 1 WHERE urutan >= $new_urutan AND urutan < $old_urutan AND id_modul != $id_modul");
        }
    }

    // Update data modul beserta urutan barunya
    $query_update = "UPDATE modul_ujian SET 
                     nama_modul = '$nama_modul', 
                     kategori_posisi = '$kategori_posisi', 
                     durasi_menit = $durasi_menit, 
                     jenis_modul = '$jenis_modul',
                     urutan = $new_urutan
                     WHERE id_modul = $id_modul";
    
    if (mysqli_query($conn, $query_update)) {
        echo "<script>alert('Modul ujian berhasil diperbarui dan diurutkan ulang!'); window.location='master_ujian.php';</script>";
        exit();
    } else {
        $pesan = "<div class='alert error'>Gagal memperbarui modul: " . mysqli_error($conn) . "</div>";
    }
}

// 2. PROSES HAPUS MODUL
if (isset($_GET['hapus'])) {
    $id_modul = intval($_GET['hapus']);
    
    // Ambil urutan modul yang mau dihapus untuk merapikan sisa urutan di bawahnya
    $q_del = mysqli_query($conn, "SELECT urutan FROM modul_ujian WHERE id_modul = $id_modul");
    if ($q_del && mysqli_num_rows($q_del) > 0) {
        $d_del = mysqli_fetch_assoc($q_del);
        $del_urutan = intval($d_del['urutan']);

        // Hapus modul
        if (mysqli_query($conn, "DELETE FROM modul_ujian WHERE id_modul = $id_modul")) {
            // Geser modul di bawahnya naik 1 angka (-1) agar tidak ada celah kosong
            mysqli_query($conn, "UPDATE modul_ujian SET urutan = urutan - 1 WHERE urutan > $del_urutan");
            echo "<script>alert('Modul ujian berhasil dihapus dan urutan dirapikan!'); window.location='master_ujian.php';</script>";
            exit();
        }
    }
    echo "<script>alert('Gagal menghapus modul.'); window.location='master_ujian.php';</script>";
}

// Ambil daftar modul ujian diurutkan berdasarkan kolom urutan secara ASC
$query_modul = mysqli_query($conn, "SELECT * FROM modul_ujian ORDER BY urutan ASC, id_modul ASC");
?>

<style>
    .card-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #e0e0e0;
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
    .btn-tambah {
        background-color: #ffb300;
        color: #000;
        border: none;
        padding: 10px 18px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13.5px;
        cursor: pointer;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-tambah:hover {
        background-color: #ffa000;
    }

    /* Tabel Kustom */
    .table-responsive {
        overflow-x: auto;
    }
    .table-custom {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 14px;
    }
    .table-custom th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: 700;
        padding: 14px 15px;
        border-bottom: 2px solid #e0e0e0;
    }
    .table-custom td {
        padding: 14px 15px;
        border-bottom: 1px solid #eee;
        color: #444;
        vertical-align: middle;
    }
    .table-custom tr:hover {
        background-color: #fafafa;
    }

    /* Badge & Tombol Aksi */
    .badge-kategori {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e8f5e9;
        color: #1b5e20;
    }
    .badge-jenis {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #e3f2fd;
        color: #0d47a1;
    }
    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: opacity 0.2s;
    }
    .btn-tatacara { background-color: #e0f2f1; color: #00695c; border: 1px solid #b2dfdb; }
    .btn-soal { background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
    .btn-edit { background-color: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
    .btn-delete { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .btn-action:hover { opacity: 0.85; }

    /* Modal Popup Tengah */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 2000;
        justify-content: center;
        align-items: center;
    }
    .modal-box {
        background: #fff;
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        position: relative;
    }
    .modal-header {
        font-size: 18px;
        font-weight: 800;
        color: #004d40;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .close-btn {
        background: none; border: none; font-size: 20px; cursor: pointer; color: #777;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group label {
        display: block; font-size: 13px; font-weight: 700; color: #333; margin-bottom: 6px;
    }
    .form-control {
        width: 100%; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px; outline: none;
    }
    .form-control:focus { border-color: #1b5e20; }
    .btn-submit-modal {
        background: #1b5e20; color: #fff; width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; margin-top: 10px;
        transition: background 0.2s;
    }
    .btn-submit-modal:hover { background: #00332c; }
    .alert { padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; }
    .alert.error { background: #ffebee; color: #c62828; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title">
            <span>📝</span> Master Modul & Ujian Psikotes
        </div>
        <!-- Tombol menuju halaman tambah modul -->
        <a href="tambah_modul.php" class="btn-tambah">
            + Tambah Modul Ujian
        </a>
    </div>

    <?php echo $pesan; ?>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>Urutan</th>
                    <th>Nama Modul Ujian</th>
                    <th>Kategori Posisi</th>
                    <th>Durasi</th>
                    <th>Jenis Tes</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($query_modul && mysqli_num_rows($query_modul) > 0) {
                    while ($row = mysqli_fetch_assoc($query_modul)) {
                ?>
                <tr>
                    <td><strong>#<?php echo $row['urutan']; ?></strong></td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['nama_modul']); ?></strong>
                    </td>
                    <td>
                        <span class="badge-kategori"><?php echo htmlspecialchars($row['kategori_posisi']); ?></span>
                    </td>
                    <td><?php echo $row['durasi_menit']; ?> Menit</td>
                    <td>
                        <span class="badge-jenis"><?php echo ucwords(str_replace('_', ' ', $row['jenis_modul'])); ?></span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                            <!-- Tombol 1: Atur Tata Cara -->
                            <a href="tata_cara_ujian.php?id_modul=<?php echo $row['id_modul']; ?>" class="btn-action btn-tatacara" title="Atur Tata Cara Ujian">
                                📋 Tata Cara
                            </a>
                            <!-- Tombol 2: Kelola Soal -->
                            <a href="tambah_soal.php?id_modul=<?php echo $row['id_modul']; ?>" class="btn-action btn-soal" title="Kelola Daftar Soal">
                                📝 Kelola Soal
                            </a>
                            <!-- Tombol Edit Modal -->
                            <button type="button" class="btn-action btn-edit" onclick="openEditModal(
                                '<?php echo $row['id_modul']; ?>',
                                '<?php echo htmlspecialchars($row['nama_modul'], ENT_QUOTES); ?>',
                                '<?php echo $row['kategori_posisi']; ?>',
                                '<?php echo $row['durasi_menit']; ?>',
                                '<?php echo $row['jenis_modul']; ?>',
                                '<?php echo $row['urutan']; ?>'
                            )">
                                ✏️ Edit
                            </button>
                            <!-- Tombol Hapus -->
                            <a href="master_ujian.php?hapus=<?php echo $row['id_modul']; ?>" class="btn-action btn-delete" onclick="return confirm('Yakin ingin menghapus modul ini? Urutan modul di bawahnya akan otomatis dirapikan.')">
                                🗑️ Hapus
                            </a>
                        </div>
                    </td>
                </tr>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center; padding: 30px; color: #777;'>Belum ada modul ujian yang terdaftar di database. Silakan klik tombol 'Tambah Modul Ujian' di atas.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL POPUP EDIT MODUL -->
<div id="modalEdit" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <span>Edit Modul Ujian & Urutan</span>
            <button type="button" class="close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form action="" method="POST">
            <input type="hidden" name="edit_modul" value="1">
            <input type="hidden" id="edit_id_modul" name="id_modul">
            
            <div class="form-group">
                <label>Nama Modul Ujian</label>
                <input type="text" id="edit_nama_modul" name="nama_modul" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nomor Urutan Tampil</label>
                <input type="number" id="edit_urutan" name="urutan" class="form-control" min="1" required>
                <small style="color: #666; font-size: 11.5px;">*Mengubah nomor urut akan otomatis menggeser modul lain di sekitarnya.</small>
            </div>

            <div class="form-group">
                <label>Kategori Posisi Target</label>
                <select id="edit_kategori_posisi" name="kategori_posisi" class="form-control" required>
                    <option value="Semua">Semua Posisi (Umum)</option>
                    <option value="Staff">Staff</option>
                    <option value="Crew">Crew / Store</option>
                </select>
            </div>

            <div class="form-group">
                <label>Durasi Pengerjaan (Menit)</label>
                <input type="number" id="edit_durasi_menit" name="durasi_menit" class="form-control" min="1" required>
            </div>

            <div class="form-group">
                <label>Jenis Modul / Soal</label>
                <select id="edit_jenis_modul" name="jenis_modul" class="form-control" required>
                    <option value="pilihan_ganda">Pilihan Ganda</option>
                    <option value="benar_salah">Benar / Salah (Ya / Tidak)</option>
                    <option value="essay">Essay / Uraian</option>
                </select>
            </div>

            <button type="submit" class="btn-submit-modal">Simpan Perubahan & Urutkan</button>
        </form>
    </div>
</div>

<script>
    function openEditModal(id, nama, kategori, durasi, jenis, urutan) {
        document.getElementById('edit_id_modul').value = id;
        document.getElementById('edit_nama_modul').value = nama;
        document.getElementById('edit_kategori_posisi').value = kategori;
        document.getElementById('edit_durasi_menit').value = durasi;
        document.getElementById('edit_jenis_modul').value = jenis;
        document.getElementById('edit_urutan').value = urutan;
        
        document.getElementById('modalEdit').style.display = 'flex';
    }

    function closeEditModal() {
        document.getElementById('modalEdit').style.display = 'none';
    }

    window.onclick = function(event) {
        var modal = document.getElementById('modalEdit');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?>
<?php 
mysqli_close($conn);
?>
        </main>
    </div>
</body>
</html>