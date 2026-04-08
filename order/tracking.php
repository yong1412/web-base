<?php
require '../_base.php';
//-----------------------------------------------------------------------------
// Security: Only Members should track their own parcels
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Member') {
    temp('info', 'Please login as a member to track your orders.');
    redirect('/security/login.php');
}

$user_id = $_SESSION['user_id'];
$order_id = get('id');

// If no Order ID is in the URL, show them a list of their orders to pick from
if (!$order_id) {
    $_title = 'Select Order to Track';
    include '../_head.php';
    
    $orders = db_fetch_all("SELECT id, created_at, status FROM orders WHERE user_id = ? ORDER BY created_at DESC", [$user_id]);
    
    echo '<div style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">';
    echo '<h2 style="margin-top: 0; color: #334155;"><i class="fa-solid fa-box-open"></i> Track Your Parcel</h2>';
    echo '<p>Select an order from your history to view its current shipping status:</p>';
    
    if (empty($orders)) {
        echo '<p style="color: #64748b;">You have no orders to track yet.</p>';
    } else {
        echo '<div style="display: flex; flex-direction: column; gap: 10px;">';
        foreach ($orders as $o) {
            $date = date('d M Y', strtotime($o->created_at));
            echo "<a href='tracking.php?id={$o->id}' style='display: flex; justify-content: space-between; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; text-decoration: none; color: #334155; font-weight: bold; transition: background 0.2s;'>
                    <span>Order #{$o->id} &middot; <span style='color: #64748b; font-weight: normal;'>$date</span></span>
                    <span style='color: #0ea5e9;'>Track &rarr;</span>
                  </a>";
        }
        echo '</div>';
    }
    echo '</div>';
    
    include '../_foot.php';
    exit();
}

// Fetch the specific order
$order = db_fetch_single("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$order_id, $user_id]);

if (!$order) {
    temp('info', 'Order not found or access denied.');
    redirect('tracking.php');
}

// Determine the progress step based on the status
$status = $order->status;
$step = 1; // Default to Pending
if ($status === 'Processing') $step = 2;
if ($status === 'Shipped') $step = 3;

//-----------------------------------------------------------------------------
$_title = "Tracking Order #$order_id";
include '../_head.php';
?>

<style>
    /* Styling for the visual progress bar */
    .track-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; }
    .progress-bar { display: flex; justify-content: space-between; position: relative; margin: 40px 0; }
    .progress-bar::before { content: ''; position: absolute; top: 50%; left: 0; width: 100%; height: 4px; background: #e2e8f0; z-index: 1; transform: translateY(-50%); }
    
    /* The dynamic fill line */
    .progress-fill { position: absolute; top: 50%; left: 0; height: 4px; background: #10b981; z-index: 2; transform: translateY(-50%); transition: width 0.4s ease; }
    
    .step { position: relative; z-index: 3; background: white; padding: 0 10px; display: flex; flex-direction: column; align-items: center; gap: 10px; width: 100px; }
    .step-icon { width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; border: 4px solid white; transition: all 0.3s; }
    .step-text { font-weight: bold; color: #64748b; font-size: 14px; }
    
    /* Active step styling */
    .step.active .step-icon { background: #10b981; color: white; box-shadow: 0 0 0 4px #d1fae5; }
    .step.active .step-text { color: #10b981; }
    
    /* Cancelled styling */
    .cancelled-box { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 20px; border-radius: 8px; margin: 20px 0; font-weight: bold; }
</style>

<div class="track-container">
    <h2 style="margin-top: 0; color: #334155;">Order #<?= $order->id ?></h2>
    <p style="color: #64748b; margin-bottom: 30px;">Placed on <?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>

    <?php if ($status === 'Cancelled'): ?>
        
        <div class="cancelled-box">
            <i class="fa-solid fa-circle-xmark" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
            This order has been cancelled. Tracking is unavailable.
        </div>
        
    <?php else: ?>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($step - 1) * 50 ?>%;"></div>
            
            <div class="step <?= $step >= 1 ? 'active' : '' ?>">
                <div class="step-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                <div class="step-text">Pending</div>
            </div>
            
            <div class="step <?= $step >= 2 ? 'active' : '' ?>">
                <div class="step-icon"><i class="fa-solid fa-box-open"></i></div>
                <div class="step-text">Processing</div>
            </div>
            
            <div class="step <?= $step >= 3 ? 'active' : '' ?>">
                <div class="step-icon"><i class="fa-solid fa-truck-fast"></i></div>
                <div class="step-text">Shipped</div>
            </div>
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: left; border: 1px solid #e2e8f0; margin-top: 30px;">
            <h4 style="margin-top: 0; color: #334155;">Shipping Details</h4>
            <p style="margin: 0;"><strong>Destination:</strong><br> <?= nl2br(encode($order->shipping_address ?? 'Address pending confirmation.')) ?></p>
        </div>

    <?php endif; ?>

    <br><br>
    <a href="tracking.php" style="color: #64748b; text-decoration: none; margin-right: 20px;">&laquo; Track Another Order</a>
    <a href="order_list.php" style="color: #64748b; text-decoration: none;">View Order History</a>
</div>

<?php include '../_foot.php'; ?>