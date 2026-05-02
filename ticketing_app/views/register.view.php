<?php $auth_page = true; require_once __DIR__ . '/../includes/header.php'; ?>

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

        <p style="margin-top:1rem;font-size:.8rem;color:var(--text-muted);text-align:center">
            By registering you agree to our Terms of Service.
        </p>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
