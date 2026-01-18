<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id_proses']) || empty($_GET['id_proses'])) {
    die("Error: ID Proses tidak ditemukan.");
}

$id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);

// Query dengan LEFT JOIN agar fakultas/prodi tidak menggagalkan hasil jika ada data kosong
$query = "SELECT m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi, 
                 k.no_kursi, k.no_kursi_p1, k.no_kursi_p2,
                 b.barcode_file, b.barcode_pendamping,
                 p.status_proses
          FROM proses_wisuda p
          JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
          JOIN prodi pr ON m.id_prodi = pr.id_prodi
          LEFT JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
          LEFT JOIN kursi k ON p.id_proses = k.id_proses
          LEFT JOIN barcode b ON p.id_proses = b.id_proses
          WHERE p.id_proses = '$id_proses'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data || $data['status_proses'] !== 'selesai') {
    die("Data tidak ditemukan atau pendaftaran Anda belum dikonfirmasi oleh petugas.");
}

// Cek di tabel kehadiran_detail
$cek_hadir = mysqli_query($conn, "SELECT status_kehadiran FROM detail_wisuda WHERE id_proses = '$id_proses'");
$h = mysqli_fetch_assoc($cek_hadir);
$status_teks = ($h) ? $h['status_kehadiran'] : "BELUM HADIR";


$html = '
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .card { border: 2px solid #333; padding: 15px; margin-bottom: 20px; border-radius: 10px; }
        .header { text-align: center; font-weight: bold; font-size: 14px; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 3px 0; }
        .label { width: 130px; font-weight: bold; }
        .barcode-section { text-align: center; margin-top: 15px; }
        .barcode-img { width: 120px; height: 120px; }
        .status-box { font-weight: bold; color: #d9534f; border: 1px solid #d9534f; padding: 2px 5px; display: inline-block; }
    </style>
</head>
<body>

    <div class="card">
        <div class="header">KARTU PESERTA WISUDA (MAHASISWA)</div>
        <table>
            <tr><td class="label">Nama Mahasiswa</td><td>: ' . htmlspecialchars($data['nama_mahasiswa']) . '</td></tr>
            <tr><td class="label">NIM</td><td>: ' . htmlspecialchars($data['nim']) . '</td></tr>
            <tr><td class="label">Fakultas</td><td>: ' . htmlspecialchars($data['nama_fakultas'] ?? '-') . '</td></tr>
            <tr><td class="label">Prodi</td><td>: ' . htmlspecialchars($data['nama_prodi']) . '</td></tr>
            <tr><td class="label">No. Kursi</td><td>: <strong style="font-size: 16px;">' . ($data['no_kursi'] ?? '-') . '</strong></td></tr>
            <tr><td class="label">Kehadiran</td><td>: <span class="status-box">' . $status_teks . '</span></td></tr>
        </table>
        <div class="barcode-section">
            <img src="' . $data['barcode_file'] . '" class="barcode-img"><br>
            <strong>' . $data['nim'] . '</strong>
        </div>
    </div>';

// HANYA TAMPILKAN KARTU PENDAMPING JIKA ADA DATA BARCODE PENDAMPING
if (!empty($data['barcode_pendamping'])) {
    $html .= '
    <div class="card">
        <div class="header">KARTU PENDAMPING WISUDA (ORANG TUA)</div>
        <table>
            <tr><td class="label">Nama Mahasiswa</td><td>: ' . htmlspecialchars($data['nama_mahasiswa']) . '</td></tr>
            <tr><td class="label">Fakultas</td><td>: ' . htmlspecialchars($data['nama_fakultas'] ?? '-') . '</td></tr>
            <tr><td class="label">Prodi</td><td>: ' . htmlspecialchars($data['nama_prodi']) . '</td></tr>
            <tr><td class="label">Kursi Pendamping 1</td><td>: <strong>' . ($data['no_kursi_p1'] ?? '-') . '</strong></td></tr>
            <tr><td class="label">Kursi Pendamping 2</td><td>: <strong>' . ($data['no_kursi_p2'] ?? '-') . '</strong></td></tr>
            <tr><td class="label">Kehadiran</td><td>: <span class="status-box">' . $status_teks . '</span></td></tr>
        </table>
        <div class="barcode-section">
            <img src="' . $data['barcode_pendamping'] . '" class="barcode-img"><br>
            <strong>PENDAMPING - ' . $data['nim'] . '</strong>
            <p style="font-size: 9px; margin-top: 5px;">* 1 Barcode berlaku untuk 2 orang pendamping</p>
        </div>
    </div>';
}

$html .= '</body></html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream("Kartu_Wisuda_" . $data['nim'] . ".pdf", ["Attachment" => 1]);
exit;
