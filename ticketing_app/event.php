<?php
require_once 'includes/config.php';

// Read and validate the event ID from the URL
$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) redirect('index.php');

// Fetch the event — redirect home if it doesn't exist
$event = db_row($conn, 'SELECT * FROM events WHERE id = ?', 'i', $event_id);
if (!$event) redirect('index.php');

// Pre-format values used in the template
$date_fmt     = date('l, d F Y',  strtotime($event['date']));
$time_fmt     = date('g:i A',     strtotime($event['date']));
$img          = $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80';
$is_low_stock = $event['available_seats'] <= 50;

$category_icons = [
    'Concert' => '&#127925;',
    'Theater' => '&#127914;',
    'Sports'  => '&#9917;',
    'Other'   => '&#9734;',
];
$icon = $category_icons[$event['category']] ?? '&#9734;';

// If logged in, go straight to booking; otherwise send through login first
$book_href = isset($_SESSION['user_id'])
    ? 'booking.php?id=' . $event['id']
    : 'login.php?redirect=' . urlencode('booking.php?id=' . $event['id']);

$page_title = htmlspecialchars($event['title']);
require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="index.php?category=<?= urlencode($event['category']) ?>">
                <?= htmlspecialchars($event['category']) ?>
            </a>
            <span>/</span>
            <?= htmlspecialchars($event['title']) ?>
        </div>
        <h1><?= htmlspecialchars($event['title']) ?></h1>
        <p><?= $icon . ' ' . htmlspecialchars($event['category']) ?>
           &nbsp;|&nbsp;
           <?= htmlspecialchars($event['location']) ?>
        </p>
    </div>
</div>

<div class="event-detail">
    <div class="container">
        <div class="event-detail-grid">

            <!-- Left: event image and full description -->
            <div>
                <div class="event-image-large">
                    <img src="<?= htmlspecialchars($img) ?>"
                         alt="<?= htmlspecialchars($event['title']) ?>">
                </div>

                <div class="event-info mt-3">
                    <h1><?= htmlspecialchars($event['title']) ?></h1>

                    <ul class="event-meta-list mt-2">
                        <li class="event-meta-row">
                            <div class="meta-icon">&#128197;</div>
                            <div class="meta-text">
                                <strong><?= htmlspecialchars($date_fmt) ?></strong>
                                <span>Doors open: <?= htmlspecialchars($time_fmt) ?></span>
                            </div>
                        </li>
                        <li class="event-meta-row">
                            <div class="meta-icon">&#127968;</div>
                            <div class="meta-text">
                                <strong><?= htmlspecialchars($event['location']) ?></strong>
                                <span>Event venue</span>
                            </div>
                        </li>
                        <li class="event-meta-row">
                            <div class="meta-icon">&#127917;</div>
                            <div class="meta-text">
                                <strong><?= htmlspecialchars($event['category']) ?></strong>
                                <span>Event type</span>
                            </div>
                        </li>
                        <li class="event-meta-row">
                            <div class="meta-icon">&#129686;</div>
                            <div class="meta-text">
                                <strong style="color:<?= $is_low_stock ? '#ef4444' : '#10b981' ?>">
                                    <?= (int)$event['available_seats'] ?> seats available
                                </strong>
                                <span>of <?= (int)$event['total_seats'] ?> total</span>
                            </div>
                        </li>
                    </ul>

                    <h3 class="mb-2" style="font-size:1.1rem;font-weight:700;">About This Event</h3>
                    <p class="event-description">
                        <?= nl2br(htmlspecialchars($event['description'])) ?>
                    </p>
                </div>
            </div>

            <!-- Right: booking sidebar with price and CTA -->
            <aside class="booking-sidebar">
                <div class="sidebar-price">
                    EGP <?= number_format((float)$event['price'], 0) ?> <span>/ ticket</span>
                </div>

                <div class="sidebar-seats <?= $is_low_stock ? 'low' : '' ?>">
                    <?php if ($event['available_seats'] == 0): ?>
                        <strong style="color:#ef4444;">Sold Out</strong>
                    <?php elseif ($is_low_stock): ?>
                        &#128293; Only <strong><?= (int)$event['available_seats'] ?> seats</strong> left!
                    <?php else: ?>
                        <strong><?= (int)$event['available_seats'] ?> seats</strong> available
                    <?php endif; ?>
                </div>

                <div class="sidebar-info-row">
                    <span>Date</span>
                    <strong><?= htmlspecialchars(date('d M Y', strtotime($event['date']))) ?></strong>
                </div>
                <div class="sidebar-info-row">
                    <span>Time</span>
                    <strong><?= htmlspecialchars($time_fmt) ?></strong>
                </div>
                <div class="sidebar-info-row">
                    <span>Location</span>
                    <strong><?= htmlspecialchars($event['location']) ?></strong>
                </div>
                <div class="sidebar-info-row"
                     style="margin-top:.5rem;padding-top:.75rem;border-top:1px solid var(--border)">
                    <span>Price per ticket</span>
                    <strong style="color:var(--accent)">
                        EGP <?= number_format((float)$event['price'], 2) ?>
                    </strong>
                </div>

                <div style="margin-top:1.5rem">
                    <?php if ($event['available_seats'] == 0): ?>
                        <button class="btn btn-ghost btn-full" disabled>Sold Out</button>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($book_href) ?>"
                           class="btn btn-primary btn-full btn-lg">
                            &#127917; Book Now
                        </a>
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <p class="text-center text-muted mt-1" style="font-size:.82rem;">
                                <a href="login.php" style="color:var(--accent);font-weight:600;">Log in</a>
                                to book tickets.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div style="margin-top:1.25rem;padding-top:1rem;border-top:1px solid var(--border);
                            font-size:.8rem;color:var(--text-muted);text-align:center">
                    &#128274; Secure booking &nbsp;|&nbsp; Instant confirmation
                </div>
            </aside>

        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
