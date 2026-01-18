<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  $_SESSION['swal_error'] = 'Akses ditolak!';
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

if (!isset($_GET['id_proses'])) {
  $_SESSION['swal_error'] = 'ID proses tidak valid!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;
}

$id_proses  = mysqli_real_escape_string($conn, $_GET['id_proses']);
$id_petugas = $_SESSION['id_petugas'];

/* ===============================
   Ambil data mahasiswa & prodi
================================ */
$sql_mhs = "
  SELECT m.nim, m.id_mahasiswa, pr.nama_prodi
  FROM proses_wisuda p
  JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
  JOIN prodi pr ON m.id_prodi = pr.id_prodi
  WHERE p.id_proses = '$id_proses'
";
$query_mhs = mysqli_query($conn, $sql_mhs);
$data_mhs  = mysqli_fetch_assoc($query_mhs);

if (!$data_mhs) {
  $_SESSION['swal_error'] = 'Data mahasiswa tidak ditemukan!';
  header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
  exit;
}

$id_mahasiswa = $data_mhs['id_mahasiswa'];
$nim          = $data_mhs['nim'];
$nama_prodi   = strtoupper($data_mhs['nama_prodi']);

/* ===============================
   PREFIX KURSI
================================ */
$prefix = match (true) {
  str_contains($nama_prodi, 'INFORMATIKA') => 'IF',
  str_contains($nama_prodi, 'SISTEM INFORMASI') => 'SI',
  str_contains($nama_prodi, 'PSIKOLOGI') => 'PSI',
  str_contains($nama_prodi, 'MANAJEMEN') => 'MNJ',
  str_contains($nama_prodi, 'AKUNTANSI') => 'AKT',
  str_contains($nama_prodi, 'DESAIN KOMUNIKASI VISUAL') => 'DKV',
  str_contains($nama_prodi, 'ILMU KOMUNIKASI') => 'ILKOM',
  default => 'UMUM'
};

/* ===============================
   NOMOR KURSI
================================ */
$q_urut = mysqli_query($conn, "SELECT COUNT(*) total FROM kursi WHERE no_kursi LIKE '$prefix-%'");
$row    = mysqli_fetch_assoc($q_urut);
$no_kursi = $prefix . "-" . str_pad($row['total'] + 1, 3, "0", STR_PAD_LEFT);

/* ===============================
   GENERATE QR
================================ */
ob_start();
QRcode::png($nim, null, QR_ECLEVEL_L, 5, 2);
$qr_image = ob_get_clean();
$base64_qr = 'data:image/png;base64,' . base64_encode($qr_image);

/* ===============================
   TRANSAKSI DATABASE
================================ */
mysqli_begin_transaction($conn);

try {

  mysqli_query($conn, "
    UPDATE proses_wisuda 
    SET status_proses = 'selesai', id_petugas = '$id_petugas'
    WHERE id_proses = '$id_proses'
  ");

  mysqli_query($conn, "
    UPDATE mahasiswa 
    SET id_akses = 1 
    WHERE id_mahasiswa = '$id_mahasiswa'
  ");

  mysqli_query($conn, "
    INSERT INTO barcode (id_proses, barcode_file)
    VALUES ('$id_proses', '$base64_qr')
    ON DUPLICATE KEY UPDATE barcode_file = '$base64_qr'
  ");

  mysqli_query($conn, "
    INSERT INTO kursi (id_proses, no_kursi)
    VALUES ('$id_proses', '$no_kursi')
    ON DUPLICATE KEY UPDATE no_kursi = '$no_kursi'
  ");

  mysqli_commit($conn);

  $_SESSION['swal_success'] = 'Wisuda berhasil dikonfirmasi. Kursi & QR Code telah dibuat.';

} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = 'Gagal konfirmasi wisuda!';
}

header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
exit;
