/* TicketEG — Session Management (session.js)
   Handles reading and writing the current user's login state
   using localStorage so the site remembers who is logged in.

   Load order: session.js → users.js → navbar.js
*/

// ── Private localStorage helpers ───────────────────────────────
// These two functions are used by all three JS files.

function lsRead(key, fallback) {
  try {
    var value = localStorage.getItem(key);
    return value !== null ? JSON.parse(value) : fallback;
  } catch (e) {
    return fallback;
  }
}

function lsSave(key, val) {
  try {
    localStorage.setItem(key, JSON.stringify(val));
  } catch (e) {}
}

// ── APP namespace ───────────────────────────────────────────────
// Create the APP object here. users.js and navbar.js add to it.
var APP = {};

// Get the currently logged-in user object, or null if not logged in
APP.getSession = function () {
  return lsRead('teg_session', null);
};

// Save a user object as the active session (called after login)
APP.setSession = function (user) {
  lsSave('teg_session', user);
};

// Remove the session (called on logout)
APP.clearSession = function () {
  localStorage.removeItem('teg_session');
};

// Redirect to login page if the user is not logged in
APP.requireLogin = function () {
  if (!APP.getSession()) {
    window.location.href = 'login.html';
    return false;
  }
  return true;
};
