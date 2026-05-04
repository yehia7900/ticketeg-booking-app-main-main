<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';
$redirect = $_POST['redirect'] ?? ($_GET['redirect'] ?? 'index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $user = db_row($conn, 'SELECT id, name, password, role FROM users WHERE email = ?', 's', $email);

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : $redirect);
        }

        $error = 'Invalid email or password. Please try again.';
    }
}

render_page('pages/login', [
    'page_title' => 'Login',
    'auth_page' => true,
    'error' => $error,
    'redirect' => $redirect,
]);
