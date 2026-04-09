<?php

require_once '../../lib/base.php';

require_login();

// Retrieve the logged-in user’s ID from the session to identify which profile to edit.
$user_id = $_SESSION['user_id'];

// Get all the details of the logged-in user from the database.
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// If no user is found, show an error message and redirect to the homepage.
if (!$user) {
    $_SESSION['error'] = 'Profile not found.';
    header('Location: ../index.php');
    exit;
}

$page_title = 'Edit Profile';
include '../_head_panel.php';
?>

<div class="card">
    <div class="card-header">
        <h2>Edit Profile</h2>
    </div>
    <div class="card-body">
<form id="edit-form" action="edit_profile_process.php" method="post" enctype="multipart/form-data">
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
            <label for="old_password" style="display: block; margin-bottom: 5px; font-weight: 600;">Old Password:</label>
            <input type="password" id="old_password" name="old_password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" placeholder="Enter old password to make changes">
        </div>

        <div class="form-col" style="margin-bottom: 15px;">
            <label for="new_password" style="display: block; margin-bottom: 5px; font-weight: 600;">New Password (leave empty to keep current):</label>
            <input type="password" id="new_password" name="new_password" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
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
                    value="<?php echo !empty($user['dob']) ? htmlspecialchars($user['dob']) : ''; ?>"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
            >
        </div>
        <div class="form-col" style="margin-bottom: 15px;">
                <label for="contact_number" style="display: block; margin-bottom: 5px; font-weight: 600;">Phone Number:</label>
                <input type="tel" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($user['contact_number'] ?? ''); ?>" pattern="[0-9]{3}-[0-9]{7,8}" title="Format: XXX-XXXXXXX (e.g., 014-2461428)" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
    </div>

        <div class="two-col-row">
            <div class="form-col" style="margin-bottom: 15px;">
                <label for="email" style="display: block; margin-bottom: 5px; font-weight: 600;">Email (Optional):</label>
                <div style="display: flex; gap: 10px;">
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" name="verify_email" value="1" form="edit-form" id="verify-btn" class="btn btn-info" style="white-space: nowrap; <?php echo ($user['email_verified'] == 1 || empty($user['email'])) ? 'display: none;' : ''; ?>"><i class="fas fa-envelope"></i> Verify</button>
                </div>
            </div>
            <div class="form-col" style="margin-bottom: 15px;"></div>
        </div>

    <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 5px; font-weight: 600;">Profile Photo:</label>
        <label for="file-input" class="drop-zone" id="drop-zone">
            <p>Drag & drop a new photo here or click to select</p>
            <input type="file" id="file-input" name="photo" accept="image/*" style="display: none;">
        </label>
        <input type="hidden" name="photo_data" id="photo-data">
        <div style="margin-top: 10px;">
            <button type="button" id="webcam-btn" class="btn btn-secondary btn-sm"><i class="fas fa-camera"></i> Take Photo from Webcam</button>
        </div>
        <div id="webcam-container" style="display: none; margin-top: 10px;">
            <video id="webcam-video" autoplay style="width: 100%; max-width: 400px; border: 1px solid #ddd;"></video>
            <br>
            <button type="button" id="capture-btn" class="btn btn-primary btn-sm" style="margin-top: 5px;"><i class="fas fa-camera"></i> Capture</button>
            <button type="button" id="cancel-webcam-btn" class="btn btn-secondary btn-sm" style="margin-top: 5px;"><i class="fas fa-times"></i> Cancel</button>
        </div>
    </div>

</form>

    </div>
    <div class="card-footer">
        <button type="submit" form="edit-form" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
        <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Profile</a>
    </div>
</div>

<script>
$(document).ready(function() {
    var dropZone = $('#drop-zone');
    var fileInput = $('#file-input');
    var photoData = $('#photo-data');
    var webcamBtn = $('#webcam-btn');
    var webcamContainer = $('#webcam-container');
    var webcamVideo = $('#webcam-video');
    var captureBtn = $('#capture-btn');
    var cancelWebcamBtn = $('#cancel-webcam-btn');
    var stream = null;

    // When a file is dragged over the drop zone, highlight the border to show it’s active.
    dropZone.on('dragover', function(e) {
        e.preventDefault();
        dropZone.css('border-color', '#007bff');
    });

    // When the dragged file leaves the drop zone, return the border to normal.
    dropZone.on('dragleave', function(e) {
        e.preventDefault();
        dropZone.css('border-color', '#ddd');
    });

    // When a file is dropped into the drop zone, get the file and process it.
    dropZone.on('drop', function(e) {
        e.preventDefault();
        dropZone.css('border-color', '#ddd');
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    // When a file is selected using the file input, process that file.
    fileInput.on('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    // Check if the file is an image, read it as a Base64 string, and display a preview in the drop zone.
    function handleFile(file) {
        if (file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                photoData.val(e.target.result);
                dropZone.html('<div style="text-align: center;"><img src="' + e.target.result + '" style="max-width: 200px; max-height: 200px; display: block; margin: 0 auto 10px;"><p>File selected: ' + file.name + '</p></div>');
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a valid image file.');
        }
    }

    // Webcam functionality - When the webcam button is clicked, ask for camera permission and show the webcam preview.
    webcamBtn.on('click', function() {
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(mediaStream) {
                    stream = mediaStream;
                    webcamVideo[0].srcObject = mediaStream;
                    webcamContainer.show();
                    webcamBtn.hide();
                })
                .catch(function(err) {
                    alert('Error accessing webcam: ' + err.message);
                });
        } else {
            alert('Webcam not supported in this browser.');
        }
    });

    // Take a snapshot from the webcam, save it as Base64, and show it in the drop zone.
    captureBtn.on('click', function() {
        var canvas = document.createElement('canvas');
        var context = canvas.getContext('2d');
        canvas.width = webcamVideo[0].videoWidth;
        canvas.height = webcamVideo[0].videoHeight;
        context.drawImage(webcamVideo[0], 0, 0, canvas.width, canvas.height);
        var dataURL = canvas.toDataURL('image/jpeg');
        photoData.val(dataURL);
        dropZone.html('<div style="text-align: center;"><img src="' + dataURL + '" style="max-width: 200px; max-height: 200px; display: block; margin: 0 auto 10px;"><p>Photo captured from webcam</p></div>');
        stopWebcam();
    });

    cancelWebcamBtn.on('click', function() {
        stopWebcam();
    });

    // Stop all webcam streams, hide the webcam container, and show the webcam button again.
    function stopWebcam() {
        if (stream) {
            stream.getTracks().forEach(function(track) {
                track.stop();
            });
            stream = null;
        }
        webcamContainer.hide();
        webcamBtn.show();
    }

    // Dynamically show the Verify button if the email is changed
    var originalEmail = "<?php echo addslashes($user['email'] ?? ''); ?>";
    var isVerified = <?php echo $user['email_verified'] ?? 0; ?>;
    $('#email').on('input', function() {
        var currentVal = $(this).val().trim();
        if (currentVal === '') {
            $('#verify-btn').hide();
        } else if (currentVal !== originalEmail) {
            $('#verify-btn').show();
        } else {
            if (isVerified == 1) {
                $('#verify-btn').hide();
            } else {
                $('#verify-btn').show();
            }
        }
    });
});
</script>

<?php
include '../_foot_panel.php';
?>