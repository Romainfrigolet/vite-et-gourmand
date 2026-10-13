/**
 * Formulaire de commande : estimation du prix en temps réel.
 * - prix du menu et remise calculés localement
 * - frais de livraison demandés au serveur (géocodage de l'adresse)
 * Le serveur recalcule TOUJOURS le prix définitif : cette estimation n'est qu'un confort.
 */
(function () {
    'use strict';

    const form = document.getElementById('order-form');
    if (!form) {
        return;
    }

    const pricePerPerson = parseFloat(form.dataset.prixPersonne);
    const minimum = parseInt(form.dataset.minimum, 10);
    const fields = {
        people: document.getElementById('nombre_personne'),
        address: document.getElementById('adresse_prestation'),
        postcode: document.getElementById('code_postal_prestation'),
        city: document.getElementById('ville_prestation'),
        menu: document.getElementById('menu_id'),
    };
    const out = {
        people: document.getElementById('recap-personnes'),
        menu: document.getElementById('recap-menu'),
        discount: document.getElementById('recap-remise'),
        delivery: document.getElementById('recap-livraison'),
        total: document.getElementById('recap-total'),
        info: document.getElementById('recap-livraison-info'),
    };

    const euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    let deliveryPrice = null;
    let timer = null;

    function render() {
        const people = parseInt(fields.people.value, 10);
        if (!Number.isFinite(people) || people < minimum) {
            out.people.textContent = String(minimum);
            out.menu.textContent = 'minimum ' + minimum + ' pers.';
            out.discount.textContent = '—';
            out.total.textContent = '—';
            return;
        }
        const menuPrice = Math.round(pricePerPerson * people * 100) / 100;
        const discount = people >= minimum + 5 ? Math.round(menuPrice * 10) / 100 : 0;
        out.people.textContent = String(people);
        out.menu.textContent = euro.format(menuPrice);
        out.discount.textContent = discount > 0 ? '− ' + euro.format(discount) : 'non applicable';
        if (deliveryPrice === null) {
            out.total.textContent = euro.format(menuPrice - discount) + ' + livraison';
        } else {
            out.total.textContent = euro.format(menuPrice - discount + deliveryPrice);
        }
    }

    async function updateDelivery() {
        const postcode = fields.postcode.value.trim();
        const city = fields.city.value.trim();
        if (!/^\d{5}$/.test(postcode) || city === '') {
            deliveryPrice = null;
            out.delivery.textContent = 'à calculer';
            render();
            return;
        }
        out.delivery.textContent = 'calcul…';
        const params = new URLSearchParams({ adresse: fields.address.value.trim(), code_postal: postcode, ville: city });
        try {
            const response = await fetch('/api/livraison?' + params.toString(), { headers: { Accept: 'application/json' } });
            const data = await response.json();
            if (!data.ok) {
                deliveryPrice = null;
                out.delivery.textContent = 'à confirmer';
                out.info.textContent = data.error || 'Adresse non reconnue.';
            } else {
                deliveryPrice = data.prix_livraison;
                out.delivery.textContent = data.bordeaux ? 'Offerte' : euro.format(data.prix_livraison);
                out.info.textContent = data.bordeaux
                    ? 'Livraison offerte à Bordeaux.'
                    : 'Hors Bordeaux : 5 € + 0,59 € × ' + String(data.distance_km).replace('.', ',') + ' km.';
            }
        } catch (e) {
            deliveryPrice = null;
            out.delivery.textContent = 'à confirmer';
        }
        render();
    }

    function scheduleDelivery() {
        clearTimeout(timer);
        timer = setTimeout(updateDelivery, 500);
    }

    fields.people.addEventListener('input', render);
    [fields.address, fields.postcode, fields.city].forEach((f) => f.addEventListener('input', scheduleDelivery));

    // Changement de menu : rechargement de la page du menu choisi (conditions et minimum différents)
    fields.menu.addEventListener('change', () => {
        window.location.href = '/commander/' + encodeURIComponent(fields.menu.value);
    });

    render();
    updateDelivery();
})();
