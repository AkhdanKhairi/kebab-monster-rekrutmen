<?php
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .admin-page-wrapper {
        max-width: 1320px;
        margin: 12px auto 36px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .page-header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 10px;
    }
    .page-heading {
        font-size: 18px;
        font-weight: 700;
        color: #111;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .badge-total {
        background-color: #0b6623;
        color: #ffffff;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 600;
    }

    .admin-card-box {
        background-color: #f4f5f9;
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }
    .card-banner-header {
        background-color: #0b6623;
        color: #ffffff;
        border-radius: 25px;
        padding: 10px 18px;
        font-size: 13.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-bottom: 14px;
        text-align: center;
    }
    .inner-table-container {
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        padding: 16px;
    }

    .filter-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .search-form-group {
        display: flex;
        gap: 8px;
        flex: 1;
        min-width: 240px;
        flex-wrap: wrap;
    }
    .form-input-filter {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 12px;
        font-size: 13px;
        color: #333;
        background-color: #fff;
    }
    .form-input-filter.search-box {
        flex: 1;
        min-width: 200px;
    }
    .btn-filter {
        background-color: #0b6623;
        color: #fff;
        border: none;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-filter:hover { background-color: #084d1a; }
    .btn-reset {
        background-color: #e2e8f0;
        color: #334155;
        text-decoration: none;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }

    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .pelamar-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
    }
    .pelamar-table th {
        background-color: #e8f5e9;
        color: #0b6623;
        font-weight: 700;
        text-align: left;
        padding: 11px 10px;
        border-bottom: 2px solid #c8e6c9;
        white-space: nowrap;
    }
    .sort-header-btn {
        background: none;
        border: none;
        padding: 0;
        font-family: inherit;
        font-size: inherit;
        font-weight: inherit;
        color: #0b6623;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    .sort-header-btn:hover {
        color: #053b13;
        text-decoration: underline;
    }
    .sort-icon {
        font-size: 10px;
        opacity: 0.8;
    }
    .pelamar-table td {
        padding: 10px 10px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
        color: #333;
    }
    .pelamar-table tbody tr:hover {
        background-color: #f9fafb;
    }

    .pelamar-profile-cell {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .mini-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #e0f2f1;
        border: 1px solid #b2dfdb;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    .mini-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pelamar-name {
        font-weight: 700;
        color: #111;
        display: block;
    }
    .pelamar-sub {
        font-size: 11.5px;
        color: #666;
        display: block;
        word-break: break-word;
    }

    .badge-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: normal;
    }
    .badge-posisi { background-color: #e0f2fe; color: #075985; font-weight: 600; }

    .select-status-lamaran {
        padding: 6px 10px;
        min-height: 34px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        border: 1.5px solid #cbd5e1;
        cursor: pointer;
        outline: none;
        transition: all 0.2s;
    }
    .st-Pending     { background-color: #fef3c7; color: #92400e; border-color: #fcd34d; }
    .st-Screening   { background-color: #e0f2fe; color: #0369a1; border-color: #7dd3fc; }
    .st-Psikotes    { background-color: #f3e8ff; color: #6b21a8; border-color: #d8b4fe; }
    .st-Interview   { background-color: #ffedd5; color: #9a3412; border-color: #fdba74; }
    .st-Hasil-Akhir { background-color: #dcfce7; color: #166534; border-color: #86efac; }

    .action-btn-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .btn-aksi {
        padding: 6px 10px;
        min-height: 34px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }
    .btn-aksi:hover { opacity: 0.88; }
    .btn-detail { background-color: #0b6623; color: #ffffff; }
    .btn-cv { background-color: #f59e0b; color: #ffffff; }
    .btn-hapus { background-color: #dc2626; color: #ffffff; }

    .alert-msg-success {
        display: none;
        background-color: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 14px;
    }
    .alert-msg-error {
        display: none;
        background-color: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    @media (max-width: 768px) {
        .admin-page-wrapper {
            margin: 6px auto 28px auto;
            padding: 0 8px;
        }
        .page-heading {
            font-size: 16px;
        }
        .admin-card-box {
            padding: 12px;
            border-radius: 12px;
        }
        .inner-table-container {
            padding: 12px;
            border-radius: 10px;
        }
        .search-form-group {
            flex-direction: column;
            min-width: 100%;
        }
        .form-input-filter,
        .btn-filter,
        .btn-reset {
            width: 100%;
            min-height: 42px;
            font-size: 16px;
        }

        .pelamar-table {
            display: block;
        }
        .pelamar-table thead {
            display: none;
        }
        .pelamar-table tbody {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .pelamar-table tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .pelamar-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: right;
            font-size: 13px;
        }
        .pelamar-table td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .pelamar-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #0b6623;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .pelamar-table td[data-label="No"] {
            justify-content: flex-start;
            font-weight: 700;
            color: #0b6623;
        }
        .pelamar-table td[data-label="Nama Pelamar"] {
            align-items: flex-start;
        }
        .pelamar-profile-cell {
            text-align: left;
        }
        .select-status-lamaran {
            min-height: 38px;
            font-size: 13px;
        }
        .action-btn-group {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }
        .btn-aksi {
            min-height: 38px;
            font-size: 12.5px;
        }
    }
</style>

<div class="admin-page-wrapper">
    <div class="page-header-row">
        <h1 class="page-heading">DATA PELAMAR</h1>
        <span class="badge-total" id="badgeTotalPelamar">Total Pelamar: 0 Orang</span>
    </div>

    <div class="alert-msg-success" id="pelamarAlertSuccess"></div>
    <div class="alert-msg-error" id="pelamarAlertError"></div>

    <div class="admin-card-box">
        <div class="card-banner-header">
            <span>👥</span> Daftar Pelamar & Tahapan Status Lamaran
        </div>

        <div class="inner-table-container">
            <form id="formFilterPelamar" class="filter-toolbar">
                <div class="search-form-group">
                    <input type="text" id="filterCariPelamar" name="cari" class="form-input-filter search-box" placeholder="Cari nama, email, kota, atau no HP...">
                    <select id="filterPendidikan" name="pendidikan" class="form-input-filter">
                        <option value="">-- Semua Pendidikan --</option>
                        <option value="SMA">SMA / SMK</option>
                        <option value="D3">D3</option>
                        <option value="S1">S1</option>
                        <option value="S2">S2</option>
                    </select>
                    <select id="filterStatusPelamar" name="filter_status" class="form-input-filter">
                        <option value="">-- Semua Status Lamaran --</option>
                        <option value="Pending">Pending</option>
                        <option value="Screening">Screening</option>
                        <option value="Psikotes">Psikotes</option>
                        <option value="Interview">Interview</option>
                        <option value="Hasil Akhir">Hasil Akhir</option>
                    </select>
                    <button type="submit" class="btn-filter">🔍 Cari</button>
                    <button type="button" id="btnResetPelamar" class="btn-reset" style="display:none;">Reset</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="pelamar-table">
                    <thead>
                        <tr>
                            <th style="width: 35px;">No</th>
                            <th><button type="button" class="sort-header-btn" data-sort="nama">Nama Pelamar <span class="sort-icon" id="sortIcon-nama">⇅</span></button></th>
                            <th><button type="button" class="sort-header-btn" data-sort="pendidikan">Pendidikan <span class="sort-icon" id="sortIcon-pendidikan">⇅</span></button></th>
                            <th><button type="button" class="sort-header-btn" data-sort="nikah">Status Nikah <span class="sort-icon" id="sortIcon-nikah">⇅</span></button></th>
                            <th><button type="button" class="sort-header-btn" data-sort="kota">Kota <span class="sort-icon" id="sortIcon-kota">⇅</span></button></th>
                            <th><button type="button" class="sort-header-btn" data-sort="lamaran">Lamaran yang Dituju <span class="sort-icon" id="sortIcon-lamaran">⇅</span></button></th>
                            <th>Kontak & Domisili</th>
                            <th><button type="button" class="sort-header-btn" data-sort="status">Status Lamaran <span class="sort-icon" id="sortIcon-status">⇅</span></button></th>
                            <th><button type="button" class="sort-header-btn" data-sort="tgl_daftar">Tgl Melamar <span class="sort-icon" id="sortIcon-tgl_daftar">▼</span></button></th>
                            <th style="text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyPelamar">
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 30px; color: #777;">
                                Memuat data pelamar...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const bulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const daftarStatus = ['Pending', 'Screening', 'Psikotes', 'Interview', 'Hasil Akhir'];

    let currentSort = 'tgl_daftar';
    let currentOrder = 'DESC';

    const formFilter = document.getElementById('formFilterPelamar');
    const inputCari = document.getElementById('filterCariPelamar');
    const selectPend = document.getElementById('filterPendidikan');
    const selectStatus = document.getElementById('filterStatusPelamar');
    const btnReset = document.getElementById('btnResetPelamar');
    const tbody = document.getElementById('tbodyPelamar');
    const badgeTotal = document.getElementById('badgeTotalPelamar');
    const alertOk = document.getElementById('pelamarAlertSuccess');
    const alertErr = document.getElementById('pelamarAlertError');

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function strimWidth(str, maxLen) {
        const s = String(str ?? '');
        if (s.length <= maxLen) return s;
        return s.slice(0, maxLen) + '...';
    }

    function showSuccess(msg) {
        alertErr.style.display = 'none';
        alertOk.style.display = 'block';
        alertOk.innerHTML = '✅ ' + msg;
    }

    function showError(msg) {
        alertOk.style.display = 'none';
        alertErr.style.display = 'block';
        alertErr.textContent = '❌ ' + msg;
    }

    function formatTanggal(tgl) {
        if (!tgl) return '-';
        const clean = String(tgl).split(' ')[0];
        const parts = clean.split('-');
        if (parts.length !== 3) return tgl;
        const bIdx = parseInt(parts[1], 10) - 1;
        return `${parts[2]} ${bulanSingkat[bIdx] || parts[1]} ${parts[0]}`;
    }

    function updateSortIcons() {
        document.querySelectorAll('.sort-icon').forEach(el => {
            el.textContent = '⇅';
        });
        const activeIcon = document.getElementById('sortIcon-' + currentSort);
        if (activeIcon) {
            activeIcon.textContent = currentOrder === 'ASC' ? '▲' : '▼';
        }
    }

    function renderPelamar(list) {
        badgeTotal.textContent = `Total Pelamar: ${(list || []).length} Orang`;
        if (!Array.isArray(list) || list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 30px; color: #777;">Belum ada data pelamar yang ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map((row, idx) => {
            const idPel = parseInt(row.id_pelamar, 10);
            const namaTampil = row.nama_lengkap || '-';
            const sudahMelamar = Boolean(row.sudah_melamar);
            const statusNorm = daftarStatus.includes(row.status_norm) ? row.status_norm : 'Pending';
            const statusCss = statusNorm.replace(/\s+/g, '-');

            const avatarHtml = (row.foto_exists && row.foto_file)
                ? `<img src="../uploads/${escapeHtml(row.foto_file)}" alt="Foto">`
                : '👤';

            const posisiHtml = row.posisi_dilamar
                ? `<span class="badge-status badge-posisi">${escapeHtml(row.posisi_dilamar)}</span>`
                : `<span class="pelamar-sub" style="font-style: italic;">Belum melamar</span>`;

            const statusHtml = sudahMelamar
                ? `<select class="select-status-lamaran st-${escapeHtml(statusCss)}" data-status-id="${idPel}" title="Klik untuk mengubah tahap seleksi pelamar">
                    ${daftarStatus.map(st => `<option value="${escapeHtml(st)}" ${statusNorm === st ? 'selected' : ''}>${escapeHtml(st)}</option>`).join('')}
                   </select>`
                : `<span class="pelamar-sub" style="font-style: italic;">Belum melamar</span>`;

            return `
                <tr>
                    <td data-label="No">${idx + 1}</td>
                    <td data-label="Nama Pelamar">
                        <div class="pelamar-profile-cell">
                            <div class="mini-avatar">${avatarHtml}</div>
                            <div>
                                <span class="pelamar-name">${escapeHtml(namaTampil)}</span>
                                <span class="pelamar-sub">${escapeHtml(row.jenis_kelamin || '-')}</span>
                            </div>
                        </div>
                    </td>
                    <td data-label="Pendidikan">
                        <div>
                            <strong>${escapeHtml(row.pend_terakhir || '-')}</strong>
                            <span class="pelamar-sub">${escapeHtml(row.pend_jurusan || '-')}</span>
                        </div>
                    </td>
                    <td data-label="Status Nikah">${escapeHtml(row.status_nikah || '-')}</td>
                    <td data-label="Kota"><strong>${escapeHtml(row.kota || '-')}</strong></td>
                    <td data-label="Lamaran yang Dituju">${posisiHtml}</td>
                    <td data-label="Kontak & Domisili">
                        <div>
                            <div>📞 ${escapeHtml(row.no_telepon || '-')}</div>
                            <span class="pelamar-sub">✉️ ${escapeHtml(row.email || '-')}</span>
                            <span class="pelamar-sub" title="${escapeHtml(row.alamat || '')}">
                                🏠 ${escapeHtml(strimWidth(row.alamat || '-', 32))}
                            </span>
                        </div>
                    </td>
                    <td data-label="Status Lamaran">${statusHtml}</td>
                    <td data-label="Tgl Melamar" style="white-space: nowrap;">${escapeHtml(formatTanggal(row.tgl_daftar_final))}</td>
                    <td data-label="Aksi">
                        <div class="action-btn-group" style="justify-content: center;">
                            <a href="detail_pelamar.php?id=${idPel}" class="btn-aksi btn-detail" title="Lihat Detail">👁️ Detail</a>
                            <a href="cetak_cv_pelamar.php?id=${idPel}" target="_blank" class="btn-aksi btn-cv" title="Cetak CV">📄 CV</a>
                            <button type="button" class="btn-aksi btn-hapus" data-delete-pelamar="${idPel}" data-nama="${escapeHtml(namaTampil)}" title="Hapus">🗑️ Hapus</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadPelamar() {
        const cari = inputCari.value.trim();
        const pend = selectPend.value.trim();
        const status = selectStatus.value.trim();
        btnReset.style.display = (cari !== '' || pend !== '' || status !== '') ? 'inline-flex' : 'none';
        updateSortIcons();

        const params = new URLSearchParams({
            sort: currentSort,
            order: currentOrder
        });
        if (cari !== '') params.set('cari', cari);
        if (pend !== '') params.set('pendidikan', pend);
        if (status !== '') params.set('filter_status', status);

        try {
            const res = await fetch(`../api/admin/pelamar.php?${params.toString()}`, {
                method: 'GET',
                credentials: 'same-origin'
            });
            const result = await res.json();

            if (res.status === 401) {
                alert(result.message || 'Silakan login terlebih dahulu sebagai HR/Admin!');
                window.location.href = '../login.php';
                return;
            }

            if (!res.ok || !result.success || !result.data) {
                throw new Error(result.message || 'Gagal memuat data pelamar');
            }

            renderPelamar(result.data.pelamar_list || []);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="10" style="text-align: center; padding: 30px; color: #c62828;">Gagal memuat data pelamar.</td></tr>`;
        }
    }

    document.querySelectorAll('.sort-header-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const col = this.getAttribute('data-sort');
            if (!col) return;
            if (currentSort === col) {
                currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                currentSort = col;
                currentOrder = col === 'tgl_daftar' ? 'DESC' : 'ASC';
            }
            loadPelamar();
        });
    });

    tbody.addEventListener('change', async function(e) {
        const sel = e.target.closest('[data-status-id]');
        if (!sel) return;
        const idPel = parseInt(sel.getAttribute('data-status-id'), 10);
        const statusBaru = sel.value;

        try {
            const res = await fetch('../api/admin/pelamar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'update_status',
                    id_pelamar: idPel,
                    status_baru: statusBaru
                })
            });
            const result = await res.json();
            if (res.ok && result.success) {
                showSuccess(`Status lamaran berhasil diperbarui menjadi <strong>${escapeHtml(statusBaru)}</strong>!`);
                loadPelamar();
            } else {
                showError(result.message || 'Gagal memperbarui status lamaran.');
            }
        } catch (err) {
            showError('Terjadi kesalahan koneksi saat mengubah status.');
        }
    });

    tbody.addEventListener('click', async function(e) {
        const delBtn = e.target.closest('[data-delete-pelamar]');
        if (!delBtn) return;
        const idPel = parseInt(delBtn.getAttribute('data-delete-pelamar'), 10);
        const nama = delBtn.getAttribute('data-nama') || '';

        if (!confirm(`Yakin ingin menghapus data pelamar ${nama}?`)) return;

        try {
            const res = await fetch('../api/admin/pelamar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    action: 'delete',
                    id_pelamar: idPel
                })
            });
            const result = await res.json();
            if (res.ok && result.success) {
                showSuccess(escapeHtml(result.message || 'Data pelamar berhasil dihapus!'));
                loadPelamar();
            } else {
                showError(result.message || 'Gagal menghapus data pelamar.');
            }
        } catch (err) {
            showError('Terjadi kesalahan koneksi saat menghapus data.');
        }
    });

    formFilter.addEventListener('submit', function(e) {
        e.preventDefault();
        loadPelamar();
    });

    btnReset.addEventListener('click', function() {
        inputCari.value = '';
        selectPend.value = '';
        selectStatus.value = '';
        loadPelamar();
    });

    loadPelamar();
})();
</script>

<?php
include __DIR__ . '/../includes/footer_admin.php';
?>