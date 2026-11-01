#!/usr/bin/env bash
# Tests de bout en bout de l'application (environnement docker compose).
# Usage : bash tests/smoke.sh [URL]   (par défaut http://localhost:8080)
set -u
BASE="${1:-http://localhost:8080}"
MAILPIT="${MAILPIT:-http://localhost:8025}"
JAR=$(mktemp); PASS=0; FAIL=0

ok()   { echo "  ✔ $1"; PASS=$((PASS+1)); }
ko()   { echo "  ✘ $1"; FAIL=$((FAIL+1)); }
code() { curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" "$BASE$1"; }
csrf() { curl -s -b "$JAR" -c "$JAR" "$BASE$1" | grep -o 'name="_csrf" value="[a-f0-9]*"' | head -1 | sed 's/.*value="//;s/"//'; }
post() { local url=$1 from=$2; shift 2; local t; t=$(csrf "$from");
         curl -s -o /dev/null -w '%{http_code} %{redirect_url}' -b "$JAR" -c "$JAR" -X POST "$BASE$url" --data-urlencode "_csrf=$t" "$@"; }
check() { [ "$2" = "$3" ] && ok "$1" || ko "$1 (obtenu : $2, attendu : $3)"; }
contains() { curl -s -b "$JAR" -c "$JAR" "$BASE$2" | grep -q "$3" && ok "$1" || ko "$1"; }
reset() { rm -f "$JAR"; JAR=$(mktemp); }

echo "== Pages publiques"
for p in / /menus /menus/1 /contact /connexion /inscription /mentions-legales /cgv /confidentialite; do check "GET $p" "$(code $p)" 200; done
check "Menu inexistant → 404" "$(code /menus/9999)" 404
contains "Avis validés sur l'accueil" / "Ils nous ont fait confiance"
contains "Horaires dans le pied de page" / "Dimanche"
contains "API filtres (thème Noël)" "/api/menus?theme=1" '"count":2'
contains "API filtres (prix max 200 €)" "/api/menus?prix_max=200" '"count":3'

echo "== Sécurité"
check "POST sans jeton CSRF → 419" "$(curl -s -o /dev/null -w '%{http_code}' -X POST "$BASE/contact" -d titre=x)" 419
check "Commander sans être connecté → connexion" "$(curl -s -o /dev/null -w '%{redirect_url}' "$BASE/commander/5" | sed "s|$BASE||")" /connexion
curl -sI "$BASE/" | grep -qi "content-security-policy" && ok "En-tête CSP présent" || ko "En-tête CSP présent"

echo "== Inscription et connexion"
EMAIL="test$(date +%s)@exemple.fr"
r=$(post /inscription /inscription --data-urlencode nom=Test --data-urlencode prenom=Alice --data-urlencode "email=$EMAIL" --data-urlencode "telephone=0611223344" --data-urlencode "adresse_postale=1 rue Test" --data-urlencode code_postal=33000 --data-urlencode ville=Bordeaux --data-urlencode password=faible --data-urlencode password_confirmation=faible --data-urlencode rgpd=1)
check "Mot de passe faible refusé" "${r#* }" "$BASE/inscription"
r=$(post /inscription /inscription --data-urlencode nom=Test --data-urlencode prenom=Alice --data-urlencode "email=$EMAIL" --data-urlencode "telephone=0611223344" --data-urlencode "adresse_postale=1 rue Test" --data-urlencode code_postal=33000 --data-urlencode ville=Bordeaux --data-urlencode 'password=Alice#Test2026' --data-urlencode 'password_confirmation=Alice#Test2026' --data-urlencode rgpd=1)
check "Inscription valide" "${r#* }" "$BASE/mon-compte"
reset
r=$(post /connexion /connexion --data-urlencode email=client@exemple.fr --data-urlencode password=mauvais)
check "Mauvais mot de passe refusé" "${r#* }" "$BASE/connexion"
r=$(post /connexion /connexion --data-urlencode email=thomas.ancien@viteetgourmand.fr --data-urlencode 'password=Julie#Employe2026')
check "Compte employé désactivé refusé" "${r#* }" "$BASE/connexion"

echo "== Parcours client"
reset
r=$(post /connexion /connexion --data-urlencode email=client@exemple.fr --data-urlencode 'password=Client#Demo2026')
check "Connexion client" "${r#* }" "$BASE/mon-compte"
contains "Livraison Pessac calculée" "/api/livraison?adresse=3+allee&code_postal=33600&ville=Pessac" '"bordeaux":false'
DATE=$(date -d '+40 days' +%F 2>/dev/null || date -v+40d +%F)
FIELDS=(--data-urlencode menu_id=5 --data-urlencode "date_prestation=$DATE" --data-urlencode heure_livraison=19:30 --data-urlencode "adresse_prestation=8 cours de l'Intendance" --data-urlencode code_postal_prestation=33000 --data-urlencode ville_prestation=Bordeaux --data-urlencode nombre_personne=10)
t=$(csrf /commander/5)
curl -s -b "$JAR" -c "$JAR" -X POST "$BASE/commander/recapitulatif" --data-urlencode "_csrf=$t" "${FIELDS[@]}" | grep -q "342,00" && ok "Récapitulatif : 10 × 38 € − 10 % = 342,00 €" || ko "Récapitulatif du prix"
r=$(post /commander/confirmer /mon-compte --data-urlencode accept_conditions=1)
case "${r#* }" in "$BASE/mon-compte/commandes/"*) ok "Commande validée";; *) ko "Commande validée ($r)";; esac
ORDER=${r##*/}
check "Accès à la commande d'un autre client → 404" "$(code /mon-compte/commandes/2)" 404
check "Client sur l'espace employé → 403" "$(code /gestion)" 403

echo "== Parcours employé"
reset
post /connexion /connexion --data-urlencode email=julie@viteetgourmand.fr --data-urlencode 'password=Julie#Employe2026' >/dev/null
check "Espace employé" "$(code /gestion)" 200
check "Employé sur l'administration → 403" "$(code /admin)" 403
r=$(post "/gestion/commandes/$ORDER/statut" "/gestion/commandes/$ORDER" --data-urlencode statut=livre)
contains "Saut de statut refusé" "/gestion/commandes/$ORDER" "pas autorisé"
for s in accepte en_preparation en_livraison livre terminee; do post "/gestion/commandes/$ORDER/statut" "/gestion/commandes/$ORDER" --data-urlencode statut=$s >/dev/null; done
contains "Commande passée à « Terminée »" "/gestion/commandes/$ORDER" "Cette commande est clôturée"
contains "Filtre des commandes par client" "/gestion/commandes?client=durand" "marie.durand@exemple.fr"

echo "== Parcours administrateur (statistiques MongoDB)"
reset
post /connexion /connexion --data-urlencode email=jose@viteetgourmand.fr --data-urlencode 'password=Jose#Admin2026' >/dev/null
check "Page statistiques" "$(code /admin)" 200
curl -s -b "$JAR" "$BASE/admin/api/stats?menu=5" | grep -q '"chiffre_affaires":722' && ok "CA MongoDB menu bordelais = 228 + 152 + 342 = 722 €" || ko "CA MongoDB menu bordelais ($(curl -s -b "$JAR" "$BASE/admin/api/stats?menu=5"))"
check "Gestion des employés" "$(code /admin/employes)" 200

echo "== E-mails (Mailpit)"
if curl -s "$MAILPIT/api/v1/messages" >/dev/null 2>&1; then
  M=$(curl -s "$MAILPIT/api/v1/messages?limit=50")
  echo "$M" | grep -q "Bienvenue" && ok "E-mail de bienvenue" || ko "E-mail de bienvenue"
  echo "$M" | grep -q "Confirmation de votre commande" && ok "E-mail de confirmation de commande" || ko "E-mail de confirmation"
  echo "$M" | grep -q "Votre avis nous intéresse" && ok "E-mail d'invitation à l'avis" || ko "E-mail d'invitation à l'avis"
else
  echo "  (Mailpit non joignable : vérification des e-mails ignorée)"
fi

echo
echo "Résultat : $PASS réussis, $FAIL échoués"
rm -f "$JAR"
[ "$FAIL" -eq 0 ]
