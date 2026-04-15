<?php
require 'order_base.php';
//-----------------------------------------------------------------------------
// Security: Only Admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    temp('info', 'Access denied. Admins only.');
    redirect('/index.php');
}

$success_count = 0;
$error_count = 0;

if (is_post() && isset($_FILES['batch_file'])) {
    $file = $_FILES['batch_file'];
    $batch_type = post('batch_type'); 
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if ($ext === 'csv' || $ext === 'txt') {
            $handle = fopen($file['tmp_name'], "r");
            
            if ($handle !== FALSE) {
                // Skip the first row (headers)
                fgetcsv($handle, 1000, ",");
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    
                    // 1. BATCH INSERT
                    if ($batch_type === 'insert' && count($data) >= 3) {
                        $user_id = trim($data[0]);
                        $total_price = trim($data[1]);
                        $status = trim($data[2]);
                        
                        if (is_numeric($user_id) && is_numeric($total_price)) {
                            db_execute("INSERT INTO orders (user_id, total_price, status) VALUES (?, ?, ?)", [$user_id, $total_price, $status]);
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                    // 2. BATCH UPDATE
                    elseif ($batch_type === 'update' && count($data) >= 2) {
                        $order_id = trim($data[0]);
                        $new_status = trim($data[1]);
                        
                        if (is_numeric($order_id)) {
                            db_execute("UPDATE orders SET status = ? WHERE id = ?", [$new_status, $order_id]);
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    }
                    
                    // 3. BATCH DELETE
                    elseif ($batch_type === 'delete' && count($data) >= 1) {
                        $order_id = trim($data[0]);
                        
                        if (is_numeric($order_id)) {
                            db_execute("DELETE FROM orders WHERE id = ?", [$order_id]);
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++; 
                    }
                }
                fclose($handle);
                temp('info', "Batch $batch_type complete! $success_count succeeded, $error_count failed.");
                redirect('batch_operations.php');
            } else {
                err('file', 'Could not read the uploaded file.');
            }
        } else {
            err('file', 'Invalid file type. Please upload a .csv or .txt file.');
        }
    } else {
        err('file', 'Error uploading file.');
    }
}

//-----------------------------------------------------------------------------
$_title = 'Admin: Batch Operations';
include '_head_panel.php';
?>

<div style="display: flex; gap: 20px; flex-wrap: wrap;">

    <div style="background: #f8fafc; padding: 25px; border-radius: 8px; border: 1px solid #e2e8f0; flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; color: #0ea5e9;">1. Batch Insert Orders</h3>
        <p style="font-size: 14px;">Format: <code>user_id, total_price, status</code></p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="batch_type" value="insert">
            <input type="file" name="batch_file" accept=".csv, .txt" required style="margin-bottom: 10px; width: 100%;">
            <button type="submit" style="padding: 10px; background: #0ea5e9; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%;">Upload & Insert</button>
        </form>
    </div>

    <div style="background: #fdf8f6; padding: 25px; border-radius: 8px; border: 1px solid #ffedd5; flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; color: #f97316;">2. Batch Update Status</h3>
        <p style="font-size: 14px;">Format: <code>order_id, new_status</code></p>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="batch_type" value="update">
            <input type="file" name="batch_file" accept=".csv, .txt" required style="margin-bottom: 10px; width: 100%;">
            <button type="submit" style="padding: 10px; background: #f97316; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%;">Upload & Update</button>
        </form>
    </div>

    <div style="background: #fef2f2; padding: 25px; border-radius: 8px; border: 1px solid #fee2e2; flex: 1; min-width: 300px;">
        <h3 style="margin-top: 0; color: #dc2626;">3. Batch Delete Orders</h3>
        <p style="font-size: 14px;">Format: <code>order_id</code></p>
        <form method="POST" enctype="multipart/form-data" onsubmit="return confirm('WARNING: This will permanently delete these orders. Continue?');">
            <input type="hidden" name="batch_type" value="delete">
            <input type="file" name="batch_file" accept=".csv, .txt" required style="margin-bottom: 10px; width: 100%;">
            <button type="submit" style="padding: 10px; background: #dc2626; color: white; border: none; border-radius: 4px; cursor: pointer; width: 100%;">Upload & Delete</button>
        </form>
    </div>

</div>

<br>
<a href="order_list.php">&laquo; Back to Order List</a>

<?php include '_foot_panel.php'; ?>