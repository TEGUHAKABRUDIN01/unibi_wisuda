<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

$events = mysqli_query($conn, "SELECT id_manajemen, angkatan, tgl_mulai, tgl_selesai, tempat FROM manajemen_wisuda ORDER BY tgl_mulai DESC");
$selected_id = intval($_GET['id_manajemen'] ?? 0);
$selected_event = $selected_id ? getManajemenById($conn, $selected_id) : null;
$event_ready = $selected_event && ($selected_event['status'] === 'selesai' || strtotime($selected_event['tgl_selesai']) <= strtotime(date('Y-m-d')));

$total_users = 0;
$total_parents = 0;
$total_hadir_mhs = 0;
$total_hadir_parents = 0;
$total_tidak_hadir_mhs = 0;
$total_tidak_hadir_parents = 0;
$user_list = [];
$parent_list = [];
$absent_parent_list = [];
$absent_student_list = [];

if ($selected_event) {
    $selected_id = intval($selected_event['id_manajemen']);

    $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM mahasiswa m JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa WHERE p.id_manajemen = $selected_id"))['total_users'];
    $total_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_parents FROM pendamping pd JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa WHERE p.id_manajemen = $selected_id"))['total_parents'];
    $total_hadir_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_mhs FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'hadir'"))['total_hadir_mhs'];
    $total_hadir_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_parents FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'hadir'"))['total_hadir_parents'];
    $total_tidak_hadir_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_tidak_hadir_mhs FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'tidak hadir'"))['total_tidak_hadir_mhs'];
    $total_tidak_hadir_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_tidak_hadir_parents FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'tidak hadir'"))['total_tidak_hadir_parents'];

    $user_list = mysqli_query($conn, "SELECT m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM mahasiswa m
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        WHERE p.id_manajemen = $selected_id
        ORDER BY m.nama_mahasiswa ASC");

    $parent_list = mysqli_query($conn, "SELECT pd.nama_pendamping, m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM pendamping pd
        JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        WHERE p.id_manajemen = $selected_id
        ORDER BY pd.nama_pendamping ASC");

    $absent_parent_list = mysqli_query($conn, "SELECT pd.nama_pendamping, m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM pendamping pd
        JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        JOIN detail_wisuda dw ON dw.id_proses = p.id_proses
        WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'tidak hadir'
        ORDER BY pd.nama_pendamping ASC");

    $absent_student_list = mysqli_query($conn, "SELECT m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM mahasiswa m
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        JOIN detail_wisuda dw ON dw.id_proses = p.id_proses
        WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'tidak hadir'
        ORDER BY m.nama_mahasiswa ASC");
}

ob_start();
?>

<h1>Arsip Wisuda</h1>
<p>Pilih tahun wisuda dari daftar di bawah untuk menampilkan data arsip dan unduhan laporan.</p>

<style>
.archive-years { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
.archive-year-btn { padding: 0.8rem 1.2rem; border: 1px solid #007bff; color: #007bff; text-decoration: none; border-radius: 6px; }
.archive-year-btn.active, .archive-year-btn:hover { background: #007bff; color: #fff; }
.archive-actions { margin: 1rem 0; display: flex; gap: 0.75rem; }
.archive-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.archive-card { padding: 1rem; border: 1px solid #ddd; border-radius: 8px; background: #fafafa; }
</style>

<div class="archive-years">
    <?php while ($event = mysqli_fetch_assoc($events)): ?>
        <a href="?id_manajemen=<?= $event['id_manajemen'] ?>" class="archive-year-btn <?= $selected_event && $selected_event['id_manajemen'] == $event['id_manajemen'] ? 'active' : '' ?>">
            <?= htmlspecialchars($event['angkatan']) ?>
        </a>
    <?php endwhile; ?>
</div>

<?php if (!$selected_event): ?>
    <div class="alert alert-info">Silakan pilih tahun wisuda untuk melihat laporan arsip.</div>
<?php else: ?>
    <div class="archive-header">
        <h2>Arsip Wisuda <?= htmlspecialchars($selected_event['angkatan']) ?></h2>
        <p>Periode: <?= htmlspecialchars($selected_event['tgl_mulai']) ?> s/d <?= htmlspecialchars($selected_event['tgl_selesai']) ?> | Tempat: <?= htmlspecialchars($selected_event['tempat']) ?></p>
    </div>

    <div class="archive-actions">
        <a href="/UNIBI_WISUDA/models/controllers/arsip_wisuda.controller.php?format=pdf&id_manajemen=<?= $selected_event['id_manajemen'] ?>" class="btn btn-primary" <?= !$event_ready ? 'style="pointer-events:none;opacity:0.6;"' : '' ?>>Download PDF</a>
        <a href="/UNIBI_WISUDA/models/controllers/arsip_wisuda.controller.php?format=excel&id_manajemen=<?= $selected_event['id_manajemen'] ?>" class="btn btn-success" <?= !$event_ready ? 'style="pointer-events:none;opacity:0.6;"' : '' ?>>Download Excel</a>
    </div>

    <?php if (!$event_ready): ?>
        <div class="alert alert-info">Data arsip akan tersedia setelah acara selesai atau status diubah menjadi <strong>selesai</strong>.</div>
    <?php endif; ?>

    <div class="archive-summary">
        <div class="archive-card">
            <h3>Jumlah User</h3>
            <p><?= $total_users ?></p>
        </div>
        <div class="archive-card">
            <h3>Jumlah Orang Tua</h3>
            <p><?= $total_parents ?></p>
        </div>
        <div class="archive-card">
            <h3>Hadir Mahasiswa</h3>
            <p><?= $total_hadir_mhs ?></p>
        </div>
        <div class="archive-card">
            <h3>Hadir Orang Tua</h3>
            <p><?= $total_hadir_parents ?></p>
        </div>
        <div class="archive-card">
            <h3>Tidak Hadir Mahasiswa</h3>
            <p><?= $total_tidak_hadir_mhs ?></p>
        </div>
        <div class="archive-card">
            <h3>Tidak Hadir Orang Tua</h3>
            <p><?= $total_tidak_hadir_parents ?></p>
        </div>
    </div>

    <h2>List Data User</h2>
    <table class="archive-table">
        <thead>
            <tr><th>Nama</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($user_list)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                    <td><?= htmlspecialchars($row['nim']) ?></td>
                    <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                    <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>List Data Orang Tua</h2>
    <table class="archive-table">
        <thead>
            <tr><th>Nama Orang Tua</th><th>Nama Mahasiswa</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($parent_list)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pendamping']) ?></td>
                    <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                    <td><?= htmlspecialchars($row['nim']) ?></td>
                    <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                    <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>List Orang Tua yang Tidak Hadir</h2>
    <table class="archive-table">
        <thead>
            <tr><th>Nama Orang Tua</th><th>Nama Mahasiswa</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($absent_parent_list)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_pendamping']) ?></td>
                    <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                    <td><?= htmlspecialchars($row['nim']) ?></td>
                    <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                    <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>List Mahasiswa yang Tidak Hadir</h2>
    <table class="archive-table">
        <thead>
            <tr><th>Nama</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($absent_student_list)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                    <td><?= htmlspecialchars($row['nim']) ?></td>
                    <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                    <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
