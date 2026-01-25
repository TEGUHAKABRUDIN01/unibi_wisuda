<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

// jumlah record per halaman
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ambil kata kunci pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

// ambil filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : "semua";

// base query
$where = "WHERE 1=1";
if ($search !== "") {
  $where .= " AND (m.nim LIKE '%$search%' OR m.nama_mahasiswa LIKE '%$search%')";
}
if ($status_filter === "selesai" || $status_filter === "proses") {
  $where .= " AND p.status_proses = '$status_filter'";
}

// hitung total data
$count_sql = "SELECT COUNT(*) as total
              FROM proses_wisuda p
              JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
              JOIN prodi pr ON m.id_prodi = pr.id_prodi
              JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
              $where";
$count_result = mysqli_query($conn, $count_sql);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// query utama
$sql = "SELECT p.id_proses, m.id_mahasiswa, m.nama_mahasiswa, m.nim, pr.nama_prodi, f.nama_fakultas, p.status_proses
        FROM proses_wisuda p
        JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        $where
        ORDER BY p.id_proses DESC
        LIMIT $limit OFFSET $offset";
$query = mysqli_query($conn, $sql);

// judul halaman
$title = "Kelola Wisudawan";

// mulai buffer
ob_start();
?>

<h1>Kelola Wisudawan</h1>
<br>

<!-- Container Filter & Search -->
<div class="filter-search-container">
  <!-- Filter status di kiri -->
  <form method="get" class="filter-form">
    <select name="status" class="filter-input">
      <option value="semua" <?= $status_filter === "semua" ? "selected" : "" ?>>Semua</option>
      <option value="proses" <?= $status_filter === "proses" ? "selected" : "" ?>>Proses</option>
      <option value="selesai" <?= $status_filter === "selesai" ? "selected" : "" ?>>Selesai</option>
    </select>
    <button type="submit" class="filter-btn">Filter</button>
  </form>

  <!-- Pencarian di kanan -->
  <form method="get" class="search-form">
    <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>" />
    <input type="text" name="search" placeholder="Cari..." 
           value="<?= htmlspecialchars($search) ?>" class="search-input" />
    <button type="submit" class="search-btn">Cari</button>
  </form>
</div>

<div class="table-container">
  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>NIM</th>
        <th>Nama Mahasiswa</th>
        <th>Fakultas</th>
        <th>Prodi</th>
        <th>Status</th>
        <th>Aksi</th>
        <th>SK Kelulusan</th> 
      </tr>
    </thead>
    <tbody>
      <?php $no = $offset + 1;
      while ($data = mysqli_fetch_assoc($query)) : ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= $data['nim']; ?></td>
          <td><?= $data['nama_mahasiswa']; ?></td>
          <td><?= $data['nama_fakultas']; ?></td>
          <td><?= $data['nama_prodi']; ?></td>
          <td>
            <span class="<?= $data['status_proses'] === 'proses' ? 'status-proses' : 'status-selesai'; ?>">
              <?= ucfirst($data['status_proses']); ?>
            </span>
          </td>
          <td>
            <?php if ($data['status_proses'] === 'proses') : ?>
              <a href="#" class="btn btn-konfirmasi" onclick="konfirmasiWisuda(<?= $data['id_proses']; ?>)">Konfirmasi</a>
            <?php else : ?>
              <a href="edit_wisuda.php?id=<?= $data['id_proses']; ?>" class="btn btn-edit">Edit</a>
              <a href="#" class="btn btn-hapus" onclick="hapusWisuda(<?= $data['id_proses']; ?>)">Hapus</a>
            <?php endif; ?>
          </td>
          <td>
            <a href="../../models/controllers/view_sk.controller.php?id_mahasiswa=<?= $data['id_mahasiswa']; ?>" target="_blank" class="btn btn-sk">Lihat SK</a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Pagination -->
<div class="pagination">
  <?php if ($page > 1): ?>
    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link">« Prev</a>
  <?php endif; ?>

  <?php for ($i = 1; $i <= $total_pages; $i++): ?>
    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link <?= ($i == $page) ? 'active' : '' ?>">
      <?= $i ?>
    </a>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status_filter) ?>" class="page-link">Next »</a>
  <?php endif; ?>
</div>

<style>
  .filter-search-container {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
  }
  .filter-input, .search-input {
    padding: 6px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
  .filter-btn, .search-btn {
    padding: 6px 12px;
    margin-left: 5px;
    border: 1px solid #007bff;
    border-radius: 4px;
    background-color: #007bff;
    color: #fff;
    cursor: pointer;
  }
  .filter-btn:hover, .search-btn:hover {
    background-color: #0056b3;
    border-color: #0056b3;
  }
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
</style>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>