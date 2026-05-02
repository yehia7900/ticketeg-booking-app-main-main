/* TicketEG — Booking Page (booking.js)
   Live price calculator for the seat-selection form.
   The price per seat is read from the select element's
   data-price attribute, which is set by booking.php.
*/

var seatsSelect = document.getElementById('seats');

// Read the price per seat set by PHP in the data-price attribute
var pricePerSeat = parseFloat(seatsSelect.getAttribute('data-price'));

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
updatePriceDisplay(); // run once on page load to show the correct starting price
