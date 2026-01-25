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

// 2. Ambil data Kehadiran & Tamu
$query_hadir_mhs = mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_mhs FROM detail_wisuda WHERE status_kehadiran = 'hadir'");
$data_hadir_mhs = mysqli_fetch_assoc($query_hadir_mhs);

$query_hadir_pendamping = mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_pendamping FROM detail_wisuda WHERE status_kehadiran_pendamping = 'hadir'");
$data_hadir_pendamping = mysqli_fetch_assoc($query_hadir_pendamping);

// jumlah tamu hadir = mahasiswa hadir + pendamping hadir
$total_tamu_hadir = $data_hadir_mhs['total_hadir_mhs'] + $data_hadir_pendamping['total_hadir_pendamping'];

// total tamu acara = semua mahasiswa + semua pendamping
$query_total_mhs = mysqli_query($conn, "SELECT COUNT(*) AS total_mhs FROM mahasiswa");
$data_total_mhs = mysqli_fetch_assoc($query_total_mhs);

$query_total_pendamping = mysqli_query($conn, "SELECT COUNT(*) AS total_pendamping FROM pendamping");
$data_total_pendamping = mysqli_fetch_assoc($query_total_pendamping);

$total_tamu_acara = $data_total_mhs['total_mhs'] + $data_total_pendamping['total_pendamping'];

// 3. Siapkan variabel untuk Layout
$title = "Dashboard Petugas";

// 4. Mulai menangkap konten (Output Buffering)
ob_start(); 
?>

<h1>Dashboard Petugas</h1>
<p>Monitoring Data Wisudawan</p>

<!-- Baris pertama: 3 badge utama -->
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
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
        </div>
        <div class="card-value"><?= $data_proses['total_proses'] ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Berhasil Konfirmasi</span>
            <div class="icon-box icon-blue">
                <i class="fa-solid fa-check-circle"></i>
            </div>
        </div>
        <div class="card-value"><?= $data_selesai['total_selesai'] ?></div>
    </div>
</div>

<!-- Baris kedua: badge tamu acara -->
<div class="cards">
    <div class="card">
        <div class="card-header">
            <span>Jumlah Kehadiran Tamu Acara</span>
            <div class="icon-box icon-green">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>
        <div class="card-value"><?= $total_tamu_hadir ?></div>
    </div>

    <div class="card">
        <div class="card-header">
            <span>Total Tamu Acara</span>
            <div class="icon-box icon-orange">
                <i class="fa-solid fa-user-friends"></i>
            </div>
        </div>
        <div class="card-value"><?= $total_tamu_acara ?></div>
    </div>
</div>

<?php
// 5. Simpan konten ke variabel $content dan panggil layout
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_SESSION['swal_success'])): ?>
<script>
  Swal.fire({
    icon: 'success',
    title: 'Login Berhasil',
    text: '<?= $_SESSION['swal_success']; ?>',
    timer: 2000,
    showConfirmButton: false
  });
</script>
<?php unset($_SESSION['swal_success']); endif; ?>