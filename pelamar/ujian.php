<?php
session_start();

// Validasi akses khusus pelamar
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

// Koneksi Database
$host = 'localhost'; 
$user = 'root'; 
$pass = ''; 
$db   = 'db_kebab_monster';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


// 1. Ambil data profil pelamar
$query_pelamar = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$profile = mysqli_fetch_assoc($query_pelamar);

// 2. Ambil data lamaran untuk mengetahui posisi yang dilamar (Crew atau Staff)
$query_lamaran = mysqli_query($conn, "
    SELECT l.*, lw.posisi_pekerjaan, lw.divisi 
    FROM lamaran l 
    JOIN lowongan lw ON l.id_lowongan = lw.id_lowongan 
    WHERE l.id_pelamar = $id_pelamar 
    ORDER BY l.id_lamaran DESC LIMIT 1
");
$data_lamaran = mysqli_fetch_assoc($query_lamaran);

// Tentukan kategori posisi pelamar (Default 'Semua' jika belum melamar)
$kategori_posisi = 'Semua';
if ($data_lamaran) {
    $posisi_lower = strtolower($data_lamaran['posisi_pekerjaan']);
    if (strpos($posisi_lower, 'crew') !== false || strpos($posisi_lower, 'store') !== false) {
        $kategori_posisi = 'Crew';
    } else {
        $kategori_posisi = 'Staff';
    }
}

// 3. Ambil daftar modul ujian yang sesuai dengan kategori posisi atau 'Semua'
$query_modul = mysqli_query($conn, "
    SELECT * FROM modul_ujian 
    WHERE kategori_posisi = '$kategori_posisi' OR kategori_posisi = 'Semua' 
    ORDER BY urutan ASC
");

// Masukkan modul ke dalam array untuk memudahkan pengecekan status beruntun
$daftar_modul = [];
while ($row = mysqli_fetch_assoc($query_modul)) {
    $id_modul = $row['id_modul'];
    $q_riwayat = mysqli_query($conn, "SELECT * FROM riwayat_ujian_pelamar WHERE id_pelamar = $id_pelamar AND id_modul = $id_modul");
    $riwayat = mysqli_fetch_assoc($q_riwayat);
    
    $row['status_pengerjaan'] = $riwayat['status_pengerjaan'] ?? 'Belum Mulai';
    $daftar_modul[] = $row;
}

include '../includes/header_pelamar.php';
?>

<style>
    .ujian-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .profile-sidebar-card {
        width: 320px;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 25px 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        text-align: center;
        height: fit-content;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: #ffe0b2;
        border-radius: 50%;
        margin: 0 auto 15px auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        overflow: hidden;
        border: 2px solid #1b5e20;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; }
    
    .modul-sidebar-list { list-style: none; padding: 0; margin: 0; text-align: left; }
    .modul-sidebar-list li {
        font-size: 13px;
        font-weight: 600;
        padding: 6px 0;
        border-bottom: 1px dashed #eee;
        display: flex;
        justify-content: space-between;
        color: #444;
    }

    .ujian-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .ujian-title { text-align: center; font-size: 22px; font-weight: 800; color: #111; margin-bottom: 25px; }

    .modul-card {
        background: #ffffff;
        border: 1.5px solid #1b5e20;
        border-radius: 12px;
        padding: 20px 25px;
        margin-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .modul-info h3 { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 4px; }
    .modul-info p { font-size: 13px; color: #555; margin: 0; }

    .btn-mulai {
        background-color: #c8e6c9;
        color: #1b5e20;
        border: 1.5px solid #2e7d32;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.2s;
    }
    .btn-mulai:hover { background-color: #a5d6a7; }

    .btn-hasil {
        background-color: #fff9c4;
        color: #f57f17;
        border: 1.5px solid #fbc02d;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-hasil:hover { background-color: #fff176; }

    .btn-locked {
        background-color: #e0e0e0;
        color: #757575;
        border: 1.5px solid #bdbdbd;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
</style>

<div class="ujian-page-container">

    <!-- SIDEBAR PROFIL & PROGRES -->
    <div class="profile-sidebar-card">
        <div class="profile-avatar">
            <?php if (!empty($profile['pas_foto'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile['pas_foto']); ?>" alt="Foto Profil">
            <?php else: ?>
                👤
            <?php endif; ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($profile['nama_lengkap'] ?? 'Nama Pelamar'); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($profile['email'] ?? '-'); ?></div>

        <ul class="modul-sidebar-list">
            <?php foreach ($daftar_modul as $index => $m): 
                $icon_status = ($m['status_pengerjaan'] == 'Selesai') ? '✅' : '🔒';
            ?>
                <li>
                    <span>Psikotes Module <?php echo ($index + 1); ?></span>
                    <span><?php echo $icon_status; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- KONTEN UTAMA DAFTAR MODUL -->
    <div class="ujian-main-content">
        <h2 class="ujian-title">DAFTAR MODUL PSIKOTES</h2>

        <?php 
        $sebelumnya_selesai = true; // Modul pertama otomatis terbuka
        foreach ($daftar_modul as $index => $modul): 
            $no = $index + 1;
            $status = $modul['status_pengerjaan'];
        ?>
            <div class="modul-card">
                <div class="modul-info">
                    <h3>Psikotes Module <?php echo $no; ?>: <?php echo htmlspecialchars($modul['nama_modul']); ?></h3>
                    <p>Status: <strong><?php echo $status; ?></strong> (Durasi: <?php echo $modul['durasi_menit']; ?> Menit)</p>
                </div>
                <div>
                    <?php if ($status == 'Selesai'): ?>
                        <a href="hasil_ujian.php?id=<?php echo $modul['id_modul']; ?>" class="btn-hasil">
                            Lihat Hasil Tes➔
                        </a>
                    <?php elseif ($sebelumnya_selesai): ?>
                        <a href="cara_ujian.php?id=<?php echo $modul['id_modul']; ?>" class="btn-mulai">
                            Mulai Tes ➔
                        </a>
                        <?php 
                        // Jika modul ini belum selesai, maka modul berikutnya akan dikunci
                        $sebelumnya_selesai = false; 
                        ?>
                    <?php else: ?>
                        <button class="btn-locked" disabled>
                            🔒 Terkunci
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>