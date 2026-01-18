<?php
session_start();
session_destroy();
session_start();
header("Location: /UNIBI_WISUDA/index.php");
exit;
