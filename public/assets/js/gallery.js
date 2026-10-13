/**
 * Galerie d'images de la fiche menu : clic sur une miniature = affichage en grand.
 */
(function () {
    'use strict';

    const main = document.getElementById('gallery-main');
    const caption = document.getElementById('gallery-caption');
    const buttons = document.querySelectorAll('.gallery-thumb-btn');
    if (!main || buttons.length === 0) {
        return;
    }

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            main.src = button.dataset.src;
            main.alt = button.dataset.alt;
            if (caption) {
                caption.textContent = button.dataset.alt;
            }
            buttons.forEach((b) => b.setAttribute('aria-pressed', b === button ? 'true' : 'false'));
        });
    });
})();
