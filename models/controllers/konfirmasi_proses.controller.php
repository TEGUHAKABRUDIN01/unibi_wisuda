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
   NOMOR KURSI MAHASISWA & PENDAMPING
================================ */
$last_digits = (int) substr($nim, -3);

// Mahasiswa kursi = NPM langsung
$no_kursi    = $prefix . "-" . str_pad($last_digits, 3, "0", STR_PAD_LEFT);

// Pendamping kursi = global urutan
$kursi_pnd1  = ($last_digits * 2) - 1;
$kursi_pnd2  = ($last_digits * 2);

$no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($kursi_pnd1, 3, "0", STR_PAD_LEFT);
$no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($kursi_pnd2, 3, "0", STR_PAD_LEFT);

/* ===============================
   GENERATE QR MAHASISWA
================================ */
ob_start();
QRcode::png($nim, null, QR_ECLEVEL_L, 5, 2);
$qr_image = ob_get_clean();
$base64_qr = 'data:image/png;base64,' . base64_encode($qr_image);

/* ===============================
   GENERATE QR PENDAMPING (satu barcode untuk berdua)
================================ */
ob_start();
QRcode::png("PND-" . $nim, null, QR_ECLEVEL_L, 5, 2);
$qr_pnd_image = ob_get_clean();
$base64_qr_pendamping = 'data:image/png;base64,' . base64_encode($qr_pnd_image);

/* ===============================
   TRANSAKSI DATABASE
================================ */
mysqli_begin_transaction($conn);

try {
  // 1. Update Status di proses_wisuda
  mysqli_query($conn, "
        UPDATE proses_wisuda 
        SET status_proses = 'selesai', id_petugas = '$id_petugas'
        WHERE id_proses = '$id_proses'
    ");

  // 2. Simpan/Update Barcode
  mysqli_query($conn, "
        INSERT INTO barcode (id_proses, barcode_file, barcode_pendamping)
        VALUES ('$id_proses', '$base64_qr', '$base64_qr_pendamping')
        ON DUPLICATE KEY UPDATE 
            barcode_file = '$base64_qr', 
            barcode_pendamping = '$base64_qr_pendamping'
    ");
  $res_barcode = mysqli_query($conn, "SELECT id_barcode FROM barcode WHERE id_proses = '$id_proses'");
  $id_barcode = mysqli_fetch_assoc($res_barcode)['id_barcode'];

  // 3. Simpan/Update Kursi
  mysqli_query($conn, "
        INSERT INTO kursi (id_proses, no_kursi, no_kursi_p1, no_kursi_p2)
        VALUES ('$id_proses', '$no_kursi', '$no_kursi_p1', '$no_kursi_p2')
        ON DUPLICATE KEY UPDATE 
            no_kursi = '$no_kursi', 
            no_kursi_p1 = '$no_kursi_p1', 
            no_kursi_p2 = '$no_kursi_p2'
    ");
  $res_kursi = mysqli_query($conn, "SELECT id_kursi FROM kursi WHERE id_proses = '$id_proses'");
  $id_kursi = mysqli_fetch_assoc($res_kursi)['id_kursi'];

  // 4. Update detail_wisuda
  $sql_update_detail = "UPDATE detail_wisuda 
                          SET id_barcode = '$id_barcode', 
                              id_kursi   = '$id_kursi' 
                          WHERE id_proses = '$id_proses'";
  mysqli_query($conn, $sql_update_detail);

  // 5. Beri Akses Login Mahasiswa
  mysqli_query($conn, "UPDATE mahasiswa SET id_akses = 1 WHERE id_mahasiswa = '$id_mahasiswa'");

  mysqli_commit($conn);
  $_SESSION['swal_success'] = 'Konfirmasi berhasil! Kursi & QR Code (Mhs & Pendamping) telah dibuat.';
} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = 'Gagal konfirmasi wisuda!';
}

header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
exit;