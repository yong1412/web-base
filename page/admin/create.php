<?php
// Create new user (admin)
ob_start();
require_once '../../lib/base.php';

require_admin();

$page_title = 'Create New User';
include '../_head_panel.php';

// Initialize variables for user data and an empty errors array.
$errors = [];
$first_name = '';
$last_name = '';
$email = '';
$contact_number = '';
$password = '';
$confirm_password = '';
$role = 'Member';
$status = 'active';
$email_verified = 1;
$dob = null;

// Handle the form submission only when the request method is POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve user input from the form and set default values.
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '') ?: null; // Set to null if empty
    $contact_number = trim($_POST['contact_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'member';
    $status = $_POST['status'] ?? 'active';
    $email_verified = isset($_POST['email_verified']) ? 1 : 0;
    $dob = $_POST['dob'] ?? null;
    $dob = !empty($dob) ? $dob : null;

    $errors = [];

    // Validate that required fields are filled, email format is correct, and password rules are met.
    if (empty($first_name)) $errors[] = 'First name is required.';
    if (empty($last_name)) $errors[] = 'Last name is required.';
    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (!empty($contact_number) && !preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $contact_number)) {
        $errors[] = 'Phone number must be in the format XXX-XXXXXXX (e.g., 014-2461428).';
    }
    if (empty($password)) $errors[] = 'Password is required.';
    elseif (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

    // Check that the date of birth is valid, not in the future, and user is at least 13 years old.
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

    // Ensure the email is not already registered in the database.
    if (empty($errors) && $email !== null) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered.';
        }
    }

    // If there are no errors, hash the password, insert the new user into the database, and redirect with a success message.
    if (empty($errors)) {
        try {
            //$hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_password = sha1($password);
            $stmt = $pdo->prepare("INSERT INTO users (first_name, last_name, contact_number, email, password, dob, role, status, email_verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $contact_number, $email, $hashed_password, $dob, $role, $status, $email_verified]);

            $_SESSION['success'] = 'User created successfully.';
            header('Location: list.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Create New User</h2>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <br/>
        <?php endif; ?>

        <form method="post">
            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="first_name" style="display: block; margin-bottom: 5px; font-weight: 600;">First Name:</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="last_name" style="display: block; margin-bottom: 5px; font-weight: 600;">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email (Optional):</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="contact_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Phone Number:</label>
                    <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($contact_number); ?>" pattern="[0-9]{3}-[0-9]{7,8}" title="Format: XXX-XXXXXXX (e.g., 014-2461428)" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="password" style="display: block; margin-bottom: 5px; font-weight: 600;">Password:</label>
                    <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="confirm_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
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
                            value="<?php echo htmlspecialchars($dob ?? ''); ?>"
                            max="<?php echo date('Y-m-d'); ?>"
                            style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                    >
                </div>

                <div class="form-col">
                    <label for="status" style="display: block; margin-bottom: 5px; font-weight: 600;">Status:</label>
                    <select id="status" name="status" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="blocked" <?php echo $status === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                    </select>
                </div>
            </div>

            <div class="two-col-row">
                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="email_verified" style="display: block; margin-bottom: 5px; font-weight: 600;">Email Verified:</label>
                    <input type="checkbox" id="email_verified" name="email_verified" value="1" <?php echo $email_verified ? 'checked' : ''; ?> style="margin-right: 5px;"> Yes
                </div>

                <div class="form-col" style="margin-bottom: 15px;">
                    <label for="role" style="display: block; margin-bottom: 5px; font-weight: 600;">Role:</label>
                    <select id="role" name="role" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="member" <?php echo $role === 'member' ? 'selected' : ''; ?>>Member</option>
                        <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
include '../_foot_panel.php';
?>