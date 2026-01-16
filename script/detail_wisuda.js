function openDetailModal(data) {
  document.getElementById('m_nama').innerText = data.nama_mahasiswa;
  document.getElementById('m_nim').innerText = data.nim;
  document.getElementById('m_p1').innerText = data.pendamping1 ?? '-';
  document.getElementById('m_p2').innerText = data.pendamping2 ?? '-';
  document.getElementById('m_kursi').innerText = data.no_kursi ?? '-';

  if (data.barcode_file) {
    document.getElementById('m_qr').src =
      "../../uploads/barcode/" + data.barcode_file;
  }

  document.getElementById('detailModal').style.display = 'flex';
}

function closeDetailModal() {
  document.getElementById('detailModal').style.display = 'none';
}
