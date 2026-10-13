<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Mailer;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\ContactRepository;

final class ContactController
{
    public function show(): void
    {
        view('pages/contact', ['title' => 'Nous contacter']);
    }

    public function send(): void
    {
        $data = [
            'titre'       => (string) input('titre', ''),
            'description' => (string) input('description', ''),
            'email'       => (string) input('email', ''),
        ];

        // Champ piège invisible : rempli uniquement par les robots de spam
        if (!empty($_POST['website'])) {
            Session::flash('success', 'Merci, votre message a bien été envoyé.');
            redirect('/contact');
        }

        $v = Validator::make($data)
            ->required('titre', 'Titre')->max('titre', 150, 'Titre')
            ->required('description', 'Description')->max('description', 3000, 'Description')
            ->required('email', 'E-mail')->email('email')->max('email', 180, 'E-mail');

        if ($v->fails()) {
            Session::withInput($data, $v->errors());
            redirect('/contact');
        }

        ContactRepository::create($data['titre'], $data['description'], $data['email']);
        Mailer::send(
            (string) Env::get('MAIL_CONTACT', 'contact@viteetgourmand.fr'),
            'Nouveau message de contact : ' . $data['titre'],
            'contact',
            $data
        );

        Session::flash('success', 'Merci, votre message a bien été envoyé. Nous vous répondrons rapidement par e-mail.');
        redirect('/contact');
    }
}
