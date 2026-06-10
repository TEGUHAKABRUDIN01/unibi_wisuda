<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

if (isset($_POST['edit_data'])) {
  $id_proses = $_POST['id_proses'];
  $id_mahasiswa = $_POST['id_mahasiswa'];
  $nama = mysqli_real_escape_string($conn, $_POST['nama']);
  $nim = mysqli_real_escape_string($conn, $_POST['nim']); // Ini suffix 6-digit
  $id_prodi = $_POST['id_prodi'];

  mysqli_begin_transaction($conn);

  try {
    // 1. Ambil data prodi baru beserta fakultas dan npm_format
    $res_prodi = mysqli_query($conn, "
      SELECT p.id_fakultas, f.npm_format, p.nama_prodi 
      FROM prodi p
      JOIN fakultas f ON p.id_fakultas = f.id_fakultas
      WHERE p.id_prodi = '$id_prodi'
    ");
    $data_prodi = mysqli_fetch_assoc($res_prodi);

    $res_mhs = mysqli_query($conn, "
    SELECT jenis_peserta
    FROM mahasiswa
    WHERE id_mahasiswa = '$id_mahasiswa'
");

    $data_mhs = mysqli_fetch_assoc($res_mhs);

    $jenis_peserta = $data_mhs['jenis_peserta'];

    if (!$data_prodi) {
      throw new Exception("Program studi tidak ditemukan.");
    }

    $id_fakultas = $data_prodi['id_fakultas'];
    $npm_format = $data_prodi['npm_format'];
    $nama_prodi = strtoupper($data_prodi['nama_prodi']);

    // Gabungkan prefix fakultas dengan suffix 6-digit
    if ($jenis_peserta === 'susulan') {

    // mahasiswa angkatan lama
    $full_nim = $nim;

} else {

    // mahasiswa angkatan sekarang
    $full_nim = $npm_format . $nim;

}

    // 2. Update data mahasiswa (termasuk id_fakultas agar tidak inkonsisten)
    $sql_mhs = "UPDATE mahasiswa 
                SET nama_mahasiswa = '$nama', 
                    nim = '$full_nim', 
                    id_prodi = '$id_prodi', 
                    id_fakultas = '$id_fakultas' 
                WHERE id_mahasiswa = '$id_mahasiswa'";
    mysqli_query($conn, $sql_mhs);

    // 3. Tandai data ini sudah pernah diedit (is_edited = 1)
    $sql_proses = "UPDATE proses_wisuda SET is_edited = 1 WHERE id_proses = '$id_proses'";
    mysqli_query($conn, $sql_proses);

    // 4. REGENERATE SEAT & QR CODE (karena NIM & Prodi berubah)
    $prefix = match (true) {
      str_contains($nama_prodi, 'INFORMATIKA') => 'IF',
      str_contains($nama_prodi, 'SISTEM INFORMASI') => 'SI',
      str_contains($nama_prodi, 'PSIKOLOGI') => 'PSI',
      str_contains($nama_prodi, 'MANAJEMEN') => 'MNJ',
      str_contains($nama_prodi, 'AKUNTANSI') => 'AKT',
      str_contains($nama_prodi, 'DESAIN KOMUNIKASI VISUAL') => 'DKV',
      str_contains($nama_prodi, 'ILMU KOMUNIKASI') => 'ILKOM',
      default => 'UMUM'
    };

    $last_digits = (int) substr($full_nim, -3);
    $no_kursi = $prefix . "-" . str_pad($last_digits, 3, "0", STR_PAD_LEFT);

    $kursi_pnd1  = ($last_digits * 2) - 1;
    $kursi_pnd2  = ($last_digits * 2);
    $no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($kursi_pnd1, 3, "0", STR_PAD_LEFT);
    $no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($kursi_pnd2, 3, "0", STR_PAD_LEFT);

    // Generate QR Mahasiswa
    ob_start();
    QRcode::png($full_nim, null, QR_ECLEVEL_L, 5, 2);
    $qr_image = ob_get_clean();
    $base64_qr = 'data:image/png;base64,' . base64_encode($qr_image);

    // Generate QR Pendamping
    ob_start();
    QRcode::png("PND-" . $full_nim, null, QR_ECLEVEL_L, 5, 2);
    $qr_pnd_image = ob_get_clean();
    $base64_qr_pendamping = 'data:image/png;base64,' . base64_encode($qr_pnd_image);

    // Update Barcode
    mysqli_query($conn, "
      UPDATE barcode 
      SET barcode_file = '$base64_qr', 
          barcode_pendamping = '$base64_qr_pendamping' 
      WHERE id_proses = '$id_proses'
    ");

    // Update Kursi
    mysqli_query($conn, "
      UPDATE kursi 
      SET no_kursi = '$no_kursi', 
          no_kursi_p1 = '$no_kursi_p1', 
          no_kursi_p2 = '$no_kursi_p2' 
      WHERE id_proses = '$id_proses'
    ");

    mysqli_commit($conn);
    $_SESSION['swal_success'] = "Data mahasiswa & kartu wisuda berhasil diperbarui.";

    header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
    exit;
  } catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['swal_error'] = "Gagal memperbarui data mahasiswa: " . $e->getMessage();

    header("Location: /UNIBI_WISUDA/views/petugas/kelola_mahasiswa.php");
    exit;
  }
}
