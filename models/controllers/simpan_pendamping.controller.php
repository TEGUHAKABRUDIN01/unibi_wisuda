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
    // 1. Ambil ID Proses, NIM, dan Nama Prodi untuk keperluan barcode & kursi
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
    mysqli_query($conn, "INSERT INTO pendamping (id_mahasiswa, nama_pendamping) VALUES ('$id_mahasiswa', '$nama_ibu')");

    // 3. Update ID Pendamping di tabel proses_wisuda agar sinkron
    mysqli_query($conn, "UPDATE proses_wisuda SET id_pendamping = '$id_pnd_baru' WHERE id_mahasiswa = '$id_mahasiswa'");

    // 4. Tentukan Prefix dan Generate Nomor Kursi Pendamping
    $prefix = match (true) {
      str_contains($nama_prodi, 'INFORMATIKA') => 'IF',
      str_contains($nama_prodi, 'SISTEM INFORMASI') => 'SI',
      str_contains($nama_prodi, 'PSIKOLOGI') => 'PSI',
      default => 'UMUM'
    };


    // AMBIL NOMOR TERAKHIR DARI P2 (Karena P2 selalu yang paling besar/terakhir)
    $q_max = mysqli_query($conn, "SELECT no_kursi_p2 FROM kursi WHERE no_kursi_p2 LIKE 'P2-$prefix-%' ORDER BY id_kursi DESC LIMIT 1");
    $row_max = mysqli_fetch_assoc($q_max);

    if ($row_max) {
      // Ambil 3 angka terakhir dari string P2-IF-006 (yaitu 006)
      $last_number = (int) substr($row_max['no_kursi_p2'], -3);
      $next_pnd_1 = $last_number + 1;
      $next_pnd_2 = $last_number + 2;
    } else {
      // Jika belum ada data sama sekali di prodi tersebut
      $next_pnd_1 = 1;
      $next_pnd_2 = 2;
    }

    // Generate string nomor kursi
    $no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($next_pnd_1, 3, "0", STR_PAD_LEFT);
    $no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($next_pnd_2, 3, "0", STR_PAD_LEFT);

    // $q_urut = mysqli_query($conn, "SELECT COUNT(no_kursi_p1) as total FROM kursi WHERE no_kursi_p1 IS NOT NULL");
    // $row_pnd = mysqli_fetch_assoc($q_urut);
    // $next_pnd = $row_pnd['total'] + 1;

    // $no_kursi_p1 = "P1-" . $prefix . "-" . str_pad($next_pnd, 3, "0", STR_PAD_LEFT);
    // $no_kursi_p2 = "P2-" . $prefix . "-" . str_pad($next_pnd + 1, 3, "0", STR_PAD_LEFT);

    // 5. Generate Barcode Pendamping (Base64)
    ob_start();
    QRcode::png("PND-" . $nim, null, QR_ECLEVEL_L, 5, 2);
    $qr_pnd_raw = ob_get_clean();
    $base64_qr_pendamping = 'data:image/png;base64,' . base64_encode($qr_pnd_raw);

    // 6. UPDATE TABEL KURSI (Tanpa Mengganggu no_kursi Mahasiswa)
    $cek_kursi = mysqli_query($conn, "SELECT id_proses FROM kursi WHERE id_proses = '$id_proses'");
    if (mysqli_num_rows($cek_kursi) > 0) {
      mysqli_query($conn, "UPDATE kursi SET 
                                no_kursi_p1 = '$no_kursi_p1', 
                                no_kursi_p2 = '$no_kursi_p2' 
                                WHERE id_proses = '$id_proses'");
    } else {
      // Jika baris belum ada, insert dengan no_kursi null/placeholder
      mysqli_query($conn, "INSERT INTO kursi (id_proses, no_kursi, no_kursi_p1, no_kursi_p2) 
                                VALUES ('$id_proses', NULL, '$no_kursi_p1', '$no_kursi_p2')");
    }

    // 7. UPDATE TABEL BARCODE (Tanpa Mengganggu barcode_file Mahasiswa)
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
