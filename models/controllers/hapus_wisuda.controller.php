<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  echo "<script>alert('Akses ditolak!'); window.location='/UNIBI_WISUDA/index.php';</script>";
  exit;
}

if (isset($_GET['id_proses'])) {
  $id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);

  $query_cari = mysqli_query($conn, "SELECT id_mahasiswa FROM proses_wisuda WHERE id_proses = '$id_proses'");
  $data = mysqli_fetch_assoc($query_cari);

  if ($data) {
    $id_mahasiswa = $data['id_mahasiswa'];

    mysqli_begin_transaction($conn);

    try {

      $sql_hapus_proses = "DELETE FROM proses_wisuda WHERE id_proses = '$id_proses'";
      if (!mysqli_query($conn, $sql_hapus_proses)) {
        throw new Exception("Gagal menghapus data di proses_wisuda");
      }


      $sql_hapus_mhs = "DELETE FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'";
      if (!mysqli_query($conn, $sql_hapus_mhs)) {
        throw new Exception("Gagal menghapus data di tabel mahasiswa");
      }

      // Jika semua lancar, simpan perubahan permanen
      mysqli_commit($conn);

      echo "<script>
                    alert('Data mahasiswa dan pendaftaran berhasil dihapus!'); 
                    window.location='/UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php';
                  </script>";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      echo "<script>
                    alert('Gagal menghapus data: " . $e->getMessage() . "'); 
                    window.history.back();
                  </script>";
    }
  } else {
    echo "<script>alert('Data tidak ditemukan!'); window.history.back();</script>";
  }
} else {
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_wisuda.php");
  exit;
}
