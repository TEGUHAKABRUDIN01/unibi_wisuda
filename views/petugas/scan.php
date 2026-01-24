  <?php
  session_start();
  include_once __DIR__ . '/../../config/config.php';


  // Proteksi halaman
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
      header("Location: /UNIBI_WISUDA/index.php");
      exit;
  }

  ob_start();
  ?>

 <h2 class="scan-title">Scan Barcode Mahasiswa</h2>

<div class="scan-container">
  <div id="reader"></div>
  <div class="scan-info">
    Arahkan kamera ke QR Code mahasiswa atau pendamping
  </div>
</div>


  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
  function onScanSuccess(decodedText) {
    html5QrcodeScanner.pause(true);

    fetch('/UNIBI_WISUDA/views/petugas/proses_kehadiran.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded'},
      body: 'nim=' + encodeURIComponent(decodedText)
    })
    .then(res => res.json())
    .then(data => {
      Swal.fire({
        icon: data.status === 'success' ? 'success' : 'error',
        title: data.status === 'success' ? 'BERHASIL' : 'GAGAL',
        text: data.status === 'success'
              ? 'Selamat datang, ' + data.nama
              : data.message
      }).then(() => location.reload());
    });
  }

  let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
  );
  html5QrcodeScanner.render(onScanSuccess);
  </script>

  <?php
  // 4. Simpan konten ke variabel $content dan panggil layout
  $content = ob_get_clean();
  include_once __DIR__ . '/../layout/layout.php';
  ?>