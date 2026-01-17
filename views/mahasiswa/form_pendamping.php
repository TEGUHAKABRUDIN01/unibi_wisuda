<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Pastikan hanya mahasiswa yang bisa akses
if (!isset($_SESSION['id_mahasiswa'])) {
  header("Location: ../../index.php");
  exit;
}

$id_mahasiswa = $_SESSION['id_mahasiswa'];

// Ambil data mahasiswa untuk ditampilkan di input (Read-Only)
$query = mysqli_query($conn, "SELECT nim, nama_mahasiswa FROM mahasiswa WHERE id_mahasiswa = '$id_mahasiswa'");
$mhs = mysqli_fetch_assoc($query);

// Cek apakah data pendamping sudah ada
$qPendamping = mysqli_query(
  $conn,
  "SELECT * FROM pendamping WHERE id_mahasiswa = '$id_mahasiswa'"
);

$pendamping_ada = mysqli_num_rows($qPendamping) > 0;


// Ambil barcode untuk mahasiswa ini (asumsi ada relasi id_proses)
$queryBarcode = mysqli_query(
    $conn,
    "SELECT barcode_file FROM barcode b
     JOIN proses_wisuda p ON b.id_proses = p.id_proses
     WHERE p.id_mahasiswa = '$id_mahasiswa' LIMIT 1"
);

$barcode_data = mysqli_fetch_assoc($queryBarcode)['barcode_file'] ?? null;

// Ambil semua barcode untuk id_proses mahasiswa
$id_proses_query = mysqli_query($conn, "SELECT id_proses FROM proses_wisuda WHERE id_mahasiswa = '$id_mahasiswa' LIMIT 1");
$id_proses_row = mysqli_fetch_assoc($id_proses_query);
$id_proses = $id_proses_row['id_proses'] ?? null;

$qr_mahasiswa = null;
$qr_pendamping = null;

if($id_proses){
    $qBarcodes = mysqli_query($conn, "SELECT barcode_file FROM barcode WHERE id_proses = '$id_proses' ORDER BY id_barcode ASC");
    $barcodes = [];
    while($row = mysqli_fetch_assoc($qBarcodes)){
        $barcodes[] = $row['barcode_file'];
    }

    $qr_mahasiswa = $barcodes[0] ?? null;  // QR pertama -> mahasiswa
    $qr_pendamping = $barcodes[1] ?? null; // QR kedua -> pendamping
}


?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Form Data Orang Tua / Pendamping</title>
</head>

<body>

<div class="page-wrapper">

  <!-- FORM KIRI -->
  <div class="form-container">

<?php if (!$pendamping_ada): ?>

  <h2>Data Pendamping Wisuda</h2>

  <form action="../../models/controllers/simpan_pendamping.controller.php" method="POST">

    <div class="form-group">
      <label>NIM Mahasiswa</label>
      <input type="text" value="<?= $mhs['nim']; ?>" readonly>
    </div>

    <div class="form-group">
      <label>Nama Mahasiswa</label>
      <input type="text" value="<?= $mhs['nama_mahasiswa']; ?>" readonly>
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

    <button type="submit" class="btn-simpan">
      Simpan Data Pendamping
    </button>

    <p class="note">
      *Data ini akan tercetak pada kartu wisuda Anda.
    </p>

  </form>

<?php else: ?>

  <h2>Data Pendamping</h2>
  <p style="color: green; font-weight: 600;">
    ✅ Data pendamping sudah diisi
  </p>
  <p>Silakan cek kartu wisuda di sebelah kanan.</p>
  <p>Atau anda bisa langsung mendownloadnya di bagian Dashboard.</p>

<?php endif; ?>

</div>


  <!-- CARD KANAN -->
  <div class="preview-card">

  <?php if ($pendamping_ada): ?>
      <h3>Kartu Wisuda</h3>

      <div class="barcode-container">
    <?php if($qr_mahasiswa): ?>
    <div class="barcode-box">
        <h4 style="font-size:14px;">QR Mahasiswa</h4>
        <img src="<?= $qr_mahasiswa ?>" alt="QR Mahasiswa">
    </div>
    <?php endif; ?>

    <?php if($qr_pendamping): ?>
    <div class="barcode-box">
        <h4 style="font-size:14px;">QR Pendamping</h4>
        <img src="<?= $qr_pendamping ?>" alt="QR Pendamping">
    </div>
    <?php endif; ?>
</div>


      <p class="barcode-text">
          Scan saat pengambilan atribut wisuda
      </p>

  <?php else: ?>
      <div class="empty-preview">
          <p>Barcode akan muncul<br>setelah data pendamping diisi</p>
      </div>
  <?php endif; ?>

</div>



</div>

</body>


</html>

<?php
// 4. Simpan konten ke variabel $content dan panggil layout
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>