<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id_proses']) || empty($_GET['id_proses'])) {
    die("Error: ID Proses tidak ditemukan.");
}

// Ambil parameter id_proses dan tipe (mhs atau pnd)
$id_proses = mysqli_real_escape_string($conn, $_GET['id_proses']);
$tipe = isset($_GET['tipe']) ? $_GET['tipe'] : 'mhs';

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

// Style CSS
$style = '
<style>
    body { font-family: sans-serif; font-size: 12px; }
    .card { border: 2px solid #333; padding: 15px; border-radius: 10px; }
    .header { text-align: center; font-weight: bold; font-size: 14px; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 3px 0; }
    .label { width: 130px; font-weight: bold; }
    .barcode-section { text-align: center; margin-top: 15px; }
    .barcode-img { width: 120px; height: 120px; }
</style>';

$html = '<html><head>' . $style . '</head><body>';

// LOGIKA PEMISAHAN KONTEN
if ($tipe === 'mhs') {
    // KARTU MAHASISWA (kursi = NPM langsung)
    $html .= '
    <div class="card">
        <div class="header">KARTU PESERTA WISUDA (MAHASISWA)</div>
        <table>
            <tr><td class="label">Nama Mahasiswa</td><td>: ' . htmlspecialchars($data['nama_mahasiswa']) . '</td></tr>
            <tr><td class="label">NIM</td><td>: ' . htmlspecialchars($data['nim']) . '</td></tr>
            <tr><td class="label">Fakultas</td><td>: ' . htmlspecialchars($data['nama_fakultas'] ?? '-') . '</td></tr>
            <tr><td class="label">Prodi</td><td>: ' . htmlspecialchars($data['nama_prodi']) . '</td></tr>
            <tr><td class="label">No. Kursi</td><td>: <strong style="font-size: 16px;">' . ($data['no_kursi'] ?? '-') . '</strong></td></tr>
        </table>
        <div class="barcode-section">
            <img src="' . $data['barcode_file'] . '" class="barcode-img"><br>
            <strong>' . $data['nim'] . '</strong>
        </div>
    </div>';
    $filename = "Kartu_Mahasiswa_" . $data['nim'];
} else {
    // KARTU PENDAMPING (kursi = global urutan)
    $html .= '
    <div class="card">
        <div class="header">KARTU PENDAMPING WISUDA (ORANG TUA)</div>
        <table>
            <tr><td class="label">Nama Mahasiswa</td><td>: ' . htmlspecialchars($data['nama_mahasiswa']) . '</td></tr>
            <tr><td class="label">Fakultas</td><td>: ' . htmlspecialchars($data['nama_fakultas'] ?? '-') . '</td></tr>
            <tr><td class="label">Prodi</td><td>: ' . htmlspecialchars($data['nama_prodi']) . '</td></tr>
            <tr><td class="label">Kursi P1</td><td>: <strong>' . ($data['no_kursi_p1'] ?? '-') . '</strong></td></tr>
            <tr><td class="label">Kursi P2</td><td>: <strong>' . ($data['no_kursi_p2'] ?? '-') . '</strong></td></tr>
        </table>
        <div class="barcode-section">
            <img src="' . $data['barcode_pendamping'] . '" class="barcode-img"><br>
            <strong>PENDAMPING - ' . $data['nim'] . '</strong>
            <p style="font-size: 9px; margin-top: 5px;">* 1 Barcode berlaku untuk 2 orang pendamping</p>
        </div>
    </div>';
    $filename = "Kartu_Pendamping_" . $data['nim'];
}

$html .= '</body></html>';

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait'); // ukuran kartu
$dompdf->render();

$dompdf->stream($filename . ".pdf", ["Attachment" => 1]);
exit;