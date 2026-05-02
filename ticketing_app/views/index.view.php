<?php require_once __DIR__ . '/../includes/header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <?php if (isset($_SESSION['user_id'])): ?>
            <h1>Welcome back, <span><?= htmlspecialchars($_SESSION['user_name']) ?>!</span></h1>
            <p>Great to see you again. Browse the latest events and book your next experience.</p>
        <?php else: ?>
            <h1>Egypt's Best Events,<br><span>One Click Away</span></h1>
            <p>Concerts, sports, theater and more — discover and book tickets to the events that matter most to you.</p>
        <?php endif; ?>
        <div class="hero-actions">
            <a href="#events" class="btn btn-primary btn-lg">Browse Events</a>
        </div>
    </div>
</section>

<div class="filter-bar">
    <div class="container">
        <span>Filter:</span>
        <a href="index.php" class="filter-btn <?= !$category ? 'active' : '' ?>">&#9734; All Events</a>
        <a href="index.php?category=Concert"  class="filter-btn <?= $category === 'Concert'  ? 'active' : '' ?>">&#127925; Concerts</a>
        <a href="index.php?category=Theater"  class="filter-btn <?= $category === 'Theater'  ? 'active' : '' ?>">&#127914; Theater</a>
        <a href="index.php?category=Sports"   class="filter-btn <?= $category === 'Sports'   ? 'active' : '' ?>">&#9917; Sports</a>
    </div>
</div>

<section class="events-section" id="events">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">
                <?= $category ? htmlspecialchars($category) . ' <span>Events</span>' : 'Upcoming <span>Events</span>' ?>
            </h2>
            <span class="events-count"><?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> found</span>
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
                    $low      = $event['available_seats'] <= 50;
                    $date_fmt = date('D, d M Y · g:i A', strtotime($event['date']));
                    $icons    = ['Concert' => '&#127925;', 'Theater' => '&#127914;', 'Sports' => '&#9917;', 'Other' => '&#9734;'];
                    $icon     = $icons[$event['category']] ?? '&#9734;';
                    $img      = $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80';
                ?>
                <article class="event-card">
                    <div class="card-image">
                        <img src="<?= htmlspecialchars($img) ?>"
                             alt="<?= htmlspecialchars($event['title']) ?>" loading="lazy">
                        <span class="card-badge"><?= $icon . ' ' . htmlspecialchars($event['category']) ?></span>
                        <span class="card-seats-badge <?= $low ? 'low' : '' ?>">
                            <?= $low ? '&#128293; ' : '' ?><?= (int)$event['available_seats'] ?> seats
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
                        <a href="event.php?id=<?= (int)$event['id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
