<?php
/** @var array $stats */
/** @var array $menus */
/** @var array $filters */
/** @var string|null $error */
/** @var array $totals */
?>
<div class="container py-5">
    <h1 class="mb-1">Statistiques</h1>
    <p class="text-muted mb-4">Données issues de la base NoSQL MongoDB. Les commandes annulées ne sont pas comptabilisées.</p>
    <?php require BASE_PATH . '/views/gestion/_nav.php'; ?>

    <form method="get" action="/admin" id="stats-filters" class="filters p-3 mb-4 row g-3 align-items-end" aria-label="Filtrer les statistiques">
        <div class="col-md-4">
            <label for="menu" class="form-label">Menu</label>
            <select id="menu" name="menu" class="form-select">
                <option value="">Tous les menus</option>
                <?php foreach ($menus as $m): ?>
                    <option value="<?= (int) $m['menu_id'] ?>" <?= $filters['menu'] === (int) $m['menu_id'] ? 'selected' : '' ?>><?= e($m['titre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label for="du" class="form-label">Commandes du</label>
            <input type="date" id="du" name="du" class="form-control" value="<?= e($filters['du']) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label for="au" class="form-label">au</label>
            <input type="date" id="au" name="au" class="form-control" value="<?= e($filters['au']) ?>">
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
            <a href="/admin" class="btn btn-outline-secondary" aria-label="Réinitialiser les filtres">×</a>
        </div>
    </form>

    <?php if ($error): ?>
        <p class="alert alert-danger"><?= e($error) ?></p>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="stat-tile p-3"><span class="value d-block" id="total-commandes"><?= (int) $totals['commandes'] ?></span><span class="small">Commandes</span></div></div>
        <div class="col-md-4"><div class="stat-tile p-3"><span class="value d-block" id="total-ca"><?= price($totals['ca']) ?></span><span class="small">Chiffre d'affaires menus (remises déduites, hors livraison)</span></div></div>
        <div class="col-md-4"><div class="stat-tile p-3"><span class="value d-block"><?= $totals['commandes'] > 0 ? price($totals['ca'] / $totals['commandes']) : '—' ?></span><span class="small">Panier moyen</span></div></div>
    </div>

    <section class="card border-0 shadow-sm p-4 mb-4" aria-labelledby="chart-title">
        <h2 id="chart-title" class="h5">Nombre de commandes par menu</h2>
        <div class="chart-box">
            <canvas id="orders-chart" role="img" aria-label="Graphique en barres du nombre de commandes et du chiffre d'affaires par menu. Les valeurs détaillées figurent dans le tableau ci-dessous."
                    data-stats="<?= e(json_encode($stats, JSON_UNESCAPED_UNICODE)) ?>"></canvas>
        </div>
    </section>

    <section class="card border-0 shadow-sm p-4" aria-labelledby="ca-title">
        <h2 id="ca-title" class="h5">Chiffre d'affaires par menu</h2>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <caption class="visually-hidden">Commandes et chiffre d'affaires par menu</caption>
                <thead><tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">Chiffre d'affaires</th><th scope="col" class="text-end">Panier moyen</th><th scope="col" class="text-end">Encaissé (avec livraison)</th></tr></thead>
                <tbody>
                <?php foreach ($stats as $s): ?>
                    <tr>
                        <th scope="row" class="fw-normal"><?= e($s['menu_titre']) ?></th>
                        <td class="text-end"><?= (int) $s['nb_commandes'] ?></td>
                        <td class="text-end"><?= price($s['chiffre_affaires']) ?></td>
                        <td class="text-end"><?= price($s['panier_moyen']) ?></td>
                        <td class="text-end"><?= price($s['total_encaisse']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$stats): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Aucune commande sur cette période.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
