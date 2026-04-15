<?php
// Admin member listing

require_once '../../lib/base.php';
require_admin(); 

$msg = ''; // Variable to hold success messages for batch actions

// ============================================================================
// Handle Batch Operations (Must be at the top before outputting HTML)
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_ids'])) {
    $action = $_POST['batch_action'] ?? '';
    $user_ids = $_POST['user_ids']; // Array of selected IDs
    
    // Create placeholders like (?, ?, ?) for the SQL IN clause
    $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
    
    try {
        if ($action === 'batch_block') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'blocked' WHERE id IN ($placeholders) AND role != 'Admin'");
            $stmt->execute($user_ids);
            $msg = count($user_ids) . " selected members have been blocked (Admins ignored).";
        } 
        elseif ($action === 'batch_unblock') {
            $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id IN ($placeholders)");
            $stmt->execute($user_ids);
            $msg = count($user_ids) . " selected members have been unblocked.";
        } 
        elseif ($action === 'batch_delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id IN ($placeholders) AND role != 'Admin'");
            $stmt->execute($user_ids);
            $msg = count($user_ids) . " selected members have been permanently deleted (Admins ignored).";
        }
    } catch (PDOException $e) {
        $msg = "Error executing batch operation: " . $e->getMessage();
    }
}
// ============================================================================

$page_title = 'Manage Members';
include '../_head_panel.php';

// Retrieve search term and pagination
$search = strtolower(trim($_GET['search'] ?? ''));
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit; 

try { 
    $query = "SELECT id, first_name, last_name, email, role, status, email_verified, photo FROM users WHERE 1";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (
            CONCAT(first_name, ' ', last_name) LIKE :search 
            OR email LIKE :search
            OR id = :exact_id
            OR role LIKE :search
            OR status LIKE :search
        )";
    }

    $query .= " LIMIT :offset, :limit"; 

    $stmt = $pdo->prepare($query); 
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
        $stmt->bindValue(':exact_id', is_numeric($search) ? (int)$search : 0, PDO::PARAM_INT);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Count total for pagination
    $count_query = "SELECT COUNT(*) FROM users WHERE 1";
    if (!empty($search)) {
        $count_query .= " AND (
            CONCAT(first_name, ' ', last_name) LIKE :search 
            OR email LIKE :search
            OR id = :exact_id
            OR role LIKE :search
            OR status LIKE :search
        )";
    }
    $stmt = $pdo->prepare($count_query);
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
        $stmt->bindValue(':exact_id', is_numeric($search) ? (int)$search : 0, PDO::PARAM_INT);
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_pages = ceil($total / $limit); 
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage()); 
}
?>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Create New User</a>
    
    <form class="member-list-search-form" method="get" style="width: 50%; display: flex; align-items: stretch; margin: 0;">
        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search" style="flex: 1; border-top-right-radius: 0; border-bottom-right-radius: 0;">
        <button class="btn btn-primary" type="submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"><i class="fas fa-search"></i></button>
    </form>
</div>

<?php if ($msg): ?>
    <div style="background: #dcfce7; color: #16a34a; padding: 15px; border-radius: 4px; border-left: 4px solid #16a34a; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<form method="POST" id="batchForm">
    <table>
        <thead>
            <tr style="background-color: #f8fafc;">
                <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAll"></th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Verified</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td style="text-align: center;">
                    <?php if ($user['role'] !== 'Admin'): ?>
                        <input type="checkbox" name="user_ids[]" class="user-checkbox" value="<?php echo $user['id']; ?>">
                    <?php endif; ?>
                </td>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><span class="badge badge-<?php echo $user['role'] === 'Admin' ? 'danger' : 'primary'; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                <td><span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($user['status']); ?></span></td>
                <td><?php echo $user['email_verified'] ? '<span style="color: #28a745; font-size: 18px;">✓</span>' : '<span style="color: #dc3545; font-size: 18px;">✗</span>'; ?></td>
                <td><?php if ($user['photo']): ?><img src="../../uploads/profiles/<?php echo $user['photo']; ?>" width="50"><?php endif; ?></td>
                <td class="actions">
                    <a href="detail.php?id=<?php echo $user['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                    <a href="../../order/order_list.php?search=<?php echo urlencode($user['first_name']); ?>" class="btn btn-primary btn-sm" style="background-color: #f97316; border-color: #f97316;" title="View Orders"><i class="fas fa-box-open"></i></a>
                    
                    <?php if ($user['status'] === 'active'): ?>
                        <button type="button" class="btn btn-danger btn-sm block-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-ban"></i> Block</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-success btn-sm unblock-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-check"></i> Unblock</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 15px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; display: flex; gap: 10px; align-items: center;">
        <strong>With Selected:</strong>
        <button type="submit" name="batch_action" value="batch_block" class="btn btn-warning btn-sm batch-submit"><i class="fas fa-ban"></i> Block</button>
        <button type="submit" name="batch_action" value="batch_unblock" class="btn btn-success btn-sm batch-submit"><i class="fas fa-check"></i> Unblock</button>
        <button type="submit" name="batch_action" value="batch_delete" class="btn btn-danger btn-sm batch-submit"><i class="fas fa-trash"></i> Delete</button>
    </div>
</form>

<?php if ($total_pages > 1): ?>
<div style="margin-top: 20px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-light'; ?> btn-sm" style="margin-right: 5px;"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script>
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

$(document).ready(function() { 
    // NEW: "Select All" Checkbox Logic
    $('#selectAll').on('change', function() {
        $('.user-checkbox').prop('checked', this.checked);
    });

    $('.user-checkbox').on('change', function() {
        if ($('.user-checkbox:checked').length == $('.user-checkbox').length) {
            $('#selectAll').prop('checked', true);
        } else {
            $('#selectAll').prop('checked', false);
        }
    });

    // NEW: Batch Submission Confirmation
    $('.batch-submit').on('click', function(e) {
        if ($('.user-checkbox:checked').length === 0) {
            e.preventDefault();
            Swal.fire('No Users Selected', 'Please check at least one box to perform a batch action.', 'info');
            return;
        }

        const action = $(this).val();
        let actionText = '';
        if (action === 'batch_block') actionText = 'block';
        if (action === 'batch_unblock') actionText = 'unblock';
        if (action === 'batch_delete') actionText = 'permanently delete';

        e.preventDefault();
        const form = $(this).closest('form');
        const button = $(this);

        Swal.fire({
            title: 'Confirm Batch Action',
            text: `Are you sure you want to ${actionText} all selected members?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: action === 'batch_delete' ? '#dc3545' : '#0ea5e9',
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionText} them`
        }).then((result) => {
            if (result.isConfirmed) {
                // Create a hidden input to pass the button's value, then submit
                $('<input>').attr({type: 'hidden', name: 'batch_action', value: action}).appendTo(form);
                form.submit();
            }
        });
    });

    // Existing Single Button Logics below...
    $('.block-btn').on('click', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        Swal.fire({
            title: 'Block User?',
            text: `Are you sure you want to block "${userName}"? They will not be able to access the system.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, block user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `block.php?id=${userId}`;
            }
        });
    });

    $('.unblock-btn').on('click', function() { 
        const userId = $(this).data('id');
        const userName = $(this).data('name');
        Swal.fire({
            title: 'Unblock User?',
            text: `Are you sure you want to unblock "${userName}"? They will regain access to the system.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, unblock user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `unblock.php?id=${userId}`;
            }
        });
    });
});
</script>

<?php
include '../_foot_panel.php';
?>