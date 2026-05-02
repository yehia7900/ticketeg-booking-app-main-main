<?php
require_once 'includes/config.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

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

    if (empty($errors)) {
        // Make sure the email is not already used by a different account
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? AND id != ?');
        $stmt->bind_param('si', $email, $user_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0)
            $errors[] = 'That email is already used by another account.';
        $stmt->close();
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $email, $user_id);
        $stmt->execute();
        $stmt->close();

        // Update the session name so the navbar reflects the change immediately
        $_SESSION['user_name'] = $name;

        header('Location: profile.php?success=profile');
        exit;
    }

    header('Location: profile.php?error=' . urlencode(implode(' ', $errors)));
    exit;
}

// ── Action: change password ──────────────────────────────────
if ($action === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new_pw  = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $errors = [];
    if (empty($current))
        $errors[] = 'Current password is required.';
    if (strlen($new_pw) < 8)
        $errors[] = 'New password must be at least 8 characters.';
    if ($new_pw !== $confirm)
        $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // Fetch the stored hash to verify the current password
        $stmt = $conn->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!password_verify($current, $row['password']))
            $errors[] = 'Current password is incorrect.';
    }

    if (empty($errors)) {
        $hash = password_hash($new_pw, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->bind_param('si', $hash, $user_id);
        $stmt->execute();
        $stmt->close();

        header('Location: profile.php?success=password');
        exit;
    }

    header('Location: profile.php?error=' . urlencode(implode(' ', $errors)));
    exit;
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

        // Use finfo to check the actual file type (not just the extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            $errors[] = 'Only JPG, PNG, GIF, or WEBP images are allowed.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Image must be smaller than 5 MB.';
        } else {
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Build a unique filename so uploads never overwrite each other
            $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
            $dest     = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // Delete the previous photo file if one exists
                $stmt = $conn->prepare('SELECT photo FROM users WHERE id = ?');
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $old_row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!empty($old_row['photo'])) {
                    $old_path = __DIR__ . '/' . $old_row['photo'];
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }

                // Save the new photo path to the database
                $photo_path = 'uploads/' . $filename;
                $stmt = $conn->prepare('UPDATE users SET photo = ? WHERE id = ?');
                $stmt->bind_param('si', $photo_path, $user_id);
                $stmt->execute();
                $stmt->close();

                header('Location: profile.php?success=photo');
                exit;
            } else {
                $errors[] = 'Upload failed. Please try again.';
            }
        }
    }

    header('Location: profile.php?error=' . urlencode(implode(' ', $errors)));
    exit;
}

// Unknown action — go back to profile
header('Location: profile.php');
exit;
