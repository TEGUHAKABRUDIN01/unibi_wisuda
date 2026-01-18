<?php
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

if (!isset($_GET['id_proses']) || !isset($_GET['type'])) {
  exit('Parameter tidak lengkap');
}

$id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);
$type = $_GET['type'];

// ===============================
// Ambil data mahasiswa
// ===============================
$q = mysqli_query($conn, "
    SELECT m.nim 
    FROM proses_wisuda pw
    JOIN mahasiswa m ON pw.id_mahasiswa = m.id_mahasiswa
    WHERE pw.id_proses = '$id_proses'
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
  exit('Data tidak ditemukan');
}

// ===============================
// Tentukan isi QR
// ===============================
if ($type === 'mahasiswa') {
  $qr_text = 'MHS-' . $data['nim'];
  $field   = 'barcode_file';
} elseif ($type === 'pendamping') {
  $qr_text = 'PENDAMPING-' . $id_proses;
  $field   = 'barcode_pendamping';
} else {
  exit('Tipe QR tidak valid');
}

// ===============================
// Simpan QR ke folder
// ===============================
$folder = __DIR__ . '/../../assets/qrcode/';
if (!is_dir($folder)) {
  mkdir($folder, 0777, true);
}

$filename = $qr_text . '.png';
$filepath = $folder . $filename;

QRcode::png($qr_text, $filepath, QR_ECLEVEL_L, 6, 2);

// ===============================
// Simpan ke database
// ===============================
$cek = mysqli_query($conn, "SELECT id_barcode FROM barcode WHERE id_proses = '$id_proses'");

if (mysqli_num_rows($cek) > 0) {
  mysqli_query($conn, "
        UPDATE barcode 
        SET $field = '$filename'
        WHERE id_proses = '$id_proses'
    ");
} else {
  mysqli_query($conn, "
        INSERT INTO barcode (id_proses, $field)
        VALUES ('$id_proses', '$filename')
    ");
}

echo 'QR berhasil dibuat';
