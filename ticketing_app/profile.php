<?php
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = (int)$_SESSION['user_id'];
$user = get_one("SELECT * FROM users WHERE id = $user_id");
if (!$user) {
    redirect('logout.php');
}

$bookings = get_all("
    SELECT t.id, t.quantity, t.total_price, t.status, t.booked_at,
           e.title AS event_title, e.date AS event_date, e.category, e.image_url
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id = $user_id
    ORDER BY t.booked_at DESC
");

$initials = '';
$words = preg_split('/\s+/', trim($user['name']));
foreach ($words as $word) {
    $initials .= strtoupper(isset($word[0]) ? $word[0] : '');
}

render_page('pages/profile', array(
    'page_title' => 'My Profile',
    'user' => $user,
    'bookings' => $bookings,
    'initials' => $initials,
    'success' => isset($_GET['success']) ? $_GET['success'] : '',
    'error' => isset($_GET['error']) ? $_GET['error'] : '',
    'total_spent' => array_sum(array_column($bookings, 'total_price'))
));
