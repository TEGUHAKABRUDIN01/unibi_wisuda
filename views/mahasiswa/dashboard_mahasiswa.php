<?php
session_start();

// hanya mahasiswa yang boleh masuk
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa') {
  header("Location: /UNIBI_WISUDA/index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa</title>
</head>

<body>

  <!-- Tombol Logout -->
  <form method="POST" action="/UNIBI_WISUDA/models/controllers/logout.controller.php">
    <button type="submit">Logout</button>
  </form>

  <h2>DASHBOARD MAHASISWA</h2>

</body>
</html>