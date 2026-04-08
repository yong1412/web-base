<?php
require '../_base.php';
//-----------------------------------------------------------------------------
if (!isset($_SESSION['user_id'])) redirect('/security/login.php');

$order_id = get('id');
$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

$order = db_fetch_single("SELECT * FROM orders WHERE id = ?", [$order_id]);

if (!$order || ($role === 'Member' && $order->user_id != $user_id)) {
    temp('info', 'Order not found or access denied.');
    redirect('order_list.php');
}

$_order_statuses = ['Pending' => 'Pending', 'Processing' => 'Processing', 'Shipped' => 'Shipped', 'Cancelled' => 'Cancelled'];
$_payment_statuses = ['Pending' => 'Pending', 'Paid' => 'Paid', 'Failed' => 'Failed', 'Refunded' => 'Refunded'];

if (is_post()) {
    $action = post('action');
    
    // Admin Updates Order Status
    if ($action === 'update_status' && $role === 'Admin') {
        $new_status = post('status');
        if (array_key_exists($new_status, $_order_statuses)) {
            db_execute("UPDATE orders SET status = ? WHERE id = ?", [$new_status, $order_id]);
            temp('info', 'Order status updated to ' . $new_status);
        }
        redirect(); 
    }
    
    // Admin Updates Payment Status
    if ($action === 'update_payment' && $role === 'Admin') {
        $new_pay_status = post('payment_status');
        if (array_key_exists($new_pay_status, $_payment_statuses)) {
            db_execute("UPDATE orders SET payment_status = ? WHERE id = ?", [$new_pay_status, $order_id]);
            temp('info', 'Payment status updated to ' . $new_pay_status);
        }
        redirect(); 
    }

    // Member Updates Shipping Address (Only if Pending)
    if ($action === 'update_address' && $role === 'Member') {
        if ($order->status === 'Pending') {
            $new_address = post('shipping_address');
            db_execute("UPDATE orders SET shipping_address = ? WHERE id = ?", [$new_address, $order_id]);
            temp('info', 'Shipping address successfully updated.');
        } else {
            temp('info', 'Cannot update address after processing has started.');
        }
        redirect();
    }

    // Member Mock Payment
    if ($action === 'mock_pay' && $role === 'Member') {
        db_execute("UPDATE orders SET payment_status = 'Paid' WHERE id = ?", [$order_id]);
        temp('info', 'Payment successful! Thank you for your purchase.');
        redirect();
    }
    
    // Order Cancellation (Both Admin and Member)
    if ($action === 'cancel_order') {
        if ($order->status === 'Pending' || $order->status === 'Processing') {
            db_execute("UPDATE orders SET status = 'Cancelled' WHERE id = ?", [$order_id]);
            temp('info', 'Order has been successfully cancelled.');
        }
        redirect();
    }
}

$items = db_fetch_all("SELECT * FROM order_details WHERE order_id = ?", [$order_id]);

//-----------------------------------------------------------------------------
$_title = "Order Details #$order_id";
include '../_head.php';
?>

<div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
    
    <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; flex: 1; min-width: 250px;">
        <h3 style="margin-top: 0; color: #334155;">Order Summary</h3>
        <p><strong>Order Status:</strong> <?= encode($order->status) ?></p>
        <p><strong>Total Price:</strong> $<?= number_format($order->total_price, 2) ?></p>
        <p><strong>Date Ordered:</strong> <?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>
    </div>

    <div style="background: #f0fdf4; padding: 20px; border-radius: 8px; border: 1px solid #bbf7d0; flex: 1; min-width: 250px;">
        <h3 style="margin-top: 0; color: #16a34a;">Payment Information</h3>
        <p><strong>Method:</strong> <?= encode($order->payment_method ?? 'N/A') ?></p>
        <p><strong>Status:</strong> <span style="font-weight: bold; color: <?= ($order->payment_status ?? 'Pending') === 'Paid' ? 'green' : 'red' ?>;"><?= encode($order->payment_status ?? 'Pending') ?></span></p>
        
        <?php if ($role === 'Member' && ($order->payment_status ?? 'Pending') === 'Pending'): ?>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="action" value="mock_pay">
                <button type="submit" style="background: #16a34a; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">Pay Now</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<div style="background: #fffbeb; padding: 20px; border-radius: 8px; border: 1px solid #fde68a; margin-bottom: 20px;">
    <h3 style="margin-top: 0; color: #d97706;">Shipping Address</h3>
    
    <?php if ($role === 'Member' && $order->status === 'Pending'): ?>
        <form method="POST">
            <input type="hidden" name="action" value="update_address">
            <textarea name="shipping_address" rows="3" required style="width: 100%; padding: 10px; border: 1px solid #fcd34d; border-radius: 4px; margin-bottom: 10px; box-sizing: border-box;"><?= encode($order->shipping_address ?? '') ?></textarea>
            <button type="submit" style="background: #d97706; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">Save Address</button>
        </form>
    <?php else: ?>
        <p><?= nl2br(encode($order->shipping_address ?? 'No address provided yet.')) ?></p>
    <?php endif; ?>
</div>

<h3>Furniture Items</h3>
<table>
    <tr style="background: #f1f5f9;">
        <th>Product Name</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Subtotal</th>
    </tr>
    <?php foreach ($items as $item): ?>
    <tr>
        <td><?= encode($item->product_name) ?></td>
        <td><?= $item->quantity ?></td>
        <td>$<?= number_format($item->price, 2) ?></td>
        <td>$<?= number_format($item->quantity * $item->price, 2) ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<hr style="margin: 30px 0;">
<h3>Management Actions</h3>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">
    <?php if ($role === 'Admin'): ?>
        <form method="POST" style="padding: 15px; background: #f4f4f4; border: 1px solid #ccc; flex: 1; min-width: 250px;">
            <h4>Update Order Status</h4>
            <input type="hidden" name="action" value="update_status">
            <?php 
                $GLOBALS['status'] = $order->status; 
                html_select('status', $_order_statuses, null, 'style="padding: 5px; margin-right: 10px;"'); 
            ?>
            <button type="submit" style="padding: 6px 12px; cursor: pointer;">Save</button>
        </form>
        
        <form method="POST" style="padding: 15px; background: #f4f4f4; border: 1px solid #ccc; flex: 1; min-width: 250px;">
            <h4>Update Payment Status</h4>
            <input type="hidden" name="action" value="update_payment">
            <?php 
                $GLOBALS['payment_status'] = $order->payment_status ?? 'Pending'; 
                html_select('payment_status', $_payment_statuses, null, 'style="padding: 5px; margin-right: 10px;"'); 
            ?>
            <button type="submit" style="padding: 6px 12px; cursor: pointer;">Save</button>
        </form>
    <?php endif; ?>

    <?php if ($order->status === 'Pending' || $order->status === 'Processing'): ?>
        <form method="POST" onsubmit="return confirm('Are you sure you want to completely cancel this order?');" style="flex: 1; min-width: 250px; display: flex; align-items: center;">
            <input type="hidden" name="action" value="cancel_order">
            <button type="submit" style="background: #dc3545; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 4px;">
                Cancel Order
            </button>
        </form>
    <?php else: ?>
        <p style="flex: 1; min-width: 250px;"><em>This order cannot be cancelled because it is currently <?= encode($order->status) ?>.</em></p>
    <?php endif; ?>
</div>

<br><br>
<a href="order_list.php">&laquo; Back to Order List</a>

<?php include '../_foot.php'; ?>