<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\AvisRepository;
use App\Repositories\MenuRepository;

final class HomeController
{
    public function index(): void
    {
        view('home/index', [
            'title'      => 'Traiteur à Bordeaux depuis 25 ans',
            'avis'       => AvisRepository::validated(6),
            'moyenne'    => AvisRepository::averageValidated(),
            'highlights' => array_slice(MenuRepository::search(), 0, 3),
        ]);
    }
}
