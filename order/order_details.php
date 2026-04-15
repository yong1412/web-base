<?php
require_once 'order_base.php';

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../security/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Member';

// 2. Safely grab the ID from the URL (Replaces get('id'))
$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: order_list.php");
    exit;
}

// ============================================================================
// Handle Form Submissions (Replaces is_post() and post())
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Admin updates status
    if ($action === 'update_status' && $role === 'Admin') {
        $status = $_POST['status'] ?? '';
        $payment_status = $_POST['payment_status'] ?? '';
        
        $stmt = $pdo->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $payment_status, $order_id]);
        
        header("Location: order_details.php?id=$order_id");
        exit;
    }

    // Member cancels order
    if ($action === 'cancel_order' && $role === 'Member') {
        // Extra security: Only allow cancelling if it's currently Pending
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
        $stmt->execute([$order_id, $user_id]);
        
        header("Location: order_details.php?id=$order_id");
        exit;
    }

    // Real-Time Chat Message
    if ($action === 'send_message') {
        $message_text = trim($_POST['message_text'] ?? '');
        if ($message_text !== '') {
            $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, message_text) VALUES (?, ?, ?)");
            $stmt->execute([$order_id, $user_id, $message_text]);
        }
        header("Location: order_details.php?id=$order_id");
        exit;
    }
}

// ============================================================================
// Fetch Data using Standard PDO (Replaces db_fetch_single and db_fetch_all)
// ============================================================================

// Fetch the main order
$stmt = $pdo->prepare("SELECT o.*, u.first_name, u.last_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_OBJ);

// Security Check: If order doesn't exist, or if a member is snooping on someone else's order
if (!$order || ($role !== 'Admin' && $order->user_id != $user_id)) {
    header("Location: order_list.php");
    exit;
}

// Fetch the items inside the order
$stmt = $pdo->prepare("SELECT * FROM order_details WHERE order_id = ?");
$stmt->execute([$order_id]);
$details = $stmt->fetchAll(PDO::FETCH_OBJ);

// Fetch the chat messages
$stmt = $pdo->prepare("SELECT m.*, u.first_name, u.role FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.order_id = ? ORDER BY m.created_at ASC");
$stmt->execute([$order_id]);
$messages = $stmt->fetchAll(PDO::FETCH_OBJ);

$page_title = 'Order Details #' . htmlspecialchars($order->id);
include '_head_panel.php';
?>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
        <h2 style="margin: 0;">Order #<?= htmlspecialchars($order->id) ?> Details</h2>
        <a href="order_list.php" style="color: #64748b; text-decoration: none;">&larr; Back to Orders</a>
    </div>
    <div class="card-body">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div style="background: #f8fafc; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">Customer Information</h4>
                <p><strong>Name:</strong> <?= htmlspecialchars($order->first_name . ' ' . $order->last_name) ?></p>
                <p><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($order->created_at)) ?></p>
                <p><strong>Shipping To:</strong> <?= htmlspecialchars($order->shipping_address ?? 'No address provided') ?></p>
            </div>
            
            <div style="background: #f8fafc; padding: 15px; border-radius: 8px;">
                <h4 style="margin-top: 0;">Order Status</h4>
                <p><strong>Status:</strong> <span style="color: #0ea5e9; font-weight: bold;"><?= htmlspecialchars($order->status) ?></span></p>
                <p><strong>Payment:</strong> <?= htmlspecialchars($order->payment_status ?? 'Pending') ?></p>
                <p><strong>Total:</strong> <span style="font-size: 1.2em; font-weight: bold; color: #16a34a;">$<?= number_format($order->total_price, 2) ?></span></p>
                
                <?php if ($role === 'Member' && $order->status === 'Pending'): ?>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="cancel_order">
                        <button type="submit" style="background: #ef4444; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">Cancel Order</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($role === 'Admin'): ?>
            <div style="background: #fffbeb; padding: 15px; border: 1px solid #fde68a; border-radius: 8px; margin-bottom: 20px;">
                <h4 style="margin-top: 0; color: #d97706;">Admin Controls: Update Status</h4>
                <form method="POST" style="display: flex; gap: 15px; align-items: flex-end;">
                    <input type="hidden" name="action" value="update_status">
                    <div>
                        <label style="display: block; font-size: 14px; margin-bottom: 5px;">Order Status</label>
                        <select name="status" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                            <option value="Pending" <?= $order->status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Processing" <?= $order->status == 'Processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="Shipped" <?= $order->status == 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="Cancelled" <?= $order->status == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 14px; margin-bottom: 5px;">Payment Status</label>
                        <select name="payment_status" style="padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                            <option value="Pending" <?= ($order->payment_status ?? 'Pending') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Paid" <?= ($order->payment_status ?? '') == 'Paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="Failed" <?= ($order->payment_status ?? '') == 'Failed' ? 'selected' : '' ?>>Failed</option>
                            <option value="Refunded" <?= ($order->payment_status ?? '') == 'Refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>
                    <button type="submit" style="background: #d97706; color: white; border: none; padding: 9px 15px; border-radius: 4px; cursor: pointer;">Save Updates</button>
                </form>
            </div>
        <?php endif; ?>

        <h3 style="margin-bottom: 10px;">Items Ordered</h3>
        <table style="width: 100%; border-collapse: collapse; text-align: left; margin-bottom: 30px;">
            <tr style="background-color: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                <th style="padding: 10px;">Product</th>
                <th style="padding: 10px;">Quantity</th>
                <th style="padding: 10px;">Price</th>
                <th style="padding: 10px;">Subtotal</th>
            </tr>
            <?php foreach ($details as $d): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 10px;"><?= htmlspecialchars($d->product_name) ?></td>
                <td style="padding: 10px;"><?= htmlspecialchars($d->quantity) ?></td>
                <td style="padding: 10px;">$<?= number_format($d->price, 2) ?></td>
                <td style="padding: 10px;">$<?= number_format($d->quantity * $d->price, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Order Support Chat</h3>
    </div>
    <div class="card-body">
        <div style="height: 300px; overflow-y: auto; background: #f8fafc; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 15px;">
            <?php if (empty($messages)): ?>
                <p style="text-align: center; color: #94a3b8; margin-top: 100px;">No messages yet. Send a message to start chatting.</p>
            <?php else: ?>
                <?php foreach ($messages as $m): 
                    $is_me = ($m->sender_id == $user_id);
                    $align = $is_me ? 'text-align: right;' : 'text-align: left;';
                    $bg = $is_me ? 'background: #0ea5e9; color: white;' : 'background: #e2e8f0; color: #333;';
                ?>
                    <div style="<?= $align ?> margin-bottom: 15px;">
                        <span style="font-size: 12px; color: #64748b; margin-bottom: 4px; display: block;">
                            <?= htmlspecialchars($m->first_name) ?> <?= $m->role === 'Admin' ? '<span style="color:red;">(Admin)</span>' : '' ?> - <?= date('h:i A', strtotime($m->created_at)) ?>
                        </span>
                        <div style="display: inline-block; padding: 10px 15px; border-radius: 15px; <?= $bg ?> max-width: 70%; text-align: left;">
                            <?= nl2br(htmlspecialchars($m->message_text)) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form method="POST" style="display: flex; gap: 10px;">
            <input type="hidden" name="action" value="send_message">
            <input type="text" name="message_text" placeholder="Type your message here..." required style="flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <button type="submit" style="background: #16a34a; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Send</button>
        </form>
    </div>
</div>

<?php include '_foot_panel.php'; ?>