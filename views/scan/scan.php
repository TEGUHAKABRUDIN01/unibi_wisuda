<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Scan Kehadiran Wisuda</title>
  <script src="https://unpkg.com/html5-qrcode"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    #reader {
      width: 500px;
      margin: auto;
    }

    .result-container {
      text-align: center;
      margin-top: 20px;
    }
  </style>
</head>

<body>

  <h2 style="text-align: center;">Scan Barcode Mahasiswa</h2>

  <div id="reader"></div>

  <script>
    function onScanSuccess(decodedText, decodedResult) {
      // decodedText berisi NIM hasil scan
      console.log("NIM Terdeteksi: ", decodedText);

      // Berhenti scan sementara agar tidak duplikat kirim data
      html5QrcodeScanner.clear();

      // 2. Kirim data NIM ke PHP menggunakan Fetch API
      fetch('proses_kehadiran.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'nim=' + encodeURIComponent(decodedText)
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'BERHASIL HADIR!',
              text: 'Selamat Datang, ' + data.nama,
              timer: 3000
            }).then(() => {
              location.reload(); // Scan lagi setelah sukses
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'GAGAL!',
              text: data.message
            }).then(() => {
              location.reload();
            });
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan koneksi ke server.');
        });
    }

    // 3. Inisialisasi Scanner
    let html5QrcodeScanner = new Html5QrcodeScanner(
      "reader", {
        fps: 10,
        qrbox: 250
      }
    );
    html5QrcodeScanner.render(onScanSuccess, {
      facingMode: "environment"
    });
  </script>
</body>

</html>