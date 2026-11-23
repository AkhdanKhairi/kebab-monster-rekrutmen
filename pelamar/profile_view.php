<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'pelamar') {
    echo "<script>alert('Silakan login terlebih dahulu sebagai pelamar!'); window.location='../login.php';</script>";
    exit();
}

include '../includes/header_pelamar.php';
?>

<style>
    .profile-page-layout { max-width: 1150px; margin: 0 auto; display: grid; grid-template-columns: 280px 1fr; gap: 25px; align-items: start; }
    .profile-card-container { display: flex; flex-direction: column; gap: 20px; position: sticky; top: 90px; }
    .profile-card-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .profile-avatar-wrapper { width: 75px; height: 75px; margin: 0 auto 12px auto; background: #e0f2f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #b2dfdb; overflow: hidden; }
    .card-username { font-size: 13px; font-weight: 800; color: #004d40; text-transform: uppercase; margin-bottom: 4px; line-height: 1.3; }
    .card-email { font-size: 11px; color: #666; margin-bottom: 15px; word-break: break-all; }
    .btn-pdf-page { background-color: #1b5e20; color: white; border: none; width: 100%; padding: 8px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; text-decoration: none; transition: background 0.2s; }
    .btn-pdf-page:hover { background-color: #00332c; }

    .progress-detail-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.03); }
    .progress-header-title { font-size: 13px; font-weight: 800; color: #004d40; margin-bottom: 15px; text-align: center; text-transform: uppercase; letter-spacing: 0.5px; }
    .progress-item { margin-bottom: 12px; }
    .progress-item:last-child { margin-bottom: 0; }
    .progress-label-wrap { display: flex; justify-content: space-between; font-size: 11.5px; font-weight: 700; color: #444; margin-bottom: 5px; }
    .progress-bar-bg { width: 100%; height: 8px; background-color: #e0e0e0; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background-color: #1b5e20; border-radius: 4px; transition: width 0.4s ease; }

    .content-right-side { display: flex; flex-direction: column; gap: 20px; }
    .notice-banner { background-color: #fff8e1; border: 1px solid #ffe082; color: #856404; padding: 12px 20px; border-radius: 6px; font-size: 13.5px; }
    .form-section-box { background: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .section-header-bar { background-color: #004d40; color: white; padding: 12px 20px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .section-body { padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .data-field-group { display: flex; flex-direction: column; gap: 4px; }
    .data-field-group.full-width { grid-column: span 2; }
    .data-field-group label { font-size: 12px; font-weight: 700; color: #333; }
    .data-value-box { background-color: #fcfcfc; border: 1px solid #dcdcdc; padding: 9px 12px; border-radius: 6px; font-size: 13px; color: #222; min-height: 18px; }
    .val-kosong { color: #b91c1c; font-style: italic; font-size: 12px; }
    .sub-section-title { grid-column: span 2; font-size: 13.5px; font-weight: 800; color: #004d40; border-bottom: 2px solid #b2dfdb; padding-bottom: 4px; margin-top: 10px; }

    .docs-grid {
        grid-column: span 2;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
    }
    .doc-card {
        border: 1px solid #dcdcdc;
        border-radius: 10px;
        padding: 14px;
        background: #fafafa;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 10px;
    }
    .doc-card-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }
    .doc-title { font-size: 13px; font-weight: 800; color: #111; }
    .doc-tag { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
    .doc-wajib { background: #e8f5e9; color: #1b5e20; }
    .doc-opsional { background: #f1f5f9; color: #475569; }
    .doc-thumb-box {
        height: 125px;
        border-radius: 8px;
        background: #ffffff;
        border: 1px dashed #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        font-size: 12.5px;
        color: #64748b;
        gap: 6px;
        padding: 6px;
        text-align: center;
    }
    .doc-thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 6px;
    }
    .doc-file-icon { font-size: 20px; font-weight: 700; color: #004d40; flex-direction: column; }
    .doc-file-icon span { font-size: 11.5px; }
    .doc-filename {
        font-size: 11.5px;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .btn-doc-view {
        display: inline-block;
        text-align: center;
        text-decoration: none;
        background: #004d40;
        color: #ffffff;
        padding: 7px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        transition: background 0.2s;
    }
    .btn-doc-view:hover { background: #1b5e20; }

    @media (max-width: 950px) {
        .profile-page-layout { grid-template-columns: 1fr; }
        .profile-card-container { position: static; }
        .section-body { grid-template-columns: 1fr; }
        .data-field-group.full-width { grid-column: span 1; }
        .sub-section-title { grid-column: span 1; }
        .docs-grid { grid-column: span 1; grid-template-columns: 1fr; }
    }
</style>

<div class="profile-page-layout">
    <div class="profile-card-container">
        <div class="profile-card-box">
            <div id="view-avatar-wrap" class="profile-avatar-wrapper">
                <span style="font-size: 40px;">👤</span>
            </div>
            <div id="view-username" class="card-username">Memuat...</div>
            <div id="view-email" class="card-email">-</div>
            <a href="generate_cv.php" target="_blank" class="btn-pdf-page"><span>📄</span> Jadikan PDF / Cetak CV</a>
        </div>

        <div class="progress-detail-box">
            <div id="score-total-title" class="progress-header-title">Status Kelengkapan (0%)</div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Data Diri</span><span id="score-datadiri-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-datadiri-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Kontak Darurat</span><span id="score-darurat-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-darurat-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Pendidikan</span><span id="score-pendidikan-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-pendidikan-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Pengalaman Kerja</span><span id="score-kerja-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-kerja-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Internship / Magang</span><span id="score-magang-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-magang-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
            <div class="progress-item">
                <div class="progress-label-wrap"><span>Upload Berkas</span><span id="score-berkas-text">0%</span></div>
                <div class="progress-bar-bg"><div id="score-berkas-bar" class="progress-bar-fill" style="width: 0%;"></div></div>
            </div>
        </div>
    </div>

    <div class="content-right-side">
        <div class="notice-banner">
            <strong>Note*</strong><br>Harap isi <strong>Data Diri</strong> terlebih dahulu pada menu <strong>"Update Profile"</strong> sebelum mengirim lamaran Anda.
        </div>

        <!-- 1. DATA DIRI -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>👤</span> Data Diri Lengkap</div>
            <div class="section-body">
                <div class="data-field-group"><label>Nama Lengkap *</label><div class="data-value-box" data-field="nama_lengkap">Memuat...</div></div>
                <div class="data-field-group"><label>No Telepon *</label><div class="data-value-box" data-field="no_telepon">Memuat...</div></div>
                <div class="data-field-group"><label>Jenis Kelamin *</label><div class="data-value-box" data-field="jenis_kelamin">Memuat...</div></div>
                <div class="data-field-group"><label>Tempat Lahir *</label><div class="data-value-box" data-field="tempat_lahir">Memuat...</div></div>
                <div class="data-field-group"><label>Tanggal Lahir *</label><div class="data-value-box" data-field="tanggal_lahir">Memuat...</div></div>
                <div class="data-field-group"><label>Agama *</label><div class="data-value-box" data-field="agama">Memuat...</div></div>
                <div class="data-field-group"><label>Status Nikah *</label><div class="data-value-box" data-field="status_nikah">Memuat...</div></div>
                <div class="data-field-group"><label>Kode Pos *</label><div class="data-value-box" data-field="kode_pos">Memuat...</div></div>
                <div class="data-field-group"><label>Kota *</label><div class="data-value-box" data-field="kota">Memuat...</div></div>
                <div class="data-field-group"><label>No KTP *</label><div class="data-value-box" data-field="no_ktp">Memuat...</div></div>
                <div class="data-field-group"><label>Kewarganegaraan *</label><div class="data-value-box" data-field="kewarganegaraan">Memuat...</div></div>
                <div class="data-field-group"><label>Jumlah Anak</label><div class="data-value-box" data-field="jumlah_anak" data-default="0">0</div></div>
                <div class="data-field-group"><label>Berat Badan *</label><div class="data-value-box" data-field="berat_badan">Memuat...</div></div>
                <div class="data-field-group"><label>Tinggi Badan *</label><div class="data-value-box" data-field="tinggi_badan">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alamat *</label><div class="data-value-box" data-field="alamat">Memuat...</div></div>
            </div>
        </div>

        <!-- 2. KONTAK DARURAT -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>🚨</span> Kontak Darurat Keluarga</div>
            <div class="section-body">
                <div class="data-field-group"><label>Nama Keluarga *</label><div class="data-value-box" data-field="darurat_nama">Memuat...</div></div>
                <div class="data-field-group"><label>Hubungan Keluarga *</label><div class="data-value-box" data-field="darurat_hubungan">Memuat...</div></div>
                <div class="data-field-group"><label>No Telepon 1 *</label><div class="data-value-box" data-field="darurat_telp1">Memuat...</div></div>
                <div class="data-field-group"><label>No Telepon 2</label><div class="data-value-box" data-field="darurat_telp2">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alamat Keluarga *</label><div class="data-value-box" data-field="darurat_alamat">Memuat...</div></div>
            </div>
        </div>

        <!-- 3. PENDIDIKAN -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>🎓</span> Latar Belakang Pendidikan</div>
            <div class="section-body">
                <div class="data-field-group"><label>Pendidikan Terakhir *</label><div class="data-value-box" data-field="pend_terakhir">Memuat...</div></div>
                <div class="data-field-group"><label>Nama Institusi / Universitas *</label><div class="data-value-box" data-field="pend_institusi">Memuat...</div></div>
                <div class="data-field-group"><label>Jurusan *</label><div class="data-value-box" data-field="pend_jurusan">Memuat...</div></div>
                <div class="data-field-group"><label>Nilai / IPK *</label><div class="data-value-box" data-field="pend_nilai">Memuat...</div></div>
                <div class="data-field-group"><label>Mulai Tanggal *</label><div class="data-value-box" data-field="pend_mulai">Memuat...</div></div>
                <div class="data-field-group"><label>Selesai Tanggal *</label><div class="data-value-box" data-field="pend_selesai">Memuat...</div></div>
            </div>
        </div>

        <!-- 4. PENGALAMAN KERJA -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>💼</span> Pengalaman Kerja</div>
            <div class="section-body">
                <div class="sub-section-title">Pengalaman Kerja 1 (Terakhir)</div>
                <div class="data-field-group"><label>Nama Perusahaan *</label><div class="data-value-box" data-field="kerja_perusahaan">Memuat...</div></div>
                <div class="data-field-group"><label>No Telepon Perusahaan *</label><div class="data-value-box" data-field="kerja_telp">Memuat...</div></div>
                <div class="data-field-group"><label>Mulai Kerja *</label><div class="data-value-box" data-field="kerja_mulai">Memuat...</div></div>
                <div class="data-field-group"><label>Akhir Kerja *</label><div class="data-value-box" data-field="kerja_akhir">Memuat...</div></div>
                <div class="data-field-group"><label>Jabatan *</label><div class="data-value-box" data-field="kerja_jabatan">Memuat...</div></div>
                <div class="data-field-group"><label>Jobdesk / Deskripsi Pekerjaan *</label><div class="data-value-box" data-field="kerja_jobdesk" data-nl2br="1">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alamat Perusahaan *</label><div class="data-value-box" data-field="kerja_alamat">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alasan Berhenti *</label><div class="data-value-box" data-field="kerja_alasan">Memuat...</div></div>

                <div class="sub-section-title">Pengalaman Kerja 2 (Sebelumnya)</div>
                <div class="data-field-group"><label>Nama Perusahaan</label><div class="data-value-box" data-field="kerja2_perusahaan">Memuat...</div></div>
                <div class="data-field-group"><label>No Telepon Perusahaan</label><div class="data-value-box" data-field="kerja2_telp">Memuat...</div></div>
                <div class="data-field-group"><label>Mulai Kerja</label><div class="data-value-box" data-field="kerja2_mulai">Memuat...</div></div>
                <div class="data-field-group"><label>Akhir Kerja</label><div class="data-value-box" data-field="kerja2_akhir">Memuat...</div></div>
                <div class="data-field-group"><label>Jabatan</label><div class="data-value-box" data-field="kerja2_jabatan">Memuat...</div></div>
                <div class="data-field-group"><label>Jobdesk / Deskripsi Pekerjaan</label><div class="data-value-box" data-field="kerja2_jobdesk" data-nl2br="1">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alamat Perusahaan</label><div class="data-value-box" data-field="kerja2_alamat">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Alasan Berhenti</label><div class="data-value-box" data-field="kerja2_alasan">Memuat...</div></div>
            </div>
        </div>

        <!-- 5. INTERNSHIP / MAGANG -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>📋</span> Pengalaman Internship / Magang</div>
            <div class="section-body">
                <div class="sub-section-title">Magang 1</div>
                <div class="data-field-group"><label>Nama Instansi</label><div class="data-value-box" data-field="magang1_instansi">Memuat...</div></div>
                <div class="data-field-group"><label>Posisi / Peran</label><div class="data-value-box" data-field="magang1_posisi">Memuat...</div></div>
                <div class="data-field-group"><label>Mulai Magang</label><div class="data-value-box" data-field="magang1_mulai">Memuat...</div></div>
                <div class="data-field-group"><label>Akhir Magang</label><div class="data-value-box" data-field="magang1_akhir">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Kegiatan</label><div class="data-value-box" data-field="magang1_jobdesk" data-nl2br="1">Memuat...</div></div>

                <div class="sub-section-title">Magang 2</div>
                <div class="data-field-group"><label>Nama Instansi</label><div class="data-value-box" data-field="magang2_instansi">Memuat...</div></div>
                <div class="data-field-group"><label>Posisi / Peran</label><div class="data-value-box" data-field="magang2_posisi">Memuat...</div></div>
                <div class="data-field-group"><label>Mulai Magang</label><div class="data-value-box" data-field="magang2_mulai">Memuat...</div></div>
                <div class="data-field-group"><label>Akhir Magang</label><div class="data-value-box" data-field="magang2_akhir">Memuat...</div></div>
                <div class="data-field-group full-width"><label>Jobdesk / Kegiatan</label><div class="data-value-box" data-field="magang2_jobdesk" data-nl2br="1">Memuat...</div></div>
            </div>
        </div>

        <!-- 6. DOKUMEN YANG DIUPLOAD -->
        <div class="form-section-box">
            <div class="section-header-bar"><span>📁</span> Dokumen / Berkas yang Diupload</div>
            <div class="section-body">
                <div id="view-docs-grid" class="docs-grid"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderDokumenCardJs(fileInfo, label, wajib = true) {
        const badgeWajib = wajib
            ? '<span class="doc-tag doc-wajib">Wajib</span>'
            : '<span class="doc-tag doc-opsional">Opsional</span>';

        const filename = fileInfo?.filename || '';
        const exists   = Boolean(fileInfo?.exists);
        const isImage  = Boolean(fileInfo?.is_image);
        const ext      = (fileInfo?.ext || 'FILE').toUpperCase();
        const url      = fileInfo?.url || '';

        let bodyHtml = '';
        if (exists) {
            if (isImage) {
                bodyHtml += `<div class="doc-thumb-box"><img src="${escapeHtml(url)}" alt="${escapeHtml(label)}"></div>`;
            } else {
                bodyHtml += `<div class="doc-thumb-box doc-file-icon">📄 <span>Dokumen ${escapeHtml(ext)}</span></div>`;
            }
            bodyHtml += `<div class="doc-filename" title="${escapeHtml(filename)}">${escapeHtml(filename)}</div>`;
            bodyHtml += `<div class="doc-actions"><a href="${escapeHtml(url)}" target="_blank" class="btn-doc-view">Lihat Berkas ↗</a></div>`;
        } else if (filename !== '') {
            bodyHtml += `<div class="doc-thumb-box doc-missing">⚠️ File tidak ditemukan di folder uploads</div>`;
            bodyHtml += `<div class="doc-filename">${escapeHtml(filename)}</div>`;
        } else {
            bodyHtml += `<div class="doc-thumb-box doc-empty">Belum diupload</div>`;
            bodyHtml += `<div class="doc-filename"><span class="val-kosong">Belum ada berkas</span></div>`;
        }

        return `
            <div class="doc-card">
                <div class="doc-card-head">
                    <span class="doc-title">${escapeHtml(label)}</span>
                    ${badgeWajib}
                </div>
                ${bodyHtml}
            </div>
        `;
    }

    async function loadProfileView() {
        try {
            const res = await fetch('../api/pelamar/profile.php', {
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (!res.ok || !result.success || !result.data?.pelamar) {
                if (res.status === 401) {
                    alert(result.message || 'Silakan login terlebih dahulu sebagai pelamar!');
                    window.location.href = '../login.php';
                    return;
                }
                alert(result.message || 'Gagal memuat profil pelamar.');
                return;
            }

            const pelamar = result.data.pelamar;
            const scores  = result.data.kelengkapan?.scores || {};
            const files   = result.data.files || {};

            // Kartu kiri
            document.getElementById('view-username').textContent = pelamar.nama_lengkap || 'Nama Pelamar';
            document.getElementById('view-email').textContent = pelamar.email || '-';

            const pasFoto = files.pas_foto;
            if (pasFoto && pasFoto.exists && pasFoto.url) {
                document.getElementById('view-avatar-wrap').innerHTML =
                    `<img src="${escapeHtml(pasFoto.url)}" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">`;
            }

            // Progress bar kelengkapan
            document.getElementById('score-total-title').textContent = `Status Kelengkapan (${scores.total ?? 0}%)`;
            ['datadiri', 'darurat', 'pendidikan', 'kerja', 'magang', 'berkas'].forEach(key => {
                const val = scores[key] ?? 0;
                const txt = document.getElementById(`score-${key}-text`);
                const bar = document.getElementById(`score-${key}-bar`);
                if (txt) txt.textContent = `${val}%`;
                if (bar) bar.style.width = `${val}%`;
            });

            // Isi semua field teks di 5 section
            document.querySelectorAll('.data-value-box[data-field]').forEach(el => {
                const field = el.getAttribute('data-field');
                const defaultVal = el.getAttribute('data-default');
                const isNl2br = el.getAttribute('data-nl2br') === '1';

                let rawVal = pelamar[field];
                if ((rawVal === null || rawVal === undefined || String(rawVal).trim() === '') && defaultVal !== null) {
                    rawVal = defaultVal;
                }

                const strVal = String(rawVal ?? '').trim();
                if (strVal === '') {
                    el.innerHTML = '<span class="val-kosong">Belum diisi</span>';
                } else if (isNl2br) {
                    el.innerHTML = escapeHtml(strVal).replace(/\r\n|\r|\n/g, '<br>');
                } else {
                    el.textContent = strVal;
                }
            });

            // Render grid 5 dokumen wajib
            const docsGrid = document.getElementById('view-docs-grid');
            docsGrid.innerHTML = [
                renderDokumenCardJs(files.ktp, 'Kartu Tanda Penduduk (KTP)', true),
                renderDokumenCardJs(files.ijazah, 'Ijazah Terakhir', true),
                renderDokumenCardJs(files.skck, 'SKCK', true),
                renderDokumenCardJs(files.kk, 'Kartu Keluarga (KK)', true),
                renderDokumenCardJs(files.pas_foto, 'Pas Foto (Foto Profil)', true),
            ].join('');
        } catch (err) {
            alert('Terjadi kesalahan saat memuat data profil dari server.');
        }
    }

    loadProfileView();
</script>

<?php include '../includes/footer_pelamar.php'; ?>