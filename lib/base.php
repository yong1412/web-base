<?php

require_once __DIR__ . '/config.php';

session_start();

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/validator.php';
require_once __DIR__ . '/html_helper.php';
require_once __DIR__ . '/mailer.php'; 

require_once __DIR__ . '/../security/db.php';
?>