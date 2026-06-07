<?php


date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db = "wisuda_unibi";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function getAutoManajemenStatus($manajemen)
{
    if (!$manajemen) {
        return 'not-configured';
    }

    $tgl_selesai = $manajemen['tgl_selesai'] ?? '';
    $jam_selesai = $manajemen['jam_selesai'] ?? '23:59:59';
    if (!empty($tgl_selesai)) {
        $end_datetime = strtotime($tgl_selesai . ' ' . $jam_selesai);
        if ($end_datetime && time() > $end_datetime) {
            return 'selesai';
        }
    }

    $tgl_mulai = $manajemen['tgl_mulai'] ?? '';
    $jam_mulai = $manajemen['jam_mulai'] ?? '00:00:00';
    if (!empty($tgl_mulai)) {
        $start_datetime = strtotime($tgl_mulai . ' ' . $jam_mulai);
        if ($start_datetime && time() < $start_datetime) {
            return 'draft';
        }
    }

    return 'aktif';
}

function getLatestManajemen($conn)
{
    $result = mysqli_query(
        $conn,
        "SELECT * FROM manajemen_wisuda ORDER BY id_manajemen DESC LIMIT 1"
    );

    $manajemen = $result ? mysqli_fetch_assoc($result) : null;
    if ($manajemen) {
        $manajemen['status'] = getAutoManajemenStatus($manajemen);
    }
    return $manajemen;
}

function getManajemenById($conn, $id)
{
    $id = intval($id);

    $result = mysqli_query(
        $conn,
        "SELECT * FROM manajemen_wisuda WHERE id_manajemen = $id LIMIT 1"
    );

    $manajemen = $result ? mysqli_fetch_assoc($result) : null;
    if ($manajemen) {
        $manajemen['status'] = getAutoManajemenStatus($manajemen);
    }
    return $manajemen;
}

function getManajemenPhase($manajemen)
{
    return getAutoManajemenStatus($manajemen);
}

function isManajemenRunning($conn)
{
    $manajemen = getLatestManajemen($conn);

    if (!$manajemen) {
        return false;
    }

    return ($manajemen['status'] ?? '') === 'aktif';
}
