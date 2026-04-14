<?php

function require_login() { // Checks if the user is logged in; if not, redirects to the login page.
    if (!isset($_SESSION['user_id'])) {
        header('Location: /security/login.php');
        exit;
    }
}


function require_admin() { // Ensures the user is logged in and is an admin; otherwise, redirects to the homepage.
    require_login(); // Must be logged in first
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        header('Location: /index.php');
        exit;
    }
}
?>