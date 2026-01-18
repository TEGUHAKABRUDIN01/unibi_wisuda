<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_mahasiswa'])) exit('Akses ditolak');

include_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$type = $_GET['type'] ?? 'mahasiswa';
if (!in_array($type, ['mahasiswa', 'pendamping'])) exit('Type invalid');

$id = (int)$_SESSION['id_mahasiswa'];

$sql = "
SELECT 
    m.nama_mahasiswa,
    k.no_kursi,
    k.no_kursi_p1,
    k.no_kursi_p2,
    b.barcode_file,
    b.barcode_pendamping,
    GROUP_CONCAT(pd.nama_pendamping ORDER BY pd.id_pendamping SEPARATOR '|') pendamping
FROM mahasiswa m
LEFT JOIN proses_wisuda p ON m.id_mahasiswa=p.id_mahasiswa
LEFT JOIN kursi k ON p.id_proses=k.id_proses
LEFT JOIN barcode b ON p.id_proses=b.id_proses
LEFT JOIN pendamping pd ON m.id_mahasiswa=pd.id_mahasiswa
WHERE m.id_mahasiswa=?
GROUP BY m.id_mahasiswa,k.no_kursi,k.no_kursi_p1,k.no_kursi_p2,b.barcode_file,b.barcode_pendamping
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$d = $stmt->get_result()->fetch_assoc();
if (!$d) exit('Data tidak ditemukan');

$pendamping = explode('|', $d['pendamping'] ?? '');

$html = '<html><body style="font-family:sans-serif;font-size:12px;text-align:center">';

if ($type === 'mahasiswa') {
    $filename = 'Tiket_Wisudawan.pdf';
    $html .= "<h3>KARTU WISUDAWAN</h3>
              <p><b>{$d['nama_mahasiswa']}</b></p>
              <p>Kursi: <b>{$d['no_kursi']}</b></p>";
} else {
    $filename = 'Tiket_Pendamping.pdf';
    $html .= "<h3>TIKET PENDAMPING</h3>
              <p>Pendamping 1: {$pendamping[0]} ({$d['no_kursi_p1']})</p>
              <p>Pendamping 2: {$pendamping[1]} ({$d['no_kursi_p2']})</p>";
}

$html .= '</body></html>';

while (ob_get_level()) ob_end_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);

$pdf = new Dompdf($options);
$pdf->loadHtml($html);
$pdf->setPaper('A5', 'portrait');
$pdf->render();
$pdf->stream($filename, ["Attachment" => true]);
exit;
