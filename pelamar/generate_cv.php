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
        @page {
            size: A4;
            margin: 10mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222;
            line-height: 1.35;
            margin: 0;
            padding: 0;
            background: #fff;
            font-size: 11px;
        }
        .cv-container {
            width: 100%;
            margin: 0 auto;
        }
        .cv-header {
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 2.5px solid #1b5e20;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .cv-photo {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            overflow: hidden;
            background: #f4f4f4;
            border: 2px solid #1b5e20;
            flex-shrink: 0;
        }
        .cv-photo img { width: 100%; height: 100%; object-fit: cover; }
        .cv-title h1 { margin: 0 0 3px 0; font-size: 19px; color: #1b5e20; text-transform: uppercase; letter-spacing: 0.5px; }
        .cv-title p { margin: 2px 0; font-size: 11px; color: #444; }
        
        .cv-section { margin-bottom: 11px; page-break-inside: avoid; }
        .cv-section h2 { 
            font-size: 11.5px; 
            text-transform: uppercase; 
            background-color: #1b5e20;
            color: #ffffff;
            padding: 4px 8px;
            margin: 0;
            letter-spacing: 0.5px; 
        }

        /* Styling Tabel CV */
        .cv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        .cv-table th, .cv-table td {
            border: 1px solid #bbb;
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }
        .cv-table th {
            background-color: #f1f8e9;
            color: #1b5e20;
            font-weight: bold;
            width: 22%;
        }
        .cv-table td {
            width: 28%;
            color: #222;
        }
        .sub-header-row th {
            background-color: #e8f5e9;
            color: #004d40;
            text-align: left;
            font-size: 11px;
        }
        
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
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
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($data['alamat'] ?? '-'); ?>, <?php echo htmlspecialchars($data['kota'] ?? ''); ?> <?php echo !empty($data['kode_pos']) ? '(' . htmlspecialchars($data['kode_pos']) . ')' : ''; ?></p>
            </div>
        </div>

        <!-- 1. INFORMASI PRIBADI -->
        <div class="cv-section">
            <h2>1. Informasi Pribadi</h2>
            <table class="cv-table">
                <tr>
                    <th>Tempat, Tanggal Lahir</th>
                    <td><?php echo htmlspecialchars(($data['tempat_lahir'] ?? '-') . ', ' . ($data['tanggal_lahir'] ?? '-')); ?></td>
                    <th>No. KTP</th>
                    <td><?php echo htmlspecialchars($data['no_ktp'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jenis Kelamin</th>
                    <td><?php echo htmlspecialchars($data['jenis_kelamin'] ?? '-'); ?></td>
                    <th>Kewarganegaraan</th>
                    <td><?php echo htmlspecialchars($data['kewarganegaraan'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Agama</th>
                    <td><?php echo htmlspecialchars($data['agama'] ?? '-'); ?></td>
                    <th>Status Pernikahan</th>
                    <td><?php echo htmlspecialchars($data['status_nikah'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Tinggi / Berat Badan</th>
                    <td><?php echo htmlspecialchars(($data['tinggi_badan'] ?? '-') . ' / ' . ($data['berat_badan'] ?? '-')); ?></td>
                    <th>Jumlah Anak</th>
                    <td><?php echo htmlspecialchars($data['jumlah_anak'] ?? '0'); ?></td>
                </tr>
                <tr>
                    <th>Kota / Kode Pos</th>
                    <td colspan="3"><?php echo htmlspecialchars(($data['kota'] ?? '-') . ' - ' . ($data['kode_pos'] ?? '-')); ?></td>
                </tr>
            </table>
        </div>

        <!-- 2. LATAR BELAKANG PENDIDIKAN -->
        <div class="cv-section">
            <h2>2. Latar Belakang Pendidikan</h2>
            <table class="cv-table">
                <tr>
                    <th>Pendidikan Terakhir</th>
                    <td><?php echo htmlspecialchars($data['pend_terakhir'] ?? '-'); ?></td>
                    <th>Institusi / Universitas</th>
                    <td><?php echo htmlspecialchars($data['pend_institusi'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jurusan</th>
                    <td><?php echo htmlspecialchars($data['pend_jurusan'] ?? '-'); ?></td>
                    <th>Nilai / IPK</th>
                    <td><?php echo htmlspecialchars($data['pend_nilai'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Periode Studi</th>
                    <td colspan="3"><?php echo htmlspecialchars(($data['pend_mulai'] ?? '-') . ' s.d. ' . ($data['pend_selesai'] ?? '-')); ?></td>
                </tr>
            </table>
        </div>

        <!-- 3. PENGALAMAN KERJA (1 & 2) -->
        <div class="cv-section">
            <h2>3. Pengalaman Kerja</h2>
            <table class="cv-table">
                <!-- Pengalaman Kerja 1 -->
                <tr class="sub-header-row">
                    <th colspan="4">Pengalaman Kerja 1 (Terakhir)</th>
                </tr>
                <tr>
                    <th>Nama Perusahaan</th>
                    <td><?php echo htmlspecialchars($data['kerja_perusahaan'] ?? '-'); ?></td>
                    <th>No. Telepon</th>
                    <td><?php echo htmlspecialchars($data['kerja_telp'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jabatan (Awal ➔ Akhir)</th>
                    <td><?php echo htmlspecialchars(($data['kerja_jabatan_awal'] ?? '-') . ' ➔ ' . ($data['kerja_jabatan_akhir'] ?? '-')); ?></td>
                    <th>Periode Kerja</th>
                    <td><?php echo htmlspecialchars(($data['kerja_mulai'] ?? '-') . ' s.d. ' . ($data['kerja_akhir'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Jobdesk / Deskripsi</th>
                    <td colspan="3"><?php echo nl2br(htmlspecialchars($data['kerja_jobdesk'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Alamat Perusahaan</th>
                    <td colspan="3"><?php echo htmlspecialchars($data['kerja_alamat'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Alasan Berhenti</th>
                    <td colspan="3"><?php echo htmlspecialchars($data['kerja_alasan'] ?? '-'); ?></td>
                </tr>

                <!-- Pengalaman Kerja 2 (Tampil jika diisi) -->
                <?php if (!empty($data['kerja2_perusahaan'])): ?>
                <tr class="sub-header-row">
                    <th colspan="4">Pengalaman Kerja 2 (Sebelumnya)</th>
                </tr>
                <tr>
                    <th>Nama Perusahaan</th>
                    <td><?php echo htmlspecialchars($data['kerja2_perusahaan'] ?? '-'); ?></td>
                    <th>No. Telepon</th>
                    <td><?php echo htmlspecialchars($data['kerja2_telp'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Jabatan (Awal ➔ Akhir)</th>
                    <td><?php echo htmlspecialchars(($data['kerja2_jabatan_awal'] ?? '-') . ' ➔ ' . ($data['kerja2_jabatan_akhir'] ?? '-')); ?></td>
                    <th>Periode Kerja</th>
                    <td><?php echo htmlspecialchars(($data['kerja2_mulai'] ?? '-') . ' s.d. ' . ($data['kerja2_akhir'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Jobdesk / Deskripsi</th>
                    <td colspan="3"><?php echo nl2br(htmlspecialchars($data['kerja2_jobdesk'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Alamat Perusahaan</th>
                    <td colspan="3"><?php echo htmlspecialchars($data['kerja2_alamat'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Alasan Berhenti</th>
                    <td colspan="3"><?php echo htmlspecialchars($data['kerja2_alasan'] ?? '-'); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <!-- 4. PENGALAMAN INTERNSHIP / MAGANG -->
        <?php if (!empty($data['magang1_instansi']) || !empty($data['magang2_instansi'])): ?>
        <div class="cv-section">
            <h2>4. Pengalaman Internship / Magang</h2>
            <table class="cv-table">
                <?php if (!empty($data['magang1_instansi'])): ?>
                <tr class="sub-header-row">
                    <th colspan="4">Internship / Magang 1</th>
                </tr>
                <tr>
                    <th>Instansi / Perusahaan</th>
                    <td><?php echo htmlspecialchars($data['magang1_instansi'] ?? '-'); ?></td>
                    <th>Posisi / Peran</th>
                    <td><?php echo htmlspecialchars($data['magang1_posisi'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Periode Magang</th>
                    <td colspan="3"><?php echo htmlspecialchars(($data['magang1_mulai'] ?? '-') . ' s.d. ' . ($data['magang1_akhir'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Jobdesk / Kegiatan</th>
                    <td colspan="3"><?php echo nl2br(htmlspecialchars($data['magang1_jobdesk'] ?? '-')); ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($data['magang2_instansi'])): ?>
                <tr class="sub-header-row">
                    <th colspan="4">Internship / Magang 2</th>
                </tr>
                <tr>
                    <th>Instansi / Perusahaan</th>
                    <td><?php echo htmlspecialchars($data['magang2_instansi'] ?? '-'); ?></td>
                    <th>Posisi / Peran</th>
                    <td><?php echo htmlspecialchars($data['magang2_posisi'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>Periode Magang</th>
                    <td colspan="3"><?php echo htmlspecialchars(($data['magang2_mulai'] ?? '-') . ' s.d. ' . ($data['magang2_akhir'] ?? '-')); ?></td>
                </tr>
                <tr>
                    <th>Jobdesk / Kegiatan</th>
                    <td colspan="3"><?php echo nl2br(htmlspecialchars($data['magang2_jobdesk'] ?? '-')); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        <?php endif; ?>

        <!-- 5. KONTAK DARURAT -->
        <div class="cv-section">
            <h2><?php echo (!empty($data['magang1_instansi']) || !empty($data['magang2_instansi'])) ? '5.' : '4.'; ?> Kontak Darurat</h2>
            <table class="cv-table">
                <tr>
                    <th>Nama Keluarga</th>
                    <td><?php echo htmlspecialchars($data['darurat_nama'] ?? '-'); ?></td>
                    <th>Hubungan</th>
                    <td><?php echo htmlspecialchars($data['darurat_hubungan'] ?? '-'); ?></td>
                </tr>
                <tr>
                    <th>No. Telepon</th>
                    <td colspan="3"><?php echo htmlspecialchars(($data['darurat_telp1'] ?? '-') . (!empty($data['darurat_telp2']) ? ' / ' . $data['darurat_telp2'] : '')); ?></td>
                </tr>
                <tr>
                    <th>Alamat Keluarga</th>
                    <td colspan="3"><?php echo htmlspecialchars($data['darurat_alamat'] ?? '-'); ?></td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>