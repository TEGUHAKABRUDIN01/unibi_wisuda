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

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Form Pendamping Wisuda</title>
</head>

<body>

  <div class="page-wrapper">

    <!-- ===============================
     FORM PENDAMPING
================================ -->
    <div class="form-container">

      <?php if (!$pendamping_ada): ?>

        <h2>Data Pendamping Wisuda</h2>

        <form action="../../models/controllers/simpan_pendamping.controller.php" method="POST">

          <div>
            <label>NIM</label>
            <input type="text" value="<?= htmlspecialchars($mhs['nim']) ?>" readonly>
          </div>

          <div>
            <label>Nama Mahasiswa</label>
            <input type="text" value="<?= htmlspecialchars($mhs['nama_mahasiswa']) ?>" readonly>
          </div>

          <hr>

          <div>
            <label>Nama Orang Tua 1 (Ayah/Wali)</label>
            <input type="text" name="nama_ayah" required>
          </div>

          <div>
            <label>Nama Orang Tua 2 (Ibu/Wali)</label>
            <input type="text" name="nama_ibu" required>
          </div>

          <button type="submit">Simpan Pendamping</button>

        </form>

      <?php else: ?>

        <h2>Data Pendamping</h2>
        <p style="color:green;font-weight:bold;">✅ Data pendamping sudah diisi</p>
        <p>Silakan cek kartu wisuda di bawah.</p>

      <?php endif; ?>

    </div>

    <!-- ===============================
     PREVIEW KARTU
================================ -->
    <div class="preview-card">

      <?php if ($pendamping_ada): ?>

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

        <p><em>QR Pendamping berlaku untuk 2 orang tua</em></p>

      <?php else: ?>

        <p>Barcode akan muncul setelah data pendamping diisi.</p>

      <?php endif; ?>

    </div>

  </div>

</body>

</html>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>