<?php
require_once 'order_base.php';
//-----------------------------------------------------------------------------

// 1. Check Login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../security/login.php"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Member';

// 2. Handle Search Query
$search = $_GET['search'] ?? '';
$search_param = '';
$search_query = '';
if (!empty($search)) {
    $search_query = " AND (o.id LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $search_param = "%$search%";
}

// 3. Handle Status Tabs Filtering (Matching tabs in image_10.png)
$active_tab = $_GET['tab'] ?? 'all';
$status_filter = '';

// Mapping tabs to internal enum/payment statuses
switch ($active_tab) {
    case 'topay':
        // Member Tab: Waiting payment, not cancelled
        $status_filter = " AND o.payment_status = 'Pending' AND o.status != 'Cancelled'";
        break;
    case 'toship':
        // Member Tab: Paid, waiting to be packed/shipped
        $status_filter = " AND o.payment_status = 'Paid' AND (o.status = 'Pending' OR o.status = 'Processing')";
        break;
    case 'toreceive':
        // Member Tab: Fully Shipped
        $status_filter = " AND o.status = 'Shipped'";
        break;
    case 'cancelled':
        // Universal Tab: Explicitly Cancelled
        $status_filter = " AND o.status = 'Cancelled'";
        break;
    default:
        // 'all' Tab: No filter
        $active_tab = 'all'; 
        break;
}

// 4. Build Dynamc SQL based on Role, Search, and Tabs
if ($role === 'Admin') {
    $sql = "SELECT o.*, u.first_name, u.last_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE 1=1 $search_query $status_filter
            ORDER BY o.created_at DESC";
    
    $stmt = $_db->prepare($sql);
    
    // Bind search parameters if needed
    if (!empty($search_query)) {
        $stmt->execute([$search_param, $search_param, $search_param]);
    } else {
        $stmt->execute();
    }
    
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
    $page_title = 'Admin: Order Listing';
    
} else {
    // Member only sees their orders, always
    $sql = "SELECT o.* FROM orders o 
            WHERE o.user_id = ? $search_query $status_filter
            ORDER BY o.created_at DESC";
            
    $stmt = $_db->prepare($sql);
    
    // Build parameters array: user_id always first
    $params = [$user_id];
    if (!empty($search_query)) {
        array_push($params, $search_param, $search_param, $search_param);
    }
    
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
    $page_title = 'Member: My Order History';
}

//-----------------------------------------------------------------------------
include '_head_panel.php'; // Using your self-created local header
?>

<div class="card" style="margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
    <div class="card-body" style="padding: 10px;">
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <i class="fas fa-search" style="color: #cbd5e1; font-size: 1.2em;"></i>
            <input type="text" name="search" placeholder="You can search by Order ID or Product name" value="<?= htmlspecialchars($search) ?>" style="flex-grow: 1; padding: 10px; border: 1px solid #e2e8f0; border-radius: 4px; border: none; font-size: 1em;">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
            <button type="submit" class="btn btn-secondary" style="padding: 10px 15px; background: #64748b; color: white; border: none; border-radius: 4px; cursor: pointer; visibility: hidden;">Search</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header" style="padding: 0; background: white; border-bottom: 1px solid #e2e8f0;">
        <div style="display: flex; list-style: none; margin: 0; padding: 0; font-size: 1.1em;">
            
            <?php 
            // Define your tabs array
            $tabs = [
                'all' => 'All',
                'topay' => 'To Pay',
                'toship' => 'To Ship',
                'toreceive' => 'To Receive',
                'cancelled' => 'Cancelled'
            ];
            
            foreach ($tabs as $tab_id => $tab_label): 
                $active = ($active_tab === $tab_id);
                // Style variables for active/inactive tabs
                $color = $active ? '#f97316' : '#64748b'; 
                $border = $active ? '3px solid #f97316' : '3px solid transparent';
            ?>
                <a href="order_list.php?tab=<?= $tab_id ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                   style="text-decoration: none; padding: 15px 30px; color: <?= $color ?>; border-bottom: <?= $border ?>; font-weight: <?= $active ? 'bold' : 'normal' ?>; transition: 0.2s; white-space: nowrap;">
                    <?= htmlspecialchars($tab_label) ?>
                </a>
            <?php endforeach; ?>
            
        </div>
    </div>
    
    <div class="card-body">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <tr style="background-color: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <th style="padding: 15px;">Order ID</th>
                <?php if ($role === 'Admin'): ?>
                    <th style="padding: 15px;">Customer Name</th>
                <?php endif; ?>
                <th style="padding: 15px;">Date Ordered</th>
                <th style="padding: 15px;">Total Price</th>
                <th style="padding: 15px;">Status</th>
                <th style="padding: 15px; text-align: right;">Action</th>
            </tr>
            <?php foreach ($orders as $o): ?>
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 15px;"><?= htmlspecialchars($o->id) ?></td>
                <?php if ($role === 'Admin'): ?>
                    <td style="padding: 15px;"><?= htmlspecialchars($o->first_name . ' ' . $o->last_name) ?></td>
                <?php endif; ?>
                <td style="padding: 15px;"><?= date('d M Y, h:i A', strtotime($o->created_at)) ?></td>
                <td style="padding: 15px;">$<?= number_format($o->total_price, 2) ?></td>
                <td style="padding: 15px;"><strong><?= htmlspecialchars($o->status) ?></strong></td>
                <td style="padding: 15px; text-align: right;">
                    <a href="order_details.php?id=<?= $o->id ?>" class="btn btn-primary" style="padding: 5px 10px;">View Details</a>
                    
                    <?php 
                    // Tracking is only available if it has shipped, AND is fully paid.
                    $show_track = ($o->status === 'Shipped') && ($o->payment_status === 'Paid');
                    
                    if ($show_track): ?>
                        <a href="tracking.php?id=<?= $o->id ?>" class="btn btn-success" style="padding: 5px 10px; margin-left: 5px; background: #16a34a; color: white;">Track Parcel</a>
                    <?php endif; ?>
                    
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if (empty($orders)): ?>
            <p style="padding: 40px; text-align: center; color: #64748b; font-size: 1.1em;">No orders found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '_foot_panel.php'; // Using your self-created local footer ?>