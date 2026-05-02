<?php
require_once 'includes/config.php';
// config.php already starts the session

// Require login — if not logged in, redirect to login page and come back after
if (!isset($_SESSION['user_id'])) {
    $return_to = 'booking.php?id=' . (int)($_GET['id'] ?? 0);
    header('Location: login.php?redirect=' . urlencode($return_to));
    exit;
}

// Read and validate the event ID
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($event_id <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch the event
$stmt = $conn->prepare('SELECT * FROM events WHERE id = ?');
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If the event is gone or sold out, send the user back to the event page
if (!$event || $event['available_seats'] == 0) {
    header('Location: event.php?id=' . $event_id);
    exit;
}

$error     = '';
$max_seats = min(10, (int)$event['available_seats']); // cap selection at 10 per order
$price     = (float)$event['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)($_POST['seats'] ?? 0);

    if ($quantity < 1 || $quantity > 10) {
        $error = 'Please select between 1 and 10 seats.';
    } elseif ($quantity > $event['available_seats']) {
        $error = 'Only ' . (int)$event['available_seats'] . ' seats are available.';
    } else {
        $total = round($quantity * $price, 2);

        // Use a database transaction so the ticket insert and seat decrement
        // either both succeed or both roll back — no half-completed bookings
        $conn->begin_transaction();
        try {
            // Insert the new ticket record
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

            // Decrement available seats — the WHERE clause prevents overselling
            $stmt = $conn->prepare(
                'UPDATE events
                 SET available_seats = available_seats - ?
                 WHERE id = ? AND available_seats >= ?'
            );
            $stmt->bind_param('iii', $quantity, $event_id, $quantity);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception('Not enough seats available.');
            }
            $stmt->close();

            $conn->commit();
            header('Location: confirmation.php?ticket_id=' . (int)$ticket_id);
            exit;

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
                        <select name="seats" id="seats" class="form-control" required>
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

                    <!-- Price calculator (updated live by the JS below) -->
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

<script>
// Live price calculator — updates the summary when the seat count changes
var pricePerSeat = <?= json_encode($price) ?>;
var seatsSelect  = document.getElementById('seats');

function formatEGP(amount) {
    return 'EGP ' + amount.toLocaleString('en-EG', { minimumFractionDigits: 2 });
}

function updatePriceDisplay() {
    var qty   = parseInt(seatsSelect.value, 10);
    var total = qty * pricePerSeat;
    document.getElementById('seats-display').textContent    = qty;
    document.getElementById('subtotal-display').textContent = formatEGP(total);
    document.getElementById('total-display').textContent    = formatEGP(total);
}

seatsSelect.addEventListener('change', updatePriceDisplay);
updatePriceDisplay(); // run once on page load
</script>

<?php require_once 'includes/footer.php'; ?>
