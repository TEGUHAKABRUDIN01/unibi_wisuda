<?php
session_start();
session_destroy();
header("Location: /UNIBI_WISUDA/index.php");
exit;
?>