<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  // 1. Cari data mahasiswa berdasarkan NIM dan Password (Teks Biasa)
  $query = "SELECT m.*, p.status_proses 
              FROM mahasiswa m 
              LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa 
              WHERE m.nim = '$nim' AND m.password = '$password'";

  $result = mysqli_query($conn, $query);
  $data = mysqli_fetch_assoc($result);

  if (mysqli_num_rows($result) > 0) {
    // 2. CEK STATUS ACC DARI ADMIN
    // Admin memberikan ACC lewat tombol konfirmasi yang merubah status menjadi 'selesai'
    if ($data['status_proses'] == 'proses') {
      echo "<script>
                alert('Akun Anda belum dikonfirmasi oleh Admin.');
                window.location='/UNIBI_WISUDA/index.php';
            </script>";
      exit;
    }

    // 3. Jika status sudah 'selesai' (Sudah di-ACC), buat session
    $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];
    $_SESSION['nama'] = $data['nama_mahasiswa'];
    $_SESSION['role'] = 'mahasiswa';

    header("Location: /UNIBI_WISUDA/views/mahasiswa/dashboard_mahasiswa.php");
  } else {
    // Jika NIM atau Password salah
    echo "<script>
            alert('NIM atau Password salah!');
            window.location='login.php';
        </script>";
  }
}
