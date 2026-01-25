<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

// Tentukan jumlah record per halaman
$limit = 5;

// Ambil halaman aktif dari parameter GET, default = 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Ambil kata kunci pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// Hitung offset
$offset = ($page - 1) * $limit;

// Hitung total data untuk pagination (hanya yg selesai + filter pencarian)
$count_sql = "SELECT COUNT(*) as total 
              FROM proses_wisuda p
              JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
              WHERE p.status_proses = 'selesai' 
              AND (m.nim LIKE '%$search%' OR m.nama_mahasiswa LIKE '%$search%')";
$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Query dengan LIMIT & OFFSET (hanya yg selesai + filter pencarian)
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
WHERE p.status_proses = 'selesai'
AND (m.nim LIKE '%$search%' OR m.nama_mahasiswa LIKE '%$search%')
ORDER BY p.id_proses DESC
LIMIT $limit OFFSET $offset";

$query = mysqli_query($conn, $sql);

$title = "Detail Wisuda";
ob_start();
?>

<h1>Detail Wisuda</h1>

<!-- Form Pencarian -->
<form method="get" class="search-form">
  <input type="text" name="search" placeholder="Cari..." 
         value="<?= htmlspecialchars($search) ?>" class="search-input" />
  <button type="submit" class="search-btn">Cari</button>
</form>

<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>NIM</th>
        <th>Nama</th>
        <th>No Kursi</th>
        <th>Status Mahasiswa</th>
        <th>Status Pendamping</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = $offset + 1;
      while ($d = mysqli_fetch_assoc($query)) :

        // Status Mahasiswa
        if ($d['status_kehadiran'] === 'hadir') {
          $status_mhs = '<span class="status-btn hadir">Hadir</span>';
        } elseif ($d['status_kehadiran'] === 'proses' || $d['status_kehadiran'] === '') {
          $status_mhs = '<span class="status-btn proses">Proses</span>';
        } else {
          $status_mhs = '<span class="status-btn tidak-hadir">Tidak Hadir</span>';
        }

        // Status Pendamping
        if ($d['status_kehadiran_pendamping'] === 'hadir') {
          $status_pendamping = '<span class="status-btn hadir">Hadir</span>';
        } elseif ($d['status_kehadiran_pendamping'] === 'proses') {
          $status_pendamping = '<span class="status-btn proses">Proses</span>';
        } else {
          $status_pendamping = '<span class="status-btn tidak-hadir">Tidak Hadir</span>';
        }
      ?>

        <tr>
          <td><?= $no++; ?></td>
          <td><?= $d['nim']; ?></td>
          <td><?= $d['nama_mahasiswa']; ?></td>
          <td><?= $d['no_kursi'] ?? '-'; ?></td>
          <td><?= $status_mhs; ?></td>
          <td><?= $status_pendamping; ?></td>
          <td>
            <button class="btn btn-detail"
              onclick='openDetailModal(<?= json_encode($d); ?>)'>
              Lihat
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="page-link">« Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="page-link">Next »</a>
  <?php endif; ?>
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

<style>
  .pagination {
    margin-top: 15px;
    text-align: center;
  }
  .page-link {
    display: inline-block;
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ccc;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
  }
  .page-link.active {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
  }

  /* Form pencarian */
.search-form {
  margin-bottom: 15px;
  text-align: right; /* sejajar kanan di atas tabel */
}

.search-input {
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.search-btn {
  padding: 6px 12px;
  margin-left: 5px;
  border: 1px solid #007bff;
  border-radius: 4px;
  background-color: #007bff;
  color: #fff;
  cursor: pointer;
}

.search-btn:hover {
  background-color: #0056b3;
  border-color: #0056b3;
}
</style>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>