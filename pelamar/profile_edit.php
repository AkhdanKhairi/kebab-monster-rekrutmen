<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

$id_pelamar = $_SESSION['user_id'];

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

$pesan_sukses = "";

// Proses Simpan Data Ketika Tombol Ditekan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Data Diri (Termasuk Berat & Tinggi Badan)
    $nama_lengkap      = mysqli_real_escape_string($conn, $_POST['nama_lengkap'] ?? '');
    $no_telepon        = mysqli_real_escape_string($conn, $_POST['no_telepon'] ?? '');
    $jenis_kelamin     = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    $tempat_lahir      = mysqli_real_escape_string($conn, $_POST['tempat_lahir'] ?? '');
    $tanggal_lahir     = mysqli_real_escape_string($conn, $_POST['tanggal_lahir'] ?? '');
    $agama             = mysqli_real_escape_string($conn, $_POST['agama'] ?? '');
    $status_nikah      = mysqli_real_escape_string($conn, $_POST['status_nikah'] ?? ''); 
    $kode_pos          = mysqli_real_escape_string($conn, $_POST['kode_pos'] ?? '');
    $kota              = mysqli_real_escape_string($conn, $_POST['kota'] ?? '');
    $no_ktp            = mysqli_real_escape_string($conn, $_POST['no_ktp'] ?? '');
    $kewarganegaraan   = mysqli_real_escape_string($conn, $_POST['kewarganegaraan'] ?? '');
    $jumlah_anak       = intval($_POST['jumlah_anak'] ?? 0);
    $berat_badan       = mysqli_real_escape_string($conn, $_POST['berat_badan'] ?? '');
    $tinggi_badan      = mysqli_real_escape_string($conn, $_POST['tinggi_badan'] ?? '');
    $alamat            = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');

    // 2. Kontak Darurat
    $darurat_nama      = mysqli_real_escape_string($conn, $_POST['darurat_nama'] ?? '');
    $darurat_hubungan  = mysqli_real_escape_string($conn, $_POST['darurat_hubungan'] ?? '');
    $darurat_telp1     = mysqli_real_escape_string($conn, $_POST['darurat_telp1'] ?? '');
    $darurat_telp2     = mysqli_real_escape_string($conn, $_POST['darurat_telp2'] ?? '');
    $darurat_alamat    = mysqli_real_escape_string($conn, $_POST['darurat_alamat'] ?? '');

    // 3. Pendidikan
    $pend_terakhir     = mysqli_real_escape_string($conn, $_POST['pend_terakhir'] ?? '');
    $pend_Institusi    = mysqli_real_escape_string($conn, $_POST['pend_institusi'] ?? '');
    $pend_Jurusan      = mysqli_real_escape_string($conn, $_POST['pend_jurusan'] ?? '');
    $pend_nilai        = mysqli_real_escape_string($conn, $_POST['pend_nilai'] ?? '');
    $pend_mulai        = mysqli_real_escape_string($conn, $_POST['pend_mulai'] ?? '');
    $pend_selesai      = mysqli_real_escape_string($conn, $_POST['pend_selesai'] ?? '');

    // 4. Pengalaman Kerja 1
    $kerja_perusahaan    = mysqli_real_escape_string($conn, $_POST['kerja_perusahaan'] ?? '');
    $kerja_telp          = mysqli_real_escape_string($conn, $_POST['kerja_telp'] ?? '');
    $kerja_mulai         = mysqli_real_escape_string($conn, $_POST['kerja_mulai'] ?? '');
    $kerja_akhir         = mysqli_real_escape_string($conn, $_POST['kerja_akhir'] ?? '');
    $kerja_jabatan_awal  = mysqli_real_escape_string($conn, $_POST['kerja_jabatan_awal'] ?? '');
    $kerja_jabatan_akhir = mysqli_real_escape_string($conn, $_POST['kerja_jabatan_akhir'] ?? '');
    $kerja_jobdesk       = mysqli_real_escape_string($conn, $_POST['kerja_jobdesk'] ?? '');
    $kerja_alamat        = mysqli_real_escape_string($conn, $_POST['kerja_alamat'] ?? '');
    $kerja_alasan        = mysqli_real_escape_string($conn, $_POST['kerja_alasan'] ?? '');

    // Pengalaman Kerja 2
    $kerja2_perusahaan    = mysqli_real_escape_string($conn, $_POST['kerja2_perusahaan'] ?? '');
    $kerja2_telp          = mysqli_real_escape_string($conn, $_POST['kerja2_telp'] ?? '');
    $kerja2_mulai         = mysqli_real_escape_string($conn, $_POST['kerja2_mulai'] ?? '');
    $kerja2_akhir         = mysqli_real_escape_string($conn, $_POST['kerja2_akhir'] ?? '');
    $kerja2_jabatan_awal  = mysqli_real_escape_string($conn, $_POST['kerja2_jabatan_awal'] ?? '');
    $kerja2_jabatan_akhir = mysqli_real_escape_string($conn, $_POST['kerja2_jabatan_akhir'] ?? '');
    $kerja2_jobdesk       = mysqli_real_escape_string($conn, $_POST['kerja2_jobdesk'] ?? '');
    $kerja2_alamat        = mysqli_real_escape_string($conn, $_POST['kerja2_alamat'] ?? '');
    $kerja2_alasan        = mysqli_real_escape_string($conn, $_POST['kerja2_alasan'] ?? '');

    // 5. Pengalaman Internship / Magang 1 & 2
    $magang1_instansi   = mysqli_real_escape_string($conn, $_POST['magang1_instansi'] ?? '');
    $magang1_mulai      = mysqli_real_escape_string($conn, $_POST['magang1_mulai'] ?? '');
    $magang1_akhir      = mysqli_real_escape_string($conn, $_POST['magang1_akhir'] ?? '');
    $magang1_posisi     = mysqli_real_escape_string($conn, $_POST['magang1_posisi'] ?? '');
    $magang1_jobdesk    = mysqli_real_escape_string($conn, $_POST['magang1_jobdesk'] ?? '');

    $magang2_instansi   = mysqli_real_escape_string($conn, $_POST['magang2_instansi'] ?? '');
    $magang2_mulai      = mysqli_real_escape_string($conn, $_POST['magang2_mulai'] ?? '');
    $magang2_akhir      = mysqli_real_escape_string($conn, $_POST['magang2_akhir'] ?? '');
    $magang2_posisi     = mysqli_real_escape_string($conn, $_POST['magang2_posisi'] ?? '');
    $magang2_jobdesk    = mysqli_real_escape_string($conn, $_POST['magang2_jobdesk'] ?? '');

    // Query Update Data Teks ke Database
    $update = "UPDATE pelamar SET 
        nama_lengkap='$nama_lengkap', no_telepon='$no_telepon', jenis_kelamin='$jenis_kelamin', 
        tempat_lahir='$tempat_lahir', tanggal_lahir='$tanggal_lahir', agama='$agama', 
        status_nikah='$status_nikah', kode_pos='$kode_pos', kota='$kota', no_ktp='$no_ktp', 
        kewarganegaraan='$kewarganegaraan', jumlah_anak=$jumlah_anak, berat_badan='$berat_badan', tinggi_badan='$tinggi_badan', alamat='$alamat',
        darurat_nama='$darurat_nama', darurat_hubungan='$darurat_hubungan', darurat_telp1='$darurat_telp1', 
        darurat_telp2='$darurat_telp2', darurat_alamat='$darurat_alamat',
        pend_terakhir='$pend_terakhir', pend_institusi='$pend_Institusi', pend_jurusan='$pend_Jurusan', 
        pend_nilai='$pend_nilai', pend_mulai='$pend_mulai', pend_selesai='$pend_selesai',
        kerja_perusahaan='$kerja_perusahaan', kerja_telp='$kerja_telp', kerja_mulai='$kerja_mulai', 
        kerja_akhir='$kerja_akhir', kerja_jabatan_awal='$kerja_jabatan_awal', kerja_jabatan_akhir='$kerja_jabatan_akhir', kerja_jobdesk='$kerja_jobdesk',
        kerja_alamat='$kerja_alamat', kerja_alasan='$kerja_alasan',
        kerja2_perusahaan='$kerja2_perusahaan', kerja2_telp='$kerja2_telp', kerja2_mulai='$kerja2_mulai', 
        kerja2_akhir='$kerja2_akhir', kerja2_jabatan_awal='$kerja2_jabatan_awal', kerja2_jabatan_akhir='$kerja2_jabatan_akhir', kerja2_jobdesk='$kerja2_jobdesk',
        kerja2_alamat='$kerja2_alamat', kerja2_alasan='$kerja2_alasan',
        magang1_instansi='$magang1_instansi', magang1_mulai='$magang1_mulai', magang1_akhir='$magang1_akhir', magang1_posisi='$magang1_posisi', magang1_jobdesk='$magang1_jobdesk',
        magang2_instansi='$magang2_instansi', magang2_mulai='$magang2_mulai', magang2_akhir='$magang2_akhir', magang2_posisi='$magang2_posisi', magang2_jobdesk='$magang2_jobdesk'
        WHERE Id_pelamar = $id_pelamar";
    mysqli_query($conn, $update);

    // Penanganan Upload File
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    $file_fields = ['ktp', 'ijazah', 'skck', 'kk', 'pas_foto'];
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_name = $_FILES[$field]['name'];
            $file_tmp  = $_FILES[$field]['tmp_name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
            
            if (in_array($file_ext, $allowed_extensions)) {
                $new_filename = $field . '_' . $id_pelamar . '_' . time() . '.' . $file_ext;
                $target_file  = $target_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $target_file)) {
                    mysqli_query($conn, "UPDATE pelamar SET $field = '$new_filename' WHERE Id_pelamar = $id_pelamar");
                }
            }
        }
    }

    $pesan_sukses = "Profil dan dokumen berhasil diperbarui!";
}

// Ambil data terbaru dari database
$query = mysqli_query($conn, "SELECT * FROM pelamar WHERE Id_pelamar = $id_pelamar");
$data  = mysqli_fetch_assoc($query);

$_SESSION['email'] = $data['email'] ?? ''; 
$_SESSION['nama']  = $data['nama_lengkap'] ?? 'Pelamar';
mysqli_close($conn);

include '../includes/header_pelamar.php';
?>

<style>
    .profile-page-layout { max-width: 1150px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 25px; align-items: start; }
    .profile-card-container { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 90px; }
    .profile-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .profile-avatar-wrapper { width: 80px; height: 80px; margin: 0 auto 12px auto; background: #e0f2f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #b2dfdb; overflow: hidden; }
    .profile-avatar-wrapper img { width: 100%; height: 100%; object-fit: cover; }
    .card-username { font-size: 13px; font-weight: 800; color: #004d40; text-transform: uppercase; margin-bottom: 4px; line-height: 1.3; }
    .card-email { font-size: 11px; color: #666; margin-bottom: 15px; word-break: break-all; }
    .btn-save-card { background-color: #1b5e20; color: white; border: none; width: 100%; padding: 10px; border-radius: 6px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; }
    .btn-save-card:hover { background-color: #00332c; }

    .tab-navigation-bar { background: #e0e0e0; border-radius: 30px; padding: 6px; display: flex; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 5px; }
    .tab-btn { background: transparent; border: none; padding: 10px 12px; border-radius: 25px; font-size: 11.5px; font-weight: 700; color: #555; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px; flex: 1; justify-content: center; white-space: nowrap; }
    .tab-btn.active { background-color: #1b5e20; color: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }

    .form-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .form-section-content { display: none; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-section-content.active { display: grid; }
    .form-field-group { display: flex; flex-direction: column; gap: 5px; }
    .form-field-group.full-width { grid-column: span 2; }
    .form-field-group label { font-size: 12px; font-weight: 700; color: #333; }
    .form-control { background-color: #ffffff; border: 1px solid #dcdcdc; padding: 9px 12px; border-radius: 6px; font-size: 13px; color: #333; width: 100%; box-sizing: border-box; }
    .sub-section-title { grid-column: span 2; font-size: 14px; font-weight: 800; color: #004d40; border-bottom: 2px solid #b2dfdb; padding-bottom: 5px; margin-top: 10px; }
    .notice-top-banner { background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; padding: 10px 15px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; }
    .alert-success { background-color: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-weight: 600; font-size: 13px; }

    @media (max-width: 950px) {
        .profile-page-layout { grid-template-columns: 1fr; }
        .profile-card-container { position: static; }
        .form-section-content { grid-template-columns: 1fr; }
        .form-field-group.full-width { grid-column: span 1; }
        .sub-section-title { grid-column: span 1; }
        .tab-navigation-bar { flex-direction: column; border-radius: 12px; }
    }
</style>

<div class="profile-page-layout">
    <form action="" method="POST" enctype="multipart/form-data" style="display: contents;">
        <div class="profile-card-container">
            <div class="profile-card-box">
                <div class="profile-avatar-wrapper">
                    <?php if (!empty($data['pas_foto']) && file_exists('../uploads/' . $data['pas_foto'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($data['pas_foto']); ?>" alt="Foto Profil">
                    <?php else: ?>
                        <span style="font-size: 40px;">👤</span>
                    <?php endif; ?>
                </div>
                <div class="card-username"><?php echo htmlspecialchars($data['nama_lengkap'] ?? 'Nama Pelamar'); ?></div>
                <div class="card-email"><?php echo htmlspecialchars($data['email'] ?? '-'); ?></div>
                <button type="submit" class="btn-save-card"><span>💾</span> Simpan Data</button>
            </div>
        </div>

        <div class="content-right-side">
            <div class="notice-top-banner">
                <strong>Catatan:</strong> Bagian yang bertanda (<span style="color: red;">*</span>) wajib di isi.
            </div>

            <?php if (!empty($pesan_sukses)): ?>
                <div class="alert-success"><?php echo $pesan_sukses; ?></div>
            <?php endif; ?>

            <div class="tab-navigation-bar">
                <button type="button" class="tab-btn active" onclick="switchTab(event, 'tab-datadiri')">👤 Data Diri</button>
                <button type="button" class="tab-btn" onclick="switchTab(event, 'tab-darurat')">🚨 Kontak Darurat</button>
                <button type="button" class="tab-btn" onclick="switchTab(event, 'tab-pendidikan')">🎓 Pendidikan</button>
                <button type="button" class="tab-btn" onclick="switchTab(event, 'tab-kerja')">💼 Pengalaman Kerja</button>
                <button type="button" class="tab-btn" onclick="switchTab(event, 'tab-magang')">📋 Internship</button>
                <button type="button" class="tab-btn" onclick="switchTab(event, 'tab-berkas')">📁 Upload Berkas</button>
            </div>

            <div class="form-card-box">
                <!-- 1. TAB DATA DIRI -->
                <div id="tab-datadiri" class="form-section-content active">
                    <div class="form-field-group">
                        <label>Nama Lengkap <span style="color: red;">*</span></label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?php echo htmlspecialchars($data['nama_lengkap'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Kode Pos <span style="color: red;">*</span></label>
                        <input type="text" name="kode_pos" class="form-control" value="<?php echo htmlspecialchars($data['kode_pos'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jenis Kelamin <span style="color: red;">*</span></label>
                        <select name="jenis_kelamin" class="form-control">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?php if(($data['jenis_kelamin'] ?? '') == 'Laki-laki') echo 'selected'; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php if(($data['jenis_kelamin'] ?? '') == 'Perempuan') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label>Kota <span style="color: red;">*</span></label>
                        <input type="text" name="kota" class="form-control" value="<?php echo htmlspecialchars($data['kota'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Tempat Lahir <span style="color: red;">*</span></label>
                        <input type="text" name="tempat_lahir" class="form-control" value="<?php echo htmlspecialchars($data['tempat_lahir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No Handphone <span style="color: red;">*</span></label>
                        <input type="text" name="no_telepon" class="form-control" value="<?php echo htmlspecialchars($data['no_telepon'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Tanggal Lahir <span style="color: red;">*</span></label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo htmlspecialchars($data['tanggal_lahir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No KTP <span style="color: red;">*</span></label>
                        <input type="text" name="no_ktp" class="form-control" value="<?php echo htmlspecialchars($data['no_ktp'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Agama <span style="color: red;">*</span></label>
                        <input type="text" name="agama" class="form-control" value="<?php echo htmlspecialchars($data['agama'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Kewarganegaraan <span style="color: red;">*</span></label>
                        <input type="text" name="kewarganegaraan" class="form-control" value="<?php echo htmlspecialchars($data['kewarganegaraan'] ?? 'Indonesia'); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Status <span style="color: red;">*</span></label>
                        <input type="text" name="status_nikah" class="form-control" value="<?php echo htmlspecialchars($data['status_nikah'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jumlah Anak</label>
                        <input type="number" name="jumlah_anak" class="form-control" value="<?php echo htmlspecialchars($data['jumlah_anak'] ?? '0'); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Berat Badan <span style="color: red;">*</span></label>
                        <input type="text" name="berat_badan" class="form-control" placeholder="Contoh: 60 kg" value="<?php echo htmlspecialchars($data['berat_badan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Tinggi Badan <span style="color: red;">*</span></label>
                        <input type="text" name="tinggi_badan" class="form-control" placeholder="Contoh: 170 cm" value="<?php echo htmlspecialchars($data['tinggi_badan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat <span style="color: red;">*</span></label>
                        <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($data['alamat'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- 2. TAB KONTAK DARURAT -->
                <div id="tab-darurat" class="form-section-content">
                    <div class="form-field-group">
                        <label>Nama Keluarga <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_nama" class="form-control" value="<?php echo htmlspecialchars($data['darurat_nama'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Hubungan Keluarga <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_hubungan" class="form-control" value="<?php echo htmlspecialchars($data['darurat_hubungan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No Telepon 1 <span style="color: red;">*</span></label>
                        <input type="text" name="darurat_telp1" class="form-control" value="<?php echo htmlspecialchars($data['darurat_telp1'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No Telepon 2</label>
                        <input type="text" name="darurat_telp2" class="form-control" value="<?php echo htmlspecialchars($data['darurat_telp2'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Keluarga <span style="color: red;">*</span></label>
                        <textarea name="darurat_alamat" class="form-control" rows="2"><?php echo htmlspecialchars($data['darurat_alamat'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- 3. TAB PENDIDIKAN -->
                <div id="tab-pendidikan" class="form-section-content">
                    <div class="form-field-group">
                        <label>Pendidikan Terakhir <span style="color: red;">*</span></label>
                        <input type="text" name="pend_terakhir" class="form-control" value="<?php echo htmlspecialchars($data['pend_terakhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Nama Sekolah / Universitas <span style="color: red;">*</span></label>
                        <input type="text" name="pend_institusi" class="form-control" value="<?php echo htmlspecialchars($data['pend_institusi'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jurusan <span style="color: red;">*</span></label>
                        <input type="text" name="pend_jurusan" class="form-control" value="<?php echo htmlspecialchars($data['pend_jurusan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Nilai / IPK <span style="color: red;">*</span></label>
                        <input type="text" name="pend_nilai" class="form-control" value="<?php echo htmlspecialchars($data['pend_nilai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Tanggal <span style="color: red;">*</span></label>
                        <input type="date" name="pend_mulai" class="form-control" value="<?php echo htmlspecialchars($data['pend_mulai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Selesai Tanggal <span style="color: red;">*</span></label>
                        <input type="date" name="pend_selesai" class="form-control" value="<?php echo htmlspecialchars($data['pend_selesai'] ?? ''); ?>">
                    </div>
                </div>

                <!-- 4. TAB PENGALAMAN KERJA (2 Kolom + Jobdesk) -->
                <div id="tab-kerja" class="form-section-content">
                    <div class="sub-section-title">Pengalaman Kerja 1 (Terakhir)</div>
                    <div class="form-field-group">
                        <label>Nama Perusahaan *</label>
                        <input type="text" name="kerja_perusahaan" class="form-control" value="<?php echo htmlspecialchars($data['kerja_perusahaan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No. Telepon Perusahaan *</label>
                        <input type="text" name="kerja_telp" class="form-control" value="<?php echo htmlspecialchars($data['kerja_telp'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Kerja *</label>
                        <input type="date" name="kerja_mulai" class="form-control" value="<?php echo htmlspecialchars($data['kerja_mulai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Kerja *</label>
                        <input type="date" name="kerja_akhir" class="form-control" value="<?php echo htmlspecialchars($data['kerja_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jabatan Awal *</label>
                        <input type="text" name="kerja_jabatan_awal" class="form-control" value="<?php echo htmlspecialchars($data['kerja_jabatan_awal'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jabatan Akhir *</label>
                        <input type="text" name="kerja_jabatan_akhir" class="form-control" value="<?php echo htmlspecialchars($data['kerja_jabatan_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Pekerjaan *</label>
                        <textarea name="kerja_jobdesk" class="form-control" rows="2" placeholder="Rincikan tugas dan tanggung jawab Anda..."><?php echo htmlspecialchars($data['kerja_jobdesk'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Perusahaan *</label>
                        <textarea name="kerja_alamat" class="form-control" rows="2"><?php echo htmlspecialchars($data['kerja_alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alasan Berhenti Bekerja *</label>
                        <textarea name="kerja_alasan" class="form-control" rows="2"><?php echo htmlspecialchars($data['kerja_alasan'] ?? ''); ?></textarea>
                    </div>

                    <div class="sub-section-title">Pengalaman Kerja 2 (Sebelumnya)</div>
                    <div class="form-field-group">
                        <label>Nama Perusahaan</label>
                        <input type="text" name="kerja2_perusahaan" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_perusahaan'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>No. Telepon Perusahaan</label>
                        <input type="text" name="kerja2_telp" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_telp'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Kerja</label>
                        <input type="date" name="kerja2_mulai" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_mulai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Kerja</label>
                        <input type="date" name="kerja2_akhir" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jabatan Awal</label>
                        <input type="text" name="kerja2_jabatan_awal" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_jabatan_awal'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Jabatan Akhir</label>
                        <input type="text" name="kerja2_jabatan_akhir" class="form-control" value="<?php echo htmlspecialchars($data['kerja2_jabatan_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Pekerjaan</label>
                        <textarea name="kerja2_jobdesk" class="form-control" rows="2" placeholder="Rincikan tugas dan tanggung jawab Anda..."><?php echo htmlspecialchars($data['kerja2_jobdesk'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alamat Perusahaan</label>
                        <textarea name="kerja2_alamat" class="form-control" rows="2"><?php echo htmlspecialchars($data['kerja2_alamat'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Alasan Berhenti Bekerja</label>
                        <textarea name="kerja2_alasan" class="form-control" rows="2"><?php echo htmlspecialchars($data['kerja2_alasan'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- 5. TAB INTERNSHIP / MAGANG -->
                <div id="tab-magang" class="form-section-content">
                    <div class="sub-section-title">Pengalaman Internship / Magang 1</div>
                    <div class="form-field-group">
                        <label>Nama Instansi / Perusahaan</label>
                        <input type="text" name="magang1_instansi" class="form-control" value="<?php echo htmlspecialchars($data['magang1_instansi'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Posisi / Peran</label>
                        <input type="text" name="magang1_posisi" class="form-control" value="<?php echo htmlspecialchars($data['magang1_posisi'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Magang</label>
                        <input type="date" name="magang1_mulai" class="form-control" value="<?php echo htmlspecialchars($data['magang1_mulai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Magang</label>
                        <input type="date" name="magang1_akhir" class="form-control" value="<?php echo htmlspecialchars($data['magang1_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Kegiatan</label>
                        <textarea name="magang1_jobdesk" class="form-control" rows="2"><?php echo htmlspecialchars($data['magang1_jobdesk'] ?? ''); ?></textarea>
                    </div>

                    <div class="sub-section-title">Pengalaman Internship / Magang 2</div>
                    <div class="form-field-group">
                        <label>Nama Instansi / Perusahaan</label>
                        <input type="text" name="magang2_instansi" class="form-control" value="<?php echo htmlspecialchars($data['magang2_instansi'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Posisi / Peran</label>
                        <input type="text" name="magang2_posisi" class="form-control" value="<?php echo htmlspecialchars($data['magang2_posisi'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Mulai Magang</label>
                        <input type="date" name="magang2_mulai" class="form-control" value="<?php echo htmlspecialchars($data['magang2_mulai'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group">
                        <label>Akhir Magang</label>
                        <input type="date" name="magang2_akhir" class="form-control" value="<?php echo htmlspecialchars($data['magang2_akhir'] ?? ''); ?>">
                    </div>
                    <div class="form-field-group full-width">
                        <label>Jobdesk / Deskripsi Kegiatan</label>
                        <textarea name="magang2_jobdesk" class="form-control" rows="2"><?php echo htmlspecialchars($data['magang2_jobdesk'] ?? ''); ?></textarea>
                    </div>
                </div>

                <!-- 6. TAB UPLOAD BERKAS -->
                <div id="tab-berkas" class="form-section-content">
                    <div class="form-field-group">
                        <label>Upload KTP <span style="color: red;">*</span></label>
                        <input type="file" name="ktp" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <?php if(!empty($data['ktp'])) echo "<small style='color:green;'>Tersimpan: {$data['ktp']}</small>"; ?>
                    </div>
                    <div class="form-field-group">
                        <label>Upload Ijazah <span style="color: red;">*</span></label>
                        <input type="file" name="ijazah" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <?php if(!empty($data['ijazah'])) echo "<small style='color:green;'>Tersimpan: {$data['ijazah']}</small>"; ?>
                    </div>
                    <div class="form-field-group">
                        <label>Upload SKCK <span style="color: red;">*</span></label>
                        <input type="file" name="skck" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <?php if(!empty($data['skck'])) echo "<small style='color:green;'>Tersimpan: {$data['skck']}</small>"; ?>
                    </div>
                    <div class="form-field-group">
                        <label>Upload Kartu Keluarga (KK) <span style="color: red;">*</span></label>
                        <input type="file" name="kk" class="form-control" accept=".jpg, .jpeg, .png, .pdf">
                        <?php if(!empty($data['kk'])) echo "<small style='color:green;'>Tersimpan: {$data['kk']}</small>"; ?>
                    </div>
                    <div class="form-field-group full-width">
                        <label>Upload Pas Foto (Foto Profil) <span style="color: red;">*</span></label>
                        <input type="file" name="pas_foto" class="form-control" accept=".jpg, .jpeg, .png">
                        <small style="color: #666;">Format: JPG/PNG. Foto ini otomatis menjadi foto profil Anda.</small>
                        <?php if(!empty($data['pas_foto'])) echo "<small style='color:green;'>Aktif: {$data['pas_foto']}</small>"; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function switchTab(evt, tabId) {
    let contents = document.getElementsByClassName("form-section-content");
    for (let i = 0; i < contents.length; i++) { contents[i].classList.remove("active"); }
    let buttons = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < buttons.length; i++) { buttons[i].classList.remove("active"); }
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");
}
</script>

<?php include '../includes/footer_pelamar.php'; ?>