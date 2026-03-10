<?php
require '../_base.php';
//-----------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    temp('info', 'Please login to view your orders.');
    redirect('login.php'); 
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'Admin') {
    $sql = "SELECT o.*, u.first_name, u.last_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    $orders = db_fetch_all($sql);
    $_title = 'Admin: Order Listing';
} else {
    $sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
    $orders = db_fetch_all($sql, [$user_id]);
    $_title = 'Member: My Order History';
}

//-----------------------------------------------------------------------------
include '../_head.php';
?>

<table>
    <tr>
        <th>Order ID</th>
        <?php if ($role === 'Admin'): ?>
            <th>Customer Name</th>
        <?php endif; ?>
        <th>Date Ordered</th>
        <th>Total Price</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    <?php foreach ($orders as $o): ?>
    <tr>
        <td><?= $o['id'] ?></td>
        <?php if ($role === 'Admin'): ?>
            <td><?= encode($o['first_name'] . ' ' . $o['last_name']) ?></td>
        <?php endif; ?>
        <td><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
        <td>$<?= number_format($o['total_price'], 2) ?></td>
        <td><strong><?= encode($o['status']) ?></strong></td>
        <td>
            <a href="order_details.php?id=<?= $o['id'] ?>">View Details</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php if (empty($orders)): ?>
    <p>No orders found.</p>
<?php endif; ?>

<?php include '../_foot.php'; ?>