<?php

$host = "localhost";
$user = "root";
$pass = "123";
$db = "wisuda_unibi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function getLatestManajemen($conn)
{
    $result = mysqli_query(
        $conn,
        "SELECT * FROM manajemen_wisuda ORDER BY id_manajemen DESC LIMIT 1"
    );

    return $result ? mysqli_fetch_assoc($result) : null;
}

function getManajemenById($conn, $id)
{
    $id = intval($id);

    $result = mysqli_query(
        $conn,
        "SELECT * FROM manajemen_wisuda WHERE id_manajemen = $id LIMIT 1"
    );

    return $result ? mysqli_fetch_assoc($result) : null;
}

function getAutoManajemenStatus($manajemen)
{
    if (!$manajemen) {
        return 'not-configured';
    }

    return $manajemen['status'] ?? 'draft';
}

function getManajemenPhase($manajemen)
{
    if (!$manajemen) {
        return 'not-configured';
    }

    return $manajemen['status'] ?? 'draft';
}

function isManajemenRunning($conn)
{
    $manajemen = getLatestManajemen($conn);

    if (!$manajemen) {
        return false;
    }

    return ($manajemen['status'] ?? '') === 'aktif';
}