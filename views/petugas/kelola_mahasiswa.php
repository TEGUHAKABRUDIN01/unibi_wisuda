<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}

$sql = "SELECT p.id_proses, m.nama_mahasiswa, m.nim, pr.nama_prodi, f.nama_fakultas, p.status_proses
        FROM proses_wisuda p
        JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        ORDER BY p.id_proses DESC";

$query = mysqli_query($conn, $sql);

// judul halaman
$title = "Kelola Wisudawan";

// mulai buffer
ob_start();
?>

<h1>Kelola Wisudawan</h1>

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
      </tr>
    </thead>
    <tbody>
      <?php $no = 1;
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
              <!-- <a href="
              ../../models/controllers/konfirmasi_proses.controller.php?id_proses=<?= $data['id_proses']; ?>" -->

              <a href="#"
                class="btn btn-konfirmasi"
                onclick="konfirmasiWisuda(<?= $data['id_proses']; ?>)">
                Konfirmasi
              </a>
            <?php else : ?>
              <a href="edit_wisuda.php?id=<?= $data['id_proses']; ?>" class="btn btn-edit">Edit</a>
              
              <!-- <a href="../../models/controllers/hapus_wisuda.controller.php?id_proses=<?= $data['id_proses']; ?>" -->

              <a href="#"
                class="btn btn-hapus"
                onclick="hapusWisuda(<?= $data['id_proses']; ?>)">
                Hapus
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php
// simpan isi ke variabel
$content = ob_get_clean();

// panggil layout
include_once __DIR__ . '/../layout/layout.php';

