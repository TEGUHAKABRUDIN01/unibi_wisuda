function openDetailModal(data) {
  console.log("Data yang diterima:", data);

  // 1. Isi teks detail dasar
  document.getElementById("m_nama").innerText = data.nama_mahasiswa;
  document.getElementById("m_nim").innerText = data.nim;
  document.getElementById("m_p1").innerText = data.pendamping1 ?? "-";
  document.getElementById("m_p2").innerText = data.pendamping2 ?? "-";
  document.getElementById("m_kursi").innerText = data.no_kursi ?? "-";

  // 2. Logika Menampilkan Status Kehadiran dari tabel detail_wisuda
  const statusElement = document.getElementById("m_status");
  console.log(statusElement);
  // Pastikan key 'status_kehadiran' sesuai dengan alias di query SQL PHP Anda
  const kehadiran = data.status_kehadiran
    ? data.status_kehadiran.toUpperCase()
    : "BELUM TERDATA";

  statusElement.innerText = kehadiran;

  // Memberikan warna visual agar petugas mudah membedakan
  if (data.status_kehadiran === "hadir") {
    statusElement.style.color = "#27ae60"; // Hijau
    statusElement.style.fontWeight = "bold";
  } else {
    statusElement.style.color = "#e74c3c"; // Merah
    statusElement.style.fontWeight = "bold";
  }

  // 3. Logika menampilkan Barcode Base64
  const qrImg = document.getElementById("m_qr");
  if (
    data.barcode_file &&
    data.barcode_file.includes("data:image/png;base64")
  ) {
    qrImg.src = data.barcode_file;
    qrImg.style.display = "block";
  } else {
    qrImg.src = "";
    qrImg.style.display = "none";
  }

  // 4. Tampilkan Modal
  document.getElementById("detailModal").style.display = "flex";
}

