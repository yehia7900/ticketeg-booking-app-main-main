<?php
require_once 'includes/config.php';

// Require login
if (!isset($_SESSION['user_id'])) redirect('login.php');

$user_id = $_SESSION['user_id'];
$success = $_GET['success'] ?? '';
$error   = $_GET['error']   ?? '';

// Fetch user data
$user = db_row($conn, 'SELECT * FROM users WHERE id = ?', 'i', $user_id);

// Build initials from the user's name (e.g. "Ahmed Hassan" → "AH")
$initials = '';
foreach (explode(' ', $user['name']) as $word) {
    $initials .= strtoupper($word[0]);
}

// Fetch all bookings with event details
$bookings = db_rows($conn, '
    SELECT t.id, t.quantity, t.total_price, t.status, t.booked_at,
           e.title     AS event_title,
           e.date      AS event_date,
           e.category  AS category,
           e.image_url AS image_url
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id = ?
    ORDER BY t.booked_at DESC
', 'i', $user_id);

// Total amount spent across all bookings
$total_spent = array_sum(array_column($bookings, 'total_price'));

$page_title = 'My Profile';
require_once 'includes/header.php';
?>

<div class="profile-page">

    <!-- Profile hero banner with avatar and name -->
    <div class="profile-hero">
        <div class="container">
            <div class="profile-hero-inner">

                <div class="profile-avatar-lg">
                    <?php if (!empty($user['photo'])): ?>
                        <img src="<?= htmlspecialchars($user['photo']) ?>" alt="Profile photo">
                    <?php else: ?>
                        <?= htmlspecialchars($initials) ?>
                    <?php endif; ?>
                </div>

                <div>
                    <h1><?= htmlspecialchars($user['name']) ?></h1>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                </div>

            </div>
        </div>
    </div>

    <div class="container">

        <!-- Flash messages from profile_update.php redirects -->
        <?php if ($success === 'profile'): ?>
            <div class="alert alert-success">&#10003; Profile updated successfully.</div>
        <?php elseif ($success === 'password'): ?>
            <div class="alert alert-success">&#10003; Password updated successfully.</div>
        <?php elseif ($success === 'photo'): ?>
            <div class="alert alert-success">&#10003; Photo uploaded successfully.</div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-edit-grid">

            <!-- ── Left column: edit forms and account info ── -->
            <div class="profile-left-col">

                <!-- Edit name and email -->
                <div class="booking-form-card">
                    <div class="card-header">
                        <h3>&#9998; Edit Profile</h3>
                        <button type="button"
                                onclick="toggleSection('edit-section', this, 'Edit Profile', 'Cancel')">
                            Edit Profile
                        </button>
                    </div>
                    <div id="edit-section" class="card-section">
                        <form method="POST" action="profile_update.php">
                            <input type="hidden" name="action" value="profile">

                            <div class="form-group">
                                <label for="profile-name">Full Name</label>
                                <input type="text" id="profile-name" name="name"
                                       class="form-control"
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="profile-email">Email Address</label>
                                <input type="email" id="profile-email" name="email"
                                       class="form-control"
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>

                <!-- Upload profile photo (separate form — action=photo) -->
                <div class="booking-form-card">
                    <div class="card-header">
                        <h3>&#128247; Upload Photo</h3>
                        <button type="button"
                                onclick="toggleSection('photo-section', this, 'Change Photo', 'Cancel')">
                            Change Photo
                        </button>
                    </div>
                    <div id="photo-section" class="card-section">
                        <form method="POST" action="profile_update.php" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="photo">

                            <div class="form-group">
                                <label for="photo-file">
                                    Choose Image (JPG, PNG, GIF, WEBP — max 5 MB)
                                </label>
                                <input type="file" id="photo-file" name="photo"
                                       class="form-control" accept="image/*" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Upload Photo</button>
                        </form>
                    </div>
                </div>

                <!-- Change password -->
                <div class="booking-form-card">
                    <div class="card-header">
                        <h3>&#128274; Change Password</h3>
                        <button type="button"
                                onclick="toggleSection('pw-section', this, 'Change Password', 'Cancel')">
                            Change Password
                        </button>
                    </div>
                    <div id="pw-section" class="card-section">
                        <form method="POST" action="profile_update.php">
                            <input type="hidden" name="action" value="password">

                            <div class="form-group">
                                <label for="current-password">Current Password</label>
                                <input type="password" id="current-password"
                                       name="current_password" class="form-control"
                                       placeholder="Enter current password" required>
                            </div>

                            <div class="form-group">
                                <label for="new-password">New Password</label>
                                <input type="password" id="new-password"
                                       name="new_password" class="form-control"
                                       placeholder="At least 8 characters" required>
                            </div>

                            <div class="form-group">
                                <label for="confirm-password">Confirm New Password</label>
                                <input type="password" id="confirm-password"
                                       name="confirm_password" class="form-control"
                                       placeholder="Repeat new password" required>
                            </div>

                            <button type="submit" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>

                <!-- Read-only account information -->
                <div class="booking-form-card account-info-card">
                    <div class="account-info-header">
                        <h3>Account Information</h3>
                    </div>
                    <div class="account-info-body">

                        <div class="account-info-row">
                            <span class="account-info-label">Full Name</span>
                            <span class="account-info-value"><?= htmlspecialchars($user['name']) ?></span>
                        </div>

                        <div class="account-info-row">
                            <span class="account-info-label">Email Address</span>
                            <span class="account-info-value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>

                        <div class="account-info-row">
                            <span class="account-info-label">Account Type</span>
                            <span class="account-info-badge <?= htmlspecialchars($user['role']) ?>">
                                <?= ucfirst(htmlspecialchars($user['role'])) ?>
                            </span>
                        </div>

                        <div class="account-info-row">
                            <span class="account-info-label">Member Since</span>
                            <span class="account-info-value">
                                <?= !empty($user['created_at'])
                                    ? date('d M Y', strtotime($user['created_at']))
                                    : '—' ?>
                            </span>
                        </div>

                    </div>
                </div>

                <!-- Quick stats and logout -->
                <div class="booking-form-card">
                    <h3>&#128202; Account Stats</h3>
                    <p>Total bookings: <strong><?= count($bookings) ?></strong></p>
                    <p>Total spent: <strong>EGP <?= number_format($total_spent, 2) ?></strong></p>
                    <a href="logout.php" class="btn btn-danger" style="margin-top:1rem">Logout</a>
                </div>

            </div><!-- /.profile-left-col -->

            <!-- ── Right column: booking history ── -->
            <div class="event-summary-card">
                <h3>&#127915; My Bookings</h3>

                <?php if (empty($bookings)): ?>
                    <p>No bookings yet. <a href="index.php">Browse events</a></p>
                <?php else: ?>
                    <?php foreach ($bookings as $booking):
                        $thumb = $booking['image_url']
                            ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=200&q=80';
                    ?>
                    <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">

                        <img src="<?= htmlspecialchars($thumb) ?>"
                             alt="<?= htmlspecialchars($booking['event_title']) ?>"
                             style="width:80px;height:80px;object-fit:cover;border-radius:8px;">

                        <div style="flex:1;">
                            <strong><?= htmlspecialchars($booking['event_title']) ?></strong><br>
                            <?= date('d M Y', strtotime($booking['event_date'])) ?>
                            &bull; <?= (int)$booking['quantity'] ?> seat<?= $booking['quantity'] > 1 ? 's' : '' ?>
                        </div>

                        <div>
                            EGP <?= number_format((float)$booking['total_price'], 2) ?>
                        </div>

                    </div>
                    <hr>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div><!-- /.event-summary-card -->

        </div><!-- /.profile-edit-grid -->
    </div><!-- /.container -->
</div><!-- /.profile-page -->

<script src="js/profile.js"></script>

<?php require_once 'includes/footer.php'; ?>
