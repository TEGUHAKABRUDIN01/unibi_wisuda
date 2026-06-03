<?php

session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['save_manajemen'])) {
    header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
    exit;
}

$id_manajemen = intval($_POST['id_manajemen'] ?? 0);

$angkatan = mysqli_real_escape_string($conn, trim($_POST['angkatan'] ?? ''));
$tgl_mulai = mysqli_real_escape_string($conn, trim($_POST['tgl_mulai'] ?? ''));
$jam_mulai = mysqli_real_escape_string($conn, trim($_POST['jam_mulai'] ?? ''));
$tgl_selesai = mysqli_real_escape_string($conn, trim($_POST['tgl_selesai'] ?? ''));
$jam_selesai = mysqli_real_escape_string($conn, trim($_POST['jam_selesai'] ?? ''));
$tempat = mysqli_real_escape_string($conn, trim($_POST['tempat'] ?? ''));
$alamat = mysqli_real_escape_string($conn, trim($_POST['alamat'] ?? ''));

if (
    empty($angkatan) ||
    empty($tgl_mulai) ||
    empty($jam_mulai) ||
    empty($tgl_selesai) ||
    empty($jam_selesai) ||
    empty($tempat) ||
    empty($alamat)
) {
    $_SESSION['swal_error'] = 'Semua data wajib diisi.';
    header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
    exit;
}

if (strtotime($tgl_selesai) < strtotime($tgl_mulai)) {
    $_SESSION['swal_error'] = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
    header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
    exit;
}

$npm_format = $_POST['npm_format'] ?? [];
$jumlah_kursi = $_POST['jumlah_kursi'] ?? [];

if (empty($npm_format) || empty($jumlah_kursi)) {
    $_SESSION['swal_error'] = 'Format NPM dan jumlah kursi fakultas harus diisi.';
    header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
    exit;
}

mysqli_begin_transaction($conn);

try {

    if ($id_manajemen > 0) {

        $sql = "
            UPDATE manajemen_wisuda
            SET
                angkatan = '$angkatan',
                tgl_mulai = '$tgl_mulai',
                jam_mulai = '$jam_mulai',
                tgl_selesai = '$tgl_selesai',
                jam_selesai = '$jam_selesai',
                tempat = '$tempat',
                alamat = '$alamat',
                status = 'aktif',
                updated_at = NOW()
            WHERE id_manajemen = $id_manajemen
        ";

    } else {

        $sql = "
            INSERT INTO manajemen_wisuda
            (
                angkatan,
                tgl_mulai,
                jam_mulai,
                tgl_selesai,
                jam_selesai,
                tempat,
                alamat,
                status,
                created_at
            )
            VALUES
            (
                '$angkatan',
                '$tgl_mulai',
                '$jam_mulai',
                '$tgl_selesai',
                '$jam_selesai',
                '$tempat',
                '$alamat',
                'aktif',
                NOW()
            )
        ";

    }

    if (!mysqli_query($conn, $sql)) {
        throw new Exception(mysqli_error($conn));
    }

    foreach ($npm_format as $id_fakultas => $format) {

        $id_fakultas = intval($id_fakultas);
        $format = mysqli_real_escape_string($conn, trim($format));
        $jumlah = intval($jumlah_kursi[$id_fakultas] ?? 0);

        $update_fakultas = "
            UPDATE fakultas
            SET
                npm_format = '$format',
                jumlah_kursi = '$jumlah'
            WHERE id_fakultas = $id_fakultas
        ";

        if (!mysqli_query($conn, $update_fakultas)) {
            throw new Exception(mysqli_error($conn));
        }
    }

    mysqli_commit($conn);

    $_SESSION['swal_success'] = 'Manajemen wisuda berhasil disimpan dan diaktifkan.';

} catch (Exception $e) {

    mysqli_rollback($conn);

    $_SESSION['swal_error'] = 'Gagal menyimpan data: ' . $e->getMessage();
}

header("Location: /UNIBI_WISUDA/views/petugas/manajemen_wisuda.php");
exit;