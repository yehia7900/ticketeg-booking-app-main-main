<?php
require_once __DIR__ . '/includes/config.php';

$allowed_categories = array('Concert', 'Theater', 'Sports', 'Other');
$category = null;

if (isset($_GET['category']) && in_array($_GET['category'], $allowed_categories)) {
    $category = $_GET['category'];
}

if ($category) {
    $events = get_all("SELECT * FROM events WHERE available_seats > 0 AND category = '" . clean($category) . "' ORDER BY date ASC");
} else {
    $events = get_all('SELECT * FROM events WHERE available_seats > 0 ORDER BY date ASC');
}

render_page('pages/home', array(
    'page_title' => 'Home',
    'category' => $category,
    'events' => $events
));
