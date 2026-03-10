<?php
require '_base.php';
//-----------------------------------------------------------------------------

$_title = 'Welcome to FurniHome Dashboard';
include '_head.php';
?>

<p>Welcome to the FurniHome management system.</p>

<?php if (isset($_SESSION['user_id'])): ?>
    <div style="background: #f4f4f4; padding: 15px; border-left: 4px solid #333;">
        <p>You are logged in as: <strong><?= encode($_SESSION['role']) ?></strong></p>
        <p>Use the navigation bar above to access your specific modules and tools.</p>
    </div>
<?php else: ?>
    <div style="background: #fff3cd; padding: 15px; border-left: 4px solid #ffeeba;">
        <p>Please login to view your orders, track shipments, or access administrative tools.</p>
    </div>
<?php endif; ?>

<?php
include '_foot.php';
?>