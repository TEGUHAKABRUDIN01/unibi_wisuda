function openDetailModal(data) {
  // TAMBAHKAN BARIS INI UNTUK CEK ISI DATA
  console.log("Data yang diterima:", data);
  console.log("Isi barcode_file:", data.barcode_file);

  document.getElementById("m_nama").innerText = data.nama_mahasiswa;
  document.getElementById("m_nim").innerText = data.nim;

  // 1. Isi teks detail
  document.getElementById("m_nama").innerText = data.nama_mahasiswa;
  document.getElementById("m_nim").innerText = data.nim;
  document.getElementById("m_p1").innerText = data.pendamping1 ?? "-";
  document.getElementById("m_p2").innerText = data.pendamping2 ?? "-";
  document.getElementById("m_kursi").innerText = data.no_kursi ?? "-";

  const qrImg = document.getElementById("m_qr");

  // 2. Logika menampilkan Barcode Base64
  if (
    data.barcode_file &&
    data.barcode_file.includes("data:image/png;base64")
  ) {
    // Jika data ada dan formatnya benar Base64, langsung tampilkan
    qrImg.src = data.barcode_file;
    qrImg.style.display = "block";
  } else {
    // Jika barcode kosong atau belum dikonfirmasi
    qrImg.src = "";
    qrImg.style.display = "none";
    console.log("Barcode belum tersedia untuk NIM: " + data.nim);
  }

  // 3. Tampilkan Modal
  document.getElementById("detailModal").style.display = "flex";
}

function closeDetailModal() {
  document.getElementById("detailModal").style.display = "none";
}
