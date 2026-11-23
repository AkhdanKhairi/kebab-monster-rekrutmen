<?php
// File: api/detail_koreksi_essay.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

$role_user = strtolower(trim($_SESSION['role'] ?? ''));
$id_admin  = intval($_SESSION['user_id'] ?? 0);

if ($id_admin <= 0 || !in_array($role_user, ['admin', 'hr', 'hrd'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Silakan login sebagai HR/Admin.']);
    exit();
}

$host = 'localhost'; $user = 'root'; $pass = ''; $db = 'db_kebab_monster';
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi database gagal: ' . mysqli_connect_error()]);
    exit();
}
mysqli_set_charset($conn, 'utf8mb4');

$method = $_SERVER['REQUEST_METHOD'];
$input  = ($method === 'POST') ? (json_decode(file_get_contents('php://input'), true) ?: $_POST) : $_GET;

$id_pelamar     = intval($input['id_pelamar'] ?? 0);
$id_hasil       = intval($input['id_hasil'] ?? 0);
$id_modul_pilih = intval($input['id_modul'] ?? 0);

if ($id_pelamar <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Parameter id_pelamar wajib diisi.']);
    mysqli_close($conn);
    exit();
}

$all_tables = [];
$q_tables = mysqli_query($conn, "SHOW TABLES");
while ($t = mysqli_fetch_row($q_tables)) {
    $all_tables[] = $t[0];
}

// Deteksi tabel hasil
$tbl_hasil = "hasil_psikotes";
foreach ($all_tables as $tbl) {
    if (preg_match('/(hasil_psikotes|hasil_ujian|nilai_psikotes|hasil_tes)/i', $tbl)) {
        $tbl_hasil = $tbl;
        break;
    }
}
$cols_hasil = [];
$q_ch = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_hasil`");
if ($q_ch) while ($c = mysqli_fetch_assoc($q_ch)) $cols_hasil[] = $c['Field'];
$pk_hasil   = $cols_hasil[0] ?? 'id_hasil';
$fk_h_pel   = in_array('id_pelamar', $cols_hasil, true) ? 'id_pelamar' : 'pelamar_id';
$col_h_skor = in_array('skor_akhir', $cols_hasil, true) ? 'skor_akhir' : (in_array('nilai', $cols_hasil, true) ? 'nilai' : 'skor');
$col_h_stat = in_array('status_lulus', $cols_hasil, true) ? 'status_lulus' : (in_array('status', $cols_hasil, true) ? 'status' : '');

// Deteksi modul
$tbl_modul      = in_array('modul_ujian', $all_tables, true) ? 'modul_ujian' : '';
$pk_modul       = 'id_modul';
$col_nama_modul = 'nama_modul';
if ($tbl_modul !== '') {
    $q_cm = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_modul`");
    $idx = 0;
    while ($cm = mysqli_fetch_assoc($q_cm)) {
        if ($idx === 0) $pk_modul = $cm['Field'];
        if (preg_match('/(nama|judul|modul|kategori|title)/i', $cm['Field']) && !preg_match('/^id_/i', $cm['Field'])) {
            $col_nama_modul = $cm['Field'];
        }
        $idx++;
    }
}

// Deteksi soal
$tbl_soal = "";
$cols_soal = [];
foreach ($all_tables as $tbl) {
    if (preg_match('/(soal|pertanyaan|bank_soal)/i', $tbl) && !preg_match('/(jawaban|hasil|riwayat)/i', $tbl)) {
        $tbl_soal = $tbl;
        break;
    }
}
if ($tbl_soal !== '') {
    $q_cs = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl_soal`");
    while ($c = mysqli_fetch_assoc($q_cs)) $cols_soal[] = $c['Field'];
}
$pk_soal   = $cols_soal[0] ?? 'id_soal';
$col_tanya = in_array('pertanyaan', $cols_soal, true) ? 'pertanyaan' : ($cols_soal[1] ?? 'pertanyaan');
$col_tipe  = '';
foreach (['tipe_soal', 'jenis_soal', 'tipe', 'jenis', 'kategori_soal', 'bentuk_soal'] as $cand) {
    if (in_array($cand, $cols_soal, true)) { $col_tipe = $cand; break; }
}
$col_kunci = '';
foreach (['kunci_jawaban', 'kunci', 'jawaban_benar', 'kunci_pg'] as $cand) {
    if (in_array($cand, $cols_soal, true)) { $col_kunci = $cand; break; }
}
$col_bobot = '';
foreach (['bobot_nilai', 'bobot', 'poin', 'skor_soal', 'nilai'] as $cand) {
    if (in_array($cand, $cols_soal, true)) { $col_bobot = $cand; break; }
}
$col_flag_alasan = '';
foreach ($cols_soal as $cs) {
    if (preg_match('/(alasan|penjelasan|uraian|essay)/i', $cs) && !preg_match('/^(id_|pertanyaan|soal)/i', $cs)) {
        $col_flag_alasan = $cs;
        break;
    }
}
$fk_modul_soal = in_array('id_modul', $cols_soal, true) ? 'id_modul' : '';

// Deteksi jawaban
$tbl_jawaban = "";
$cols_jawaban = [];
foreach ($all_tables as $tbl) {
    if (in_array($tbl, [$tbl_hasil, $tbl_soal, $tbl_modul, 'riwayat_ujian_pelamar'], true)) continue;
    if (preg_match('/(jawaban|detail_ujian|detail_tes|respon)/i', $tbl)) {
        $cols_tmp = [];
        $q_tmp = mysqli_query($conn, "SHOW COLUMNS FROM `$tbl`");
        while ($c = mysqli_fetch_assoc($q_tmp)) $cols_tmp[] = $c['Field'];
        if ((in_array('id_soal', $cols_tmp, true) || in_array('soal_id', $cols_tmp, true)) &&
            (in_array('id_pelamar', $cols_tmp, true) || in_array('pelamar_id', $cols_tmp, true))) {
            $tbl_jawaban = $tbl;
            $cols_jawaban = $cols_tmp;
            break;
        }
    }
}
$pk_jawaban   = $cols_jawaban[0] ?? 'id_jawaban';
$fk_j_pelamar = in_array('id_pelamar', $cols_jawaban, true) ? 'id_pelamar' : 'pelamar_id';
$fk_j_soal    = in_array('id_soal', $cols_jawaban, true) ? 'id_soal' : 'soal_id';

$kandidat_jwb = [];
$col_alasan_jwb = '';
foreach ($cols_jawaban as $cj) {
    if (preg_match('/(alasan|penjelasan|keterangan_jawaban|argumen)/i', $cj) && !preg_match('/^(id_|catatan_koreksi)/i', $cj)) {
        $col_alasan_jwb = $cj;
        continue;
    }
    if (preg_match('/(jawaban|pilihan|opsi|respon|isi|teks|essay)/i', $cj) && !preg_match('/^(id_|is_|status|catatan|persentase|nilai)/i', $cj)) {
        $kandidat_jwb[] = "NULLIF(TRIM(j.`$cj`), '')";
    }
}
$expr_jwb    = !empty($kandidat_jwb) ? "COALESCE(" . implode(", ", $kandidat_jwb) . ", '')" : "''";
$expr_alasan = $col_alasan_jwb !== '' ? "COALESCE(NULLIF(TRIM(j.`$col_alasan_jwb`), ''), '')" : "''";

function apiKlasifikasiSoal($tipe_raw, $kunci_str, $jwb_user, $alasan_user, $flag_alasan) {
    $tipe_l  = strtolower(trim((string)$tipe_raw));
    $kunci_l = strtolower(trim((string)$kunci_str));
    $jwb_l   = strtolower(trim((string)$jwb_user));
    $flag_l  = strtolower(trim((string)$flag_alasan));

    $is_bs = (
        strpos($tipe_l, 'benar') !== false ||
        strpos($tipe_l, 'salah') !== false ||
        strpos($tipe_l, 'true') !== false ||
        in_array($tipe_l, ['bs', 'b/s'], true) ||
        in_array($kunci_l, ['benar', 'salah', 'true', 'false'], true)
    );
    $is_essay = (
        strpos($tipe_l, 'essay') !== false ||
        strpos($tipe_l, 'esai') !== false ||
        strpos($tipe_l, 'uraian') !== false ||
        strpos($tipe_l, 'isian') !== false ||
        strpos($tipe_l, 'studi') !== false ||
        strpos($tipe_l, 'kasus') !== false
    );
    if (!$is_bs && !$is_essay) {
        $k_up = strtoupper(trim((string)$kunci_str));
        if ($tipe_l === '' && !in_array($k_up, ['A', 'B', 'C', 'D', 'E', '1', '2', '3', '4', '5'], true)) {
            $is_essay = true;
        }
    }
    $bs_alasan = false;
    if ($is_bs) {
        if (trim((string)$alasan_user) !== '' || in_array($flag_l, ['1', 'ya', 'yes', 'true', 'wajib'], true) ||
            (trim((string)$jwb_user) !== '' && !in_array($jwb_l, ['benar', 'salah', 'true', 'false', 'b', 's'], true))) {
            $bs_alasan = true;
        }
    }
    return [
        'is_bs'        => $is_bs,
        'is_essay'     => $is_essay,
        'bs_alasan'    => $bs_alasan,
        'pakai_slider' => ($is_essay || ($is_bs && $bs_alasan)),
    ];
}

// Ambil hasil aktif
$where_h = ($id_hasil > 0) ? "`$pk_hasil` = $id_hasil" : "`$fk_h_pel` = $id_pelamar ORDER BY `$pk_hasil` DESC LIMIT 1";
$q_h = mysqli_query($conn, "SELECT * FROM `$tbl_hasil` WHERE $where_h");
$data_hasil = $q_h ? mysqli_fetch_assoc($q_h) : null;
if ($data_hasil && $id_hasil <= 0) {
    $id_hasil = intval($data_hasil[$pk_hasil]);
}

if ($method === 'POST' && $tbl_jawaban !== '' && $tbl_soal !== '') {
    $persen_arr  = $input['persentase'] ?? [];
    $bobot_arr   = $input['bobot_soal'] ?? [];
    $catatan_arr = $input['catatan_koreksi'] ?? [];
    $auto_status = !empty($input['auto_status_lulus']);

    if (is_array($persen_arr)) {
        $stmt_u = mysqli_prepare(
            $conn,
            "UPDATE `$tbl_jawaban`
             SET `persentase_essay` = ?, `nilai_diperoleh` = ?, `catatan_koreksi` = ?, `is_dikoreksi` = 1
             WHERE `$pk_jawaban` = ? AND `$fk_j_pelamar` = ?"
        );
        foreach ($persen_arr as $id_jwb_raw => $persen_raw) {
            $id_jwb = intval($id_jwb_raw);
            if ($id_jwb <= 0) continue;
            $persen = max(0, min(100, intval($persen_raw)));
            $bobot  = floatval($bobot_arr[$id_jwb] ?? 10);
            $cat_k  = (string)($catatan_arr[$id_jwb] ?? '');
            $poin   = round(($persen / 100) * $bobot, 2);
            if ($stmt_u) {
                mysqli_stmt_bind_param($stmt_u, "idsii", $persen, $poin, $cat_k, $id_jwb, $id_pelamar);
                mysqli_stmt_execute($stmt_u);
            }
        }
        if ($stmt_u) mysqli_stmt_close($stmt_u);
    }

    $q_recalc = mysqli_query($conn, "
        SELECT j.*,
               $expr_jwb AS teks_jwb,
               $expr_alasan AS teks_alasan,
               " . ($col_tipe !== '' ? "s.`$col_tipe` AS tipe_raw," : "'' AS tipe_raw,") . "
               " . ($col_kunci !== '' ? "s.`$col_kunci` AS kunci_jwb," : "'' AS kunci_jwb,") . "
               " . ($col_flag_alasan !== '' ? "s.`$col_flag_alasan` AS flag_alasan," : "'' AS flag_alasan,") . "
               " . ($col_bobot !== '' ? "COALESCE(NULLIF(s.`$col_bobot`, 0), 10) AS bobot_s" : "10 AS bobot_s") . "
        FROM `$tbl_jawaban` j
        INNER JOIN `$tbl_soal` s ON j.`$fk_j_soal` = s.`$pk_soal`
        WHERE j.`$fk_j_pelamar` = $id_pelamar
    ");

    $total_poin  = 0;
    $total_bobot = 0;
    if ($q_recalc) {
        while ($rc = mysqli_fetch_assoc($q_recalc)) {
            $b_rc = floatval($rc['bobot_s'] ?? 10);
            $total_bobot += $b_rc;
            $klas = apiKlasifikasiSoal($rc['tipe_raw'], $rc['kunci_jwb'], $rc['teks_jwb'], $rc['teks_alasan'], $rc['flag_alasan']);
            if ($klas['pakai_slider']) {
                $total_poin += round((intval($rc['persentase_essay'] ?? 0) / 100) * $b_rc, 2);
            } elseif (!empty($rc['kunci_jwb']) && strcasecmp(trim($rc['teks_jwb']), trim($rc['kunci_jwb'])) === 0) {
                $total_poin += $b_rc;
            }
        }
    }

    $skor_akhir_baru = ($total_bobot > 0) ? round(($total_poin / $total_bobot) * 100, 2) : 0;
    $st_baru = ($skor_akhir_baru >= 70) ? 'Lulus' : 'Tidak Lulus';

    if ($id_hasil > 0) {
        $upd_parts = ["`$col_h_skor` = $skor_akhir_baru"];
        if ($col_h_stat !== '' && $auto_status) {
            $upd_parts[] = "`$col_h_stat` = '" . mysqli_real_escape_string($conn, $st_baru) . "'";
        }
        mysqli_query($conn, "UPDATE `$tbl_hasil` SET " . implode(", ", $upd_parts) . " WHERE `$pk_hasil` = $id_hasil");
    }

    echo json_encode([
        'success'         => true,
        'message'         => 'Nilai koreksi berhasil disimpan dan skor akhir diperbarui.',
        'id_pelamar'      => $id_pelamar,
        'id_hasil'        => $id_hasil,
        'skor_akhir_baru' => $skor_akhir_baru,
        'status_otomatis' => $st_baru,
    ]);
    mysqli_close($conn);
    exit();
}

// GET: Ambil profil pelamar & soal (jika id_modul dipilih)
$q_pel = mysqli_query($conn, "SELECT * FROM `pelamar` WHERE `id_pelamar` = $id_pelamar LIMIT 1");
$pelamar = $q_pel ? mysqli_fetch_assoc($q_pel) : null;

$soal_list = [];
if ($id_modul_pilih > 0 && $tbl_jawaban !== '' && $tbl_soal !== '' && $fk_modul_soal !== '') {
    $q_s = mysqli_query($conn, "
        SELECT j.*,
               $expr_jwb AS teks_jawaban_pelamar,
               $expr_alasan AS teks_alasan_pelamar,
               s.`$col_tanya` AS pertanyaan,
               " . ($col_tipe !== '' ? "s.`$col_tipe` AS tipe_soal_raw," : "'' AS tipe_soal_raw,") . "
               " . ($col_kunci !== '' ? "s.`$col_kunci` AS kunci_jawaban," : "'' AS kunci_jawaban,") . "
               " . ($col_flag_alasan !== '' ? "s.`$col_flag_alasan` AS flag_alasan_soal," : "'' AS flag_alasan_soal,") . "
               " . ($col_bobot !== '' ? "COALESCE(NULLIF(s.`$col_bobot`, 0), 10) AS bobot_soal" : "10 AS bobot_soal") . "
        FROM `$tbl_jawaban` j
        INNER JOIN `$tbl_soal` s ON j.`$fk_j_soal` = s.`$pk_soal`
        WHERE j.`$fk_j_pelamar` = $id_pelamar AND s.`$fk_modul_soal` = $id_modul_pilih
        ORDER BY s.`$pk_soal` ASC
    ");
    if ($q_s) {
        while ($item = mysqli_fetch_assoc($q_s)) {
            $klas = apiKlasifikasiSoal(
                $item['tipe_soal_raw'] ?? '',
                $item['kunci_jawaban'] ?? '',
                $item['teks_jawaban_pelamar'] ?? '',
                $item['teks_alasan_pelamar'] ?? '',
                $item['flag_alasan_soal'] ?? ''
            );
            $item['pakai_slider'] = $klas['pakai_slider'];
            $item['bs_alasan']    = $klas['bs_alasan'];
            $soal_list[] = $item;
        }
    }
}

echo json_encode([
    'success'    => true,
    'id_pelamar' => $id_pelamar,
    'id_hasil'   => $id_hasil,
    'pelamar'    => $pelamar,
    'hasil'      => $data_hasil,
    'soal'       => $soal_list,
]);
mysqli_close($conn);