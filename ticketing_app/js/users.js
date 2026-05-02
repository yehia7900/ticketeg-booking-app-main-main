/* TicketEG — User Accounts & Authentication (users.js)
   Manages demo accounts, registered users stored in localStorage,
   and the login/registration logic used by the HTML pages.

   Depends on: session.js (for lsRead, lsSave, APP)
*/

// ── Built-in demo accounts ──────────────────────────────────────
// These are always available even before anyone registers.
var DEMO_ACCOUNTS = [
  { name: 'Admin User',   email: 'admin@tickets.eg',  password: 'password', role: 'admin' },
  { name: 'Ahmed Hassan', email: 'ahmed@example.com', password: 'password', role: 'user'  },
  { name: 'Sara Mahmoud', email: 'sara@example.com',  password: 'password', role: 'user'  },
];

// ── User account functions ──────────────────────────────────────

// Get the list of users who have registered via the HTML form
APP.getRegistered = function () {
  return lsRead('teg_users', []);
};

// Save a new registered user to localStorage
APP.saveUser = function (user) {
  var list = APP.getRegistered();
  list.push(user);
  lsSave('teg_users', list);
};

// Check if an email is already taken (demo or registered)
APP.emailTaken = function (email) {
  var all = DEMO_ACCOUNTS.concat(APP.getRegistered());
  return all.some(function (u) {
    return u.email.toLowerCase() === email.toLowerCase();
  });
};

// Try to log in — returns the user object on success, or null on failure
APP.authenticate = function (email, password) {
  var all = DEMO_ACCOUNTS.concat(APP.getRegistered());
  return all.find(function (u) {
    return u.email.toLowerCase() === email.trim().toLowerCase()
        && u.password === password;
  }) || null;
};
