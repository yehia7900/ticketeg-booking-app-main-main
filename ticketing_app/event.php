<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) {
    redirect('index.php');
}

$event = db_row($conn, 'SELECT * FROM events WHERE id = ?', 'i', $event_id);
if (!$event) {
    redirect('index.php');
}

$book_href = isset($_SESSION['user_id'])
    ? 'booking.php?id=' . $event['id']
    : 'login.php?redirect=' . urlencode('booking.php?id=' . $event['id']);

render_page('pages/event', [
    'page_title' => $event['title'],
    'event' => $event,
    'book_href' => $book_href,
    'date_fmt' => date('l, d F Y', strtotime($event['date'])),
    'time_fmt' => date('g:i A', strtotime($event['date'])),
    'is_low_stock' => (int)$event['available_seats'] <= 50,
    'image_url' => $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80',
]);
