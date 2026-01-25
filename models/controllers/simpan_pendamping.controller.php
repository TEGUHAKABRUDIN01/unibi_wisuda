<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once __DIR__ . '/../../libs/phpqrcode/qrlib.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id_mahasiswa = $_SESSION['id_mahasiswa'];
  $nama_ayah = mysqli_real_escape_string($conn, $_POST['nama_ayah']);
  $nama_ibu = mysqli_real_escape_string($conn, $_POST['nama_ibu']);

  mysqli_begin_transaction($conn);

  try {
    // 1. Ambil ID Proses, NIM, dan Nama Prodi
    $sql_info = mysqli_query($conn, "SELECT m.nim, pr.nama_prodi, p.id_proses 
                                        FROM mahasiswa m 
                                        JOIN prodi pr ON m.id_prodi = pr.id_prodi 
                                        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa 
                                        WHERE m.id_mahasiswa = '$id_mahasiswa'");
    $info = mysqli_fetch_assoc($sql_info);

    if (!$info) {
      throw new Exception("Data proses wisuda tidak ditemukan.");
    }

    $id_proses = $info['id_proses'];
    $nim = $info['nim'];
    $nama_prodi = strtoupper($info['nama_prodi']);

    // 2. Simpan data ke tabel pendamping
    mysqli_query($conn, "DELETE FROM pendamping WHERE id_mahasiswa = '$id_mahasiswa'");
    mysqli_query($conn, "INSERT INTO pendamping (id_mahasiswa, nama_pendamping) VALUES ('$id_mahasiswa', '$nama_ayah')");
    $id_pnd_baru = mysqli_insert_id($conn);
    if (!empty($nama_ibu)) {
      mysqli_query($conn, "INSERT INTO pendamping (id_mahasiswa, nama_pendamping) VALUES ('$id_mahasiswa', '$nama_ibu')");
    }

    // 3. Update ID Pendamping di tabel proses_wisuda
    mysqli_query($conn, "UPDATE proses_wisuda SET id_pendamping = '$id_pnd_baru' WHERE id_mahasiswa = '$id_mahasiswa'");

    // 4. Tentukan Prefix
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

    // 5. Generate nomor kursi mahasiswa & pendamping
    $last_digits = (int) substr($nim, -3);

    // Mahasiswa kursi = NPM langsung
    $no_kursi_mhs = $prefix . "-" . str_pad($last_digits, 3, "0", STR_PAD_LEFT);

    // Pendamping kursi = global urutan
    $kursi_pnd1  = ($last_digits * 2) - 1;
    $kursi_pnd2  = ($last_digits * 2);

    $no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($kursi_pnd1, 3, "0", STR_PAD_LEFT);
    $no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($kursi_pnd2, 3, "0", STR_PAD_LEFT);

    // 6. Generate Barcode Pendamping (Base64)
    ob_start();
    QRcode::png("PND-" . $nim, null, QR_ECLEVEL_L, 5, 2);
    $qr_pnd_raw = ob_get_clean();
    $base64_qr_pendamping = 'data:image/png;base64,' . base64_encode($qr_pnd_raw);

    // 7. UPDATE TABEL KURSI
    $cek_kursi = mysqli_query($conn, "SELECT id_proses FROM kursi WHERE id_proses = '$id_proses'");
    if (mysqli_num_rows($cek_kursi) > 0) {
      mysqli_query($conn, "UPDATE kursi SET 
                                no_kursi = '$no_kursi_mhs',
                                no_kursi_p1 = '$no_kursi_p1', 
                                no_kursi_p2 = '$no_kursi_p2' 
                                WHERE id_proses = '$id_proses'");
    } else {
      mysqli_query($conn, "INSERT INTO kursi (id_proses, no_kursi, no_kursi_p1, no_kursi_p2) 
                                VALUES ('$id_proses', '$no_kursi_mhs', '$no_kursi_p1', '$no_kursi_p2')");
    }

    // 8. UPDATE TABEL BARCODE
    $cek_barcode = mysqli_query($conn, "SELECT id_proses FROM barcode WHERE id_proses = '$id_proses'");
    if (mysqli_num_rows($cek_barcode) > 0) {
      mysqli_query($conn, "UPDATE barcode SET 
                                barcode_pendamping = '$base64_qr_pendamping' 
                                WHERE id_proses = '$id_proses'");
    } else {
      mysqli_query($conn, "INSERT INTO barcode (id_proses, barcode_pendamping) 
                                VALUES ('$id_proses', '$base64_qr_pendamping')");
    }

    mysqli_commit($conn);
    $_SESSION['swal'] = [
      'icon'  => 'success',
      'title' => 'Berhasil',
      'text'  => 'Data pendamping & barcode berhasil disimpan.'
    ];

    header("Location: ../../views/mahasiswa/dashboard_mahasiswa.php");
    exit;

  } catch (Exception $e) {
    mysqli_rollback($conn);
    echo "Gagal: " . $e->getMessage();
  }
}