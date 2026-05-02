<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="confirmation-page">
    <div class="confirmation-card">

        <div class="confirmation-icon">&#10003;</div>

        <h2>Booking Confirmed!</h2>
        <p>Your tickets are booked. See you at the event!</p>

        <div class="booking-id-badge">
            #<?= str_pad((string)$ticket['id'], 6, '0', STR_PAD_LEFT) ?>
        </div>

        <table class="booking-summary-table">
            <tr><td>Event</td>         <td><?= htmlspecialchars($ticket['event_title']) ?></td></tr>
            <tr><td>Category</td>      <td><?= htmlspecialchars($ticket['category'])    ?></td></tr>
            <tr><td>Date &amp; Time</td><td><?= htmlspecialchars($date_fmt)             ?></td></tr>
            <tr><td>Location</td>      <td><?= htmlspecialchars($ticket['location'])    ?></td></tr>
            <tr><td>Tickets</td>       <td><?= (int)$ticket['quantity'] ?> seat<?= $ticket['quantity'] > 1 ? 's' : '' ?></td></tr>
            <tr>
                <td>Total Paid</td>
                <td style="color:var(--accent);font-size:1.05rem">
                    EGP <?= number_format((float)$ticket['total_price'], 2) ?>
                </td>
            </tr>
            <tr>
                <td>Status</td>
                <td><span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>">
                    <?= htmlspecialchars(ucfirst($ticket['status'])) ?>
                </span></td>
            </tr>
            <tr><td>Booked By</td>  <td><?= htmlspecialchars($ticket['user_name'])  ?></td></tr>
            <tr><td>Booked At</td>  <td><?= htmlspecialchars($booked_fmt)           ?></td></tr>
        </table>

        <div style="background:var(--off-white);border-radius:var(--radius-sm);padding:1rem;
                    font-size:.85rem;color:var(--text-muted);margin-bottom:1.5rem;text-align:left">
            &#128274; Confirmation linked to <strong><?= htmlspecialchars($ticket['user_email']) ?></strong>.<br>
            Please bring a valid ID to the event.
        </div>

        <div class="confirmation-actions">
            <a href="index.php" class="btn btn-primary">&#127917; Browse More Events</a>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
