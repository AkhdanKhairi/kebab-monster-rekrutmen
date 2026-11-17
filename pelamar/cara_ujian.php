<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];
$id_modul = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Koneksi Database
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) { die("Koneksi database gagal: " . mysqli_connect_error()); }

// Ambil data profil
$query_pelamar = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$profile = mysqli_fetch_assoc($query_pelamar);

// Ambil detail modul berdasarkan ID
$query_modul = mysqli_query($conn, "SELECT * FROM modul_ujian WHERE id_modul = $id_modul");
$modul = mysqli_fetch_assoc($query_modul);

if (!$modul) {
    echo "<script>alert('Modul ujian tidak ditemukan!'); window.location='ujian.php';</script>";
    exit();
}

include '../includes/header_pelamar.php';
?>

<style>
    .cara-page-container {
        max-width: 1100px;
        margin: 20px auto;
        padding: 20px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        display: flex;
        gap: 30px;
    }
    .cara-main-content {
        flex: 1;
        background: #fff;
        border: 1.5px solid #1b5e20;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .cara-title {
        font-size: 24px;
        font-weight: 800;
        color: #111;
        margin-bottom: 10px;
    }
    .modul-subtitle {
        font-size: 18px;
        font-weight: 700;
        color: #1b5e20;
        margin-bottom: 20px;
    }
    .instruksi-box {
        font-size: 14.5px;
        color: #444;
        line-height: 1.8;
        background: #f9fbe7;
        border: 1.5px solid #c5e1a5;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    /* Mengatur list agar rapi berurutan ke bawah */
    .instruksi-content ol {
        padding-left: 20px;
        margin-top: 10px;
    }
    .instruksi-content li {
        margin-bottom: 8px;
    }
    .instruksi-box img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .btn-mulai-tes {
        background-color: #ffe082;
        color: #5d4037;
        border: 1.5px solid #fbc02d;
        padding: 12px 28px;
        border-radius: 30px;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        box-shadow: 0 3px 6px rgba(0,0,0,0.05);
        transition: background 0.2s, transform 0.1s;
        align-self: flex-end;
    }
    .btn-mulai-tes:hover {
        background-color: #ffd54f;
        transform: translateY(-1px);
    }
</style>

<div class="cara-page-container">
    <div class="cara-main-content">
        <div>
            <div class="cara-title">Tata Cara Mengerjakan Ujian</div>
            <div class="modul-subtitle"><?php echo htmlspecialchars($modul['nama_modul']); ?></div>
            
            <div class="instruksi-box">
                <!-- Informasi Durasi & Jenis Tes -->
                <p style="margin-bottom: 12px; font-weight: 600; color: #1b5e20;">
                    ⏱️ Durasi Pengerjaan: <?php echo $modul['durasi_menit']; ?> Menit &nbsp;|&nbsp; 
                    📋 Jenis Tes: <?php echo ucwords(str_replace('_', ' ', $modul['jenis_modul'])); ?>
                </p>
                <hr style="border: 0; border-top: 1px solid #c5e1a5; margin-bottom: 18px;">

                <!-- Menampilkan tata cara per poin ke bawah secara otomatis -->
                <div class="instruksi-content">
                    <?php 
                    if (!empty($modul['tata_cara'])) {
                        // Memecah teks berdasarkan baris baru (enter dari textarea admin)
                        $baris_teks = explode("\n", trim($modul['tata_cara']));
                        if (count($baris_teks) > 1) {
                            echo "<ol>";
                            foreach ($baris_teks as $teks) {
                                if (trim($teks) != "") {
                                    // Bersihkan angka manual jika sempat diketik, agar tidak double numbering
                                    $bersih = preg_replace('/^\d+[\.\)]\s*/', '', trim($teks));
                                    echo "<li>" . htmlspecialchars($bersih) . "</li>";
                                }
                            }
                            echo "</ol>";
                        } else {
                            echo "<p>" . nl2br(htmlspecialchars($modul['tata_cara'])) . "</p>";
                        }
                    } else {
                        echo "<p>Belum ada tata cara khusus yang diatur oleh HR untuk modul ini.</p>";
                    }
                    ?>
                </div>

                <!-- Gambar Pendukung Tata Cara jika ada -->
                <?php if (!empty($modul['gambar_petunjuk'])): ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <img src="../assets/img/tatacara/<?php echo $modul['gambar_petunjuk']; ?>" alt="Petunjuk Gambar Tata Cara">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <a href="kerjakan_ujian.php?id=<?php echo $modul['id_modul']; ?>" class="btn-mulai-tes">
                Mulai Tes <?php echo htmlspecialchars($modul['nama_modul']); ?> ➔
            </a>
        </div>
    </div>
</div>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>