<?php
// Member registration page

require_once '../../lib/base.php';

$page_title = 'Register';
include '../_head.php';
?>

<main>
    <h2>Member Registration</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <?php echo error_message($_SESSION['error']);
        unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <?php echo success_message($_SESSION['success']);
        unset($_SESSION['success']); ?>
    <?php endif; ?> 

    <form id="registration-form" action="register_process.php" method="post">
        <div style="margin-bottom: 24px;">
            <label for="first_name">First Name:</label>
            <?php echo input_field('text', 'first_name', '', ['required' => 'required']); ?>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="last_name">Last Name:</label>
            <?php echo input_field('text', 'last_name', '', ['required' => 'required']); ?>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="password">Password:</label>
            <div style="position: relative;">
                <?php echo input_field('password', 'password', '', ['id' => 'password', 'required' => 'required']); ?>
                <i class="fas fa-eye-slash toggle-password" data-target="password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="confirm_password">Confirm Password:</label>
            <div style="position: relative;">
                <?php echo input_field('password', 'confirm_password', '', ['id' => 'confirm_password', 'required' => 'required']); ?>
                <i class="fas fa-eye-slash toggle-password" data-target="confirm_password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666;"></i>
            </div>
        </div>

        <div style="margin-bottom: 24px;">
            <label for="contact_number">Phone Number:</label>
            <?php echo input_field('text', 'contact_number', '', ['required' => 'required', 'pattern' => '[0-9]{3}-[0-9]{7,8}', 'title' => 'Format: XXX-XXXXXXX (e.g., 014-2461428)']); ?>
        </div>
   
        <div style="margin-bottom: 24px;">
            <label for="email">Email (Optional):</label>
            <?php echo input_field('email', 'email', '', []); ?>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-success"><i class="fas fa-user-plus"></i> Register</button>
            <button type="button" onclick="history.back()" class="btn btn-secondary" style="margin-left: 10px;"><i class="fas fa-arrow-left"></i> Back</button>
        </div>
    </form>
</main>

<script>
    $(document).ready(function() {
        $('.toggle-password').on('click', function() {
            const targetId = $(this).data('target');
            // Fallback to name selector if the input_field function doesn't generate the id attribute
            const input = $('#' + targetId).length ? $('#' + targetId) : $('input[name="' + targetId + '"]');
            
            if (input.length && input.attr('type') === 'password') {
                input.attr('type', 'text');
                $(this).removeClass('fa-eye-slash').addClass('fa-eye');
            } else if (input.length) {
                input.attr('type', 'password');
                $(this).removeClass('fa-eye').addClass('fa-eye-slash');
            }
        });
    });
</script>

<?php
include '../_foot.php';
?>