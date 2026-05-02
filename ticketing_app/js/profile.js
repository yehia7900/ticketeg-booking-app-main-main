/* TicketEG — Profile Page (profile.js)
   Handles the collapsible sections (Edit Profile, Upload Photo,
   Change Password) on the profile page.
*/

// Toggles a collapsible section open or closed
// id       — the id of the <div> to show/hide
// btn      — the button that was clicked
// openText — button label when the section is closed
// closeText — button label when the section is open
function toggleSection(id, btn, openText, closeText) {
    var el   = document.getElementById(id);
    var open = el.classList.toggle('open');
    btn.textContent = open ? closeText : openText;
}
