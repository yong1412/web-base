<?php

function input_field($type, $name, $value = '', $attributes = []) { // Generates an HTML input field with given type, name, value, and attributes.
    $attr_str = '';
    foreach ($attributes as $key => $val) {
        $attr_str .= " $key=\"$val\"";
    }
    return "<input type=\"$type\" name=\"$name\" value=\"$value\"$attr_str>";
}


function textarea_field($name, $value = '', $attributes = []) { // Generates an HTML textarea with given name, value, and attributes.
    $attr_str = '';
    foreach ($attributes as $key => $val) {
        $attr_str .= " $key=\"$val\"";
    }
    return "<textarea name=\"$name\"$attr_str>$value</textarea>";
}


function select_field($name, $options, $selected = '', $attributes = []) { // Generates a dropdown list with options and selected value.
    $attr_str = '';
    foreach ($attributes as $key => $val) {
        $attr_str .= " $key=\"$val\"";
    }
    $html = "<select name=\"$name\"$attr_str>";
    foreach ($options as $value => $label) {
        $sel = ($value == $selected) ? ' selected' : '';
        $html .= "<option value=\"$value\"$sel>$label</option>";
    }
    $html .= "</select>";
    return $html;
}


function form_open($action, $method = 'post', $attributes = []) { // Creates the opening <form> tag with action, method, and attributes.
    $attr_str = '';
    foreach ($attributes as $key => $val) {
        $attr_str .= " $key=\"$val\"";
    }
    return "<form action=\"$action\" method=\"$method\"$attr_str>";
}

function form_close() { // Returns the closing </form> tag.
    return "</form>";
}


function error_message($message) { // Displays an error message if it exists.
    if (!empty($message)) {
        return "<div class=\"error\">$message</div>";
    }
    return '';
}


function success_message($message) { // Displays a success message if it exists.
    if (!empty($message)) {
        return "<div class=\"success\">$message</div>";
    }
    return '';
}
?>