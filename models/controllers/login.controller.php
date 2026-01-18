<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nim = mysqli_real_escape_string($conn, $_POST['nim']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);

  $query = "SELECT m.*, p.status_proses 
            FROM mahasiswa m 
            LEFT JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa 
            WHERE m.nim = '$nim' AND m.password = '$password'";

  $result = mysqli_query($conn, $query);

  if (mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);

    if ($data['status_proses'] == 'proses') {
      $_SESSION['swal'] = [
        'icon'  => 'warning',
        'title' => 'Belum Dikonfirmasi',
        'text'  => 'Akun Anda belum dikonfirmasi oleh Admin.'
      ];
        header("Location: /UNIBI_WISUDA/views/mahasiswa/login_mahasiswa.php");
      exit;
    }

    // ✅ LOGIN BERHASIL
    $_SESSION['id_mahasiswa'] = $data['id_mahasiswa'];
    $_SESSION['nama'] = $data['nama_mahasiswa'];
    $_SESSION['role'] = 'mahasiswa';

    $_SESSION['swal'] = [
      'icon'  => 'success',
      'title' => 'Login Berhasil',
      'text'  => 'Selamat datang, ' . $data['nama_mahasiswa']
    ];

    header("Location: /UNIBI_WISUDA/views/mahasiswa/dashboard_mahasiswa.php");
    exit;

  } else {
    $_SESSION['swal'] = [
      'icon'  => 'error',
      'title' => 'Login Gagal',
      'text'  => 'NIM atau Password salah!'
    ];
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
  }
}
