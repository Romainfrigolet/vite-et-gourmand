<?php

declare(strict_types=1);

namespace App\Controllers\Gestion;

use App\Repositories\AvisRepository;
use App\Repositories\CommandeRepository;

final class DashboardController
{
    public function index(): void
    {
        view('gestion/dashboard', [
            'title'      => 'Espace employé',
            'counts'     => CommandeRepository::countByStatus(),
            'avisCount'  => AvisRepository::countPending(),
            'aTraiter'   => array_slice(CommandeRepository::search('en_attente', null), 0, 10),
        ]);
    }
}
