<?php
session_start();
include_once __DIR__ . '/../../config/config.php';


// Proteksi halaman
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

// 1. Ambil data Wisudawan
$query_proses = mysqli_query($conn, "SELECT COUNT(*) AS total_proses FROM proses_wisuda WHERE status_proses = 'proses'");
$data_proses = mysqli_fetch_assoc($query_proses);

$query_selesai = mysqli_query($conn, "SELECT COUNT(*) AS total_selesai FROM proses_wisuda WHERE status_proses = 'selesai'");
$data_selesai = mysqli_fetch_assoc($query_selesai);

$total_wisudawan = $data_proses['total_proses'] + $data_selesai['total_selesai'];

// 2. Siapkan variabel untuk Layout
$title = "Dashboard Petugas";

// 3. Mulai menangkap konten (Output Buffering)
ob_start(); 
?>

<h1>Dashboard Petugas</h1>
<p>Monitoring Data Wisudawan</p>

<div class="cards">
    <div class="card">
        <div class="card-header">
            <span>Total Wisudawan</span>
            <div class="icon-box icon-blue">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="card-value"><?= $total_wisudawan ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Menunggu Konfirmasi</span>
            <div class="icon-box icon-blue">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="card-value"><?= $data_proses['total_proses'] ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Berhasil Konfirmasi</span>
            <div class="icon-box icon-blue">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>
        <div class="card-value"><?= $data_selesai['total_selesai'] ?></div>
    </div>
</div>

<?php
// 4. Simpan konten ke variabel $content dan panggil layout
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>