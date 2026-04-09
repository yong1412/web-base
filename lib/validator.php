<?php

function validate_required($value, $field_name) { // Checks if a field is empty and returns an error if it is.
    if (empty(trim($value))) {
        return "$field_name is required.";
    }
    return '';
}


function validate_email($email) { // Checks if an email is in a valid format.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "Invalid email format.";
    }
    return '';
}


function validate_min_length($value, $min_length, $field_name) { // Ensures a field meets the minimum character length.
    if (strlen(trim($value)) < $min_length) {
        return "$field_name must be at least $min_length characters.";
    }
    return '';
}


function validate_max_length($value, $max_length, $field_name) { // Ensures a field does not exceed the maximum character length.
    if (strlen(trim($value)) > $max_length) {
        return "$field_name must not exceed $max_length characters.";
    }
    return '';
}


function validate_numeric($value, $field_name) { // Checks if a field contains a numeric value.
    if (!is_numeric($value)) {
        return "$field_name must be a number.";
    }
    return '';
}

function validate_phone_number($value, $field_name) { // Checks if a phone number matches the format XXX-XXXXXXX.
    if (!preg_match('/^[0-9]{3}-[0-9]{7,8}$/', $value)) {
        return "$field_name must be in the format XXX-XXXXXXX (e.g., 014-2461428).";
    }
    return '';
}


function sanitize_input($input) { // Cleans input to prevent XSS by trimming and converting special characters.
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>