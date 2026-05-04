<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$ticket_id = (int)($_GET['ticket_id'] ?? 0);
if ($ticket_id <= 0) {
    redirect('index.php');
}

$ticket = db_row($conn, '
    SELECT t.id, t.quantity, t.total_price, t.status, t.booked_at,
           e.title AS event_title, e.location, e.date AS event_date, e.category,
           u.name AS user_name, u.email AS user_email
    FROM tickets t
    JOIN events e ON e.id = t.event_id
    JOIN users u ON u.id = t.user_id
    WHERE t.id = ? AND t.user_id = ?
', 'ii', $ticket_id, (int)$_SESSION['user_id']);

if (!$ticket) {
    redirect('index.php');
}

render_page('pages/confirmation', [
    'page_title' => 'Booking Confirmed',
    'ticket' => $ticket,
    'date_fmt' => date('l, d F Y - g:i A', strtotime($ticket['event_date'])),
    'booked_fmt' => date('d M Y H:i', strtotime($ticket['booked_at'])),
]);
