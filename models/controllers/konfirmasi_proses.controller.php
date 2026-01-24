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
  SELECT m.nim, m.id_mahasiswa, pr.nama_prodi, p.id_pendamping
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
   NOMOR KURSI MAHASISWA
================================ */
$q_urut_mhs = mysqli_query($conn, "SELECT COUNT(*) total FROM kursi WHERE no_kursi LIKE '$prefix-%'");
$row_mhs    = mysqli_fetch_assoc($q_urut_mhs);
$no_kursi   = $prefix . "-" . str_pad($row_mhs['total'] + 1, 3, "0", STR_PAD_LEFT);

/* ===============================
   LOGIKA PENDAMPING (1 Barcode, 2 Kursi)
================================ */
$no_kursi_p1 = null;
$no_kursi_p2 = null;
$base64_qr_pendamping = null;

// Cek apakah mahasiswa sudah mengisi form pendamping
$q_cek_pnd = mysqli_query($conn, "SELECT * FROM pendamping WHERE id_mahasiswa = '$id_mahasiswa'");
if (mysqli_num_rows($q_cek_pnd) > 0) {
  // 1. Tentukan nomor urut kursi pendamping (Dimulai dari 1 dan seterusnya)
  $q_urut_pnd = mysqli_query($conn, "SELECT COUNT(no_kursi_p1) as total FROM kursi WHERE no_kursi_p1 IS NOT NULL");
  $row_pnd    = mysqli_fetch_assoc($q_urut_pnd);
  $next_pnd   = $row_pnd['total'] + 1;

  // Kursi P1 (Ayah) dan P2 (Ibu)
  $no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($next_pnd, 3, "0", STR_PAD_LEFT);
  $no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($next_pnd + 1, 3, "0", STR_PAD_LEFT);

  // 2. Generate SATU Barcode Khusus Pendamping (untuk berdua)
  ob_start();
  QRcode::png("PND-" . $nim, null, QR_ECLEVEL_L, 5, 2);
  $qr_pnd_image = ob_get_clean();
  $base64_qr_pendamping = 'data:image/png;base64,' . base64_encode($qr_pnd_image);
}

/* ===============================
   GENERATE QR MAHASISWA
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
  // 1. Update Status di proses_wisuda
  mysqli_query($conn, "
        UPDATE proses_wisuda 
        SET status_proses = 'selesai', id_petugas = '$id_petugas'
        WHERE id_proses = '$id_proses'
    ");

  // 2. Simpan/Update Barcode dan ambil ID-nya
  mysqli_query($conn, "
        INSERT INTO barcode (id_proses, barcode_file, barcode_pendamping)
        VALUES ('$id_proses', '$base64_qr', '$base64_qr_pendamping')
        ON DUPLICATE KEY UPDATE 
            barcode_file = '$base64_qr', 
            barcode_pendamping = '$base64_qr_pendamping'
    ");
  // Ambil ID Barcode (Gunakan query jika ON DUPLICATE KEY tidak mengembalikan insert_id yang baru)
  $res_barcode = mysqli_query($conn, "SELECT id_barcode FROM barcode WHERE id_proses = '$id_proses'");
  $id_barcode = mysqli_fetch_assoc($res_barcode)['id_barcode'];

  // 3. Simpan/Update Kursi dan ambil ID-nya
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

  // 4. STEP 2: Update tabel detail_wisuda (MENGISI YANG TADI NULL)
  // Sekarang variabel $id_barcode dan $id_kursi sudah ada isinya
  $sql_update_detail = "UPDATE detail_wisuda 
                          SET id_barcode = '$id_barcode', 
                              id_kursi   = '$id_kursi' 
                          WHERE id_proses = '$id_proses'";

  mysqli_query($conn, $sql_update_detail);

  // 5. Beri Akses Login Mahasiswa (id_akses 1)
  mysqli_query($conn, "UPDATE mahasiswa SET id_akses = 1 WHERE id_mahasiswa = '$id_mahasiswa'");
  mysqli_commit($conn);
  $_SESSION['swal_success'] = 'Konfirmasi berhasil! Kursi & QR Code (Mhs & Pendamping) telah dibuat.';
} catch (Exception $e) {
  mysqli_rollback($conn);
  $_SESSION['swal_error'] = 'Gagal konfirmasi wisuda!';
}

header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
exit;
