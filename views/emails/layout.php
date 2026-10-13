<?php /** @var string $content */ /** @var string $subject */ ?><!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title><?= e($subject) ?></title></head>
<body style="margin:0;padding:0;background:#FBF7F2;font-family:Arial,Helvetica,sans-serif;color:#2B2118;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FBF7F2;padding:24px 0;">
    <tr><td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;">
            <tr><td style="background:#7B1E3A;color:#ffffff;padding:20px 28px;font-size:22px;font-weight:bold;">Vite &amp; Gourmand</td></tr>
            <tr><td style="padding:28px;font-size:15px;line-height:1.6;"><?= $content ?></td></tr>
            <tr><td style="background:#F1E6DA;padding:16px 28px;font-size:12px;color:#5F5A55;">
                Vite &amp; Gourmand — 12 rue Sainte-Catherine, 33000 Bordeaux — 05 56 00 00 00<br>
                Cet e-mail vous est envoyé automatiquement, merci de ne pas y répondre directement.
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
