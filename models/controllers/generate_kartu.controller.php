<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_SESSION['id_mahasiswa'])) {
  exit("Akses Ditolak");
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

$sql = "SELECT m.nama_mahasiswa, m.nim, pr.nama_prodi, k.no_kursi, b.barcode_file,
        (SELECT nama_pendamping FROM pendamping WHERE id_mahasiswa = m.id_mahasiswa LIMIT 1 OFFSET 0) as p1,
        (SELECT nama_pendamping FROM pendamping WHERE id_mahasiswa = m.id_mahasiswa LIMIT 1 OFFSET 1) as p2
        FROM mahasiswa m
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        LEFT JOIN kursi k ON p.id_proses = k.id_proses
        LEFT JOIN barcode b ON p.id_proses = b.id_proses
        WHERE m.id_mahasiswa = '$id_mahasiswa'";

$query = mysqli_query($conn, $sql);
$d = mysqli_fetch_assoc($query);

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = '
<div style="border: 3px solid #000; padding: 25px; font-family: sans-serif;">
    <h2 style="text-align: center; margin: 0;">KARTU PESERTA WISUDA</h2>
    <p style="text-align: center; margin: 5px 0 20px 0;">Universitas Informatika dan Bisnis Indonesia</p>
    <hr>
    <table style="width: 100%; margin-top: 20px;">
        <tr><td width="35%">NIM</td><td>: ' . $d['nim'] . '</td></tr>
        <tr><td>NAMA</td><td>: ' . $d['nama_mahasiswa'] . '</td></tr>
        <tr><td>PRODI</td><td>: ' . $d['nama_prodi'] . '</td></tr>
        <tr><td>NO. KURSI</td><td>: ' . ($d['no_kursi'] ?? '-') . '</td></tr>
        <tr><td>PENDAMPING 1</td><td>: ' . (!empty($d['p1']) ? $d['p1'] : '-') . '</td></tr>
        <tr><td>PENDAMPING 2</td><td>: ' . (!empty($d['p2']) ? $d['p2'] : '-') . '</td></tr>
    </table>
    <div style="text-align: center; margin-top: 30px;">
        <img src="' . $d['barcode_file'] . '" width="140">
    </div>
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait');
$dompdf->render();
$dompdf->stream("Kartu_Wisuda_" . $d['nim'] . ".pdf", ["Attachment" => 1]);
