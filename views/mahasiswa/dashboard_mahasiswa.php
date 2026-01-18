<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['id_mahasiswa'])) {
  echo "<script>alert('Sesi berakhir');location='../../index.php';</script>";
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

$sql = "
SELECT m.nim, m.nama_mahasiswa, pr.nama_prodi, p.status_proses
FROM mahasiswa m
JOIN prodi pr ON m.id_prodi = pr.id_prodi
LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
WHERE m.id_mahasiswa = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Dashboard Mahasiswa</title>
  <style>
    .btn-unduh {
      background: #28a745;
      color: #fff;
      padding: 8px 15px;
      border: 0;
      border-radius: 4px
    }

    .status-badge {
      color: #777;
      font-style: italic
    }
  </style>
</head>

<body>
  <h3>Dashboard Mahasiswa</h3>

  <table border="1" cellpadding="10">
    <tr>
      <th>NIM</th>
      <th>Nama</th>
      <th>Prodi</th>
      <th>Aksi</th>
    </tr>
    <tr>
      <td><?= htmlspecialchars($data['nim']) ?></td>
      <td><?= htmlspecialchars($data['nama_mahasiswa']) ?></td>
      <td><?= htmlspecialchars($data['nama_prodi']) ?></td>
      <td>
        <?php if (isset($data['status_proses']) && $data['status_proses'] === 'selesai'): ?>
          <a href="../../models/controllers/generate_kartu.controller.php" class="btn-unduh" target="_blank">
            UNDUH PDF
          </a>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <a href="form_pendamping.php">Form Pendamping</a>

  <script>
    function unduhSemuaTiket() {
      const urls = [
        "../../models/controllers/generate_kartu.controller.php?type=mahasiswa",
        "../../models/controllers/generate_kartu.controller.php?type=pendamping"
      ];
      urls.forEach((url, i) => {
        setTimeout(() => window.open(url, '_blank'), i * 1200);
      });
    }
  </script>
</body>

</html>