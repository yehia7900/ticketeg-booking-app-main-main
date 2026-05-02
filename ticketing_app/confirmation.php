<?php
require_once 'includes/config.php';
// config.php already starts the session

// Require login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Read and validate the ticket ID from the URL
$ticket_id = isset($_GET['ticket_id']) ? (int)$_GET['ticket_id'] : 0;
if ($ticket_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch the ticket along with event and user details in one query
// The WHERE clause also checks user_id so users can only view their own bookings
$stmt = $conn->prepare('
    SELECT t.id, t.quantity, t.total_price, t.status, t.booked_at,
           e.title    AS event_title,
           e.location AS location,
           e.date     AS event_date,
           e.category AS category,
           u.name     AS user_name,
           u.email    AS user_email
    FROM tickets t
    JOIN events e ON e.id = t.event_id
    JOIN users  u ON u.id = t.user_id
    WHERE t.id = ? AND t.user_id = ?
');
$user_id = $_SESSION['user_id'];
$stmt->bind_param('ii', $ticket_id, $user_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If ticket not found (or belongs to someone else), go home
if (!$ticket) {
    header('Location: index.php');
    exit;
}

$date_fmt   = date('l, d F Y · g:i A', strtotime($ticket['event_date']));
$booked_fmt = date('d M Y H:i',        strtotime($ticket['booked_at']));

$page_title = 'Booking Confirmed';
require_once 'includes/header.php';
?>

<div class="confirmation-page">
    <div class="confirmation-card">

        <div class="confirmation-icon">&#10003;</div>

        <h2>Booking Confirmed!</h2>
        <p>Your tickets are booked. See you at the event!</p>

        <!-- Zero-padded booking reference number -->
        <div class="booking-id-badge">
            #<?= str_pad((string)$ticket['id'], 6, '0', STR_PAD_LEFT) ?>
        </div>

        <table class="booking-summary-table">
            <tr>
                <td>Event</td>
                <td><?= htmlspecialchars($ticket['event_title']) ?></td>
            </tr>
            <tr>
                <td>Category</td>
                <td><?= htmlspecialchars($ticket['category']) ?></td>
            </tr>
            <tr>
                <td>Date &amp; Time</td>
                <td><?= htmlspecialchars($date_fmt) ?></td>
            </tr>
            <tr>
                <td>Location</td>
                <td><?= htmlspecialchars($ticket['location']) ?></td>
            </tr>
            <tr>
                <td>Tickets</td>
                <td><?= (int)$ticket['quantity'] ?> seat<?= $ticket['quantity'] > 1 ? 's' : '' ?></td>
            </tr>
            <tr>
                <td>Total Paid</td>
                <td style="color:var(--accent);font-size:1.05rem">
                    EGP <?= number_format((float)$ticket['total_price'], 2) ?>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    <span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>">
                        <?= ucfirst(htmlspecialchars($ticket['status'])) ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>Booked By</td>
                <td><?= htmlspecialchars($ticket['user_name']) ?></td>
            </tr>
            <tr>
                <td>Booked At</td>
                <td><?= htmlspecialchars($booked_fmt) ?></td>
            </tr>
        </table>

        <div style="background:var(--off-white);border-radius:var(--radius-sm);padding:1rem;
                    font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;text-align:left">
            &#128274; Confirmation linked to
            <strong><?= htmlspecialchars($ticket['user_email']) ?></strong>.<br>
            Please bring a valid ID to the event.
        </div>

        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-primary">&#127917; Browse More Events</a>
        </div>

    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
