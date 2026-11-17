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

// Ambil detail modul
$query_modul = mysqli_query($conn, "SELECT * FROM modul_ujian WHERE id_modul = $id_modul");
$modul = mysqli_fetch_assoc($query_modul);

if (!$modul) {
    echo "<script>alert('Modul ujian tidak ditemukan!'); window.location='ujian.php';</script>";
    exit();
}

// Cek riwayat pengerjaan
$q_riwayat = mysqli_query($conn, "SELECT * FROM riwayat_ujian_pelamar WHERE id_pelamar = $id_pelamar AND id_modul = $id_modul");
$riwayat = mysqli_fetch_assoc($q_riwayat);
if (!$riwayat) {
    mysqli_query($conn, "INSERT INTO riwayat_ujian_pelamar (id_pelamar, id_modul, status_pengerjaan, waktu_mulai) VALUES ($id_pelamar, $id_modul, 'Sedang Dikerjakan', NOW())");
} elseif ($riwayat['status_pengerjaan'] == 'Selesai') {
    echo "<script>alert('Anda sudah menyelesaikan modul ini!'); window.location='ujian.php';</script>";
    exit();
}

// Ambil semua soal untuk modul ini
$query_soal = mysqli_query($conn, "SELECT * FROM soal_ujian WHERE id_modul = $id_modul");
$arr_soal = [];
while ($row = mysqli_fetch_assoc($query_soal)) {
    $arr_soal[] = $row;
}
$total_soal = count($arr_soal);

// PROSES SUBMIT JAWABAN AKHIR
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_ujian'])) {
    $jawaban_array = $_POST['jawaban'] ?? [];

    foreach ($arr_soal as $soal) {
        $id_soal = $soal['id_soal'];
        $ans = $jawaban_array[$id_soal] ?? '';
        $jawaban_user = mysqli_real_escape_string($conn, $ans);
        
        $skor = 0;
        $status_penilaian = 'Sudah Dinilai';

        if ($modul['jenis_modul'] == 'pilihan_ganda') {
            if (trim(strtoupper($jawaban_user)) == trim(strtoupper($soal['kunci_jawaban']))) {
                $skor = 100;
            }
        } else {
            $status_penilaian = 'Belum Dinilai'; // Untuk essay / benar-salah
        }

        $cek_jwb = mysqli_query($conn, "SELECT * FROM jawaban_pelamar WHERE id_pelamar = $id_pelamar AND id_soal = $id_soal");
        if (mysqli_num_rows($cek_jwb) > 0) {
            mysqli_query($conn, "UPDATE jawaban_pelamar SET jawaban_user = '$jawaban_user', status_penilaian = '$status_penilaian', skor = $skor WHERE id_pelamar = $id_pelamar AND id_soal = $id_soal");
        } else {
            mysqli_query($conn, "INSERT INTO jawaban_pelamar (id_pelamar, id_soal, jawaban_user, status_penilaian, skor) VALUES ($id_pelamar, $id_soal, '$jawaban_user', '$status_penilaian', $skor)");
        }
    }

    mysqli_query($conn, "UPDATE riwayat_ujian_pelamar SET status_pengerjaan = 'Selesai', waktu_selesai = NOW() WHERE id_pelamar = $id_pelamar AND id_modul = $id_modul");
    echo "<script>alert('Ujian berhasil dikirim!'); window.location='ujian.php';</script>";
    exit();
}

include '../includes/header_pelamar.php';
?>

<style>
    .ujian-page { max-width: 1100px; margin: 20px auto; padding: 20px; display: flex; gap: 25px; font-family: 'Segoe UI', sans-serif; }
    
    /* Sidebar Kiri */
    .ujian-sidebar { width: 280px; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px; padding: 20px; text-align: center; height: fit-content; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
    .timer-circle { width: 110px; height: 110px; border: 4px solid #1b5e20; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 20px; font-weight: 800; color: #111; background: #f1f8e9; }
    .pelamar-info { font-size: 13px; color: #555; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    
    /* Grid Nomor Soal */
    .grid-nomor { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; }
    .btn-nomor { aspect-ratio: 1; background: #e0e0e0; border: 1px solid #ccc; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #333; }
    .btn-nomor.active { background: #c8e6c9; border-color: #1b5e20; color: #1b5e20; }
    .btn-nomor.answered { background: #2e7d32; color: #fff; border-color: #1b5e20; }

    /* Konten Kanan */
    .ujian-content { flex: 1; background: #fff; border: 1.5px solid #1b5e20; border-radius: 16px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; }
    
    /* Progress Bar */
    .progress-container { margin-bottom: 25px; }
    .progress-text { font-size: 13px; font-weight: 700; color: #333; margin-bottom: 6px; }
    .progress-bar-bg { width: 100%; height: 8px; background: #e0e0e0; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background: #2e7d32; width: 0%; transition: width 0.3s; }

    /* Soal Card Layout */
    .soal-card-layout { display: flex; gap: 20px; margin-bottom: 25px; align-items: flex-start; }
    .soal-image-box { width: 45%; background: #e0e0e0; border-radius: 10px; min-height: 180px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .soal-image-box img { width: 100%; height: auto; object-fit: cover; }
    .soal-text-box { flex: 1; font-size: 14.5px; color: #333; line-height: 1.6; }
    
    .options-list label { display: flex; align-items: center; gap: 10px; font-size: 14px; margin-bottom: 10px; cursor: pointer; }

    /* Navigasi Bawah */
    .nav-buttons { display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 20px; }
    .btn-nav { background: #e0e0e0; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 700; cursor: pointer; }
    .btn-nav:hover { background: #d0d0d0; }
    .btn-next { background: #ffe082; color: #5d4037; }
    .btn-next:hover { background: #ffd54f; }
</style>

<div class="ujian-page">
    <!-- SIDEBAR KIRI -->
    <div class="ujian-sidebar">
        <div class="timer-circle" id="countdown">00:00</div>
        <div class="pelamar-info">
            <strong>Lembar Jawaban</strong><br>
            <span><?php echo htmlspecialchars($profile['email'] ?? '-'); ?></span>
        </div>

        <!-- Grid Angka 1 sampai 40 -->
        <div class="grid-nomor" id="gridNomor">
            <?php for ($i = 0; $i < $total_soal; $i++): ?>
                <button type="button" class="btn-nomor <?php echo ($i == 0) ? 'active' : ''; ?>" onclick="tampilSoal(<?php echo $i; ?>)" id="btnGrid<?php echo $i; ?>">
                    <?php echo ($i + 1); ?>
                </button>
            <?php endfor; ?>
        </div>
    </div>

    <!-- KONTEN UTAMA KANAN -->
    <div class="ujian-content">
        <form action="" method="POST" id="formUjian">
            <!-- Progress Bar -->
            <div class="progress-container">
                <div class="progress-text" id="progressText">Progres: 1/<?php echo $total_soal; ?> Soal</div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" id="progressBar" style="width: <?php echo (1/$total_soal)*100; ?>%;"></div>
                </div>
            </div>

            <!-- Area Soal (Ditampilkan per 1 soal menggunakan JavaScript) -->
            <div id="containerSoal">
                <?php foreach ($arr_soal as $index => $soal): ?>
                    <div class="soal-slide" id="slideSoal<?php echo $index; ?>" style="display: <?php echo ($index == 0) ? 'block' : 'none'; ?>;">
                        <div style="font-weight: 800; font-size: 16px; margin-bottom: 15px; color: #1b5e20;">Soal Nomor <?php echo ($index + 1); ?></div>
                        
                        <div class="soal-card-layout">
                            <!-- Jika ada gambar soal -->
                            <?php if (!empty($soal['gambar_soal'])): ?>
                                <div class="soal-image-box">
                                    <img src="../uploads/<?php echo htmlspecialchars($soal['gambar_soal']); ?>" alt="Gambar Soal">
                                </div>
                            <?php endif; ?>

                            <div class="soal-text-box">
                                <?php echo nl2br(htmlspecialchars($soal['pertanyaan'])); ?>
                            </div>
                        </div>

                        <!-- Opsi Jawaban -->
                        <div class="options-list">
                            <?php if (!empty($soal['opsi_a'])): ?>
                                <label><input type="radio" name="jawaban[<?php echo $soal['id_soal']; ?>]" value="A" onchange="tandaiTerjawab(<?php echo $index; ?>)"> A. <?php echo htmlspecialchars($soal['opsi_a']); ?></label>
                            <?php endif; ?>
                            <?php if (!empty($soal['opsi_b'])): ?>
                                <label><input type="radio" name="jawaban[<?php echo $soal['id_soal']; ?>]" value="B" onchange="tandaiTerjawab(<?php echo $index; ?>)"> B. <?php echo htmlspecialchars($soal['opsi_b']); ?></label>
                            <?php endif; ?>
                            <?php if (!empty($soal['opsi_c'])): ?>
                                <label><input type="radio" name="jawaban[<?php echo $soal['id_soal']; ?>]" value="C" onchange="tandaiTerjawab(<?php echo $index; ?>)"> C. <?php echo htmlspecialchars($soal['opsi_c']); ?></label>
                            <?php endif; ?>
                            <?php if (!empty($soal['opsi_d'])): ?>
                                <label><input type="radio" name="jawaban[<?php echo $soal['id_soal']; ?>]" value="D" onchange="tandaiTerjawab(<?php echo $index; ?>)"> D. <?php echo htmlspecialchars($soal['opsi_d']); ?></label>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Navigasi Bawah -->
            <div class="nav-buttons">
                <button type="button" class="btn-nav" id="btnSebelumnya" onclick="geserSoal(-1)" style="visibility: hidden;">← Sebelumnya</button>
                <button type="button" class="btn-nav btn-next" id="btnSelanjutnya" onclick="geserSoal(1)">Selanjutnya →</button>
                <button type="submit" name="submit_ujian" id="btnSubmit" class="btn-nav btn-next" style="display: none; background: #2e7d32; color: #fff;" onclick="return confirm('Yakin ingin menyelesaikan ujian ini?')">Selesai & Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript Interaktif (Timer, Navigasi Soal, Grid Nomor, Progres) -->
<script>
    var currentIndex = 0;
    var totalSoal = <?php echo $total_soal; ?>;

    function tampilSoal(index) {
        document.querySelectorAll('.soal-slide').forEach(el => el.style.display = 'none');
        document.getElementById('slideSoal' + index).style.display = 'block';
        
        document.querySelectorAll('.btn-nomor').forEach(el => el.classList.remove('active'));
        document.getElementById('btnGrid' + index).classList.add('active');

        currentIndex = index;

        // Atur tombol navigasi
        document.getElementById('btnSebelumnya').style.visibility = (currentIndex === 0) ? 'hidden' : 'visible';
        if (currentIndex === totalSoal - 1) {
            document.getElementById('btnSelanjutnya').style.display = 'none';
            document.getElementById('btnSubmit').style.display = 'inline-block';
        } else {
            document.getElementById('btnSelanjutnya').style.display = 'inline-block';
            document.getElementById('btnSubmit').style.display = 'none';
        }

        // Update teks progres
        document.getElementById('progressText').innerText = "Progres: " + (currentIndex + 1) + "/" + totalSoal + " Soal";
        document.getElementById('progressBar').style.width = ((currentIndex + 1) / totalSoal) * 100 + "%";
    }

    function geserSoal(arah) {
        var target = currentIndex + arah;
        if (target >= 0 && target < totalSoal) {
            tampilSoal(target);
        }
    }

    function tandaiTerjawab(index) {
        document.getElementById('btnGrid' + index).classList.add('answered');
    }

    // Timer Mundur
    var durasiMenit = <?php echo $modul['durasi_menit']; ?>;
    var waktuTotal = durasiMenit * 60;

    var timer = setInterval(function () {
        var menit = Math.floor(waktuTotal / 60);
        var detik = waktuTotal % 60;
        menit = menit < 10 ? "0" + menit : menit;
        detik = detik < 10 ? "0" + detik : detik;

        document.getElementById("countdown").innerHTML = menit + ":" + detik;

        if (waktuTotal <= 0) {
            clearInterval(timer);
            alert("Waktu ujian habis! Jawaban otomatis dikirim.");
            document.getElementById("formUjian").submit();
        }
        waktuTotal--;
    }, 1000);
</script>

<?php 
mysqli_close($conn);
include '../includes/footer_pelamar.php'; 
?>