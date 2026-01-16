function konfirmasiHapus(id) {
  if (confirm("Yakin untuk menghapus?")) {
    window.location.href =
      "../../models/controllers/hapus_wisuda.controller.php?id_proses=" + id;
  } else {
    alert("Data gagal dihapus!");
  }
}

