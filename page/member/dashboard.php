<?php

require_once '../../lib/base.php';

require_login(); 

// Get the logged-in user’s information from the database using their session ID.
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If user is an Admin, fetch registration statistics for the report graph
$stats = [];
if ($user['role'] === 'Admin') {
    try {
        $stats = [
            '1_month' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)")->fetchColumn(),
            '3_months' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)")->fetchColumn(),
            '6_months' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)")->fetchColumn(),
            '1_year' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)")->fetchColumn(),
            'all_time' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        ];
    } catch (PDOException $e) {
        // Fallback to 0 if the 'created_at' column does not exist in your database yet
        $stats = ['1_month' => 0, '3_months' => 0, '6_months' => 0, '1_year' => 0, 'all_time' => 0];
    }
}

$page_title = 'Dashboard';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $user['role'] === 'Admin' ? 'Admin' : 'Member'; ?> Dashboard</h2>
    </div>
    <div class="card-body">
        <p class="dashboard-welcome">Welcome back, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! We're glad to have you here.</p>
    </div>
 <div class="card-footer">
    <a href="profile.php" class="btn btn-primary"><i class="fas fa-user"></i> View Profile</a>
    
    <a href="../../order/order_list.php" class="btn btn-info" style="margin-left: 10px;"><i class="fas fa-box-open"></i> My Orders (History & Tracking)</a>
    <a href="../../security/logout.php" class="btn btn-secondary" style="margin-left: 10px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
</div>

<?php if ($user['role'] === 'Admin'): ?>
<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h2>New Member Registration Report</h2>
    </div>
    <div class="card-body">
        <canvas id="registrationChart" width="400" height="150"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('registrationChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Past 1 Month', 'Past 3 Months', 'Past 6 Months', 'Past 1 Year', 'All Time'],
                datasets: [{
                    label: 'Number of Registrations',
                    data: [
                        <?php echo $stats['1_month']; ?>,
                        <?php echo $stats['3_months']; ?>,
                        <?php echo $stats['6_months']; ?>,
                        <?php echo $stats['1_year']; ?>,
                        <?php echo $stats['all_time']; ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(153, 102, 255, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>
<?php endif; ?>

<?php
include '../_foot_panel.php';
?>