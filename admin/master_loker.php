<?php
include __DIR__ . '/../includes/header_admin.php';
include __DIR__ . '/../includes/sidebar_admin.php';
?>

<style>
    .loker-wrapper {
        max-width: 1200px;
        margin: 12px auto 36px auto;
        padding: 0 16px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .card-container {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
    }
    .page-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #f1f5f9;
        padding-bottom: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .page-title {
        font-size: 19px;
        font-weight: 800;
        color: #0b6623;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .badge-total-loker {
        background-color: #e8f5e9;
        color: #0b6623;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 12.5px;
        font-weight: 700;
        border: 1px solid #c8e6c9;
    }
    .btn-tambah {
        background: #0b6623;
        color: #ffffff;
        min-height: 38px;
        padding: 9px 16px;
        border-radius: 8px;
        font-weight: 700;
        text-decoration: none;
        font-size: 13px;
        box-shadow: 0 2px 6px rgba(11, 102, 35, 0.2);
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-tambah:hover {
        background: #084d1a;
    }

    .filter-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .input-filter {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        min-height: 38px;
        padding: 8px 12px;
        font-size: 13px;
        color: #334155;
        background-color: #fff;
    }
    .input-filter:focus {
        outline: none;
        border-color: #0b6623;
        box-shadow: 0 0 0 2px rgba(11, 102, 35, 0.12);
    }
    .input-filter.search-input {
        flex: 1;
        min-width: 210px;
    }
    .btn-cari {
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
    .btn-cari:hover { background-color: #084d1a; }
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
    .btn-reset:hover { background-color: #cbd5e1; }

    .table-loker-wrap {
        width: 100%;
        overflow-x: auto;
    }
    .table-loker {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .table-loker th {
        background-color: #e8f5e9;
        color: #0b6623;
        padding: 12px 14px;
        border-bottom: 2px solid #c8e6c9;
        text-align: left;
        font-weight: 700;
        white-space: nowrap;
    }
    .table-loker th.th-aksi {
        text-align: center;
        min-width: 245px;
    }
    .table-loker td {
        padding: 12px 14px;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
        vertical-align: middle;
    }
    .table-loker tbody tr:hover {
        background-color: #f8fafc;
    }

    .badge-divisi {
        padding: 4px 11px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        background: #e0f2fe;
        color: #0369a1;
    }
    .badge-shift {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
    }
    .badge-aktif {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
        white-space: nowrap;
    }
    .badge-nonaktif {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
        white-space: nowrap;
    }

    .action-buttons {
        display: flex;
        gap: 6px;
        align-items: center;
        justify-content: center;
        flex-wrap: nowrap;
    }
    .btn-action {
        height: 32px;
        padding: 0 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border: 1px solid transparent;
        transition: all 0.2s;
        cursor: pointer;
        white-space: nowrap;
        box-sizing: border-box;
    }
    .btn-toggle-on {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }
    .btn-toggle-on:hover { background: #fee2e2; }
    .btn-toggle-off {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }
    .btn-toggle-off:hover { background: #bbf7d0; }
    .btn-edit {
        background: #fef3c7;
        color: #b45309;
        border-color: #fde68a;
    }
    .btn-edit:hover { background: #fde68a; }
    .btn-hapus {
        background: #fee2e2;
        color: #b91c1c;
        border-color: #fecaca;
    }
    .btn-hapus:hover { background: #fecaca; }

    .alert-success {
        display: none;
        background-color: #dcfce7;
        border: 1px solid #86efac;
        color: #166534;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }
    .alert-error {
        display: none;
        background-color: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 16px;
    }

    @media (max-width: 768px) {
        .loker-wrapper {
            margin: 8px auto 28px auto;
            padding: 0 8px;
        }
        .card-container {
            padding: 14px 12px;
            border-radius: 12px;
        }
        .page-header-flex {
            flex-direction: column;
            align-items: stretch;
            margin-bottom: 14px;
            padding-bottom: 12px;
        }
        .page-title {
            font-size: 17px;
        }
        .header-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .badge-total-loker {
            text-align: center;
        }
        .btn-tambah {
            width: 100%;
            min-height: 42px;
            font-size: 14px;
        }
        .filter-bar {
            flex-direction: column;
            gap: 8px;
        }
        .input-filter,
        .btn-cari,
        .btn-reset {
            width: 100%;
            min-height: 42px;
            font-size: 16px;
        }
        .btn-cari,
        .btn-reset {
            font-size: 14px;
        }

        .table-loker-wrap {
            overflow-x: visible;
        }
        .table-loker {
            display: block;
        }
        .table-loker thead {
            display: none;
        }
        .table-loker tbody {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .table-loker tbody tr {
            display: flex;
            flex-direction: column;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        .table-loker td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px dashed #e5e7eb;
            text-align: right;
            font-size: 13px;
            word-break: break-word;
        }
        .table-loker td:last-child {
            border-bottom: none;
            padding-top: 10px;
            justify-content: stretch;
        }
        .table-loker td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #0b6623;
            font-size: 12px;
            text-align: left;
            flex-shrink: 0;
        }
        .action-buttons {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 7px;
        }
        .action-buttons .btn-action.btn-toggle-full {
            grid-column: 1 / -1;
        }
        .btn-action {
            width: 100%;
            height: auto;
            min-height: 38px;
            padding: 7px 10px;
            font-size: 12.5px;
        }
    }
</style>

<div class="loker-wrapper">
    <div class="card-container">
        <div class="page-header-flex">
            <div class="page-title"><span>💼</span> Master Kelola Karir & Lowongan</div>
            <div class="header-actions">
                <span class="badge-total-loker" id="badgeTotalLoker">Total: 0 Lowongan</span>
                <a href="tambah_loker.php" class="btn-tambah">+ Tambah Data Karir</a>
            </div>
        </div>

        <div class="alert-success" id="alertLokerSuccess"></div>
        <div class="alert-error" id="alertLokerError"></div>

        <form id="formFilterLoker" class="filter-bar">
            <input type="text" id="filterCari" name="cari" class="input-filter search-input" placeholder="Cari posisi pekerjaan, divisi, atau shift...">
            <select id="filterDivisi" name="divisi" class="input-filter">
                <option value="">-- Semua Divisi --</option>
            </select>
            <select id="filterStatus" name="status" class="input-filter">
                <option value="">-- Semua Status --</option>
                <option value="aktif">🟢 Aktif (Bisa Dilamar)</option>
                <option value="nonaktif">🔴 Nonaktif (Ditutup)</option>
            </select>
            <button type="submit" class="btn-cari">🔍 Cari</button>
            <button type="button" id="btnResetLoker" class="btn-reset" style="display:none;">Reset</button>
        </form>

        <div class="table-loker-wrap">
            <table class="table-loker">
                <thead>
                    <tr>
                        <th style="width: 65px;">ID</th>
                        <th>Posisi Pekerjaan</th>
                        <th>Divisi</th>
                        <th style="width: 140px;">Shift / Waktu</th>
                        <th style="width: 120px;">Status</th>
                        <th style="width: 130px;">Tgl Dibuat</th>
                        <th class="th-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tbodyLowongan">
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 35px; color: #64748b;">
                            Memuat data lowongan pekerjaan...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    const bulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const formFilter = document.getElementById('formFilterLoker');
    const inputCari = document.getElementById('filterCari');
    const selectDivisi = document.getElementById('filterDivisi');
    const selectStatus = document.getElementById('filterStatus');
    const btnReset = document.getElementById('btnResetLoker');
    const tbody = document.getElementById('tbodyLowongan');
    const badgeTotal = document.getElementById('badgeTotalLoker');
    const alertOk = document.getElementById('alertLokerSuccess');
    const alertErr = document.getElementById('alertLokerError');

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
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
        if (!tgl || tgl === '0000-00-00') return '-';
        const parts = String(tgl).split('-');
        if (parts.length !== 3) return tgl;
        const bIdx = parseInt(parts[1], 10) - 1;
        return `${parts[2]} ${bulanSingkat[bIdx] || parts[1]} ${parts[0]}`;
    }

    function renderDivisiOptions(divisiList, selectedVal) {
        const current = selectedVal ?? selectDivisi.value;
        selectDivisi.innerHTML = '<option value="">-- Semua Divisi --</option>' +
            (divisiList || []).map(d => `<option value="${escapeHtml(d)}" ${d === current ? 'selected' : ''}>${escapeHtml(d)}</option>`).join('');
    }

    function renderRows(list) {
        badgeTotal.textContent = `Total: ${(list || []).length} Lowongan`;
        if (!Array.isArray(list) || list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 35px; color: #64748b;">Belum ada data lowongan pekerjaan yang ditemukan.</td></tr>`;
            return;
        }

        tbody.innerHTML = list.map(row => {
            const idLok = parseInt(row.id_lowongan, 10);
            const posisiLok = row.posisi_pekerjaan || '-';
            const isAktif = Boolean(row.is_aktif);
            const statusBadge = isAktif
                ? '<span class="badge-aktif">🟢 Aktif</span>'
                : '<span class="badge-nonaktif">🔴 Nonaktif</span>';

            const toggleBtn = isAktif
                ? `<button type="button" class="btn-action btn-toggle-on btn-toggle-full" data-toggle-id="${idLok}" data-posisi="${escapeHtml(posisiLok)}" data-aktif="1">⏸️ Matikan</button>`
                : `<button type="button" class="btn-action btn-toggle-off btn-toggle-full" data-toggle-id="${idLok}" data-posisi="${escapeHtml(posisiLok)}" data-aktif="0">▶️ Aktifkan</button>`;

            return `
                <tr>
                    <td data-label="ID"><strong>#${idLok}</strong></td>
                    <td data-label="Posisi"><strong style="color: #111827;">${escapeHtml(posisiLok)}</strong></td>
                    <td data-label="Divisi"><span class="badge-divisi">${escapeHtml(row.divisi || '-')}</span></td>
                    <td data-label="Shift"><span class="badge-shift">${escapeHtml(row.shift || '-')}</span></td>
                    <td data-label="Status">${statusBadge}</td>
                    <td data-label="Tgl Dibuat" style="white-space: nowrap;">${escapeHtml(formatTanggal(row.tgl_dibuat))}</td>
                    <td data-label="Aksi" style="text-align: center;">
                        <div class="action-buttons">
                            ${toggleBtn}
                            <a href="edit_loker.php?id=${idLok}" class="btn-action btn-edit">✏️ Edit</a>
                            <button type="button" class="btn-action btn-hapus" data-delete-id="${idLok}" data-posisi="${escapeHtml(posisiLok)}">🗑️ Hapus</button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function loadLowongan() {
        const cari = inputCari.value.trim();
        const divisi = selectDivisi.value.trim();
        const status = selectStatus.value.trim();
        btnReset.style.display = (cari !== '' || divisi !== '' || status !== '') ? 'inline-flex' : 'none';

        const params = new URLSearchParams();
        if (cari !== '') params.set('cari', cari);
        if (divisi !== '') params.set('divisi', divisi);
        if (status !== '') params.set('status', status);

        try {
            const res = await fetch(`../api/admin/lowongan.php?${params.toString()}`, {
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
                throw new Error(result.message || 'Gagal memuat data lowongan');
            }

            renderDivisiOptions(result.data.divisi_list || [], divisi);
            renderRows(result.data.lowongan_list || []);
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="7" style="text-align: center; padding: 35px; color: #c62828;">Gagal memuat data lowongan pekerjaan.</td></tr>`;
        }
    }

    tbody.addEventListener('click', async function(e) {
        const toggleBtn = e.target.closest('[data-toggle-id]');
        if (toggleBtn) {
            const idLok = parseInt(toggleBtn.getAttribute('data-toggle-id'), 10);
            const posisi = toggleBtn.getAttribute('data-posisi') || '';
            const sedangAktif = toggleBtn.getAttribute('data-aktif') === '1';
            const pesanKonfirm = sedangAktif
                ? `Matikan lowongan ${posisi}? Lowongan tidak akan bisa dilamar pelamar.`
                : `Aktifkan kembali lowongan ${posisi} agar bisa dilamar pelamar?`;

            if (!confirm(pesanKonfirm)) return;

            try {
                const res = await fetch('../api/admin/lowongan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'toggle_status', id_lowongan: idLok })
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    showSuccess(escapeHtml(result.message || 'Status lowongan diperbarui.'));
                    loadLowongan();
                } else {
                    showError(result.message || 'Gagal mengubah status lowongan.');
                }
            } catch (err) {
                showError('Terjadi kesalahan koneksi.');
            }
            return;
        }

        const delBtn = e.target.closest('[data-delete-id]');
        if (delBtn) {
            const idLok = parseInt(delBtn.getAttribute('data-delete-id'), 10);
            const posisi = delBtn.getAttribute('data-posisi') || '';
            if (!confirm(`Yakin ingin menghapus lowongan ${posisi}?`)) return;

            try {
                const res = await fetch('../api/admin/lowongan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'delete', id_lowongan: idLok })
                });
                const result = await res.json();
                if (res.ok && result.success) {
                    showSuccess(escapeHtml(result.message || 'Lowongan berhasil dihapus.'));
                    loadLowongan();
                } else {
                    showError(result.message || 'Gagal menghapus lowongan.');
                }
            } catch (err) {
                showError('Terjadi kesalahan koneksi.');
            }
        }
    });

    formFilter.addEventListener('submit', function(e) {
        e.preventDefault();
        loadLowongan();
    });

    btnReset.addEventListener('click', function() {
        inputCari.value = '';
        selectDivisi.value = '';
        selectStatus.value = '';
        loadLowongan();
    });

    loadLowongan();
})();
</script>

<?php
if (file_exists(__DIR__ . '/../includes/footer_admin.php')) {
    include __DIR__ . '/../includes/footer_admin.php';
} else {
    echo "</main></div></body></html>";
}
?>