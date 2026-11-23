<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = strtolower(trim((string) ($_SESSION['role'] ?? '')));
if (!isset($_SESSION['user_id']) || !in_array($role, ['admin', 'hr', 'hrd'], true)) {
    exit("Akses ditolak.");
}

$id_pelamar = intval($_GET['id'] ?? 0);
if ($id_pelamar <= 0) {
    exit("ID pelamar tidak valid.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title id="cvDocTitle">CV - Pelamar</title>
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
<body>

    <div class="cv-container" id="cvContent">
        <div style="text-align:center; padding:30px; color:#555;">Memuat data CV pelamar...</div>
    </div>

    <script>
    (async function() {
        const idPelamar = <?php echo intval($id_pelamar); ?>;
        const cvContainer = document.getElementById('cvContent');

        function valOrDash(v) {
            return (v !== null && v !== undefined && String(v).trim() !== '') ? String(v) : '-';
        }

        function esc(v) {
            return valOrDash(v)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function nl2br(v) {
            return esc(v).replace(/\r\n|\r|\n/g, '<br>');
        }

        try {
            const res = await fetch(`../api/admin/pelamar_detail.php?id=${idPelamar}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (!res.ok || !result.success || !result.data || !result.data.pelamar) {
                cvContainer.innerHTML = `<div style="text-align:center; padding:30px; color:#c62828;">${esc(result.message || 'Data pelamar tidak ditemukan.')}</div>`;
                return;
            }

            const d = result.data.pelamar;
            document.title = 'CV - ' + valOrDash(d.nama_lengkap);

            const photoHtml = (d.foto_exists && d.foto_file)
                ? `<img src="../uploads/${esc(d.foto_file)}" alt="Foto">`
                : `<div style="display:flex; align-items:center; justify-content:center; height:100%; font-size:26px;">👤</div>`;

            const kodePosText = (d.kode_pos && String(d.kode_pos).trim() !== '') ? ` (${esc(d.kode_pos)})` : '';
            const adaKerja2 = Boolean(d.kerja2_perusahaan && String(d.kerja2_perusahaan).trim() !== '');
            const adaMagang1 = Boolean(d.magang1_instansi && String(d.magang1_instansi).trim() !== '');
            const adaMagang2 = Boolean(d.magang2_instansi && String(d.magang2_instansi).trim() !== '');
            const adaMagang = adaMagang1 || adaMagang2;
            const noDaruratSection = adaMagang ? '5.' : '4.';

            const kerja2Html = adaKerja2 ? `
                <tr class="sub-header-row">
                    <th colspan="4">Pengalaman Kerja 2 (Sebelumnya)</th>
                </tr>
                <tr>
                    <th>Nama Perusahaan</th>
                    <td>${esc(d.kerja2_perusahaan)}</td>
                    <th>No. Telepon</th>
                    <td>${esc(d.kerja2_telp)}</td>
                </tr>
                <tr>
                    <th>Jabatan (Awal ➔ Akhir)</th>
                    <td>${esc(d.kerja2_jabatan_awal)} ➔ ${esc(d.kerja2_jabatan_akhir)}</td>
                    <th>Periode Kerja</th>
                    <td>${esc(d.kerja2_mulai)} s.d. ${esc(d.kerja2_akhir)}</td>
                </tr>
                <tr>
                    <th>Jobdesk / Deskripsi</th>
                    <td colspan="3">${nl2br(d.kerja2_jobdesk)}</td>
                </tr>
                <tr>
                    <th>Alamat Perusahaan</th>
                    <td colspan="3">${esc(d.kerja2_alamat)}</td>
                </tr>
                <tr>
                    <th>Alasan Berhenti</th>
                    <td colspan="3">${esc(d.kerja2_alasan)}</td>
                </tr>
            ` : '';

            const magang1Html = adaMagang1 ? `
                <tr class="sub-header-row">
                    <th colspan="4">Internship / Magang 1</th>
                </tr>
                <tr>
                    <th>Instansi / Perusahaan</th>
                    <td>${esc(d.magang1_instansi)}</td>
                    <th>Posisi / Peran</th>
                    <td>${esc(d.magang1_posisi)}</td>
                </tr>
                <tr>
                    <th>Periode Magang</th>
                    <td colspan="3">${esc(d.magang1_mulai)} s.d. ${esc(d.magang1_akhir)}</td>
                </tr>
                <tr>
                    <th>Jobdesk / Kegiatan</th>
                    <td colspan="3">${nl2br(d.magang1_jobdesk)}</td>
                </tr>
            ` : '';

            const magang2Html = adaMagang2 ? `
                <tr class="sub-header-row">
                    <th colspan="4">Internship / Magang 2</th>
                </tr>
                <tr>
                    <th>Instansi / Perusahaan</th>
                    <td>${esc(d.magang2_instansi)}</td>
                    <th>Posisi / Peran</th>
                    <td>${esc(d.magang2_posisi)}</td>
                </tr>
                <tr>
                    <th>Periode Magang</th>
                    <td colspan="3">${esc(d.magang2_mulai)} s.d. ${esc(d.magang2_akhir)}</td>
                </tr>
                <tr>
                    <th>Jobdesk / Kegiatan</th>
                    <td colspan="3">${nl2br(d.magang2_jobdesk)}</td>
                </tr>
            ` : '';

            const magangSectionHtml = adaMagang ? `
                <div class="cv-section">
                    <h2>4. Pengalaman Internship / Magang</h2>
                    <table class="cv-table">
                        ${magang1Html}
                        ${magang2Html}
                    </table>
                </div>
            ` : '';

            const daruratTelp = (d.darurat_telp2 && String(d.darurat_telp2).trim() !== '')
                ? `${esc(d.darurat_telp1)} / ${esc(d.darurat_telp2)}`
                : esc(d.darurat_telp1);

            cvContainer.innerHTML = `
                <div class="cv-header">
                    <div class="cv-photo">${photoHtml}</div>
                    <div class="cv-title">
                        <h1>${esc(d.nama_lengkap)}</h1>
                        <p><strong>Email:</strong> ${esc(d.email)} &nbsp;|&nbsp; <strong>No. HP:</strong> ${esc(d.no_telepon)}</p>
                        <p><strong>Alamat:</strong> ${esc(d.alamat)}, ${esc(d.kota)}${kodePosText}</p>
                    </div>
                </div>

                <div class="cv-section">
                    <h2>1. Informasi Pribadi</h2>
                    <table class="cv-table">
                        <tr>
                            <th>Tempat, Tanggal Lahir</th>
                            <td>${esc(d.tempat_lahir)}, ${esc(d.tanggal_lahir)}</td>
                            <th>No. KTP</th>
                            <td>${esc(d.no_ktp)}</td>
                        </tr>
                        <tr>
                            <th>Jenis Kelamin</th>
                            <td>${esc(d.jenis_kelamin)}</td>
                            <th>Kewarganegaraan</th>
                            <td>${esc(d.kewarganegaraan)}</td>
                        </tr>
                        <tr>
                            <th>Agama</th>
                            <td>${esc(d.agama)}</td>
                            <th>Status Pernikahan</th>
                            <td>${esc(d.status_nikah)}</td>
                        </tr>
                        <tr>
                            <th>Tinggi / Berat Badan</th>
                            <td>${esc(d.tinggi_badan)} / ${esc(d.berat_badan)}</td>
                            <th>Jumlah Anak</th>
                            <td>${d.jumlah_anak !== null && d.jumlah_anak !== undefined && String(d.jumlah_anak) !== '' ? esc(d.jumlah_anak) : '0'}</td>
                        </tr>
                        <tr>
                            <th>Kota / Kode Pos</th>
                            <td colspan="3">${esc(d.kota)} - ${esc(d.kode_pos)}</td>
                        </tr>
                    </table>
                </div>

                <div class="cv-section">
                    <h2>2. Latar Belakang Pendidikan</h2>
                    <table class="cv-table">
                        <tr>
                            <th>Pendidikan Terakhir</th>
                            <td>${esc(d.pend_terakhir)}</td>
                            <th>Institusi / Universitas</th>
                            <td>${esc(d.pend_institusi)}</td>
                        </tr>
                        <tr>
                            <th>Jurusan</th>
                            <td>${esc(d.pend_jurusan)}</td>
                            <th>Nilai / IPK</th>
                            <td>${esc(d.pend_nilai)}</td>
                        </tr>
                        <tr>
                            <th>Periode Studi</th>
                            <td colspan="3">${esc(d.pend_mulai)} s.d. ${esc(d.pend_selesai)}</td>
                        </tr>
                    </table>
                </div>

                <div class="cv-section">
                    <h2>3. Pengalaman Kerja</h2>
                    <table class="cv-table">
                        <tr class="sub-header-row">
                            <th colspan="4">Pengalaman Kerja 1 (Terakhir)</th>
                        </tr>
                        <tr>
                            <th>Nama Perusahaan</th>
                            <td>${esc(d.kerja_perusahaan)}</td>
                            <th>No. Telepon</th>
                            <td>${esc(d.kerja_telp)}</td>
                        </tr>
                        <tr>
                            <th>Jabatan (Awal ➔ Akhir)</th>
                            <td>${esc(d.kerja_jabatan_awal)} ➔ ${esc(d.kerja_jabatan_akhir)}</td>
                            <th>Periode Kerja</th>
                            <td>${esc(d.kerja_mulai)} s.d. ${esc(d.kerja_akhir)}</td>
                        </tr>
                        <tr>
                            <th>Jobdesk / Deskripsi</th>
                            <td colspan="3">${nl2br(d.kerja_jobdesk)}</td>
                        </tr>
                        <tr>
                            <th>Alamat Perusahaan</th>
                            <td colspan="3">${esc(d.kerja_alamat)}</td>
                        </tr>
                        <tr>
                            <th>Alasan Berhenti</th>
                            <td colspan="3">${esc(d.kerja_alasan)}</td>
                        </tr>
                        ${kerja2Html}
                    </table>
                </div>

                ${magangSectionHtml}

                <div class="cv-section">
                    <h2>${noDaruratSection} Kontak Darurat</h2>
                    <table class="cv-table">
                        <tr>
                            <th>Nama Keluarga</th>
                            <td>${esc(d.darurat_nama)}</td>
                            <th>Hubungan</th>
                            <td>${esc(d.darurat_hubungan)}</td>
                        </tr>
                        <tr>
                            <th>No. Telepon</th>
                            <td colspan="3">${daruratTelp}</td>
                        </tr>
                        <tr>
                            <th>Alamat Keluarga</th>
                            <td colspan="3">${esc(d.darurat_alamat)}</td>
                        </tr>
                    </table>
                </div>
            `;

            setTimeout(function() {
                window.print();
            }, 150);
        } catch (err) {
            cvContainer.innerHTML = `<div style="text-align:center; padding:30px; color:#c62828;">Gagal memuat data CV pelamar.</div>`;
        }
    })();
    </script>

</body>
</html>