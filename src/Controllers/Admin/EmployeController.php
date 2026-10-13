<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserRepository;

/**
 * Gestion des comptes employés par l'administrateur.
 * - Création d'un compte « employe » uniquement (aucun compte administrateur ne peut être créé depuis l'application)
 * - Le mot de passe n'est jamais envoyé par e-mail : l'employé doit le demander à l'administrateur
 * - Désactivation / réactivation d'un compte (départ de l'entreprise)
 */
final class EmployeController
{
    public function index(): void
    {
        view('admin/employes', ['title' => 'Employés', 'employes' => UserRepository::employees()]);
    }

    public function store(): void
    {
        $data = [
            'prenom'                => (string) input('prenom', ''),
            'nom'                   => (string) input('nom', ''),
            'email'                 => mb_strtolower((string) input('email', '')),
            'password'              => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];

        $v = Validator::make($data)
            ->required('prenom', 'Prénom')->max('prenom', 80, 'Prénom')
            ->required('nom', 'Nom')->max('nom', 80, 'Nom')
            ->required('email', 'E-mail')->email('email')->max('email', 180, 'E-mail')
            ->required('password', 'Mot de passe')->strongPassword('password')
            ->confirmed('password', 'password_confirmation');
        if (!$v->fails() && UserRepository::emailExists($data['email'])) {
            $v->addError('email', 'Un compte existe déjà avec cette adresse e-mail.');
        }
        if ($v->fails()) {
            Session::withInput($data, $v->errors());
            redirect('/admin/employes');
        }

        $id = UserRepository::createEmployee($data);
        $employe = UserRepository::findById($id);
        Mailer::send($employe['email'], 'Votre compte employé Vite & Gourmand', 'employee-created', ['employe' => $employe]);

        Session::flash('success', 'Compte employé créé pour ' . $employe['prenom'] . ' ' . $employe['nom'] . '. Un e-mail l\'a informé (sans le mot de passe, à lui communiquer de vive voix).');
        redirect('/admin/employes');
    }

    public function toggle(int $id): void
    {
        $employe = UserRepository::findById($id);
        if (!$employe || $employe['role'] !== 'employe') {
            abort(404);
        }
        $activate = (int) $employe['actif'] !== 1;
        UserRepository::setActive($id, $activate);
        Session::flash('success', sprintf(
            'Le compte de %s %s a été %s.',
            $employe['prenom'],
            $employe['nom'],
            $activate ? 'réactivé' : 'désactivé : il ne peut plus se connecter'
        ));
        redirect('/admin/employes');
    }
}
