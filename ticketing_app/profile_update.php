<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

function redirect_profile_error(array $errors): never
{
    redirect('profile.php?error=' . urlencode(implode(' ', $errors)));
}

if ($action === 'profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $errors = [];

    if (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!$errors && db_row($conn, 'SELECT id FROM users WHERE email = ? AND id != ?', 'si', $email, $user_id)) {
        $errors[] = 'That email is already used by another account.';
    }
    if ($errors) {
        redirect_profile_error($errors);
    }

    $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->bind_param('ssi', $name, $email, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['user_name'] = $name;
    redirect('profile.php?success=profile');
}

if ($action === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $errors = [];

    if ($current === '') {
        $errors[] = 'Current password is required.';
    }
    if (strlen($new_password) < 8) {
        $errors[] = 'New password must be at least 8 characters.';
    }
    if ($new_password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    $user = $errors ? null : db_row($conn, 'SELECT password FROM users WHERE id = ?', 'i', $user_id);
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
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->bind_param('si', $hash, $user_id);
    $stmt->execute();
    $stmt->close();

    redirect('profile.php?success=password');
}

if ($action === 'photo') {
    $errors = [];
    $file = $_FILES['photo'] ?? null;

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid image file.';
    }

    if (!$errors) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types, true)) {
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
        redirect_profile_error(['Upload failed. Please try again.']);
    }

    $old = db_row($conn, 'SELECT photo FROM users WHERE id = ?', 'i', $user_id);
    if (!empty($old['photo'])) {
        $old_path = __DIR__ . '/' . $old['photo'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

    $photo_path = 'uploads/' . $filename;
    $stmt = $conn->prepare('UPDATE users SET photo = ? WHERE id = ?');
    $stmt->bind_param('si', $photo_path, $user_id);
    $stmt->execute();
    $stmt->close();

    redirect('profile.php?success=photo');
}

redirect('profile.php');
