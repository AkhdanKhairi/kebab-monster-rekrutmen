<?php
// Sertakan file header (navbar sticky)
include 'includes/header.php';

// Hubungkan ke Database MySQL
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_kebab_monster';

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Ambil data lowongan yang status view-nya 'aktif'
$query = "SELECT * FROM lowongan WHERE view = 'aktif' ORDER BY tgl_dibuat DESC";
$result = mysqli_query($conn, $query);
?>

<style>
    /* Banner Halaman Career Persis Gambar */
    .career-banner {
        background-color: #004d40;
        color: white;
        text-align: center;
        padding: 60px 20px 50px 20px;
        width: 100%;
    }
    .career-banner h1 {
        font-size: 32px;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 15px;
        letter-spacing: 0.5px;
    }
    .career-banner h1 span {
        color: #ffb300; /* Warna kuning/oranye pada tulisan Kebab Monster */
    }
    .career-banner p {
        font-size: 14.5px;
        color: #e0f2f1;
        max-width: 750px;
        margin: 0 auto 25px auto;
        line-height: 1.6;
    }

    /* Badge/Tombol Lokasi & Tanpa Biaya di dalam Banner */
    .banner-tags {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .banner-tag-item {
        background-color: rgba(0, 0, 0, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #ffffff;
        padding: 8px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* Container Utama Career */
    .career-container {
        width: 90%;
        max-width: 1100px;
        margin: 40px auto 60px auto;
    }

    .career-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Model Kartu Horizontal Lowongan */
    .job-card-h {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        padding: 22px 25px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: box-shadow 0.2s ease;
    }
    .job-card-h:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    /* Bagian Atas Kartu */
    .job-top-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 6px;
    }
    .job-title-area {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 10px;
        min-width: 0;
    }
    .job-title {
        font-size: 18px;
        font-weight: 800;
        color: #111;
        letter-spacing: 0.3px;
        overflow-wrap: break-word;
    }
    .job-divisi {
        font-size: 13px;
        color: #666;
        font-weight: 600;
    }

    .job-meta-badges {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        flex-shrink: 0;
    }
    .badge-shift {
        background-color: #e0f2f1;
        color: #00796b;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 6px;
    }
    .job-date {
        background-color: #fff8e1;
        color: #f57f17;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 6px;
    }

    .job-company {
        font-size: 13px;
        color: #333;
        font-weight: 600;
        margin-bottom: 15px;
        overflow-wrap: break-word;
    }

    /* Garis Pemisah Tipis */
    .job-divider {
        border: none;
        border-top: 1px solid #f0f0f0;
        margin: 15px 0;
    }

    /* Bagian Bawah Kartu */
    .job-bottom-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }
    .job-location {
        font-size: 13px;
        color: #555;
        display: flex;
        align-items: flex-start;
        gap: 6px;
        max-width: 70%;
        overflow-wrap: break-word;
    }
    .job-action-wrap {
        flex-shrink: 0;
    }

    /* Tombol Liat Detail Lamaran */
    .btn-detail {
        background-color: #ffb300;
        color: #111;
        padding: 9px 18px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        transition: background-color 0.2s;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        white-space: nowrap;
    }
    .btn-detail:hover {
        background-color: #ffa000;
    }

    .no-job {
        text-align: center;
        padding: 40px;
        background: white;
        border-radius: 8px;
        color: #666;
        font-size: 14px;
        border: 1px solid #e0e0e0;
    }

    /* Penyesuaian Layar HP (Mirip Referensi Foto) */
    @media (max-width: 768px) {
        .career-banner {
            padding: 36px 16px;
        }
        .career-banner h1 {
            font-size: 24px;
        }
        .career-container {
            width: 92%;
            margin: 22px auto 45px auto;
        }
        .job-card-h {
            display: flex;
            flex-direction: column;
            padding: 18px 16px;
            border-radius: 14px;
            border: 1px solid #2e7d32;
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        }
        .job-top-row {
            order: 1;
            margin-bottom: 6px;
        }
        .job-title-area {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }
        .job-title {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            color: #111827;
            line-height: 1.25;
        }
        .job-divisi {
            display: inline-block;
            background-color: #e8f5e9;
            color: #2e7d32;
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
        }
        /* Sembunyikan badge shift & tanggal di baris atas saat mobile, pindah ke bawah */
        .job-top-row .job-meta-badges {
            display: none;
        }

        .job-company {
            order: 2;
            font-size: 12.5px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 10px;
            line-height: 1.45;
        }

        .job-bottom-row {
            order: 3;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
        }
        .job-location {
            max-width: 100%;
            font-size: 12.5px;
            color: #4b5563;
            line-height: 1.45;
            padding-bottom: 14px;
            border-bottom: 1px solid #eef2f0;
        }

        /* Tampilkan badge shift & tanggal di atas tombol pada mode HP */
        .job-mobile-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
            margin-bottom: 12px;
        }
        .job-mobile-meta .badge-shift {
            background-color: #dcedc8;
            color: #2e7d32;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 11.5px;
        }
        .job-mobile-meta .job-date {
            background-color: #ffe0b2;
            color: #e65100;
            border-radius: 999px;
            padding: 4px 12px;
            font-size: 11.5px;
        }

        .job-action-wrap {
            width: 100%;
        }
        .btn-detail {
            width: 100%;
            box-sizing: border-box;
            background-color: #ffe082;
            color: #5d4037;
            border-radius: 10px;
            padding: 11px 16px;
            font-size: 13px;
            font-weight: 700;
            box-shadow: none;
        }
        .btn-detail:hover {
            background-color: #ffd54f;
        }

        .job-divider {
            display: none;
        }
    }

    @media (min-width: 769px) {
        .job-mobile-meta {
            display: none;
        }
    }
</style>

<!-- Banner Halaman Career Sesuai Referensi -->
<div class="career-banner">
    <h1>Karier di <span>Kebab Monster</span></h1>
    <p>Di Kebab Monster, kami percaya setiap individu punya potensi luar biasa untuk berkembang bersama. Temukan posisi yang cocok dan raih karier cemerlang bersama kami.</p>
    <div class="banner-tags">
        <div class="banner-tag-item">📍 Bogor, Jawa Barat</div>
        <div class="banner-tag-item">🛡️ Tanpa Biaya Rekrutmen</div>
    </div>
</div>

<div class="career-container">
    <div class="career-list">
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $formatted_date = date('d-M-Y', strtotime($row['tgl_dibuat']));
                ?>
                <div class="job-card-h">
                    <div class="job-top-row">
                        <div class="job-title-area">
                            <div class="job-title"><?php echo htmlspecialchars($row['posisi_pekerjaan']); ?></div>
                            <div class="job-divisi"><?php echo htmlspecialchars($row['divisi']); ?></div>
                        </div>
                        <div class="job-meta-badges">
                            <span class="badge-shift"><?php echo htmlspecialchars($row['shift']); ?></span>
                            <span class="job-date"><?php echo $formatted_date; ?></span>
                        </div>
                    </div>

                    <div class="job-company">
                        PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
                    </div>

                    <hr class="job-divider">

                    <div class="job-bottom-row">
                        <div class="job-location">
                            <span>📍</span> <span><?php echo htmlspecialchars($row['lokasi_pekerjaan']); ?></span>
                        </div>
                        <div class="job-mobile-meta">
                            <span class="badge-shift"><?php echo htmlspecialchars($row['shift']); ?></span>
                            <span class="job-date"><?php echo $formatted_date; ?></span>
                        </div>
                        <div class="job-action-wrap">
                            <a href="detail.php?id=<?php echo $row['id_lowongan']; ?>" class="btn-detail">
                                Lihat Detail Lamaran &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="no-job">Belum ada lowongan pekerjaan yang aktif saat ini.</div>';
        }

        mysqli_close($conn);
        ?>
    </div>
</div>

<script>
(function() {
    if (!window.fetch) return;
    fetch('api/career.php')
        .then(res => res.ok ? res.json() : null)
        .then(json => {
            if (!json || !json.success || !Array.isArray(json.lowongan)) return;
            const listEl = document.querySelector('.career-list');
            if (!listEl) return;

            const esc = (str) => String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');

            if (json.lowongan.length === 0) {
                listEl.innerHTML = '<div class="no-job">Belum ada lowongan pekerjaan yang aktif saat ini.</div>';
                return;
            }

            listEl.innerHTML = json.lowongan.map(row => `
                <div class="job-card-h">
                    <div class="job-top-row">
                        <div class="job-title-area">
                            <div class="job-title">${esc(row.posisi_pekerjaan)}</div>
                            <div class="job-divisi">${esc(row.divisi)}</div>
                        </div>
                        <div class="job-meta-badges">
                            <span class="badge-shift">${esc(row.shift)}</span>
                            <span class="job-date">${esc(row.formatted_date)}</span>
                        </div>
                    </div>

                    <div class="job-company">
                        PT. BERKARYA INDONESIA SEJAHTERA ANDAL / Kebab Monster
                    </div>

                    <hr class="job-divider">

                    <div class="job-bottom-row">
                        <div class="job-location">
                            <span>📍</span> <span>${esc(row.lokasi_pekerjaan)}</span>
                        </div>
                        <div class="job-mobile-meta">
                            <span class="badge-shift">${esc(row.shift)}</span>
                            <span class="job-date">${esc(row.formatted_date)}</span>
                        </div>
                        <div class="job-action-wrap">
                            <a href="detail.php?id=${encodeURIComponent(row.id_lowongan)}" class="btn-detail">
                                Lihat Detail Lamaran &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(() => {});
})();
</script>

<?php
include 'includes/footer.php';
?>