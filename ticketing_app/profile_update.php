<?php
require_once 'includes/config.php';

// Require login
if (!isset($_SESSION['user_id'])) redirect('login.php');

$user_id = (int)$_SESSION['user_id'];
$action  = $_POST['action'] ?? '';

// ── Action: update name and email ────────────────────────────
if ($action === 'profile') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');

    $errors = [];
    if (strlen($name) < 2)
        $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Please enter a valid email address.';

    // Check the email is not already used by a different account
    if (empty($errors)) {
        $taken = db_row($conn, 'SELECT id FROM users WHERE email = ? AND id != ?', 'si', $email, $user_id);
        if ($taken) $errors[] = 'That email is already used by another account.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update the navbar display name immediately
        $_SESSION['user_name'] = $name;
        redirect('profile.php?success=profile');
    }

    redirect('profile.php?error=' . urlencode(implode(' ', $errors)));
}

// ── Action: change password ──────────────────────────────────
if ($action === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new_pw  = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $errors = [];
    if (empty($current))       $errors[] = 'Current password is required.';
    if (strlen($new_pw) < 8)   $errors[] = 'New password must be at least 8 characters.';
    if ($new_pw !== $confirm)  $errors[] = 'Passwords do not match.';

    // Verify the current password against the stored hash
    if (empty($errors)) {
        $row = db_row($conn, 'SELECT password FROM users WHERE id = ?', 'i', $user_id);
        if (!password_verify($current, $row['password']))
            $errors[] = 'Current password is incorrect.';
    }

    if (empty($errors)) {
        $hash = password_hash($new_pw, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $user_id);
        $stmt->execute();
        $stmt->close();
        redirect('profile.php?success=password');
    }

    redirect('profile.php?error=' . urlencode(implode(' ', $errors)));
}

// ── Action: upload profile photo ─────────────────────────────
if ($action === 'photo') {
    $errors = [];

    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please select a valid image file.';
    } else {
        $file          = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size      = 5 * 1024 * 1024; // 5 MB

        // Check the actual MIME type, not just the file extension
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Image must be smaller than 5 MB.';
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            // Unique filename prevents overwrites
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                // Delete the old photo file if one exists
                $old = db_row($conn, 'SELECT photo FROM users WHERE id = ?', 'i', $user_id);
                if (!empty($old['photo'])) {
                    $old_path = __DIR__ . '/' . $old['photo'];
                    if (file_exists($old_path)) unlink($old_path);
                }

                // Save the new photo path to the database
                $photo_path = 'uploads/' . $filename;
                $stmt = $conn->prepare('UPDATE users SET photo = ? WHERE id = ?');
                $stmt->bind_param('si', $photo_path, $user_id);
                $stmt->execute();
                $stmt->close();

                redirect('profile.php?success=photo');
            } else {
                $errors[] = 'Upload failed. Please try again.';
            }
        }
    }

    redirect('profile.php?error=' . urlencode(implode(' ', $errors)));
}

// Unknown action — go back to profile
redirect('profile.php');
