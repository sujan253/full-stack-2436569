<?php
// logout.php - destroys session and redirects
session_start();
session_destroy();
header('Location: index.php');
exit;
?>
