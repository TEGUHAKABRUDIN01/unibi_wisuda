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

$total_mahasiswa = 0;
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

    $total_mahasiswa = mysqli_fetch_assoc(mysqli_query($conn,
"
SELECT COUNT(*) AS total_mahasiswa
FROM proses_wisuda
WHERE id_manajemen = $selected_id
"
))['total_mahasiswa'];


$total_parents = mysqli_fetch_assoc(mysqli_query($conn,
"
SELECT COUNT(*) AS total_parents
FROM pendamping pd
JOIN mahasiswa m ON pd.id_mahasiswa = m.id_mahasiswa
JOIN proses_wisuda p ON p.id_mahasiswa = m.id_mahasiswa
WHERE p.id_manajemen = $selected_id
"
))['total_parents'];

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

<style>
    /* =========================
   STYLE REFORMAT ARCHIVE
========================= */
    .archive-title-section {
        margin-bottom: 24px;
    }

    .archive-title-section h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .archive-title-section p {
        color: #64748b;
        font-size: 14px;
    }

    /* Filter Angkatan / Tahun Pills Button */
    .archive-years {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 24px;
        background: #f8fafc;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }

    .archive-year-btn {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 600;
        color: #475569;
        background: #fff;
        text-decoration: none;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        transition: all 0.2s ease;
    }

    .archive-year-btn:hover {
        border-color: #3b82f6;
        color: #3b82f6;
        transform: translateY(-1px);
    }

    .archive-year-btn.active {
        background: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Header Info Event */
    .archive-header {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        border-left: 5px solid #3b82f6;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        margin-bottom: 24px;
    }

    .archive-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 6px;
    }

    .archive-header p {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* Action Buttons (Download) */
    .archive-actions {
        margin: 20px 0;
        display: flex;
        gap: 12px;
    }

    .btn-download {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        border-radius: 8px;
        color: #fff;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .btn-pdf {
        background: #ef4444;
    }

    .btn-pdf:hover:not([style*="pointer-events"]) {
        background: #dc2626;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
    }

    .btn-excel {
        background: #10b981;
    }

    .btn-excel:hover:not([style*="pointer-events"]) {
        background: #059669;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }

    /* Alerts */
    .alert {
        padding: 14px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .alert-info {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    /* Grid Cards Summary (Anti-Pudar) */
    .archive-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .archive-card {
        background: #fff;
        padding: 20px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease;
    }

    .archive-card:hover {
        transform: translateY(-3px);
    }

    .archive-card-info h3 {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0 0 6px 0;
    }

    .archive-card-info p {
        font-size: 32px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
        line-height: 1;
    }

    /* Icon Box Modern dengan Gradien Terang & High Contrast Glow */
    .archive-icon-box {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .archive-icon-box i {
        color: #ffffff !important;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.15));
    }

    .bg-blue {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    }

    .bg-purple {
        background: linear-gradient(135deg, #9333ea, #7e22ce);
        box-shadow: 0 4px 12px rgba(147, 51, 234, 0.35);
    }

    .bg-green {
        background: linear-gradient(135deg, #16a34a, #15803d);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.35);
    }

    .bg-teal {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.35);
    }

    .bg-orange {
        background: linear-gradient(135deg, #ea580c, #c2410c);
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
    }

    .bg-red {
        background: linear-gradient(135deg, #ef4444, #b91c1c);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.35);
    }

    /* Table Section Styling */
    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: #1e293b;
        margin: 32px 0 14px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .archive-table-wrapper {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        margin-bottom: 24px;
    }

    .archive-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 14px;
    }

    .archive-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        padding: 14px 18px;
        border-bottom: 1px solid #e2e8f0;
    }

    .archive-table td {
        padding: 14px 18px;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
    }

    .archive-table tbody tr:last-child td {
        border-bottom: none;
    }

    .archive-table tbody tr:hover td {
        background: #f8fafc;
    }
</style>

<div class="archive-title-section">
    <h1>Arsip Wisuda</h1>
    <p>Pilih tahun wisuda dari daftar di bawah untuk menampilkan data arsip dan unduhan laporan.</p>
</div>

<div class="archive-years">
    <?php while ($event = mysqli_fetch_assoc($events)): ?>
        <a href="?id_manajemen=<?= $event['id_manajemen'] ?>" class="archive-year-btn <?= $selected_event && $selected_event['id_manajemen'] == $event['id_manajemen'] ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days" style="margin-right: 6px;"></i> Angkatan <?= htmlspecialchars($event['angkatan']) ?>
        </a>
    <?php endwhile; ?>
</div>

<?php if (!$selected_event): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info"></i> Silakan pilih tahun wisuda untuk melihat laporan arsip.
    </div>
<?php else: ?>
    <div class="archive-header">
        <h2>Arsip Wisuda Angkatan <?= htmlspecialchars($selected_event['angkatan']) ?></h2>
        <p><i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Periode: <?= htmlspecialchars($selected_event['tgl_mulai']) ?> s/d <?= htmlspecialchars($selected_event['tgl_selesai']) ?> &nbsp;|&nbsp; <i class="fa-solid fa-location-dot" style="margin-right: 4px;"></i> Tempat: <?= htmlspecialchars($selected_event['tempat']) ?></p>
    </div>

    <div class="archive-actions">
        <a href="/UNIBI_WISUDA/models/controllers/arsip_wisuda.controller.php?format=pdf&id_manajemen=<?= $selected_event['id_manajemen'] ?>" class="btn-download btn-pdf" <?= !$event_ready ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>
            <i class="fa-solid fa-file-pdf"></i> Download PDF
        </a>
        <a href="/UNIBI_WISUDA/models/controllers/arsip_wisuda.controller.php?format=excel&id_manajemen=<?= $selected_event['id_manajemen'] ?>" class="btn-download btn-excel" <?= !$event_ready ? 'style="pointer-events:none;opacity:0.5;"' : '' ?>>
            <i class="fa-solid fa-file-excel"></i> Download Excel
        </a>
    </div>

    <?php if (!$event_ready): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-triangle-exclamation"></i> Data arsip akan tersedia penuh setelah acara selesai atau status manajemen diubah menjadi <strong>selesai</strong>.
        </div>
    <?php endif; ?>

    <div class="archive-summary">
        <div class="archive-card">
            <div class="archive-card-info">
                <h3>Jumlah Mahasiswa</h3>
                <p><?= $total_mahasiswa ?></p>
            </div>
            <div class="archive-icon-box bg-green"><i class="fa-solid fa-user-check"></i></div>
        </div>
        <div class="archive-card">
            <div class="archive-card-info">
                <h3>Hadir Mahasiswa</h3>
                <p><?= $total_hadir_mhs ?></p>
            </div>
            <div class="archive-icon-box bg-green"><i class="fa-solid fa-user-check"></i></div>
        </div>
        <div class="archive-card">
            <div class="archive-card-info">
                <h3>Jumlah Orang Tua / Pendamping</h3>
                <p><?= $total_parents ?></p>
            </div>
            <div class="archive-icon-box bg-purple"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="archive-card">
            <div class="archive-card-info">
                <h3>Hadir Orang Tua / Pendamping</h3>
                <p><?= $total_hadir_parents ?></p>
            </div>
            <div class="archive-icon-box bg-teal"><i class="fa-solid fa-user-tag"></i></div>
        </div>
    </div>

    <div class="table-title"><i class="fa-solid fa-list"></i> List Data User (Mahasiswa)</div>
    <div class="archive-table-wrapper">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NPM</th>
                    <th>Fakultas</th>
                    <th>Prodi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($user_list) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($user_list)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nama_mahasiswa']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8;">Tidak ada data mahasiswa.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-title"><i class="fa-solid fa-people-roof"></i> List Data Orang Tua / Pendamping</div>
    <div class="archive-table-wrapper">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>Nama Orang Tua</th>
                    <th>Nama Mahasiswa</th>
                    <th>NPM</th>
                    <th>Fakultas</th>
                    <th>Prodi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($parent_list) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($parent_list)): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['nama_pendamping']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">Tidak ada data orang tua.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-title"><i class="fa-solid fa-user-clock"></i> List Orang Tua yang Tidak Hadir</div>
    <div class="archive-table-wrapper">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>Nama Orang Tua</th>
                    <th>Nama Mahasiswa</th>
                    <th>NPM</th>
                    <th>Fakultas</th>
                    <th>Prodi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($absent_parent_list) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($absent_parent_list)): ?>
                        <tr>
                            <td style="color: #ef4444;"><strong><?= htmlspecialchars($row['nama_pendamping']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8;">Semua orang tua terkonfirmasi hadir / belum ada data absen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-title"><i class="fa-solid fa-user-slash"></i> List Mahasiswa yang Tidak Hadir</div>
    <div class="archive-table-wrapper">
        <table class="archive-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>NPM</th>
                    <th>Fakultas</th>
                    <th>Prodi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($absent_student_list) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($absent_student_list)): ?>
                        <tr>
                            <td style="color: #ef4444;"><strong><?= htmlspecialchars($row['nama_mahasiswa']) ?></strong></td>
                            <td><?= htmlspecialchars($row['nim']) ?></td>
                            <td><?= htmlspecialchars($row['nama_fakultas']) ?></td>
                            <td><?= htmlspecialchars($row['nama_prodi']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8;">Semua mahasiswa terkonfirmasi hadir / belum ada data absen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
?>