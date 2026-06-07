<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: /UNIBI_WISUDA/index.php");
    exit;
}

// Data manajemen wisuda
$result_manajemen = mysqli_query($conn, "SELECT * FROM manajemen_wisuda ORDER BY id_manajemen DESC LIMIT 1");
$manajemen = mysqli_fetch_assoc($result_manajemen);

// Proteksi: jika sudah ada event (aktif/selesai), redirect ke dashboard
if ($manajemen) {
    $status_skrg = getAutoManajemenStatus($manajemen);
    if ($status_skrg === 'aktif' || $status_skrg === 'selesai') {
        header("Location: /UNIBI_WISUDA/views/petugas/dashboard_petugas.php");
        exit;
    }
}


// Data fakultas untuk konfigurasi NPM dan kursi
$query_fakultas = mysqli_query($conn, "SELECT * FROM fakultas ORDER BY nama_fakultas ASC");
$fakultas = [];
while ($row = mysqli_fetch_assoc($query_fakultas)) {
    $fakultas[] = $row;
}

ob_start();
?>

<h1>Manajemen Wisuda</h1>
<p>Isi data acara, format NPM per fakultas, dan tanggal agar sistem berjalan saat wisuda dimulai.</p>

<style>
.management-form .form-step { display: none; }
.management-form .form-step.active { display: block; }
.step-buttons { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
.step-btn { padding: 0.75rem 1rem; border: 1px solid #ccc; background: #fff; cursor: pointer; }
.step-btn.active { background: #007bff; color: #fff; border-color: #007bff; }
</style>

<div class="management-card">
    <?php if ($manajemen): ?>
        <div class="management-status">
            <strong>Status:</strong> 
            <?php $auto_status = getAutoManajemenStatus($manajemen); ?>
            <span class="badge <?= $auto_status === 'aktif' ? 'badge-success' : ($auto_status === 'selesai' ? 'badge-secondary' : 'badge-warning') ?>">
                <?= htmlspecialchars(strtoupper($auto_status)) ?>
            </span>
            <small>(Otomatis berdasarkan tanggal)</small>
        </div>
        <div>
            <strong>Angkatan:</strong> <?= htmlspecialchars($manajemen['angkatan']) ?>
            &nbsp;|&nbsp;
            <strong>Tanggal:</strong> <?= htmlspecialchars($manajemen['tgl_mulai']) ?> s/d <?= htmlspecialchars($manajemen['tgl_selesai']) ?>
            &nbsp;|&nbsp;
            <strong>Tempat:</strong> <?= htmlspecialchars($manajemen['tempat']) ?>
        </div>
    <?php else: ?>
        <div class="management-status">
            <strong>Belum ada konfigurasi wisuda.</strong>
        </div>
    <?php endif; ?>
</div>

<form action="/UNIBI_WISUDA/models/controllers/manajemen_wisuda.controller.php" method="POST" class="management-form">
    <input type="hidden" name="id_manajemen" value="<?= htmlspecialchars($manajemen['id_manajemen'] ?? '') ?>">

    <div class="step-buttons">
        <button type="button" class="step-btn active" data-step="step-angkatan">Angkatan</button>
        <button type="button" class="step-btn" data-step="step-npm">Format NPM</button>
        <button type="button" class="step-btn" data-step="step-jadwal">Jadwal & Lokasi</button>
    </div>

    <div class="form-step active" id="step-angkatan">
        <div class="form-group">
            <label for="angkatan">Angkatan / Tahun Wisuda</label>
            <input type="text" id="angkatan" name="angkatan" required placeholder="Contoh: 2025" value="<?= htmlspecialchars($manajemen['angkatan'] ?? '') ?>">
        </div>
    </div>

    <div class="form-step" id="step-npm">
        <h2>Format NPM dan Jumlah Kursi Fakultas</h2>
        <div class="faculty-table">
            <div class="faculty-row faculty-header">
                <div>Fakultas</div>
                <div>Format NPM</div>
                <div>Jumlah Kursi</div>
            </div>
            <?php foreach ($fakultas as $row): ?>
                <div class="faculty-row">
                    <div><?= htmlspecialchars($row['nama_fakultas']) ?></div>
                    <div>
                        <input type="text" name="npm_format[<?= $row['id_fakultas'] ?>]" placeholder="Contoh: 123" value="<?= htmlspecialchars($row['npm_format'] ?? '') ?>" required>
                    </div>
                    <div>
                        <input type="number" min="0" name="jumlah_kursi[<?= $row['id_fakultas'] ?>]" value="<?= htmlspecialchars($row['jumlah_kursi'] ?? 0) ?>" required>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="form-step" id="step-jadwal">
        <div class="form-group">
            <label for="tempat">Nama Tempat Wisuda</label>
            <input type="text" id="tempat" name="tempat" required placeholder="Contoh: Auditorium Utama" value="<?= htmlspecialchars($manajemen['tempat'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="alamat">Alamat Lengkap</label>
            <input type="text" id="alamat" name="alamat" required placeholder="Contoh: Jl. Raya No. 1" value="<?= htmlspecialchars($manajemen['alamat'] ?? '') ?>">
        </div>
        <div class="form-inline">
            <div class="form-group">
                <label for="tgl_mulai">Tanggal Mulai Wisuda</label>
                <input type="date" id="tgl_mulai" name="tgl_mulai" required value="<?= htmlspecialchars($manajemen['tgl_mulai'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="jam_mulai">Jam Mulai</label>
                <input type="time" id="jam_mulai" name="jam_mulai" required value="<?= htmlspecialchars($manajemen['jam_mulai'] ?? '') ?>">
            </div>
        </div>
        <div class="form-inline">
            <div class="form-group">
                <label for="tgl_selesai">Tanggal Selesai Wisuda</label>
                <input type="date" id="tgl_selesai" name="tgl_selesai" required value="<?= htmlspecialchars($manajemen['tgl_selesai'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="jam_selesai">Jam Selesai</label>
                <input type="time" id="jam_selesai" name="jam_selesai" required value="<?= htmlspecialchars($manajemen['jam_selesai'] ?? '') ?>">
            </div>
        </div>
    </div>

    <button type="submit" name="save_manajemen">Simpan Manajemen Wisuda</button>
</form>

<script>
const stepButtons = document.querySelectorAll('.step-btn');
const stepSections = document.querySelectorAll('.form-step');
stepButtons.forEach(button => {
    button.addEventListener('click', () => {
        const target = button.dataset.step;
        stepButtons.forEach(btn => btn.classList.toggle('active', btn === button));
        stepSections.forEach(section => {
            section.classList.toggle('active', section.id === target);
        });
    });
});
</script>

<?php
$content = ob_get_clean();
include_once __DIR__ . '/../layout/layout.php';
