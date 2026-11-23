<?php
// api/auth/login.php
require_once __DIR__ . '/../_helper.php';

requireMethod('POST');

$input      = getRequestInput();
$identifier = trim((string) ($input['identifier'] ?? ''));
$password   = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    jsonResponse(400, false, 'Username/email dan password wajib diisi.');
}

$conn = getDbConnection();

// 1. Cek tabel ADMIN (sesuai struktur login.php: username & password plain-text)
$stmtAdmin = mysqli_prepare($conn, "SELECT id_admin, nama, username, password FROM admin WHERE username = ? LIMIT 1");
if (!$stmtAdmin) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query admin.');
}

mysqli_stmt_bind_param($stmtAdmin, 's', $identifier);
mysqli_stmt_execute($stmtAdmin);
$resAdmin = mysqli_stmt_get_result($stmtAdmin);

if ($resAdmin && mysqli_num_rows($resAdmin) === 1) {
    $admin = mysqli_fetch_assoc($resAdmin);
    mysqli_stmt_close($stmtAdmin);

    // Catatan: jika suatu saat password admin di-hash dengan password_hash(),
    // kamu bisa menyesuaikan pengecekan di sini.
    if (hash_equals((string) $admin['password'], $password)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $admin['id_admin'];
        $_SESSION['nama']    = $admin['nama'];
        $_SESSION['email']   = $admin['username'];
        $_SESSION['role']    = 'admin';

        mysqli_close($conn);
        jsonResponse(200, true, 'Login berhasil sebagai Admin/HR.', [
            'user' => [
                'id'       => $admin['id_admin'],
                'nama'     => $admin['nama'],
                'username' => $admin['username'],
                'role'     => 'admin',
            ],
        ]);
    }
} else {
    mysqli_stmt_close($stmtAdmin);
}

// 2. Cek tabel PELAMAR (sesuai struktur login.php: email & password MD5)
$passwordMd5 = md5($password);
$stmtPelamar = mysqli_prepare($conn, "SELECT id_pelamar, nama_lengkap, email, password FROM pelamar WHERE email = ? LIMIT 1");
if (!$stmtPelamar) {
    mysqli_close($conn);
    jsonResponse(500, false, 'Gagal menyiapkan query pelamar.');
}

mysqli_stmt_bind_param($stmtPelamar, 's', $identifier);
mysqli_stmt_execute($stmtPelamar);
$resPelamar = mysqli_stmt_get_result($stmtPelamar);

if ($resPelamar && mysqli_num_rows($resPelamar) === 1) {
    $pelamar = mysqli_fetch_assoc($resPelamar);
    mysqli_stmt_close($stmtPelamar);

    if (hash_equals(strtolower((string) $pelamar['password']), strtolower($passwordMd5))) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $pelamar['id_pelamar'];
        $_SESSION['nama']    = $pelamar['nama_lengkap'];
        $_SESSION['email']   = $pelamar['email'];
        $_SESSION['role']    = 'pelamar';

        mysqli_close($conn);
        jsonResponse(200, true, 'Login berhasil.', [
            'user' => [
                'id'           => $pelamar['id_pelamar'],
                'nama_lengkap' => $pelamar['nama_lengkap'],
                'email'        => $pelamar['email'],
                'role'         => 'pelamar',
            ],
        ]);
    }
} else {
    mysqli_stmt_close($stmtPelamar);
}

mysqli_close($conn);
jsonResponse(401, false, 'Email/Username atau password salah.');