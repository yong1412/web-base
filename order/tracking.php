<?php
require_once 'order_base.php';

// 1. Standard login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../security/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Using native PHP instead of the get() function
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: order_list.php");
    exit;
}

// 3. Fetch the order safely using standard PDO
$stmt = $_db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_OBJ);

// If order doesn't exist or doesn't belong to the user, boot them back to the list
if (!$order) {
    header("Location: order_list.php");
    exit;
}

// Determine tracking step
$status = $order->status;
$step = 1; 
if ($status == 'Processing') $step = 2;
if ($status == 'Shipped') $step = 3;
if ($status == 'Cancelled') $step = 0; 

$page_title = 'Track Parcel #' . htmlspecialchars($order->id);
include '_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Parcel Tracking: Order #<?= htmlspecialchars($order->id) ?></h2>
    </div>
    <div class="card-body" style="text-align: center;">
        
        <?php if ($step === 0): ?>
            <div style="padding: 20px; background: #fee2e2; color: #dc2626; border-radius: 8px;">
                <h3>Order Cancelled</h3>
                <p>This order has been cancelled and will not be shipped.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; justify-content: center; align-items: center; margin: 40px 0;">
                
                <div style="text-align: center; width: 100px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto; line-height: 40px; background: <?= $step >= 1 ? '#16a34a' : '#e2e8f0' ?>; color: <?= $step >= 1 ? 'white' : '#64748b' ?>;">1</div>
                    <p style="margin-top: 10px; font-weight: bold;">Pending</p>
                </div>
                
                <div style="width: 100px; height: 4px; background: <?= $step >= 2 ? '#16a34a' : '#e2e8f0' ?>; margin-top: -30px;"></div>
                
                <div style="text-align: center; width: 100px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto; line-height: 40px; background: <?= $step >= 2 ? '#16a34a' : '#e2e8f0' ?>; color: <?= $step >= 2 ? 'white' : '#64748b' ?>;">2</div>
                    <p style="margin-top: 10px; font-weight: bold;">Processing</p>
                </div>

                <div style="width: 100px; height: 4px; background: <?= $step >= 3 ? '#16a34a' : '#e2e8f0' ?>; margin-top: -30px;"></div>
                
                <div style="text-align: center; width: 100px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; margin: 0 auto; line-height: 40px; background: <?= $step >= 3 ? '#16a34a' : '#e2e8f0' ?>; color: <?= $step >= 3 ? 'white' : '#64748b' ?>;">3</div>
                    <p style="margin-top: 10px; font-weight: bold;">Shipped</p>
                </div>
            </div>
        <?php endif; ?>
        
        <br>
        <a href="order_list.php" class="btn btn-secondary" style="padding: 10px 20px; background: #64748b; color: white; text-decoration: none; border-radius: 4px;">Back to Order History</a>
    </div>
</div>

<?php include '_foot_panel.php'; ?>