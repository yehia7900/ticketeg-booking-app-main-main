<?php
require_once __DIR__ . '/includes/config.php';

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!isset($_SESSION['user_id'])) {
    redirect('login.php?redirect=' . urlencode('booking.php?id=' . $event_id));
}
if ($event_id <= 0) {
    redirect('index.php');
}

$event = get_one("SELECT * FROM events WHERE id = $event_id");
if (!$event || (int)$event['available_seats'] == 0) {
    redirect('event.php?id=' . $event_id);
}

$error = '';
$price = (float)$event['price'];
$max_seats = min(10, (int)$event['available_seats']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quantity = isset($_POST['seats']) ? (int)$_POST['seats'] : 0;

    if ($quantity < 1 || $quantity > 10) {
        $error = 'Please select between 1 and 10 seats.';
    } elseif ($quantity > (int)$event['available_seats']) {
        $error = 'Only ' . (int)$event['available_seats'] . ' seats are available.';
    } else {
        $total = round($quantity * $price, 2);
        $status = 'confirmed';
        $user_id = (int)$_SESSION['user_id'];

        $sql = "INSERT INTO tickets (user_id, event_id, quantity, total_price, status)
                VALUES ($user_id, $event_id, $quantity, $total, '$status')";
        $flag = mysqli_query($conn, $sql);

        if ($flag) {
            $ticket_id = mysqli_insert_id($conn);
            $update = "UPDATE events
                       SET available_seats = available_seats - $quantity
                       WHERE id = $event_id AND available_seats >= $quantity";
            mysqli_query($conn, $update);

            if (mysqli_affected_rows($conn) > 0) {
                redirect('confirmation.php?ticket_id=' . $ticket_id);
            } else {
                $error = 'Not enough seats available.';
            }
        } else {
            $error = 'Booking failed. Please try again.';
        }
    }
}

render_page('pages/booking', array(
    'page_title' => 'Book Tickets',
    'event' => $event,
    'event_id' => $event_id,
    'error' => $error,
    'price' => $price,
    'max_seats' => $max_seats,
    'date_fmt' => date('D, d M Y - g:i A', strtotime($event['date'])),
    'image_url' => $event['image_url'] ? $event['image_url'] : 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80'
));
