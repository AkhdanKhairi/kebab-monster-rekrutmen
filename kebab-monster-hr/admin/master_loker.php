<?php
include '../includes/header_admin.php';
include '../includes/sidebar_admin.php';

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Proses Hapus Data Lowongan dari tabel 'lowongan'
if (isset($_GET['hapus'])) {
    $id_lowongan = intval($_GET['hapus']);
    if (mysqli_query($conn, "DELETE FROM lowongan WHERE id_lowongan = $id_lowongan")) {
        echo "<script>alert('Data lowongan berhasil dihapus!'); window.location='master_loker.php';</script>";
        exit();
    } else {
        echo "<script>alert('Gagal menghapus data.'); window.location='master_loker.php';</script>";
    }
}

// Ambil daftar dari tabel 'lowongan'
$query_loker = mysqli_query($conn, "SELECT * FROM lowongan ORDER BY id_lowongan DESC");
?>

<style>
    .card-container { background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e0e0e0; max-width: 1200px; margin: 0 auto; }
    .page-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #f1f3f5; padding-bottom: 15px; }
    .page-title { font-size: 20px; font-weight: 800; color: #004d40; display: flex; align-items: center; gap: 10px; }
    .btn-tambah { background: #1b5e20; color: #fff; padding: 10px 20px; border-radius: 8px; font-weight: 700; text-decoration: none; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); transition: background 0.2s; }
    .btn-tambah:hover { background: #00332c; }
    
    .table-loker { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13.5px; }
    .table-loker th { background-color: #f8f9fa; color: #333; padding: 14px 15px; border-bottom: 2px solid #ddd; text-align: left; font-weight: 700; }
    .table-loker td { padding: 14px 15px; border-bottom: 1px solid #eee; color: #444; vertical-align: middle; }
    
    .badge-status { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; background: #e8f5e9; color: #2e7d32; }
    
    .action-buttons { display: flex; gap: 6px; align-items: center; }
    .btn-action { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; border: 1px solid transparent; }
    .btn-edit { background: #fff3e0; color: #ef6c00; border-color: #ffe0b2; }
    .btn-edit:hover { background: #ffe0b2; }
    .btn-hapus { background: #ffebee; color: #c62828; border-color: #ffcdd2; }
    .btn-hapus:hover { background: #ffcdd2; }
</style>

<div class="card-container">
    <div class="page-header-flex">
        <div class="page-title"><span>💼</span> Master Kelola Karir & Lowongan</div>
        <a href="tambah_loker.php" class="btn-tambah">+ Tambah Data Karir</a>
    </div>

    <div style="overflow-x: auto;">
        <table class="table-loker">
            <thead>
                <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Posisi Pekerjaan</th>
                    <th>Divisi</th>
                    <th style="width: 140px;">Shift / Waktu</th>
                    <th style="width: 130px;">Tgl Dibuat</th>
                    <th style="width: 180px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?>
                <?php 
                if ($query_loker && mysqli_num_rows($query_loker) > 0) {
                    while ($row = mysqli_fetch_assoc($query_loker)) {
                ?>
                <tr>
                    <td><strong>#<?php echo $row['id_lowongan']; ?></strong></td>
                    <td><strong><?php echo htmlspecialchars($row['posisi_pekerjaan']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['divisi']); ?></td>
                    <td><?php echo htmlspecialchars($row['shift'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['tgl_dibuat'] ?? '-'); ?></td>
                    <td style="text-align: center;">
                        <div class="action-buttons" style="justify-content: center;">
                            <a href="edit_loker.php?id=<?php echo $row['id_lowongan']; ?>" class="btn-action btn-edit">✏️ Edit</a>
                            <a href="master_loker.php?hapus=<?php echo $row['id_lowongan']; ?>" class="btn-action btn-hapus" onclick="return confirm('Yakin ingin menghapus lowongan ini?')">🗑️ Hapus</a>
                        </div>
                    </td>
                </tr>
                <?>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align: center; padding: 30px; color: #777;'>Belum ada data lowongan pekerjaan.</td></tr>";
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