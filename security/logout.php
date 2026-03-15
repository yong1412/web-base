<?php
require '../_base.php';

setcookie('remember_token', '', time() - 3600, "/");
session_destroy();

redirect("login.php");
?>