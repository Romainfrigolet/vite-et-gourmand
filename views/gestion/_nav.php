<?php use App\Core\Auth; ?>
<nav class="admin-nav mb-4" aria-label="Navigation de l'espace de gestion">
    <ul class="nav nav-pills flex-wrap gap-1">
        <li class="nav-item"><a class="nav-link<?= is_current('/gestion') && !is_current('/gestion/') ? ' active" aria-current="page' : '' ?>" href="/gestion">Tableau de bord</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion/commandes') ?>" href="/gestion/commandes">Commandes</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion/menus') ?>" href="/gestion/menus">Menus</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion/plats') ?>" href="/gestion/plats">Plats</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion/horaires') ?>" href="/gestion/horaires">Horaires</a></li>
        <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion/avis') ?>" href="/gestion/avis">Avis</a></li>
        <?php if (Auth::isAdmin()): ?>
            <li class="nav-item"><a class="nav-link<?= nav_attr('/admin/employes') ?>" href="/admin/employes">Employés</a></li>
            <li class="nav-item"><a class="nav-link<?= is_current('/admin') && !is_current('/admin/employes') ? ' active" aria-current="page' : '' ?>" href="/admin">Statistiques</a></li>
        <?php endif; ?>
    </ul>
</nav>
