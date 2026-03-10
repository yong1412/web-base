<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

// db.php
$_db = new PDO('mysql:host=localhost;dbname=furnihome', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
]);
?>