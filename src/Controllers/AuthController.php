<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\RateLimiter;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserRepository;

/**
 * Inscription, connexion, déconnexion et réinitialisation du mot de passe.
 */
final class AuthController
{
    // --- Inscription ------------------------------------------------------------------------

    public function registerForm(): void
    {
        view('auth/register', ['title' => 'Créer un compte']);
    }

    public function register(): void
    {
        $data = $this->profileInput();
        $data['password'] = (string) ($_POST['password'] ?? '');
        $data['password_confirmation'] = (string) ($_POST['password_confirmation'] ?? '');
        $data['rgpd'] = $_POST['rgpd'] ?? '';

        $v = self::profileValidator($data)
            ->required('password', 'Mot de passe')->strongPassword('password')
            ->confirmed('password', 'password_confirmation')
            ->accepted('rgpd', 'Vous devez accepter la politique de confidentialité pour créer un compte.');

        if (!$v->fails() && UserRepository::emailExists($data['email'])) {
            $v->addError('email', 'Un compte existe déjà avec cette adresse e-mail.');
        }

        if ($v->fails()) {
            Session::withInput($data, $v->errors());
            redirect('/inscription');
        }

        $id = UserRepository::createCustomer($data);
        $user = UserRepository::findById($id);

        Mailer::send($user['email'], 'Bienvenue chez Vite & Gourmand !', 'welcome', ['user' => $user]);

        Auth::login($user);
        Session::flash('success', 'Bienvenue ' . $user['prenom'] . ' ! Votre compte a bien été créé. Un e-mail de bienvenue vient de vous être envoyé.');
        $this->redirectAfterLogin();
    }

    // --- Connexion ---------------------------------------------------------------------------

    public function loginForm(): void
    {
        view('auth/login', ['title' => 'Connexion']);
    }

    public function login(): void
    {
        $email = mb_strtolower((string) input('email', ''));
        $password = (string) ($_POST['password'] ?? '');

        if (RateLimiter::tooManyAttempts($email)) {
            Session::withInput(['email' => $email], ['email' => 'Trop de tentatives de connexion. Veuillez patienter 15 minutes avant de réessayer.']);
            redirect('/connexion');
        }

        $user = UserRepository::findByEmail($email);

        // Vérification à temps comparable que l'utilisateur existe ou non (limite l'énumération des comptes)
        $valid = $user !== null
            ? password_verify($password, $user['password'])
            : password_verify($password, '$2y$12$EqHUHtCraWfsQBaf1HiK2eO/8qYPMaqIUVKFwPr4mQh4pYfTZA33W');

        if (!$valid || (int) $user['actif'] !== 1) {
            RateLimiter::hit($email);
            // Message volontairement générique : on ne révèle pas si l'e-mail existe
            Session::withInput(['email' => $email], ['email' => 'Identifiants incorrects ou compte désactivé.']);
            redirect('/connexion');
        }

        RateLimiter::clear($email);
        UserRepository::rehashIfNeeded($user, $password);
        Auth::login($user);
        Session::flash('success', 'Bonjour ' . $user['prenom'] . ', vous êtes connecté.');
        $this->redirectAfterLogin();
    }

    public function logout(): void
    {
        Auth::logout();
        Session::start();
        Session::flash('info', 'Vous êtes déconnecté. À bientôt !');
        redirect('/');
    }

    // --- Mot de passe oublié -------------------------------------------------------------------

    public function forgotForm(): void
    {
        view('auth/forgot', ['title' => 'Mot de passe oublié']);
    }

    public function forgot(): void
    {
        $email = mb_strtolower((string) input('email', ''));
        $v = Validator::make(['email' => $email])->required('email', 'E-mail')->email('email');
        if ($v->fails()) {
            Session::withInput(['email' => $email], $v->errors());
            redirect('/mot-de-passe-oublie');
        }

        $user = UserRepository::findByEmail($email);
        if ($user && (int) $user['actif'] === 1) {
            $token = UserRepository::createResetToken((int) $user['utilisateur_id']);
            Mailer::send($user['email'], 'Réinitialisation de votre mot de passe', 'reset-password', [
                'user' => $user,
                'link' => url('/reinitialiser-mot-de-passe?token=' . $token),
            ]);
        }

        // Même message que le compte existe ou non (pas d'énumération des comptes)
        Session::flash('success', 'Si un compte correspond à cette adresse, un e-mail contenant un lien de réinitialisation vient d\'être envoyé. Le lien est valable 1 heure.');
        redirect('/connexion');
    }

    public function resetForm(): void
    {
        $token = (string) ($_GET['token'] ?? '');
        if (!UserRepository::findValidReset($token)) {
            Session::flash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.');
            redirect('/mot-de-passe-oublie');
        }
        view('auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token]);
    }

    public function reset(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $reset = UserRepository::findValidReset($token);
        if (!$reset) {
            Session::flash('error', 'Ce lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.');
            redirect('/mot-de-passe-oublie');
        }

        $data = [
            'password'              => (string) ($_POST['password'] ?? ''),
            'password_confirmation' => (string) ($_POST['password_confirmation'] ?? ''),
        ];
        $v = Validator::make($data)
            ->required('password', 'Mot de passe')->strongPassword('password')
            ->confirmed('password', 'password_confirmation');
        if ($v->fails()) {
            Session::withInput([], $v->errors());
            redirect('/reinitialiser-mot-de-passe?token=' . urlencode($token));
        }

        UserRepository::updatePassword((int) $reset['utilisateur_id'], $data['password']);
        UserRepository::consumeReset((int) $reset['reset_id']);

        Session::flash('success', 'Votre mot de passe a été modifié. Vous pouvez maintenant vous connecter.');
        redirect('/connexion');
    }

    // --- Outils partagés ----------------------------------------------------------------------

    public static function profileFields(): array
    {
        return ['nom', 'prenom', 'email', 'telephone', 'adresse_postale', 'code_postal', 'ville'];
    }

    private function profileInput(): array
    {
        $data = [];
        foreach (self::profileFields() as $field) {
            $data[$field] = (string) input($field, '');
        }
        return $data;
    }

    public static function profileValidator(array $data): Validator
    {
        return Validator::make($data)
            ->required('nom', 'Nom')->max('nom', 80, 'Nom')
            ->required('prenom', 'Prénom')->max('prenom', 80, 'Prénom')
            ->required('email', 'E-mail')->email('email')->max('email', 180, 'E-mail')
            ->required('telephone', 'Numéro de GSM')->phone('telephone')
            ->required('adresse_postale', 'Adresse postale')->max('adresse_postale', 255, 'Adresse postale')
            ->required('code_postal', 'Code postal')->postalCode('code_postal')
            ->required('ville', 'Ville')->max('ville', 100, 'Ville');
    }

    private function redirectAfterLogin(): never
    {
        $intended = (string) Session::get('_intended', '');
        Session::forget('_intended');
        // On n'accepte qu'un chemin interne (pas de redirection ouverte vers un site tiers)
        if ($intended !== '' && str_starts_with($intended, '/') && !str_starts_with($intended, '//')) {
            redirect($intended);
        }
        if (Auth::isAdmin()) {
            redirect('/admin');
        }
        if (Auth::isStaff()) {
            redirect('/gestion');
        }
        redirect('/mon-compte');
    }
}
