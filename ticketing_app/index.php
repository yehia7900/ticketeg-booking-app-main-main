<?php
require_once 'includes/config.php';
$page_title = 'Home';

// Allowed category values (prevents arbitrary input)
$allowed_categories = ['Concert', 'Theater', 'Sports', 'Other'];
$category = (isset($_GET['category']) && in_array($_GET['category'], $allowed_categories))
    ? $_GET['category']
    : null;

// Fetch upcoming events, optionally filtered by category
if ($category) {
    $stmt = $conn->prepare(
        'SELECT * FROM events WHERE available_seats > 0 AND category = ? ORDER BY date ASC'
    );
    $stmt->bind_param('s', $category);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $result = $conn->query('SELECT * FROM events WHERE available_seats > 0 ORDER BY date ASC');
    $events = $result->fetch_all(MYSQLI_ASSOC);
}

require_once 'includes/header.php';

// Category icons — defined once here, reused in the loop below
$category_icons = [
    'Concert' => '&#127925;',
    'Theater' => '&#127914;',
    'Sports'  => '&#9917;',
    'Other'   => '&#9734;',
];
?>

<section class="hero">
    <div class="hero-content">
        <h1>Egypt's Best Events,<br><span>One Click Away</span></h1>
        <p>Concerts, sports, theater and more — discover and book tickets to the events that matter most to you.</p>
        <div class="hero-actions">
            <a href="#events" class="btn btn-primary btn-lg">Browse Events</a>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="register.php" class="btn btn-secondary btn-lg">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<div class="filter-bar">
    <div class="container">
        <span>Filter:</span>
        <a href="index.php"
           class="filter-btn <?= !$category ? 'active' : '' ?>">&#9734; All Events</a>
        <a href="index.php?category=Concert"
           class="filter-btn <?= $category === 'Concert' ? 'active' : '' ?>">&#127925; Concerts</a>
        <a href="index.php?category=Theater"
           class="filter-btn <?= $category === 'Theater' ? 'active' : '' ?>">&#127914; Theater</a>
        <a href="index.php?category=Sports"
           class="filter-btn <?= $category === 'Sports'  ? 'active' : '' ?>">&#9917; Sports</a>
    </div>
</div>

<section class="events-section" id="events">
    <div class="container">

        <div class="section-header">
            <h2 class="section-title">
                <?= $category
                    ? htmlspecialchars($category) . ' <span>Events</span>'
                    : 'Upcoming <span>Events</span>' ?>
            </h2>
            <span class="events-count">
                <?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> found
            </span>
        </div>

        <?php if (empty($events)): ?>
            <div class="no-events">
                <h3>No events found</h3>
                <p>Try a different category or check back later.</p>
                <a href="index.php" class="btn btn-outline mt-2">View All Events</a>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event):
                    $is_low_stock = $event['available_seats'] <= 50;
                    $date_fmt     = date('D, d M Y · g:i A', strtotime($event['date']));
                    $icon         = $category_icons[$event['category']] ?? '&#9734;';
                    $img          = $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80';
                ?>
                <article class="event-card">
                    <div class="card-image">
                        <img src="<?= htmlspecialchars($img) ?>"
                             alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
                        <span class="card-badge">
                            <?= $icon . ' ' . htmlspecialchars($event['category']) ?>
                        </span>
                        <span class="card-seats-badge <?= $is_low_stock ? 'low' : '' ?>">
                            <?= $is_low_stock ? '&#128293; ' : '' ?><?= (int)$event['available_seats'] ?> seats
                        </span>
                    </div>
                    <div class="card-body">
                        <h3 class="card-title"><?= htmlspecialchars($event['title']) ?></h3>
                        <div class="card-meta">
                            <div class="card-meta-item">
                                <span class="icon">&#128197;</span> <?= htmlspecialchars($date_fmt) ?>
                            </div>
                            <div class="card-meta-item">
                                <span class="icon">&#128205;</span> <?= htmlspecialchars($event['location']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="card-price">
                            EGP <?= number_format((float)$event['price'], 0) ?> <span>/ ticket</span>
                        </div>
                        <a href="event.php?id=<?= (int)$event['id'] ?>" class="btn btn-primary btn-sm">
                            Book Now
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
