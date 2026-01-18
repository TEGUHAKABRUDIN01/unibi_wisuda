/* =========================
   HAPUS DATA WISUDA
========================= */
function konfirmasiHapus(id) {
  Swal.fire({
    title: 'Yakin ingin menghapus?',
    text: 'Data wisuda akan dihapus permanen!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        `/UNIBI_WISUDA/models/controllers/hapus_wisuda.controller.php?id=${id}`;
    }
  });
}



function logout() {
  Swal.fire({
    title: 'Logout?',
    text: 'Anda akan keluar dari sistem',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Logout',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href =
        '/UNIBI_WISUDA/models/controllers/logout.controller.php';
    }
  });
}
