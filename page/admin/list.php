<?php
// Admin member listing

require_once '../../lib/base.php';

require_admin(); 

$page_title = 'Manage Members';
include '../_head_panel.php';

// Retrieve the search term and page number from the URL, defaulting to an empty string and page 1.
$search = strtolower(trim($_GET['search'] ?? ''));
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit; // Set the number of users per page and calculate the starting record for the current page.

try { 
    // Initialize the base SQL query to fetch users and prepare an empty array for query parameters.
    $query = "SELECT id, first_name, last_name, email, role, status, email_verified, photo FROM users WHERE 1";
    $params = [];

    // If a search term is provided, filter users by name, email, ID, role, or status.
    if (!empty($search)) {
        $query .= " AND (
            CONCAT(first_name, ' ', last_name) LIKE :search 
            OR email LIKE :search
            OR id = :exact_id
            OR role LIKE :search
            OR status LIKE :search
        )";
    }

    $query .= " LIMIT :offset, :limit"; // Only get the users for this page, not all users.

    $stmt = $pdo->prepare($query); // Tell the database the search word, page start, and how many to show, then get the users.
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");

        if (is_numeric($search)) {
            $stmt->bindValue(':exact_id', (int)$search, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':exact_id', 0, PDO::PARAM_INT);
        }
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll();

    // Count total - Count how many users match the search so we know how many pages we need.
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

        if (is_numeric($search)) {
            $stmt->bindValue(':exact_id', (int)$search, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':exact_id', 0, PDO::PARAM_INT);
        }
    }
    $stmt->execute();
    $total = $stmt->fetchColumn();
    $total_pages = ceil($total / $limit); // Figure out the total number of pages for all users.
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage()); // Stop and show an error if something goes wrong with the database.
}
?>

<div style="margin-bottom: 20px;">
    <a href="create.php" class="btn btn-success"><i class="fas fa-plus"></i> Create New User</a>
</div>

<form class="member-list-search-form" method="get">
    <div style="width: 50%; display: flex; align-items: stretch;">
        <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search" style="flex: 1; border-top-right-radius: 0; border-bottom-right-radius: 0;">
        <button class="btn btn-primary" type="submit" style="border-top-left-radius: 0; border-bottom-left-radius: 0;"><i class="fas fa-search"></i></button>
    </div>
</form>

<table>
    <thead>
        <tr>
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
                <?php if ($user['status'] === 'active'): ?>
                    <button type="button" class="btn btn-danger btn-sm block-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-ban"></i> Block</button>
                <?php else: ?>
                    <button type="button" class="btn btn-success btn-sm unblock-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-check"></i> Unblock</button>
                <?php endif; ?>
                <!-- <button type="button" class="btn btn-delete btn-sm delete-btn" data-id="<?php echo $user['id']; ?>" data-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>"><i class="fas fa-trash"></i></button> -->
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($total_pages > 1): ?>
<div style="margin-top: 20px;">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-light'; ?> btn-sm" style="margin-right: 5px;"><?php echo $i; ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<script>
    /** When press F5 refresh, the search result reset **/
    if (window.location.search) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }

$(document).ready(function() { 
    // When the “Block” button is clicked, show a confirmation popup using SweetAlert.
    $('.block-btn').on('click', function() {
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Display a warning popup asking the admin to confirm blocking the user.
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
                // Redirect to block page
                window.location.href = `block.php?id=${userId}`;
            }
        });
    });

    $('.unblock-btn').on('click', function() { // When the “Unblock” button is clicked, show a confirmation popup for unblocking.
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Ask the admin to confirm that they want to unblock the user.
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
                // Redirect to unblock page
                window.location.href = `unblock.php?id=${userId}`;
            }
        });
    });

    $('.delete-btn').on('click', function() { // When the “Delete” button is clicked, show a popup warning about permanent deletion.
        const userId = $(this).data('id');
        const userName = $(this).data('name');

        // Tell the admin this action is permanent and ask for confirmation.
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete user "${userName}". This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete user',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Go to delete.php with the user ID to delete the user.
                window.location.href = `delete.php?id=${userId}`;
            }
        });
    });
});
</script>

<?php
include '../_foot_panel.php';
?>