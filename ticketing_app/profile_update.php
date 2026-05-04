<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['user_id'];
$action = isset($_POST['action']) ? $_POST['action'] : '';

function redirect_profile_error($errors)
{
    redirect('profile.php?error=' . urlencode(implode(' ', $errors)));
}

if ($action == 'profile') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $errors = array();

    if (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    $safe_email = clean($email);
    $duplicate = get_one("SELECT id FROM users WHERE email = '$safe_email' AND id != $user_id");
    if (!$errors && $duplicate) {
        $errors[] = 'That email is already used by another account.';
    }
    if ($errors) {
        redirect_profile_error($errors);
    }

    $sql = "UPDATE users
            SET name = '" . clean($name) . "', email = '" . clean($email) . "'
            WHERE id = $user_id";
    mysqli_query($conn, $sql);

    $_SESSION['user_name'] = $name;
    redirect('profile.php?success=profile');
}

if ($action == 'password') {
    $current = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $errors = array();

    if ($current == '') {
        $errors[] = 'Current password is required.';
    }
    if (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($new_password != $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    $user = false;
    if (!$errors) {
        $user = get_one("SELECT password FROM users WHERE id = $user_id");
    }
    if (!$errors && !$user) {
        $errors[] = 'Account not found.';
    }
    if (!$errors && !password_verify($current, $user['password'])) {
        $errors[] = 'Current password is incorrect.';
    }
    if ($errors) {
        redirect_profile_error($errors);
    }

    $hash = password_hash($new_password, PASSWORD_BCRYPT);
    $sql = "UPDATE users SET password = '" . clean($hash) . "' WHERE id = $user_id";
    mysqli_query($conn, $sql);

    redirect('profile.php?success=password');
}

if ($action == 'photo') {
    $errors = array();
    $file = isset($_FILES['photo']) ? $_FILES['photo'] : null;

    if (!$file || $file['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid image file.';
    }

    if (!$errors) {
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
        $max_size = 5 * 1024 * 1024;

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Image must be smaller than 5 MB.';
        }
    }

    if ($errors) {
        redirect_profile_error($errors);
    }

    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
        redirect_profile_error(array('Upload failed. Please try again.'));
    }

    $old = get_one("SELECT photo FROM users WHERE id = $user_id");
    if (!empty($old['photo'])) {
        $old_path = __DIR__ . '/' . $old['photo'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

    $photo_path = 'uploads/' . $filename;
    $sql = "UPDATE users SET photo = '" . clean($photo_path) . "' WHERE id = $user_id";
    mysqli_query($conn, $sql);

    redirect('profile.php?success=photo');
}

redirect('profile.php');
