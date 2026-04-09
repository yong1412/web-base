<?php
session_start();
session_destroy();
$_SESSION['success'] = 'Logged out successfully.';
header("Location: /security/login.php");
exit;
?>