<?php

// index.php - Home page
// use php -S localhost:8000 to run the server

require_once 'lib/base.php';

$page_title = 'Home';
include 'page/_head.php';
?>

<main>
    <h2>Welcome to FurniHome</h2>
    <p>This is the home page of the academic PHP project.</p>
    <p>Features include user registration, email verification, profile photo upload with drag & drop, and admin panel for member management.</p>
</main>

<?php
include 'page/_foot.php';
?>