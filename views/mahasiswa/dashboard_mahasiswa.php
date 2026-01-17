<?php
session_start();

$title = "Dashboard Mahasiswa";

include_once __DIR__ . '/../../config/config.php';

ob_start();

// 1. Cek apakah user sudah login
if (!isset($_SESSION['id_mahasiswa'])) {
  echo "<script>alert('Sesi Berakhir, Silakan Login Kembali'); window.location='../../index.php';</script>";
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// 2. Ambil data mahasiswa dengan JOIN ke prodi
$sql = "
SELECT 
    m.nim,
    m.nama_mahasiswa,
    pr.nama_prodi,
    p.status_proses,
    d.nama_pendamping,
    d.nama_pendamping
FROM mahasiswa m
JOIN prodi pr ON m.id_prodi = pr.id_prodi
LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
LEFT JOIN pendamping d ON m.id_mahasiswa = d.id_mahasiswa
WHERE m.id_mahasiswa = '$id_mahasiswa'
";


$query = mysqli_query($conn, $sql);
$data = mysqli_fetch_assoc($query);
$pendamping_ada = !empty($data['nama_pendamping']);


// 3. Validasi jika data tidak ditemukan agar tidak error Offset on Null
if (!$data) {
  die("Error: Data profil mahasiswa tidak ditemukan di database.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Dashboard Mahasiswa</title>
</head>

<body class="dashboard-page">


<div class="main">
  <div class="card">
  <h2>Dashboard</h2>

  <?php if ($pendamping_ada): ?>

    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>NIM</th>
          <th>Nama</th>
          <th>Program Studi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1</td>
          <td><?= htmlspecialchars($data['nim']); ?></td>
          <td><?= htmlspecialchars($data['nama_mahasiswa']); ?></td>
          <td><?= htmlspecialchars($data['nama_prodi']); ?></td>
          <td>
            <?php if ($data['status_proses'] === 'selesai'): ?>
              <a href="../../models/controllers/generate_kartu.controller.php"
                 class="btn-unduh"
                 target="_blank">
                Unduh PDF
              </a>
            <?php else: ?>
              <span class="badge pending">Menunggu Konfirmasi</span>
            <?php endif; ?>
          </td>
        </tr>
      </tbody>
    </table>

  <?php endif; ?>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function logout(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Logout?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Logout',
    cancelButtonText: 'Batal'
  }).then((r) => {
    if (r.isConfirmed) {
      window.location.href =
        '/UNIBI_WISUDA/models/controllers/logout.controller.php';
    }
  });
}
</script>

</body>


</html>

<?php
// 4. Simpan konten ke variabel $content dan panggil layout
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>