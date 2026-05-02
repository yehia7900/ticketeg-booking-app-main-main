<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="index.php">Home</a><span>/</span>
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
                                <option value="<?= $i ?>" <?= (isset($_POST['seats']) && (int)$_POST['seats'] === $i) ? 'selected' : '' ?>>
                                    <?= $i ?> seat<?= $i > 1 ? 's' : '' ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <p class="form-hint">
                            Max 10 per order &nbsp;•&nbsp; <?= (int)$event['available_seats'] ?> seats left
                        </p>
                    </div>

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
                            <span>Booking fee</span><span>Free</span>
                        </div>
                        <div class="price-calc-row total">
                            <span>Total</span>
                            <span id="total-display">EGP <?= number_format($price, 2) ?></span>
                        </div>
                    </div>

                    <div style="margin-top:1.5rem">
                        <button type="submit" class="btn btn-primary btn-full btn-lg">&#10003; Confirm Booking</button>
                        <a href="event.php?id=<?= (int)$event_id ?>" class="btn btn-ghost btn-full mt-1">Cancel</a>
                    </div>
                </form>
            </div>

            <div class="event-summary-card">
                <div class="event-summary-img">
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                </div>
                <div class="event-summary-body">
                    <h3><?= htmlspecialchars($event['title']) ?></h3>
                    <div class="card-meta" style="gap:.5rem">
                        <div class="card-meta-item"><span class="icon">&#128197;</span> <?= htmlspecialchars($date_fmt) ?></div>
                        <div class="card-meta-item"><span class="icon">&#128205;</span> <?= htmlspecialchars($event['location']) ?></div>
                        <div class="card-meta-item">
                            <span class="icon">&#128100;</span>
                            Booked as: <strong style="margin-left:.25rem"><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
var pricePerSeat = <?= json_encode($price) ?>;
var sel = document.getElementById('seats');

function fmt(n) { return 'EGP ' + n.toLocaleString('en-EG', {minimumFractionDigits:2}); }

function update() {
    var s = parseInt(sel.value, 10);
    document.getElementById('seats-display').textContent   = s;
    document.getElementById('subtotal-display').textContent = fmt(s * pricePerSeat);
    document.getElementById('total-display').textContent   = fmt(s * pricePerSeat);
}

sel.addEventListener('change', update);
update();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
