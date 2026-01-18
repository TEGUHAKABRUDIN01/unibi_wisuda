<?php
if (isset($_SESSION['swal'])):
?>
<script>
Swal.fire({
  icon: "<?= $_SESSION['swal']['icon'] ?>",
  title: "<?= $_SESSION['swal']['title'] ?>",
  text: "<?= $_SESSION['swal']['text'] ?>",
  confirmButtonText: "OK"
});
</script>
<?php
unset($_SESSION['swal']);
endif;
?>
