<?php
require '../_base.php';
//-----------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) redirect('login.php');

$order_id = get('id');
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$order = db_fetch_single("SELECT * FROM orders WHERE id = ?", [$order_id]);

if (!$order || ($role === 'Member' && $order['user_id'] != $user_id)) {
    temp('info', 'Order not found or access denied.');
    redirect('order_list.php');
}

$_order_statuses = [
    'Pending' => 'Pending',
    'Processing' => 'Processing',
    'Shipped' => 'Shipped',
    'Cancelled' => 'Cancelled'
];

if (is_post()) {
    $action = post('action');
    
    // ACTION 1: Update Status (ADMIN ONLY)
    if ($action === 'update_status' && $role === 'Admin') {
        $new_status = post('status');
        if (array_key_exists($new_status, $_order_statuses)) {
            db_execute("UPDATE orders SET status = ? WHERE id = ?", [$new_status, $order_id]);
            temp('info', 'Order status successfully updated to ' . $new_status);
        } else {
            temp('info', 'Invalid status selected.');
        }
        redirect(); 
    }
    
    // ACTION 2: Cancel Order (BOTH ADMIN AND MEMBER)
    if ($action === 'cancel_order') {
        if ($order['status'] === 'Pending' || $order['status'] === 'Processing') {
            db_execute("UPDATE orders SET status = 'Cancelled' WHERE id = ?", [$order_id]);
            temp('info', 'Order has been successfully cancelled.');
        } else {
            temp('info', 'Cannot cancel an order that is already Shipped or Cancelled.');
        }
        redirect();
    }
}

$items = db_fetch_all("SELECT * FROM order_details WHERE order_id = ?", [$order_id]);

//-----------------------------------------------------------------------------
$_title = "Order Details #$order_id";
include '../_head.php';
?>

<div style="margin-bottom: 20px;">
    <p><strong>Current Status:</strong> <?= encode($order['status']) ?></p>
    <p><strong>Total Price:</strong> $<?= number_format($order['total_price'], 2) ?></p>
</div>

<h3>Furniture Items</h3>
<table>
    <tr>
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Subtotal</th>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td><?= encode($item['product_name']) ?></td>
        <td><?= $item['quantity'] ?></td>
        <td>$<?= number_format($item['price'], 2) ?></td>
        <td>$<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<hr style="margin: 30px 0;">
<h3>Order Actions</h3>

<?php if ($role === 'Admin'): ?>
    <form method="POST" style="margin-bottom: 20px; padding: 15px; background: #f4f4f4; border: 1px solid #ccc;">
        <h4>Admin Control: Update Status</h4>
        <input type="hidden" name="action" value="update_status">
        <label for="status">Change Status:</label>
        <?php 
            $GLOBALS['status'] = $order['status']; 
            html_select('status', $_order_statuses, null); 
        ?>
        <button type="submit">Save Status</button>
    </form>
<?php endif; ?>

<?php if ($order['status'] === 'Pending' || $order['status'] === 'Processing'): ?>
    <form method="POST" onsubmit="return confirm('Are you sure you want to completely cancel this order?');">
        <input type="hidden" name="action" value="cancel_order">
        <button type="submit" style="background: #dc3545; color: white; padding: 10px 15px; border: none; cursor: pointer;">
            Cancel Order
        </button>
    </form>
<?php else: ?>
    <p><em>This order cannot be cancelled because it is currently <?= encode($order['status']) ?>.</em></p>
<?php endif; ?>

<br><br>
<a href="order_list.php">&laquo; Back to Order List</a>

<?php include '../_foot.php'; ?>