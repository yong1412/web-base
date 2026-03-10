<?php
require '../_base.php';

// Optional: Kick out guests if you only want logged-in users to see this menu
if (!isset($_SESSION['user_id'])) {
    temp('info', 'Please login to access the order module.');
    redirect('/security/login.php');
}

$_title = 'Order Module Dashboard';
include '../_head.php';
?>

<div style="max-width: 600px; margin: 0 auto;">
    <div style="background: #f8fafc; padding: 30px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
        <h2 style="margin-top: 0; color: #334155;">📦 Order Management</h2>
        <p>Welcome to the order module. Choose an action below:</p>
        
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;">

        <div style="margin-bottom: 30px;">
            <h3>1. View All Orders</h3>
            <p style="color: #64748b; font-size: 14px;">View the complete list of orders (Admin) or your personal order history (Member).</p>
            <a href="order_list.php" style="display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">
                Go to Order List &rarr;
            </a>
        </div>

        <div>
            <h3>2. Quick Lookup (Order Details)</h3>
            <p style="color: #64748b; font-size: 14px;">Enter an Order ID to jump directly to its details, status updates, and cancellation page.</p>
            
            <form action="order_details.php" method="GET" style="display: flex; gap: 10px;">
                <input type="number" name="id" placeholder="Enter Order ID (e.g., 1)" required style="padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 200px;">
                <button type="submit" style="padding: 10px 20px; background: #10b981; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">
                    View Details &rarr;
                </button>
            </form>
        </div>
    </div>
    
    <div style="text-align: center;">
        <a href="/index.php" style="color: #64748b; text-decoration: none;">&laquo; Back to Main Site Dashboard</a>
    </div>
</div>

<?php include '../_foot.php'; ?>