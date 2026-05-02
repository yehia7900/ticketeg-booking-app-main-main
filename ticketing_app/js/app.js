/* TicketEG — app.js | Session, auth, bookings, navbar */
var APP = (function () {

  /* Built-in demo accounts */
  var DEMO = [
    { name: 'Admin User',   email: 'admin@tickets.eg',  password: 'password', role: 'admin' },
    { name: 'Ahmed Hassan', email: 'ahmed@example.com', password: 'password', role: 'user'  },
    { name: 'Sara Mahmoud', email: 'sara@example.com',  password: 'password', role: 'user'  },
  ];

  /* localStorage helpers */
  function save(key, val) {
    try { localStorage.setItem(key, JSON.stringify(val)); } catch(e) {}
  }
  function read(key, fallback) {
    try {
      var v = localStorage.getItem(key);
      return v !== null ? JSON.parse(v) : fallback;
    } catch(e) { return fallback; }
  }

  var pub = {};

  /* ── Session ── */
  pub.getSession   = function ()  { return read('teg_session', null); };
  pub.setSession   = function (u) { save('teg_session', u); };
  pub.clearSession = function ()  { localStorage.removeItem('teg_session'); };

  /* ── Users ── */
  pub.getRegistered = function () { return read('teg_users', []); };

  pub.saveUser = function (u) {
    var list = pub.getRegistered();
    list.push(u);
    save('teg_users', list);
  };

  pub.emailTaken = function (email) {
    return DEMO.concat(pub.getRegistered()).some(function (u) {
      return u.email.toLowerCase() === email.toLowerCase();
    });
  };

  /* Core fix: check demo + registered users, case-insensitive email */
  pub.authenticate = function (email, password) {
    var all = DEMO.concat(pub.getRegistered());
    return all.find(function (u) {
      return u.email.toLowerCase() === email.trim().toLowerCase()
          && u.password === password;
    }) || null;
  };

  /* ── Bookings ── */
  pub.getBookings = function (email) {
    var all = read('teg_bookings', []);
    return email ? all.filter(function (b) { return b.userEmail === email; }) : all;
  };

  pub.saveBooking = function (b) {
    var all = read('teg_bookings', []);
    all.unshift(b);
    save('teg_bookings', all);
    save('teg_last', b);   /* quick access for confirmation.html */
  };

  pub.genId = function () {
    return '#TKT' + (100000 + Math.floor(Math.random() * 899999));
  };

  /* ── Redirect to login if not authenticated ── */
  pub.requireLogin = function () {
    if (!pub.getSession()) {
      window.location.href = 'login.html';
      return false;
    }
    return true;
  };

  /* ── Navbar init ──────────────────────────────────────────────
     Call APP.initNav() on every page (except register.html).
     opts.onSearch(q) — callback used by index.html for live filter.
  ─────────────────────────────────────────────────────────────── */
  pub.initNav = function (opts) {
    opts = opts || {};
    var session  = pub.getSession();
    var guestEl  = document.getElementById('nav-guest');
    var authEl   = document.getElementById('nav-auth');

    if (session) {
      if (guestEl) guestEl.style.display = 'none';
      if (authEl)  authEl.style.display  = 'flex';
      var nameEl = document.getElementById('nav-user-name');
      if (nameEl) nameEl.textContent = session.name;
    } else {
      if (guestEl) guestEl.style.display = '';
      if (authEl)  authEl.style.display  = 'none';
    }

    /* Search box wiring */
    var inp = document.getElementById('nav-search');
    if (inp) {
      var qParam = new URLSearchParams(window.location.search).get('q');
      if (qParam) {
        inp.value = qParam;
        if (opts.onSearch) opts.onSearch(qParam);
      }

      function run() {
        var q = inp.value.trim();
        if (!q) return;
        if (opts.onSearch) {
          opts.onSearch(q);
        } else {
          window.location.href = 'index.html?q=' + encodeURIComponent(q);
        }
      }

      inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') run(); });
      var btn = document.getElementById('nav-search-btn');
      if (btn) btn.addEventListener('click', run);
    }
  };

  return pub;
})();