<?php
include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['id_proses'])) {
  die('id_proses tidak ada');
}

$id_proses = (int) $_GET['id_proses'];

/* ===============================
   Ambil NIM & Jurusan Mahasiswa
================================ */
$q = mysqli_query($conn, "
    SELECT m.nim, j.kode_jurusan, m.id_mahasiswa
    FROM proses_wisuda p
    JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
    JOIN jurusan j ON m.id_jurusan = j.id_jurusan
    WHERE p.id_proses = '$id_proses'
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
  die('Data mahasiswa tidak ditemukan');
}

$nim   = $data['nim'];
$kode  = $data['kode_jurusan'];
$id_mahasiswa = $data['id_mahasiswa'];

/* ===============================
   Generate kursi mahasiswa & pendamping
   Mahasiswa kursi = NPM langsung
   Pendamping kursi = global urutan
================================ */
$last_digits = (int) substr($nim, -3);

// Mahasiswa kursi = NPM langsung
$no_kursi_mhs = $kode . "-" . str_pad($last_digits, 3, '0', STR_PAD_LEFT);

// Pendamping kursi = global urutan
$kursi_pnd1 = ($last_digits * 2) - 1;
$kursi_pnd2 = ($last_digits * 2);

$p1 = 'PENDAMPING-' . $kode . '-' . str_pad($kursi_pnd1, 3, '0', STR_PAD_LEFT);
$p2 = 'PENDAMPING-' . $kode . '-' . str_pad($kursi_pnd2, 3, '0', STR_PAD_LEFT);

/* ===============================
   UPDATE tabel kursi
================================ */
mysqli_query($conn, "
    UPDATE kursi 
    SET no_kursi = '$no_kursi_mhs',
        no_kursi_p1 = '$p1',
        no_kursi_p2 = '$p2'
    WHERE id_proses = '$id_proses'
");

echo "Kursi mahasiswa & pendamping berhasil dibuat";