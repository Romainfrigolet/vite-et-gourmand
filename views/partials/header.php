<?php use App\Core\Auth; ?>
<header class="site-header">
    <nav class="navbar navbar-expand-lg" aria-label="Navigation principale">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="<?= asset('assets/img/logo.svg') ?>" alt="" width="42" height="42">
                <span>Vite &amp; Gourmand</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                    aria-controls="mainNav" aria-expanded="false" aria-label="Ouvrir le menu de navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link<?= nav_attr('/') ?>" href="/">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link<?= nav_attr('/menus') ?>" href="/menus">Nos menus</a></li>
                    <li class="nav-item"><a class="nav-link<?= nav_attr('/contact') ?>" href="/contact">Contact</a></li>
                    <?php if (Auth::check()): ?>
                        <?php if (Auth::isStaff()): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_attr('/gestion') ?>" href="/gestion">Espace employé</a></li>
                        <?php endif; ?>
                        <?php if (Auth::isAdmin()): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_attr('/admin') ?>" href="/admin">Administration</a></li>
                        <?php endif; ?>
                        <?php if (Auth::isCustomer()): ?>
                            <li class="nav-item"><a class="nav-link<?= nav_attr('/mon-compte') ?>" href="/mon-compte">Mon espace</a></li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <form method="post" action="/deconnexion" class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-outline-primary btn-sm ms-lg-2">
                                    Se déconnecter<span class="visually-hidden"> (<?= e(Auth::user()['prenom']) ?>)</span>
                                </button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link<?= nav_attr('/inscription') ?>" href="/inscription">Créer un compte</a></li>
                        <li class="nav-item"><a class="btn btn-primary btn-sm ms-lg-2" href="/connexion">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
