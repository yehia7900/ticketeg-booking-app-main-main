<?php
require_once 'includes/config.php';

// Already logged in — nothing to do here
if (isset($_SESSION['user_id'])) redirect('index.php');

$error    = '';
$redirect = 'index.php'; // where to go after a successful login

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // A hidden form field carries the page the user was trying to reach
    $redirect = $_POST['redirect'] ?? 'index.php';
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = db_row($conn,
            'SELECT id, name, password, role FROM users WHERE email = ?',
            's', $email
        );

        if ($user && password_verify($password, $user['password'])) {
            // Regenerate session ID on login to prevent session fixation attacks
            session_regenerate_id(true);
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role']      = $user['role'];

            // Admins always go to the dashboard; regular users go to $redirect
            $destination = ($user['role'] === 'admin') ? 'admin/dashboard.php' : $redirect;
            redirect($destination);
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
} else {
    // GET request: read the intended destination from the URL
    $redirect = isset($_GET['redirect']) ? rawurldecode($_GET['redirect']) : 'index.php';
}

$page_title = 'Login';
$auth_page  = true; // tells header.php to hide the main nav links
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Welcome back</h2>
        <p class="auth-sub">
            Don't have an account? <a href="register.php">Sign up free</a>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">&#10003; Account created! Please log in.</div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="you@example.com" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">
                Login to Your Account
            </button>
        </form>

        <div class="demo-hint">
            <strong>Demo accounts:</strong><br>
            User: <code>ahmed@example.com</code> / <code>user123</code>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
