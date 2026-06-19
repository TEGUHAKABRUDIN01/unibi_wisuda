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

    // Hanya blokir saat wisuda sedang aktif
    if ($status_skrg === 'aktif') {
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

.management-card{
    background:#fff;
    padding:20px;
    border-radius:16px;
    box-shadow:0 4px 20px rgba(0,0,0,.05);
    margin-bottom:25px;
}

.management-status{
    margin-bottom:10px;
}

.badge{
    padding:6px 12px;
    border-radius:30px;
    font-size:12px;
    font-weight:600;
}

.badge-success{
    background:#dcfce7;
    color:#166534;
}

.badge-warning{
    background:#fef3c7;
    color:#92400e;
}

.badge-secondary{
    background:#e2e8f0;
    color:#334155;
}


/* ===== Step ===== */

.step-buttons{
    display:flex;
    gap:10px;
    margin-bottom:25px;
}

.step-btn{
    flex:1;

    padding:14px;

    border:none;

    border-radius:10px;

    background:#f1f5f9;

    color:#475569;

    font-weight:600;

    cursor:pointer;

    transition:.3s;
}

.step-btn:hover{
    background:#dbeafe;
}


.step-btn.active{

    background:#2563eb;

    color:white;

    box-shadow:0 4px 15px rgba(37,99,235,.3);

}


/* ===== Form ===== */

.management-form{

    background:white;

    padding:25px;

    border-radius:16px;

    box-shadow:0 4px 20px rgba(0,0,0,.05);

}


.form-step{

    display:none;

}


.form-step.active{

    display:block;

}


.form-group{

    margin-bottom:20px;

}


.form-group label{

    display:block;

    margin-bottom:8px;

    font-weight:600;

    color:#334155;

}


.form-group input{

    width:100%;

    padding:12px 15px;

    border:1px solid #cbd5e1;

    border-radius:10px;

    font-size:14px;

    transition:.3s;

}


.form-group input:focus{

    outline:none;

    border-color:#2563eb;

    box-shadow:0 0 0 3px rgba(37,99,235,.15);

}



/* ===== Inline ===== */


.form-inline{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;

}



/* ===== Fakultas Table ===== */

.faculty-table{

    margin-top:20px;

    border:1px solid #e2e8f0;

    border-radius:14px;

    overflow:hidden;

}


.faculty-row{

    display:grid;

    grid-template-columns:2fr 2fr 1fr;

    align-items:center;

}


.faculty-header{

    background:#f8fafc;

    font-weight:700;

    color:#334155;

}


.faculty-row div{

    padding:16px;

    border-bottom:1px solid #e2e8f0;

}


.faculty-row:last-child div{

    border-bottom:none;

}


.faculty-row input{

    width:100%;

    padding:10px;

    border:1px solid #cbd5e1;

    border-radius:8px;

}


.faculty-row input:focus{

    outline:none;

    border-color:#2563eb;

}



/* ===== Button Save ===== */

button[name="save_manajemen"]{

    margin-top:25px;

    padding:14px 25px;

    border:none;

    border-radius:10px;

    background:#2563eb;

    color:white;

    font-size:15px;

    font-weight:600;

    cursor:pointer;

    transition:.3s;

}


button[name="save_manajemen"]:hover{

    background:#1d4ed8;

    transform:translateY(-2px);

}



/* ===== Responsive ===== */

@media(max-width:768px){

    .form-inline{
        grid-template-columns:1fr;
    }

    .faculty-row{
        grid-template-columns:1fr;
    }

    .step-buttons{
        flex-direction:column;
    }

}

</style>
</>

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
