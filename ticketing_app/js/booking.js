var seatsSelect = document.getElementById('seats');
var pricePerSeat = parseFloat(seatsSelect.getAttribute('data-price'));

function formatEGP(amount) {
    return 'EGP ' + amount.toLocaleString('en-EG', { minimumFractionDigits: 2 });
}

function updatePriceDisplay() {
    var quantity = parseInt(seatsSelect.value, 10);
    var total = quantity * pricePerSeat;

    document.getElementById('seats-display').textContent = quantity;
    document.getElementById('subtotal-display').textContent = formatEGP(total);
    document.getElementById('total-display').textContent = formatEGP(total);
}

seatsSelect.addEventListener('change', updatePriceDisplay);
updatePriceDisplay();
