<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$event_id = (int)($_GET['id'] ?? 0);
if (!isset($_SESSION['user_id'])) {
    redirect('login.php?redirect=' . urlencode('booking.php?id=' . $event_id));
}
if ($event_id <= 0) {
    redirect('index.php');
}

$event = db_row($conn, 'SELECT * FROM events WHERE id = ?', 'i', $event_id);
if (!$event || (int)$event['available_seats'] === 0) {
    redirect('event.php?id=' . $event_id);
}

$error = '';
$price = (float)$event['price'];
$max_seats = min(10, (int)$event['available_seats']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['seats'] ?? 0);

    if ($quantity < 1 || $quantity > 10) {
        $error = 'Please select between 1 and 10 seats.';
    } elseif ($quantity > (int)$event['available_seats']) {
        $error = 'Only ' . (int)$event['available_seats'] . ' seats are available.';
    } else {
        $conn->begin_transaction();

        try {
            $total = round($quantity * $price, 2);
            $status = 'confirmed';
            $user_id = (int)$_SESSION['user_id'];

            $stmt = $conn->prepare('INSERT INTO tickets (user_id, event_id, quantity, total_price, status) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iiids', $user_id, $event_id, $quantity, $total, $status);
            $stmt->execute();
            $ticket_id = $conn->insert_id;
            $stmt->close();

            $stmt = $conn->prepare('UPDATE events SET available_seats = available_seats - ? WHERE id = ? AND available_seats >= ?');
            $stmt->bind_param('iii', $quantity, $event_id, $quantity);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new RuntimeException('Not enough seats available.');
            }

            $stmt->close();
            $conn->commit();
            redirect('confirmation.php?ticket_id=' . $ticket_id);
        } catch (Throwable $exception) {
            $conn->rollback();
            $error = $exception->getMessage();
        }
    }
}

render_page('pages/booking', [
    'page_title' => 'Book Tickets',
    'event' => $event,
    'event_id' => $event_id,
    'error' => $error,
    'price' => $price,
    'max_seats' => $max_seats,
    'date_fmt' => date('D, d M Y - g:i A', strtotime($event['date'])),
    'image_url' => $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80',
]);
