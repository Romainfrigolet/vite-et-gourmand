<?php

declare(strict_types=1);

namespace App\Controllers;

final class PageController
{
    public function mentions(): void
    {
        view('pages/mentions-legales', ['title' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        view('pages/cgv', ['title' => 'Conditions générales de vente']);
    }

    public function confidentialite(): void
    {
        view('pages/confidentialite', ['title' => 'Politique de confidentialité']);
    }
}
