<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['user_id'];
$user = db_row($conn, 'SELECT * FROM users WHERE id = ?', 'i', $user_id);
if (!$user) {
    redirect('logout.php');
}

$bookings = db_rows($conn, '
    SELECT t.id, t.quantity, t.total_price, t.status, t.booked_at,
           e.title AS event_title, e.date AS event_date, e.category, e.image_url
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id = ?
    ORDER BY t.booked_at DESC
', 'i', $user_id);

$initials = '';
foreach (preg_split('/\s+/', trim($user['name'])) as $word) {
    $initials .= strtoupper($word[0] ?? '');
}

render_page('pages/profile', [
    'page_title' => 'My Profile',
    'user' => $user,
    'bookings' => $bookings,
    'initials' => $initials,
    'success' => $_GET['success'] ?? '',
    'error' => $_GET['error'] ?? '',
    'total_spent' => array_sum(array_column($bookings, 'total_price')),
]);
