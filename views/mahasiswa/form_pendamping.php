<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
ob_start();

/* ===============================
   AUTH
================================ */
if (!isset($_SESSION['id_mahasiswa'])) {
  header("Location: ../../index.php");
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

/* ===============================
   DATA MAHASISWA
================================ */
$mhsQuery = mysqli_query(
  $conn,
  "SELECT nim, nama_mahasiswa FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'"
);
$mhs = mysqli_fetch_assoc($mhsQuery);

/* ===============================
   CEK DATA PENDAMPING
================================ */
$qPendamping = mysqli_query(
  $conn,
  "SELECT 1 FROM pendamping WHERE id_mahasiswa = '$id_mahasiswa' LIMIT 1"
);
$pendamping_ada = mysqli_num_rows($qPendamping) > 0;

/* ===============================
   AMBIL BARCODE
================================ */
$id_proses = null;
$qr_mahasiswa = null;
$qr_pendamping = null;

$qBarcode = mysqli_query($conn, "
  SELECT 
    p.id_proses,
    b.barcode_file,
    b.barcode_pendamping
  FROM proses_wisuda p
  LEFT JOIN barcode b ON p.id_proses = b.id_proses
  WHERE p.id_mahasiswa = '$id_mahasiswa'
  LIMIT 1
");

if ($row = mysqli_fetch_assoc($qBarcode)) {
  $id_proses = $row['id_proses'];
  $qr_mahasiswa = $row['barcode_file'];
  $qr_pendamping = $row['barcode_pendamping'];
}
?>

<div class="page-wrapper">

  <!-- FORM PENDAMPING -->
  <div class="page-wrapper">

  <?php if (!$pendamping_ada): ?>
    <!-- ================= FORM PENDAMPING ================= -->
    <div class="form-container">

      <h2>Data Pendamping Wisuda</h2>

      <form action="../../models/controllers/simpan_pendamping.controller.php" method="POST">

        <div class="form-group">
          <label>NIM</label>
          <input type="text" value="<?= htmlspecialchars($mhs['nim']) ?>" readonly>
        </div>

        <div class="form-group">
          <label>Nama Mahasiswa</label>
          <input type="text" value="<?= htmlspecialchars($mhs['nama_mahasiswa']) ?>" readonly>
        </div>

        <hr>

        <div class="form-group">
          <label>Nama Orang Tua 1 (Ayah/Wali)</label>
          <input type="text" name="nama_ayah" required>
        </div>

        <div class="form-group">
          <label>Nama Orang Tua 2 (Ibu/Wali)</label>
          <input type="text" name="nama_ibu" required>
        </div>

        <button type="submit" class="btn-simpan">Simpan Pendamping</button>
        <p class="note">Pastikan semua data sudah benar sebelum disimpan.</p>

      </form>
    </div>

  <?php else: ?>
    <!-- ================= KARTU WISUDA SAJA ================= -->
    <div class="preview-card full">

      <h3>Kartu Wisuda</h3>

      <div class="barcode-container">

        <?php if ($qr_mahasiswa): ?>
          <div class="barcode-box">
            <h4>QR Mahasiswa</h4>
            <img src="<?= $qr_mahasiswa ?>" alt="QR Mahasiswa">
          </div>
        <?php endif; ?>

        <?php if ($qr_pendamping): ?>
          <div class="barcode-box">
            <h4>QR Pendamping</h4>
            <img src="<?= $qr_pendamping ?>" alt="QR Pendamping">
          </div>
        <?php endif; ?>

      </div>

      <p class="note"><em>QR Pendamping berlaku untuk 2 orang tua</em></p>

    </div>
  <?php endif; ?>

</div>


<?php
$content = ob_get_clean();
$title = "Form Pendamping";
include_once __DIR__ . '/../layout/layout.php';
?>