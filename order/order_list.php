<?php
require_once 'order_base.php';
//-----------------------------------------------------------------------------

// 1. Standard check for login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../security/login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Member';

if ($role === 'Admin') {
    $sql = "SELECT o.*, u.first_name, u.last_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    $page_title = 'Admin: Order Listing';
} else {
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    $page_title = 'Member: My Order History';
}

//-----------------------------------------------------------------------------
include '_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2><?= htmlspecialchars($page_title) ?></h2>
    </div>
    <div class="card-body">
        
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 10px;">Order ID</th>
                <?php if ($role === 'Admin'): ?>
                    <th style="padding: 10px;">Customer Name</th>
                <?php endif; ?>
                <th style="padding: 10px;">Date Ordered</th>
                <th style="padding: 10px;">Total Price</th>
                <th style="padding: 10px;">Status</th>
                <th style="padding: 10px;">Action</th>
            </tr>
            
            <?php foreach ($orders as $o): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px;"><?= htmlspecialchars($o->id) ?></td>
                <?php if ($role === 'Admin'): ?>
                    <td style="padding: 10px;"><?= htmlspecialchars($o->first_name . ' ' . $o->last_name) ?></td>
                <?php endif; ?>
                <td style="padding: 10px;"><?= date('d M Y, h:i A', strtotime($o->created_at)) ?></td>
                <td style="padding: 10px;">$<?= number_format($o->total_price, 2) ?></td>
                <td style="padding: 10px;"><strong><?= htmlspecialchars($o->status) ?></strong></td>
                <td style="padding: 10px;">
                    <a href="order_details.php?id=<?= $o->id ?>" style="padding: 5px 10px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 4px;">View Details</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if (empty($orders)): ?>
            <p style="padding: 20px; text-align: center; color: #64748b;">No orders found.</p>
        <?php endif; ?>
        
    </div>
</div>

<?php include '_foot_panel.php'; ?>