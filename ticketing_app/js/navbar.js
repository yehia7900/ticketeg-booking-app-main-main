/* TicketEG — Bookings & Navbar (navbar.js)
   Manages the user's booking history in localStorage and
   updates the navigation bar to show the right links based
   on whether the user is logged in.

   Depends on: session.js (for lsRead, lsSave, APP)
*/

// ── Booking functions ───────────────────────────────────────────

// Get all bookings, or filter by email if provided
APP.getBookings = function (email) {
  var all = lsRead('teg_bookings', []);
  return email
    ? all.filter(function (b) { return b.userEmail === email; })
    : all;
};

// Save a new booking and store it as the latest for confirmation.html
APP.saveBooking = function (booking) {
  var all = lsRead('teg_bookings', []);
  all.unshift(booking);           // add to the front so newest appears first
  lsSave('teg_bookings', all);
  lsSave('teg_last', booking);    // quick access for confirmation.html
};

// Generate a random ticket ID string (e.g. "#TKT482931")
APP.genId = function () {
  return '#TKT' + (100000 + Math.floor(Math.random() * 899999));
};

// ── Navbar initialization ───────────────────────────────────────
// Call APP.initNav() on every HTML page to show/hide the correct
// nav links based on the current session.
//
// opts.onSearch(q) — optional callback for live search on index.html

APP.initNav = function (opts) {
  opts = opts || {};

  var session = APP.getSession();
  var guestEl = document.getElementById('nav-guest');  // links shown when logged out
  var authEl  = document.getElementById('nav-auth');   // links shown when logged in

  if (session) {
    // User is logged in — show auth nav, fill in the user's name
    if (guestEl) guestEl.style.display = 'none';
    if (authEl)  authEl.style.display  = 'flex';
    var nameEl = document.getElementById('nav-user-name');
    if (nameEl) nameEl.textContent = session.name;
  } else {
    // User is not logged in — show guest nav
    if (guestEl) guestEl.style.display = '';
    if (authEl)  authEl.style.display  = 'none';
  }

  // Wire up the search box if it exists on the page
  var searchInput = document.getElementById('nav-search');
  if (searchInput) {
    // Pre-fill the search box if a ?q= param is in the URL
    var qParam = new URLSearchParams(window.location.search).get('q');
    if (qParam) {
      searchInput.value = qParam;
      if (opts.onSearch) opts.onSearch(qParam);
    }

    function runSearch() {
      var q = searchInput.value.trim();
      if (!q) return;
      if (opts.onSearch) {
        opts.onSearch(q);                                      // live filter on index.html
      } else {
        window.location.href = 'index.html?q=' + encodeURIComponent(q); // navigate
      }
    }

    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') runSearch();
    });

    var searchBtn = document.getElementById('nav-search-btn');
    if (searchBtn) searchBtn.addEventListener('click', runSearch);
  }
};
