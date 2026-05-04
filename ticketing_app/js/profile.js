document.querySelectorAll('[data-toggle-section]').forEach(function (button) {
    button.addEventListener('click', function () {
        var section = document.getElementById(button.dataset.toggleSection);
        var isOpen = section.classList.toggle('open');
        button.textContent = isOpen ? button.dataset.closeText : button.dataset.openText;
    });
});
