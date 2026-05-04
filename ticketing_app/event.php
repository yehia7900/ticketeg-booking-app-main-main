<?php
require_once __DIR__ . '/includes/config.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    redirect('index.php');
}

$event = get_one("SELECT * FROM events WHERE id = $event_id");
if (!$event) {
    redirect('index.php');
}

if (isset($_SESSION['user_id'])) {
    $book_href = 'booking.php?id=' . $event['id'];
} else {
    $book_href = 'login.php?redirect=' . urlencode('booking.php?id=' . $event['id']);
}

render_page('pages/event', array(
    'page_title' => $event['title'],
    'event' => $event,
    'book_href' => $book_href,
    'date_fmt' => date('l, d F Y', strtotime($event['date'])),
    'time_fmt' => date('g:i A', strtotime($event['date'])),
    'is_low_stock' => (int)$event['available_seats'] <= 50,
    'image_url' => $event['image_url'] ? $event['image_url'] : 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80'
));
