<?php
// Admin member edit

require_once '../../lib/base.php';

require_admin(); 

// Retrieve the user ID from the URL query string, defaulting to 0 if not provided.
$id = $_GET['id'] ?? 0;

// If no ID is provided, redirect back to the user list page.
if (!$id) {
    header('Location: list.php');
    exit;
}

// Retrieve the user’s current information from the database using the provided ID.
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

// If no user is found with this ID, show an error message and redirect to the user list.
if (!$user) {
    $_SESSION['error'] = 'User not found.';
    header('Location: list.php');
    exit;
}

// Check if form is submitted - when the form is submitted via POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect all form input values, trimming spaces and setting default values where needed.
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '') ?: null; // Set to null if empty
    $contact_number = trim($_POST['contact_number'] ?? '');
    $role = $_POST['role'] ?? 'Member';
    $status = $_POST['status'] ?? 'active';
    $email_verified = isset($_POST['email_verified']) ? 1 : 0;
    $dob = $_POST['dob'] ?? null;
    $dob = !empty($dob) ? $dob : null;

    $errors = [];

    // Check that the first and last names are not empty; if they are, add an error message.
    if (empty($first_name)) $errors[] = 'First name is required.';
    if (empty($last_name)) $errors[] = 'Last name is required.';
    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (!empty($contact_number) && !preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $contact_number)) {
        $errors[] = 'Phone number must be in the format XXX-XXXXXXX (e.g., 014-2461428).';
    }

    // Check if email is already taken by another user
    if ($email !== null && $email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already taken by another user.';
        }
    }

    // If a date of birth is provided, check that it’s valid, not in the future, and that the user is at least 13 years old.
    if (!empty($dob)) {
        $birthDate = DateTime::createFromFormat('Y-m-d', $dob);
        $today = new DateTime();

        if (!$birthDate) {
            $errors[] = 'Invalid date of birth.';
        } elseif ($birthDate > $today) {
            $errors[] = 'Date of birth cannot be in the future.';
        } else {
            $age = $today->diff($birthDate)->y;
            if ($age < 13) {
                $errors[] = 'User must be at least 13 years old.';
            }
        }
    }

    // If there are no validation errors, update the user’s information in the database.
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, contact_number = ?, email = ?, dob = ?, role = ?, status = ?, email_verified = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $contact_number, $email, $dob, $role, $status, $email_verified, $id]);

            $_SESSION['success'] = 'User updated successfully.';
            header('Location: detail.php?id=' . $id);
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Edit Member';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Edit Member</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div style="color: #dc3545; margin-bottom: 20px;">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" id="edit-form">
            <div style="margin: 0 auto;width: max-content;">
                <strong>Current Profile Photo:</strong><br>
                <?php if ($user['photo']): ?>
                    <img src="../../uploads/profiles/<?php echo $user['photo']; ?>" alt="Profile Photo" style="max-width: 150px; border-radius: 8px; margin: 10px 0;">
                <?php else: ?>
                    <div style="width: 150px; height: 150px; background: #f8f9fa; border: 2px dashed #dee2e6; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #6c757d; margin: 10px auto;">
                        No photo uploaded
                    </div>
                <?php endif; ?>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="first_name" style="display: block; margin-bottom: 5px; font-weight: 600;">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="last_name" style="display: block; margin-bottom: 5px; font-weight: 600;">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email (Optional):</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="contact_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Phone Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" pattern="[0-9]{3}-[0-9]{7,8}" title="Format: XXX-XXXXXXX (e.g., 014-2461428)" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="role" style="display: block; margin-bottom: 5px; font-weight: 600;">Role:</label>
                    <span style="display: block; padding: 8px; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;">
                        <?php echo htmlspecialchars($user['role']); ?>
                        <input type="hidden" name="role" value="<?php echo htmlspecialchars($user['role']); ?>">
                    </span>
                    <!--select id="role" name="role" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="member" <?php echo $user['role'] === 'Member' ? 'selected' : ''; ?>>Member</option>
                        <option value="admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                    </select-->
                </div>
                
                <div class="form-col" style="margin-bottom: 15px;"></div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="dob" style="display: block; margin-bottom: 5px; font-weight: 600;">
                        Date of Birth:
                    </label>
                    <input
                            type="date"
                            id="dob"
                            name="dob"
                            value="<?php echo !empty($user['dob']) ? htmlspecialchars($user['dob']) : ''; ?>"
                            max="<?php echo date('Y-m-d'); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    >
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <?php if (!empty($user['dob'])): ?>
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Age:</label>
                        <div style="padding:8px;background:#f8f9fa;border-radius:4px;">
                            <?php
                            $dob = new DateTime($user['dob']);
                            echo (new DateTime())->diff($dob)->y . ' years old';
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="status" style="display: block; margin-bottom: 5px; font-weight: 600;">Status:</label>
                    <select id="status" name="status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="blocked" <?php echo $user['status'] === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">
                        <input type="checkbox" name="email_verified" value="1" <?php echo $user['email_verified'] ? 'checked' : ''; ?> style="margin-right: 8px;">
                        Email Verified
                    </label>
                </div>
            </div>
        </form>
    </div>
    <div class="card-footer">
        <button type="submit" form="edit-form" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <a href="detail.php?id=<?php echo $id; ?>" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Detail</a>
        <a href="list.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to List</a>
    </div>
</div>

<?php
include '../_foot_panel.php';
?>