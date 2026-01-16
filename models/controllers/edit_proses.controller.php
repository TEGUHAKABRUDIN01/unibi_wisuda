<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (isset($_POST['edit_data'])) {
  $id_proses = $_POST['id_proses'];
  $id_mahasiswa = $_POST['id_mahasiswa'];
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);
  $id_prodi = $_POST['id_prodi'];

  mysqli_begin_transaction($conn);

  try {

    $sql_mhs = "UPDATE mahasiswa SET nama_mahasiswa = '$nama', nim = '$nim', id_prodi = '$id_prodi' WHERE id_mahasiswa = '$id_mahasiswa'";
    mysqli_query($conn, $sql_mhs);

    // Tandai bahwa data ini sudah pernah diedit (is_edited = 1)
    $sql_proses = "UPDATE proses_wisuda SET is_edited = 1 WHERE id_proses = '$id_proses'";
    mysqli_query($conn, $sql_proses);

    mysqli_commit($conn);
    echo "<script>alert('Data diedit!'); window.location='/UNIBI_WISUDA/views/petugas/dashboard_petugas.php';</script>";
  } catch (Exception $e) {
    mysqli_rollback($conn);
    echo "<script>alert('Gagal update data!'); window.history.back();</script>";
  }
}
