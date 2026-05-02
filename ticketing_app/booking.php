<?php
require_once 'includes/config.php';

// Require login — store the intended URL so we can return here after login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php?redirect=' . urlencode('booking.php?id=' . (int)($_GET['id'] ?? 0)));
}

// Read and validate the event ID
$event_id = (int)($_GET['id'] ?? 0);
if ($event_id <= 0) redirect('index.php');

// Fetch the event — redirect back if it's gone or sold out
$event = db_row($conn, 'SELECT * FROM events WHERE id = ?', 'i', $event_id);
if (!$event || $event['available_seats'] == 0) redirect('event.php?id=' . $event_id);

$error     = '';
$max_seats = min(10, (int)$event['available_seats']); // cap at 10 per order
$price     = (float)$event['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['seats'] ?? 0);

    if ($quantity < 1 || $quantity > 10) {
        $error = 'Please select between 1 and 10 seats.';
    } elseif ($quantity > $event['available_seats']) {
        $error = 'Only ' . (int)$event['available_seats'] . ' seats are available.';
    } else {
        $total = round($quantity * $price, 2);

        // Use a transaction so the insert and seat decrement happen together
        // If either step fails, both are rolled back — no half-completed bookings
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare(
                'INSERT INTO tickets (user_id, event_id, quantity, total_price, status)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $user_id = $_SESSION['user_id'];
            $status  = 'confirmed';
            $stmt->bind_param('iiids', $user_id, $event_id, $quantity, $total, $status);
            $stmt->execute();
            $ticket_id = $conn->insert_id;
            $stmt->close();

            // Decrement seats — the WHERE clause prevents overselling
            $stmt = $conn->prepare(
                'UPDATE events SET available_seats = available_seats - ?
                 WHERE id = ? AND available_seats >= ?'
            );
            $stmt->bind_param('iii', $quantity, $event_id, $quantity);
            $stmt->execute();

            if ($stmt->affected_rows === 0) throw new Exception('Not enough seats available.');
            $stmt->close();

            $conn->commit();
            redirect('confirmation.php?ticket_id=' . (int)$ticket_id);

        } catch (Exception $e) {
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Book Tickets';
$img        = $event['image_url'] ?: 'https://images.unsplash.com/photo-1501386761578-eac5c94b800a?w=800&q=80';
$date_fmt   = date('D, d M Y · g:i A', strtotime($event['date']));

require_once 'includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a>
            <span>/</span>
            <a href="event.php?id=<?= (int)$event['id'] ?>"><?= htmlspecialchars($event['title']) ?></a>
            <span>/</span>Book Tickets
        </div>
        <h1>Book Tickets</h1>
        <p>Complete your booking for <?= htmlspecialchars($event['title']) ?></p>
    </div>
</div>

<div class="booking-page">
    <div class="container">
        <div class="booking-grid">

            <!-- Left: seat selection form -->
            <div class="booking-form-card">
                <h2>&#127917; Seat Selection</h2>

                <?php if ($error): ?>
                    <div class="alert alert-error">&#9888; <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="booking.php?id=<?= (int)$event_id ?>">
                    <div class="form-group">
                        <label for="seats">Number of Seats</label>
                        <select name="seats" id="seats" class="form-control"
                                data-price="<?= $price ?>" required>
                            <?php for ($i = 1; $i <= $max_seats; $i++): ?>
                                <option value="<?= $i ?>"
                                    <?= (isset($_POST['seats']) && (int)$_POST['seats'] === $i) ? 'selected' : '' ?>>
                                    <?= $i ?> seat<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p class="form-hint">
                            Max 10 per order &nbsp;&bull;&nbsp; <?= (int)$event['available_seats'] ?> seats left
                        </p>
                    </div>

                    <!-- Price summary — updated live by booking.js -->
                    <div class="price-calc">
                        <div class="price-calc-row">
                            <span>Price per ticket</span>
                            <span>EGP <?= number_format($price, 2) ?></span>
                        </div>
                        <div class="price-calc-row">
                            <span>Seats</span>
                            <span id="seats-display">1</span>
                        </div>
                        <div class="price-calc-row">
                            <span>Subtotal</span>
                            <span id="subtotal-display">EGP <?= number_format($price, 2) ?></span>
                        </div>
                        <div class="price-calc-row">
                            <span>Booking fee</span>
                            <span>Free</span>
                        </div>
                        <div class="price-calc-row total">
                            <span>Total</span>
                            <span id="total-display">EGP <?= number_format($price, 2) ?></span>
                        </div>
                    </div>

                    <div style="margin-top:1.5rem">
                        <button type="submit" class="btn btn-primary btn-full btn-lg">
                            &#10003; Confirm Booking
                        </button>
                        <a href="event.php?id=<?= (int)$event_id ?>"
                           class="btn btn-ghost btn-full mt-1">Cancel</a>
                    </div>
                </form>
            </div>

            <!-- Right: event summary card -->
            <div class="event-summary-card">
                <div class="event-summary-img">
                    <img src="<?= htmlspecialchars($img) ?>"
                         alt="<?= htmlspecialchars($event['title']) ?>">
                </div>
                <div class="event-summary-body">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <div class="card-meta" style="gap:.5rem">
                        <div class="card-meta-item">
                            <span class="icon">&#128197;</span> <?= htmlspecialchars($date_fmt) ?>
                        </div>
                        <div class="card-meta-item">
                            <span class="icon">&#128205;</span> <?= htmlspecialchars($event['location']) ?>
                        </div>
                        <div class="card-meta-item">
                            <span class="icon">&#128100;</span>
                            Booked as:
                            <strong style="margin-left:.25rem">
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="js/booking.js"></script>

<?php require_once 'includes/footer.php'; ?>
