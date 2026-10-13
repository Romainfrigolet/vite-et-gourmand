/**
 * Comportements communs à toutes les pages.
 */
(function () {
    'use strict';

    // Demande de confirmation avant une action irréversible (annulation, suppression…)
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (form instanceof HTMLFormElement && form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });

    // Indicateur des règles de mot de passe (champ avec data-password-rules="#id-de-la-liste")
    document.querySelectorAll('[data-password-rules]').forEach((input) => {
        const list = document.querySelector(input.dataset.passwordRules);
        if (!list) {
            return;
        }
        const rules = {
            length: (v) => v.length >= 10,
            upper: (v) => /[A-Z]/.test(v),
            lower: (v) => /[a-z]/.test(v),
            digit: (v) => /\d/.test(v),
            special: (v) => /[^A-Za-z0-9]/.test(v),
        };
        const update = () => {
            Object.entries(rules).forEach(([name, test]) => {
                const item = list.querySelector('[data-rule="' + name + '"]');
                if (item) {
                    const ok = test(input.value);
                    item.classList.toggle('ok', ok);
                    item.querySelector('.visually-hidden').textContent = ok ? ' (respecté)' : ' (non respecté)';
                }
            });
        };
        input.addEventListener('input', update);
        update();
    });

    // Bouton « afficher / masquer le mot de passe »
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        const input = document.querySelector(button.dataset.togglePassword);
        if (!input) {
            return;
        }
        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Masquer' : 'Afficher';
            button.setAttribute('aria-pressed', show ? 'true' : 'false');
        });
    });
})();
