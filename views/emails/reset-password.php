<?php /** @var array $user */ /** @var string $link */ ?>
<p>Bonjour <?= e($user['prenom']) ?>,</p>
<p>Vous avez demandé à réinitialiser le mot de passe de votre compte Vite &amp; Gourmand.</p>
<p><a href="<?= e($link) ?>" style="background:#7B1E3A;color:#ffffff;padding:10px 18px;border-radius:6px;text-decoration:none;display:inline-block;">Choisir un nouveau mot de passe</a></p>
<p>Ce lien est valable <strong>1 heure</strong> et ne peut être utilisé qu'une seule fois.</p>
<p>Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet e-mail : votre mot de passe actuel reste inchangé.</p>
