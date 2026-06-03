<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

$format = strtolower($_GET['format'] ?? 'pdf');
$selected_id = intval($_GET['id_manajemen'] ?? 0);
$selected_event = getManajemenById($conn, $selected_id);

if (!$selected_event) {
    header("Location: /UNIBI_WISUDA/views/petugas/arsip_wisuda.php");
    exit;
}

$event_ready = $selected_event['status'] === 'selesai' || strtotime($selected_event['tgl_selesai']) <= strtotime(date('Y-m-d'));

function buildArchiveHtml($conn, $selected_event) {
    $selected_id = intval($selected_event['id_manajemen']);
    $total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_users FROM mahasiswa m JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa WHERE p.id_manajemen = $selected_id"))['total_users'];
    $total_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_parents FROM pendamping pd JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa WHERE p.id_manajemen = $selected_id"))['total_parents'];
    $total_hadir_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_mhs FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'hadir'"))['total_hadir_mhs'];
    $total_hadir_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_hadir_parents FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'hadir'"))['total_hadir_parents'];
    $total_tidak_hadir_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_tidak_hadir_mhs FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'tidak hadir'"))['total_tidak_hadir_mhs'];
    $total_tidak_hadir_parents = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total_tidak_hadir_parents FROM detail_wisuda dw JOIN proses_wisuda p ON dw.id_proses = p.id_proses WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'tidak hadir'"))['total_tidak_hadir_parents'];

    $users = mysqli_query($conn, "SELECT m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM mahasiswa m
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        WHERE p.id_manajemen = $selected_id
        ORDER BY m.nama_mahasiswa ASC");

    $parents = mysqli_query($conn, "SELECT pd.nama_pendamping, m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM pendamping pd
        JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        WHERE p.id_manajemen = $selected_id
        ORDER BY pd.nama_pendamping ASC");

    $absent_parents = mysqli_query($conn, "SELECT pd.nama_pendamping, m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM pendamping pd
        JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        JOIN detail_wisuda dw ON dw.id_proses = p.id_proses
        WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran_pendamping = 'tidak hadir'
        ORDER BY pd.nama_pendamping ASC");

    $absent_students = mysqli_query($conn, "SELECT m.nama_mahasiswa, m.nim, f.nama_fakultas, pr.nama_prodi
        FROM mahasiswa m
        JOIN proses_wisuda p ON m.id_mahasiswa = p.id_mahasiswa
        JOIN prodi pr ON m.id_prodi = pr.id_prodi
        JOIN fakultas f ON pr.id_fakultas = f.id_fakultas
        JOIN detail_wisuda dw ON dw.id_proses = p.id_proses
        WHERE p.id_manajemen = $selected_id AND dw.status_kehadiran = 'tidak hadir'
        ORDER BY m.nama_mahasiswa ASC");

    ob_start();
    ?>
    <h1>Arsip Wisuda <?= htmlspecialchars($selected_event['angkatan']) ?></h1>
    <p>Periode: <?= htmlspecialchars($selected_event['tgl_mulai'] . ' s/d ' . $selected_event['tgl_selesai']) ?> | Tempat: <?= htmlspecialchars($selected_event['tempat']) ?> | Status: <?= htmlspecialchars($selected_event['status']) ?></p>

    <h2>Ringkasan</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr><td>Jumlah User</td><td><?= $total_users ?></td></tr>
        <tr><td>Jumlah Orang Tua</td><td><?= $total_parents ?></td></tr>
        <tr><td>Jumlah Kehadiran Mahasiswa</td><td><?= $total_hadir_mhs ?></td></tr>
        <tr><td>Jumlah Kehadiran Orang Tua</td><td><?= $total_hadir_parents ?></td></tr>
        <tr><td>Jumlah Mahasiswa Tidak Hadir</td><td><?= $total_tidak_hadir_mhs ?></td></tr>
        <tr><td>Jumlah Orang Tua Tidak Hadir</td><td><?= $total_tidak_hadir_parents ?></td></tr>
    </table>

    <h2>Daftar User</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr><th>Nama</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td><?= htmlspecialchars($row['nim']) ?></td>
                <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Daftar Orang Tua</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr><th>Nama Orang Tua</th><th>Nama Mahasiswa</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($parents)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_pendamping']) ?></td>
                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td><?= htmlspecialchars($row['nim']) ?></td>
                <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Daftar Orang Tua Tidak Hadir</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr><th>Nama Orang Tua</th><th>Nama Mahasiswa</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($absent_parents)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_pendamping']) ?></td>
                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td><?= htmlspecialchars($row['nim']) ?></td>
                <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Daftar Mahasiswa Tidak Hadir</h2>
    <table border="1" cellpadding="6" cellspacing="0" width="100%">
        <tr><th>Nama</th><th>NPM</th><th>Fakultas</th><th>Prodi</th></tr>
        <?php while ($row = mysqli_fetch_assoc($absent_students)): ?>
            <tr>
                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                <td><?= htmlspecialchars($row['nim']) ?></td>
                <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
    <?php
    return ob_get_clean();
}

if (!$event_ready) {
    header("Location: /UNIBI_WISUDA/views/petugas/arsip_wisuda.php?id_manajemen={$selected_id}");
    exit;
}

$content = buildArchiveHtml($conn, $selected_event);

if ($format === 'pdf') {
    require_once __DIR__ . '/../../libs/dompdf/autoload.inc.php';
    $dompdf = new Dompdf\Dompdf();
    $dompdf->loadHtml($content);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename=arsip_wisuda_' . $selected_event['angkatan'] . '.pdf');
    $dompdf->stream('arsip_wisuda', ['Attachment' => 1]);
    exit;
}

if ($format === 'excel') {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename=arsip_wisuda_' . $selected_event['angkatan'] . '.xls');
    echo $content;
    exit;
}

header("Location: /UNIBI_WISUDA/views/petugas/arsip_wisuda.php?id_manajemen={$selected_id}");
exit;
