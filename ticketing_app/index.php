<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$allowed_categories = ['Concert', 'Theater', 'Sports', 'Other'];
$category = in_array(($_GET['category'] ?? ''), $allowed_categories, true) ? $_GET['category'] : null;

$events = $category
    ? db_rows($conn, 'SELECT * FROM events WHERE available_seats > 0 AND category = ? ORDER BY date ASC', 's', $category)
    : db_rows($conn, 'SELECT * FROM events WHERE available_seats > 0 ORDER BY date ASC');

render_page('pages/home', [
    'page_title' => 'Home',
    'category' => $category,
    'events' => $events,
]);
