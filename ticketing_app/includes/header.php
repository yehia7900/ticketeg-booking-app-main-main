<?php
// Determine which page is active so the navbar can highlight the correct link
$current_page = basename($_SERVER['PHP_SELF']);

// $root_path is set by pages in sub-directories (e.g. admin/) to point back to the app root
$base = $root_path ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' — ' : '' ?>TicketEG</title>
    <link rel="stylesheet" href="<?= $base ?>css/style.css">
</head>
<body>

<header class="site-header">
    <nav class="navbar">

        <a href="<?= $base ?>index.php" class="nav-logo">
            <span class="logo-icon">&#127916;</span> TicketEG
        </a>

        <ul class="nav-links">
            <?php if (!isset($auth_page)): ?>
                <li>
                    <a href="<?= $base ?>index.php"
                       class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Home</a>
                </li>
                <li>
                    <a href="<?= $base ?>index.php#events"
                       class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Events</a>
                </li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                    <li>
                        <a href="<?= $base ?>admin/dashboard.php"
                           class="<?= in_array($current_page, ['dashboard.php', 'add_event.php']) ? 'active' : '' ?>">
                            Admin
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <a href="<?= $base ?>profile.php"
                       class="nav-profile-link <?= $current_page === 'profile.php' ? 'active' : '' ?>">
                        <span class="nav-avatar">&#128100;</span>
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Profile') ?>
                    </a>
                </li>
                <li>
                    <a href="<?= $base ?>logout.php" class="btn-nav-logout">Logout</a>
                </li>

            <?php else: ?>
                <li>
                    <a href="<?= $base ?>login.php"
                       class="<?= $current_page === 'login.php' ? 'active' : '' ?>">Login</a>
                </li>
                <li>
                    <a href="<?= $base ?>register.php"
                       class="btn-nav-register <?= $current_page === 'register.php' ? 'active' : '' ?>">Register</a>
                </li>
            <?php endif; ?>
        </ul>

    </nav>
</header>

<main class="main-content">
