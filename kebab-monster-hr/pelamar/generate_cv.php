<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'pelamar') {
    exit("Akses ditolak.");
}

$id_pelamar = $_SESSION['user_id'];
$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);

$query = mysqli_query($conn, "SELECT * FROM pelamar WHERE id_pelamar = $id_pelamar");
$data  = mysqli_fetch_assoc($query);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CV - <?php echo htmlspecialchars($data['nama_lengkap'] ?? 'Pelamar'); ?></title>
    <style>
        /* Ukuran standar kertas A4 dan margin minimal agar pas 1 halaman */
        @page {
            size: A4;
            margin: 8mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 11.5px;
        }
        .cv-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
        }
        .cv-header {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 2px solid #1b5e20;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .cv-photo {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            overflow: hidden;
            background: #eee;
            border: 2px solid #1b5e20;
            flex-shrink: 0;
        }
        .cv-photo img { width: 100%; height: 100%; object-fit: cover; }
        .cv-title h1 { margin: 0; font-size: 20px; color: #1b5e20; text-transform: uppercase; letter-spacing: 0.5px; }
        .cv-title p { margin: 2px 0; font-size: 11.5px; color: #555; }
        
        .cv-section { margin-bottom: 8px; }
        .cv-section h2 { 
            font-size: 12.5px; 
            text-transform: uppercase; 
            color: #1b5e20; 
            border-bottom: 1px solid #1b5e20; 
            padding-bottom: 2px; 
            margin-bottom: 6px; 
            letter-spacing: 0.5px; 
        }
        
        .cv-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: 3px 15px; 
            font-size: 11.5px; 
        }
        .cv-item { margin-bottom: 2px; }
        .cv-label { font-weight: bold; color: #444; font-size: 10.5px; text-transform: uppercase; }
        .cv-value { color: #222; }
        
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="cv-container">
        <!-- HEADER CV -->
        <div class="cv-header">
            <div class="cv-photo">
                <?php if (!empty($data['pas_foto']) && file_exists('../uploads/' . $data['pas_foto'])): ?>
                    <img src="../uploads/<?php echo htmlspecialchars($data['pas_foto']); ?>" alt="Foto">
                <?php else: ?>
                    <div style="display:flex; align-items:center; justify-content:center; height:100%; font-size:26px;">👤</div>
                <?php endif; ?>
            </div>
            <div class="cv-title">
                <h1><?php echo htmlspecialchars($data['nama_lengkap'] ?? '-'); ?></h1>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($data['email'] ?? '-'); ?> &nbsp;|&nbsp; <strong>No. HP:</strong> <?php echo htmlspecialchars($data['no_telepon'] ?? '-'); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($data['alamat'] ?? '-'); ?>, <?php echo htmlspecialchars($data['kota'] ?? ''); ?></p>
            </div>
        </div>

        <!-- 1. DATA DIRI -->
        <div class="cv-section">
            <h2>Informasi Pribadi</h2>
            <div class="cv-grid">
                <div class="cv-item"><div class="cv-label">Tempat, Tanggal Lahir</div><div class="cv-value"><?php echo htmlspecialchars(($data['tempat_lahir'] ?? '-') . ', ' . ($data['tanggal_lahir'] ?? '-')); ?></div></div>
                <div class="cv-item"><div class="cv-label">Jenis Kelamin</div><div class="cv-value"><?php echo htmlspecialchars($data['jenis_kelamin'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Agama</div><div class="cv-value"><?php echo htmlspecialchars($data['agama'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Status Pernikahan</div><div class="cv-value"><?php echo htmlspecialchars($data['status_nikah'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">No. KTP</div><div class="cv-value"><?php echo htmlspecialchars($data['no_ktp'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Kewarganegaraan</div><div class="cv-value"><?php echo htmlspecialchars($data['kewarganegaraan'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 2. PENDIDIKAN -->
        <div class="cv-section">
            <h2>Latar Belakang Pendidikan</h2>
            <div class="cv-grid">
                <div class="cv-item"><div class="cv-label">Pendidikan Terakhir</div><div class="cv-value"><?php echo htmlspecialchars($data['pend_terakhir'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Institusi / Universitas</div><div class="cv-value"><?php echo htmlspecialchars($data['pend_institusi'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Jurusan</div><div class="cv-value"><?php echo htmlspecialchars($data['pend_jurusan'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Nilai / IPK</div><div class="cv-value"><?php echo htmlspecialchars($data['pend_nilai'] ?? '-'); ?></div></div>
                <div class="cv-item" style="grid-column: span 2;"><div class="cv-label">Periode Studi</div><div class="cv-value"><?php echo htmlspecialchars(($data['pend_mulai'] ?? '-') . ' s.d ' . ($data['pend_selesai'] ?? '-')); ?></div></div>
            </div>
        </div>

        <!-- 3. PENGALAMAN KERJA -->
        <div class="cv-section">
            <h2>Pengalaman Kerja</h2>
            <div class="cv-grid">
                <div class="cv-item"><div class="cv-label">Perusahaan</div><div class="cv-value"><?php echo htmlspecialchars($data['kerja_perusahaan'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Jabatan (Awal ➔ Akhir)</div><div class="cv-value"><?php echo htmlspecialchars(($data['kerja_jabatan_awal'] ?? '-') . ' ➔ ' . ($data['kerja_jabatan_akhir'] ?? '-')); ?></div></div>
                <div class="cv-item"><div class="cv-label">Periode Kerja</div><div class="cv-value"><?php echo htmlspecialchars(($data['kerja_mulai'] ?? '-') . ' s.d ' . ($data['kerja_akhir'] ?? '-')); ?></div></div>
                <div class="cv-item"><div class="cv-label">Kontak Perusahaan</div><div class="cv-value"><?php echo htmlspecialchars($data['kerja_telp'] ?? '-'); ?></div></div>
                <div class="cv-item" style="grid-column: span 2;"><div class="cv-label">Alamat Perusahaan</div><div class="cv-value"><?php echo htmlspecialchars($data['kerja_alamat'] ?? '-'); ?></div></div>
                <div class="cv-item" style="grid-column: span 2;"><div class="cv-label">Alasan Berhenti</div><div class="cv-value"><?php echo htmlspecialchars($data['kerja_alasan'] ?? '-'); ?></div></div>
            </div>
        </div>

        <!-- 4. KONTAK DARURAT -->
        <div class="cv-section">
            <h2>Kontak Darurat</h2>
            <div class="cv-grid">
                <div class="cv-item"><div class="cv-label">Nama Keluarga</div><div class="cv-value"><?php echo htmlspecialchars($data['darurat_nama'] ?? '-'); ?></div></div>
                <div class="cv-item"><div class="cv-label">Hubungan</div><div class="cv-value"><?php echo htmlspecialchars($data['darurat_hubungan'] ?? '-'); ?></div></div>
                <div class="cv-item" style="grid-column: span 2;"><div class="cv-label">No. Telepon</div><div class="cv-value"><?php echo htmlspecialchars(($data['darurat_telp1'] ?? '-') . (!empty($data['darurat_telp2']) ? ' / ' . $data['darurat_telp2'] : '')); ?></div></div>
            </div>
        </div>
    </div>

</body>
</html>