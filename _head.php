<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'FurniHome' ?></title>
    
    <link rel="shortcut icon" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    
    <style>
        body { font-family: sans-serif; margin: 20px; line-height: 1.6; }
        header { margin-bottom: 20px; }
        nav { background: #333; padding: 10px; margin-bottom: 20px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        nav a:hover { text-decoration: underline; }
        #info { color: darkgreen; font-weight: bold; margin-bottom: 15px; }
        .err { color: red; font-size: 0.9em; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <div id="info"><?= temp('info') ?></div>

    <header>
        <h1><a href="/index.php" style="color: black; text-decoration: none;">FurniHome</a></h1>
    </header>

    <nav>
        <a href="/index.php">Dashboard</a>
        
        <?php if (isset($_SESSION['role'])): ?>
            
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="/security/admin.php">Admin Panel</a>
                <a href="/order/order_list.php">Manage Orders</a>
                <a href="/order/batch_operations.php">Batch Tools</a>
            <?php else: ?>
                <a href="/security/member.php">My Profile</a>
                <a href="/order/order_list.php">My Order History</a>
                <?php endif; ?>
            
            <a href="/security/logout.php">Logout</a>
            
        <?php else: ?>
            
            <a href="/security/login.php">Login</a>
            
        <?php endif; ?>
    </nav>

    <main>
        <h2><?= $_title ?? 'Untitled Page' ?></h2>