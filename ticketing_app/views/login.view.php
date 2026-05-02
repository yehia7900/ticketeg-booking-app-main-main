<?php $auth_page = true; require_once __DIR__ . '/../includes/header.php'; ?>

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

        <form method="POST" action="login.php?redirect=<?= htmlspecialchars(urlencode($redirect)) ?>">
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

        <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--border);
                    font-size:.8rem;color:var(--text-muted);text-align:center">
            <strong>Demo accounts:</strong><br>
            Admin: <code>admin@ticketeg.com</code> / <code>admin123</code><br>
            User: <code>ahmed@example.com</code> / <code>user123</code>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
