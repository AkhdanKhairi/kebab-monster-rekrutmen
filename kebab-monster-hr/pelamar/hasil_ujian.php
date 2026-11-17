<?php
session_start();



if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi database gagal: " . mysqli_connect_error()); }

// Ambil data profil pelamar
$query_pelamar = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$profile = mysqli_fetch_assoc($query_pelamar);

// Ambil data lamaran untuk mengecek apakah HR sudah memberikan konfirmasi ACC
$q_lamaran = mysqli_query($conn, "SELECT * FROM lamaran WHERE id_pelamar = $id_pelamar ORDER BY id_lamaran DESC LIMIT 1");
$data_lamaran = mysqli_fetch_assoc($q_lamaran);

// Cek apakah HR sudah meng-ACC (Jika kolom belum ada di database, default-nya 'Menunggu')
$status_konfirmasi = $data_lamaran['status_konfirmasi_ujian'] ?? 'Menunggu';

// Cek juga apakah masih ada soal (seperti essay/benar-salah) yang berstatus 'Belum Dinilai' oleh HR
$q_cek_penilaian = mysqli_query($conn, "
    SELECT jp.* FROM jawaban_pelamar jp 
    WHERE jp.id_pelamar = $id_pelamar AND jp.status_penilaian = 'Belum Dinilai'
");
$ada_yang_belum_dinilai = mysqli_num_rows($q_cek_penilaian) > 0;

// KONDISI BELUM DI-ACC: Jika status masih 'Menunggu' ATAU masih ada soal yang belum dinilai HR
$belum_dikonfirmasi = ($status_konfirmasi == 'Menunggu' || $ada_yang_belum_dinilai);

// JIKA SUDAH DI-ACC SEMUA, AMBIL REKAP NILAI LENGKAP
$rekap_modul = [];
if (!$belum_dikonfirmasi) {
    $q_modul_selesai = mysqli_query($conn, "
        SELECT DISTINCT m.id_modul, m.nama_modul, m.urutan 
        FROM modul_ujian m
        JOIN soal_ujian s ON m.id_modul = s.id_modul
        JOIN jawaban_pelamar jp ON s.id_soal = jp.id_soal
        WHERE jp.id_pelamar = $id_pelamar
        ORDER BY m.urutan ASC
    ");

    while ($modul = mysqli_fetch_assoc($q_modul_selesai)) {
        $id_modul = $modul['id_modul'];
        
        $q_skor = mysqli_query($conn, "
            SELECT AVG(jp.skor) as rata_rata 
            FROM jawaban_pelamar jp 
            JOIN soal_ujian s ON jp.id_soal = s.id_soal 
            WHERE jp.id_pelamar = $id_pelamar AND s.id_modul = $id_modul
        ");
        $d_skor = mysqli_fetch_assoc($q_skor);
        $modul['skor_akhir'] = round($d_skor['rata_rata'] ?? 0);
        
        $rekap_modul[] = $modul;
    }
}

include '../includes/header_pelamar.php';
?>

<style>
    .hasil-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .profile-sidebar-card {
        width: 320px; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px;
        padding: 25px 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); text-align: center; height: fit-content;
    }
    .profile-avatar {
        width: 100px; height: 100px; background: #ffe0b2; border-radius: 50%;
        margin: 0 auto 15px auto; display: flex; align-items: center; justify-content: center;
        font-size: 40px; overflow: hidden; border: 2px solid #1b5e20;
    }
    .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .profile-name { font-size: 16px; font-weight: 800; color: #111; margin-bottom: 2px; }
    .profile-email { font-size: 12.5px; color: #666; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 12px; }
    
    .hasil-main-content {
        flex: 1; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px;
        padding: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); text-align: center;
        display: flex; flex-direction: column; justify-content: space-between; align-items: center;
    }

    .waiting-wrapper {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        flex: 1;
        width: 100%;
        padding: 40px 0;
    }
    .waiting-title {
        font-size: 24px;
        font-weight: 800;
        color: #111;
        line-height: 1.4;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-rekap {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        margin-bottom: 30px;
    }
    .table-rekap th, .table-rekap td {
        border: 1px solid #ddd;
        padding: 12px 15px;
        text-align: left;
        font-size: 14px;
    }
    .table-rekap th {
        background-color: #1b5e20;
        color: white;
        font-weight: 700;
    }
    .table-rekap tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .badge-nilai {
        background: #c8e6c9;
        color: #1b5e20;
        padding: 5px 12px;
        border-radius: 12px;
        font-weight: 800;
    }

    .btn-kembali {
        background-color: #1b5e20; color: #fff; text-decoration: none; padding: 10px 25px;
        border-radius: 20px; font-weight: 700; font-size: 14px; transition: background 0.2s;
        display: inline-block;
    }
    .btn-kembali:hover { background-color: #2e7d32; }
</style>

<div class="hasil-page-container">

    <!-- SIDEBAR PROFIL -->
    <div class="profile-sidebar-card">
        <div class="profile-avatar">
            <?php if (!empty($profile['pas_foto'])): ?>
                <img src="../uploads/<?php echo htmlspecialchars($profile['pas_foto']); ?>" alt="Foto Profil">
            <?php else: ?> 👤 <?php endif; ?>
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($profile['nama_lengkap'] ?? 'Nama Pelamar'); ?></div>
        <div class="profile-email"><?php echo htmlspecialchars($profile['email'] ?? '-'); ?></div>

        <ul class="modul-sidebar-list" style="list-style: none; padding: 0; margin-top: 20px; text-align: left;">
            <li style="font-size: 13px; font-weight: 600; padding: 6px 0; border-bottom: 1px dashed #eee;">
                Status: <?php echo $belum_dikonfirmasi ? '⏳ Menunggu Konfirmasi HR' : '✅ Selesai Divalidasi'; ?>
            </li>
        </ul>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="hasil-main-content">
        <?php if ($belum_dikonfirmasi): ?>
            <!-- KONDISI 1: BELUM DI-ACC HR (MUNCUL TAMPILAN PERINGATAN / PENGAMANAN) -->
            <div class="waiting-wrapper">
                <div class="waiting-title">
                    JAWABAN ANDA SEDANG DI KOREKSI OLEH TIM HR,<br> 
                    HARAP TUNGGU UNTUK MELIHAT HASILNYA
                </div>
            </div>
        <?php else: ?>
            <!-- KONDISI 2: SUDAH DI-ACC HR, MUNCUL TABEL HASIL DARI MODUL 1 DST -->
            <div style="width: 100%;">
                <h2 style="font-size: 22px; font-weight: 800; color: #111; margin-bottom: 10px;">
                    REKAPITULASI HASIL PSIKOTES
                </h2>
                <p style="font-size: 13.5px; color: #666; margin-bottom: 20px;">
                    Seluruh modul ujian Anda telah selesai divalidasi dan dikonfirmasi oleh tim HR Kebab Monster.
                </p>

                <table class="table-rekap">
                    <thead>
                        <tr>
                            <th style="width: 10%; text-align: center;">No</th>
                            <th style="width: 65%;">Nama Modul Psikotes</th>
                            <th style="width: 25%; text-align: center;">Hasil / Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekap_modul as $index => $rm): ?>
                            <tr>
                                <td style="text-align: center; font-weight: 700;"><?php echo ($index + 1); ?></td>
                                <td><?php echo htmlspecialchars($rm['nama_modul']); ?></td>
                                <td style="text-align: center;">
                                    <span class="badge-nilai"><?php echo $rm['skor_akhir']; ?> / 100</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="align-self: flex-start; width: 100%; text-align: left; border-top: 1px solid #eee; padding-top: 15px;">
            <a href="ujian.php" class="btn-kembali">← Kembali ke Daftar Modul</a>
        </div>
    </div>

</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>