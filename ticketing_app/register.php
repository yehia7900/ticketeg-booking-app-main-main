<?php
require_once 'includes/config.php';

// Already logged in — send them home
if (isset($_SESSION['user_id'])) redirect('index.php');

$errors = [];
$values = ['name' => '', 'email' => '']; // repopulate form fields on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']             ?? '');
    $email    = trim($_POST['email']            ?? '');
    $password =      $_POST['password']         ?? '';
    $confirm  =      $_POST['confirm_password'] ?? '';

    $values = ['name' => $name, 'email' => $email];

    // Validate each field
    if (strlen($name) < 2)
        $errors['name']     = 'Full name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors['email']    = 'Please enter a valid email address.';
    if (strlen($password) < 8)
        $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm)
        $errors['confirm']  = 'Passwords do not match.';

    if (empty($errors)) {
        // Check if this email is already taken
        $existing = db_row($conn, 'SELECT id FROM users WHERE email = ?', 's', $email);

        if ($existing) {
            $errors['email'] = 'An account with this email already exists.';
        } else {
            // Hash the password before saving — never store plain text
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $role = 'user';

            $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $name, $email, $hash, $role);
            $stmt->execute();
            $stmt->close();

            redirect('login.php?registered=1');
        }
    }
}

$page_title = 'Register';
$auth_page  = true;
require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <h2>Create an account</h2>
        <p class="auth-sub">Already have an account? <a href="login.php">Log in</a></p>

        <form method="POST" action="register.php" novalidate>

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?= htmlspecialchars($values['name']) ?>"
                       placeholder="Ahmed Hassan" required>
                <?php if (!empty($errors['name'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['name']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($values['email']) ?>"
                       placeholder="you@example.com" required>
                <?php if (!empty($errors['email'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="At least 8 characters" required>
                <?php if (!empty($errors['password'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                       placeholder="Repeat your password" required>
                <?php if (!empty($errors['confirm'])): ?>
                    <p class="form-error"><?= htmlspecialchars($errors['confirm']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg">Create Account</button>
        </form>

        <p class="terms-note">By registering you agree to our Terms of Service.</p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
