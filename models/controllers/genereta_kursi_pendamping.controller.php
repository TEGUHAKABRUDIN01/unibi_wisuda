<?php
include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['id_proses'])) {
  die('id_proses tidak ada');
}

$id_proses = (int) $_GET['id_proses'];

/* ===============================
   Ambil jurusan mahasiswa
================================ */
$q = mysqli_query($conn, "
    SELECT j.kode_jurusan
    FROM proses_wisuda p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN jurusan j ON m.id_jurusan = j.id_jurusan
    WHERE p.id_proses = '$id_proses'
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
  die('Jurusan tidak ditemukan');
}

$kode = $data['kode_jurusan']; // IF

/* ===============================
   Ambil nomor pendamping terakhir
================================ */
$qLast = mysqli_query($conn, "
    SELECT no_kursi_p2 
    FROM kursi 
    WHERE no_kursi_p2 LIKE 'PENDAMPING-$kode-%'
    ORDER BY id_kursi DESC
    LIMIT 1
");

$last = mysqli_fetch_assoc($qLast);

$last_number = 0;
if ($last && $last['no_kursi_p2']) {
  preg_match('/(\d+)$/', $last['no_kursi_p2'], $m);
  $last_number = (int)$m[1];
}

/* ===============================
   Generate kursi pendamping
================================ */
$p1 = 'PENDAMPING-' . $kode . '-' . str_pad($last_number + 1, 3, '0', STR_PAD_LEFT);
$p2 = 'PENDAMPING-' . $kode . '-' . str_pad($last_number + 2, 3, '0', STR_PAD_LEFT);

/* ===============================
   UPDATE tabel kursi
================================ */
mysqli_query($conn, "
    UPDATE kursi 
    SET 
        no_kursi_p1 = '$p1',
        no_kursi_p2 = '$p2'
    WHERE id_proses = '$id_proses'
");

echo "Kursi pendamping berhasil dibuat";
