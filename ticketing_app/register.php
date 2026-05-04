<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$errors = [];
$values = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $values = ['name' => $name, 'email' => $email];

    if (strlen($name) < 2) {
        $errors['name'] = 'Full name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    if (!$errors && db_row($conn, 'SELECT id FROM users WHERE email = ?', 's', $email)) {
        $errors['email'] = 'An account with this email already exists.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user';
        $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        $stmt->execute();
        $stmt->close();

        redirect('login.php?registered=1');
    }
}

render_page('pages/register', [
    'page_title' => 'Register',
    'auth_page' => true,
    'errors' => $errors,
    'values' => $values,
]);
