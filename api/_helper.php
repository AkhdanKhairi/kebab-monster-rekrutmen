<?php
// api/_helper.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Header JSON standar untuk semua endpoint API
header('Content-Type: application/json; charset=utf-8');

/**
 * Mengirim respons JSON standar lalu menghentikan eksekusi skrip.
 */
function jsonResponse(int $statusCode, bool $success, string $message, $data = null, array $errors = []): void
{
    http_response_code($statusCode);

    $payload = [
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ];

    if (!empty($errors)) {
        $payload['errors'] = $errors;
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Membuat dan mengembalikan koneksi MySQLi ke database db_kebab_monster.
 * Jika proyekmu sudah punya config/database.php yang menghasilkan $conn,
 * kamu bisa menyesuaikan bagian ini agar tidak duplikat.
 */
function getDbConnection(): mysqli
{
    $host = 'localhost';
    $user = 'root';
    $pass = '';
    $db   = 'db_kebab_monster';

    $conn = @mysqli_connect($host, $user, $pass, $db);

    if (!$conn) {
        jsonResponse(500, false, 'Koneksi database gagal.');
    }

    mysqli_set_charset($conn, 'utf8mb4');

    return $conn;
}

/**
 * Memastikan request menggunakan HTTP method tertentu (misalnya GET atau POST).
 */
function requireMethod(string $allowedMethod): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $expected = strtoupper($allowedMethod);

    if ($method !== $expected) {
        jsonResponse(405, false, "Method {$method} tidak diizinkan. Gunakan {$expected}.");
    }
}

/**
 * Mengambil body request JSON (jika dikirim sebagai application/json)
 * atau fallback ke $_POST jika dikirim sebagai form-data / x-www-form-urlencoded.
 */
function getRequestInput(): array
{
    $rawBody = file_get_contents('php://input');

    if (!empty($rawBody)) {
        $decoded = json_decode($rawBody, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }

    return $_POST ?? [];
}

/**
 * Memastikan user sudah login (dan opsional mengecek role tertentu, misal 'admin' atau 'pelamar').
 */
function requireAuth(?string $requiredRole = null): array
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        jsonResponse(401, false, 'Silakan login terlebih dahulu.');
    }

    $role = (string) $_SESSION['role'];

    if ($requiredRole !== null && $role !== $requiredRole) {
        jsonResponse(403, false, 'Anda tidak memiliki akses ke endpoint ini.');
    }

    return [
        'user_id' => $_SESSION['user_id'],
        'nama'    => $_SESSION['nama'] ?? '',
        'email'   => $_SESSION['email'] ?? '',
        'role'    => $role,
    ];
}