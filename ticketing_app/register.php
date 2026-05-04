<?php
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$errors = array();
$values = array('name' => '', 'email' => '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $values = array('name' => $name, 'email' => $email);

    if (strlen($name) < 2) {
        $errors['name'] = 'Full name must be at least 2 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }
    if ($password != $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    $safe_email = clean($email);
    if (!$errors && get_one("SELECT id FROM users WHERE email = '$safe_email'")) {
        $errors['email'] = 'An account with this email already exists.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $role = 'user';
        $sql = "INSERT INTO users (name, email, password, role)
                VALUES ('" . clean($name) . "', '" . clean($email) . "', '" . clean($hash) . "', '$role')";
        mysqli_query($conn, $sql);

        redirect('login.php?registered=1');
    }
}

render_page('pages/register', array(
    'page_title' => 'Register',
    'auth_page' => true,
    'errors' => $errors,
    'values' => $values
));
