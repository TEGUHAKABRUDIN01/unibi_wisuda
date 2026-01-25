<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
ob_start();

/* ===============================
   1. AUTH & ROLE CHECK
================================ */
if (
  !isset($_SESSION['id_mahasiswa']) ||
  !isset($_SESSION['role']) ||
  $_SESSION['role'] !== 'mahasiswa'
) {
  header("Location: ../../index.php");
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

/* ===============================
   2. AMBIL DATA MAHASISWA
================================ */
$sql = "
  SELECT 
    m.nim,
    m.nama_mahasiswa,
    pr.nama_prodi,
    p.status_proses,
    p.id_proses,
    (SELECT COUNT(*) FROM pendamping WHERE id_mahasiswa = m.id_mahasiswa) as jml_pendamping
  FROM mahasiswa m
  JOIN prodi pr ON m.id_prodi = pr.id_prodi
  LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
  WHERE m.id_mahasiswa = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
  die("Data mahasiswa tidak ditemukan.");
}

// Cek apakah pendamping sudah diisi
$pendamping_ada = ($data['jml_pendamping'] > 0);
?>

<div class="main">
  <div class="card">
    <h2>Dashboard Mahasiswa</h2>

    <?php if (!$data): ?>
      <p class="empty-text">Data mahasiswa tidak ditemukan.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>NIM</th>
            <th>Nama</th>
            <th>Prodi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><?= htmlspecialchars($data['nim']) ?></td>
            <td><?= htmlspecialchars($data['nama_mahasiswa']) ?></td>
            <td><?= htmlspecialchars($data['nama_prodi']) ?></td>
            <td>
              <?php if ($data['status_proses'] === 'selesai' && !empty($data['id_proses'])): ?>
                <div style="display: flex; flex-direction: column; gap: 5px;">
                  <!-- Tombol Mahasiswa selalu muncul -->
                  <a href="../../models/controllers/generate_kartu.controller.php?id_proses=<?= $data['id_proses']; ?>&tipe=mhs"
                    class="btn-unduh"
                    target="_blank"
                    style="background-color: #28a745; text-align: center;">
                    UNDUH KARTU MHS
                  </a>

                  <!-- Tombol Pendamping hanya muncul jika sudah ada -->
                  <?php if ($pendamping_ada): ?>
                    <a href="../../models/controllers/generate_kartu.controller.php?id_proses=<?= $data['id_proses']; ?>&tipe=pnd"
                      class="btn-unduh"
                      target="_blank"
                      style="background-color: #17a2b8; text-align: center;">
                      UNDUH KARTU PENDAMPING
                    </a>
                  <?php else: ?>
                    <span class="badge pending">Kartu pendamping belum tersedia</span>
                  <?php endif; ?>
                </div>
              <?php else: ?>
                <span class="badge pending">Belum tersedia</span>
              <?php endif; ?>
            </td>
          </tr>
        </tbody>
      </table>

      <?php if ($pendamping_ada): ?>
        <p class="empty-text"><em>Data pendamping sudah diisi.</em></p>
      <?php else: ?>
        <p class="empty-text"><em>Belum ada data pendamping.</em></p>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
$title = "Dashboard Mahasiswa";
include_once __DIR__ . '/../layout/layout.php';
?>