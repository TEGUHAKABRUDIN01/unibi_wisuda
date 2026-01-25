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

<h2 class="scan-title">Scan Barcode Mahasiswa / Pendamping</h2>

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
    if (data.status === 'success') {
      if (data.role === 'mahasiswa') {
        Swal.fire({
          icon: 'success',
          title: 'Selamat Datang ' + data.nama,
          text: "No Kursi: " + data.no_kursi + 
                "\nProdi: " + data.prodi + 
                "\nFakultas: " + data.fakultas
        }).then(() => location.reload());
      } else if (data.role === 'pendamping') {
        Swal.fire({
          icon: 'success',
          title: 'Selamat Datang Yth. Tamu Undangan',
          text: "Pendamping dari: " + data.nama + 
                "\nNo Kursi: " + data.no_kursi_p1 + " & " + data.no_kursi_p2 +
                "\nProdi: " + data.prodi +
                "\nFakultas: " + data.fakultas
        }).then(() => location.reload());
      }
    } else {
      Swal.fire({
        icon: 'error',
        title: 'GAGAL',
        text: data.message
      }).then(() => location.reload());
    }
  });
}

let html5QrcodeScanner = new Html5QrcodeScanner(
  "reader",
  { fps: 10, qrbox: 250 }
);
html5QrcodeScanner.render(onScanSuccess);
</script>

<?php
  $content = ob_get_clean();
  include_once __DIR__ . '/../layout/layout.php';
?>