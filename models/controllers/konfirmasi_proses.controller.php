<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// memastikan hanya petugas yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') {
  echo "<script>alert('Akses ditolak!'); window.location='/UNIBI_WISUDA/index.php';</script>";
  exit;
}

// cek id pendaftaran (id_proses)
if (isset($_GET['id_proses'])) {
  $id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);

  // mengambil id dari session petugas
  $id_petugas = $_SESSION['id_petugas'];

  // id_mahasiswa dari proses_wisuda untuk mengaktifkan akun konfirmasi
  $query =  mysqli_query($conn, "SELECT id_mahasiswa FROM proses_wisuda WHERE id_proses = '$id_proses'");

  $data_pendaftaran = mysqli_fetch_assoc($query);

  if ($data_pendaftaran) {
    $id_mahasiswa = $data_pendaftaran['id_mahasiswa'];

    // agar jika salah satu query gagal, semua query dibatalkan
    mysqli_begin_transaction($conn);

    try {
      $sql_update = "UPDATE proses_wisuda SET status_proses = 'selesai', id_petugas = '$id_petugas' WHERE id_proses = '$id_proses'";

      if (!mysqli_query($conn, $sql_update)) {
        throw new Exception("Gagal mengupdate tabel proses_wisuda.");
      }

      $sql_update_mhs = "UPDATE mahasiswa SET id_akses = 1 WHERE id_mahasiswa = '$id_mahasiswa'";

      if (!mysqli_query($conn, $sql_update_mhs)) {
        throw new Exception("Gagal update status akses mahasiswa");
      }

      // jika semuanya berhasil, simpan perubahan secara permanen
      mysqli_commit($conn);

      echo "<script>alert('Berhasil!!, Akun mahasiswa berhasil diaktifkan'); window.location='/UNIBI_WISUDA/views/petugas/dashboard_petugas.php';</script>";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      die("Gagal Update! Error: " . $e->getMessage());
    }
  } else {
    echo "<script>alert('Data pendaftaran tidak ditemukan!'); window.history.back();</script>";
  }
} else {
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_wisuda.php");
  exit;
}
