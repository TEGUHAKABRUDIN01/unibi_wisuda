<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

// 1. Keamanan: Hanya Petugas
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') {
  echo "<script>alert('Akses ditolak!'); window.location='/UNIBI_WISUDA/index.php';</script>";
  exit;
}

if (isset($_GET['id_proses'])) {
  $id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);
  $id_petugas = $_SESSION['id_petugas'];

  // 2. QUERY DATABASE (JOIN Mahasiswa dengan Prodi)
  $sql_mhs = "SELECT m.nim, m.id_mahasiswa, pr.nama_prodi 
                FROM proses_wisuda p 
                JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
                JOIN prodi pr ON m.id_prodi = pr.id_prodi 
                WHERE p.id_proses = '$id_proses'";

  $query_mhs = mysqli_query($conn, $sql_mhs);
  $data_mhs = mysqli_fetch_assoc($query_mhs);

  if ($data_mhs) {
    $id_mahasiswa = $data_mhs['id_mahasiswa'];
    $nim = $data_mhs['nim'];
    $nama_prodi = strtoupper($data_mhs['nama_prodi']);

    // 3. LOGIKA PREFIX KURSI BERDASARKAN NAMA PRODI
    $prefix = "";
    if (strpos($nama_prodi, 'INFORMATIKA') !== false) {
      $prefix = "IF";
    } elseif (strpos($nama_prodi, 'SISTEM INFORMASI') !== false) {
      $prefix = "SI";
    } elseif (strpos($nama_prodi, 'PSIKOLOGI') !== false) {
      $prefix = "PSI";
    } elseif (strpos($nama_prodi, 'MANAJEMEN') !== false || strpos($nama_prodi, 'MANAGEMENN') !== false) {
      $prefix = "MNJ";
    } elseif (strpos($nama_prodi, 'AKUNTANSI') !== false) {
      $prefix = "AKT";
    } elseif (strpos($nama_prodi, 'DESAIN KOMUNIKASI VISUAL') !== false) {
      $prefix = "DKV";
    } elseif (strpos($nama_prodi, 'ILMU KOMUNIKASI') !== false) {
      $prefix = "ILKOM";
    } else {
      $prefix = "UMUM";
    }

    // 4. GENERATE NOMOR URUT KURSI
    $query_urut = mysqli_query($conn, "SELECT COUNT(*) as total FROM kursi WHERE no_kursi LIKE '$prefix-%'");
    $row_urut = mysqli_fetch_assoc($query_urut);
    $nomor_baru = $row_urut['total'] + 1;
    $no_kursi = $prefix . "-" . str_pad($nomor_baru, 3, "0", STR_PAD_LEFT);

    // 5. GENERATE BARCODE BASE64
    ob_start();
    QRcode::png($nim, null, QR_ECLEVEL_L, 5, 2);
    $image_binary = ob_get_contents();
    ob_end_clean();
    $base64_image = 'data:image/png;base64,' . base64_encode($image_binary);

    // 6. EKSEKUSI DATABASE (TRANSACTION)
    mysqli_begin_transaction($conn);
    try {
      // Update Status Proses
      mysqli_query($conn, "UPDATE proses_wisuda SET status_proses = 'selesai', id_petugas = '$id_petugas' WHERE id_proses = '$id_proses'");

      // Update Akses Mahasiswa
      mysqli_query($conn, "UPDATE mahasiswa SET id_akses = 1 WHERE id_mahasiswa = '$id_mahasiswa'");

      // Simpan Barcode (Gunakan ON DUPLICATE agar tidak double)
      mysqli_query($conn, "INSERT INTO barcode (id_proses, barcode_file) 
                                VALUES ('$id_proses', '$base64_image') 
                                ON DUPLICATE KEY UPDATE barcode_file='$base64_image'");

      // Simpan Kursi
      mysqli_query($conn, "INSERT INTO kursi (id_proses, no_kursi) 
                                VALUES ('$id_proses', '$no_kursi') 
                                ON DUPLICATE KEY UPDATE no_kursi='$no_kursi'");

      mysqli_commit($conn);
      echo "<script>alert('Berhasil! Barcode dibuat & Kursi diberikan: $no_kursi'); window.location='/UNIBI_WISUDA/views/petugas/kelola_wisuda.php';</script>";
    } catch (Exception $e) {
      mysqli_rollback($conn);
      die("Error Database: " . $e->getMessage());
    }
  } else {
    echo "<script>alert('Data Mahasiswa atau Prodi tidak ditemukan!'); window.history.back();</script>";
  }
} else {
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_wisuda.php");
  exit;
}
