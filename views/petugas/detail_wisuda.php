<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

$sql = "SELECT 
    p.id_proses,
    m.nim,
    m.nama_mahasiswa,
    (SELECT nama_pendamping FROM pendamping 
      WHERE id_mahasiswa = m.id_mahasiswa LIMIT 1 OFFSET 0) AS pendamping1,
    (SELECT nama_pendamping FROM pendamping 
      WHERE id_mahasiswa = m.id_mahasiswa LIMIT 1 OFFSET 1) AS pendamping2,
    (SELECT no_kursi FROM kursi 
      WHERE id_proses = p.id_proses LIMIT 1) AS no_kursi,
    b.barcode_file,
    dw.status_kehadiran,
    dw.status_kehadiran_pendamping
FROM proses_wisuda p
JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
LEFT JOIN barcode b ON p.id_proses = b.id_proses   
LEFT JOIN detail_wisuda dw ON p.id_proses = dw.id_proses
ORDER BY p.id_proses DESC";


$query = mysqli_query($conn, $sql);

$title = "Detail Wisuda";
ob_start();
?>

<h1>Detail Wisuda</h1>

<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>No Kursi</th>
        <th>Status Pendamping</th>
        <th>Aksi</th>
        <th>Status Mahasiswa</th>
      </tr>
    </thead>
    <tbody>
      <tbody>
<?php $no = 1;
while ($d = mysqli_fetch_assoc($query)) :

  // status mahasiswa
// status mahasiswa
$status_mhs = ($d['status_kehadiran'] === 'hadir')
  ? '<span class="status-btn hadir">Hadir</span>'
  : '<span class="status-btn tidak-hadir">Tidak Hadir</span>';

$status_pendamping = ($d['status_kehadiran_pendamping'] === 'hadir')
  ? '<span class="status-btn hadir">Hadir</span>'
  : '<span class="status-btn tidak-hadir">Tidak Hadir</span>';

?>
  

        <tr>
          <td><?= $no++; ?></td>
          <td><?= $d['nim']; ?></td>
          <td><?= $d['nama_mahasiswa']; ?></td>
          <td><?= $d['no_kursi'] ?? '-'; ?></td>
        </td>
        <td><?= $status_mhs; ?></td>
        <td><?= $status_pendamping; ?></td>
          <td>
            <button class="btn btn-detail"
              onclick='openDetailModal(<?= json_encode($d); ?>)'>
              Lihat
            </button>

        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- MODAL -->
<div id="detailModal" class="modal">
  <div class="modal-content">
    <span class="close" onclick="closeDetailModal()">&times;</span>

    <h3>Detail Wisudawan</h3>
    <div class="detail-row">
      <span class="label">Nama</span>
      <span class="colon">:</span>
      <span id="m_nama"></span>
    </div>

    <div class="detail-row">
      <span class="label">NIM</span>
      <span class="colon">:</span>
      <span id="m_nim"></span>
    </div>

    <div class="detail-row">
      <span class="label">Pendamping 1</span>
      <span class="colon">:</span>
      <span id="m_p1"></span>
    </div>

    <div class="detail-row">
      <span class="label">Pendamping 2</span>
      <span class="colon">:</span>
      <span id="m_p2"></span>
    </div>

    <div class="detail-row">
      <span class="label">No Kursi</span>
      <span class="colon">:</span>
      <span id="m_kursi"></span>
    </div>

    <div class="detail-row">
      <span class="label">Status Kehadiran</span>
      <span class="colon">:</span>
      <span id="m_status"></span>
    </div>

    <div class="detail-row">
      <span class="label">Qr Code</span>
      <span class="colon">:</span>

    </div>

    <div class="qr-box">
      <img id="m_qr" alt="QR Code" src="">
    </div>
  </div>
</div>

<script src="../../script/detail_wisuda.js"></script>

<script>
function closeDetailModal() {
  document.getElementById("detailModal").style.display = "none";
}
</script>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>