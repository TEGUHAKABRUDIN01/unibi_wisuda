<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id_mahasiswa = $_SESSION['id_mahasiswa'];
  $nama_ayah = mysqli_real_escape_string($conn, $_POST['nama_ayah']);
  $nama_ibu = mysqli_real_escape_string($conn, $_POST['nama_ibu']);

  // Mulai Transaksi
  mysqli_begin_transaction($conn);

  try {
    // Hapus data lama jika sudah pernah isi (agar tidak double)
    mysqli_query($conn, "DELETE FROM pendamping WHERE id_mahasiswa = '$id_mahasiswa'");

    // Simpan Orang Tua 1
    mysqli_query($conn, "INSERT INTO pendamping (id_mahasiswa, nama_pendamping) VALUES ('$id_mahasiswa', '$nama_ayah')");

    // Simpan Orang Tua 2
    mysqli_query($conn, "INSERT INTO pendamping (id_mahasiswa, nama_pendamping) VALUES ('$id_mahasiswa', '$nama_ibu')");

    mysqli_commit($conn);
    echo "<script>alert('Data pendamping berhasil disimpan!'); window.location='../../views/mahasiswa/dashboard_mahasiswa.php';</script>";
  } catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Gagal menyimpan: " . $e->getMessage();
  }
}
