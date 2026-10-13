<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Validation côté serveur des données de formulaire.
 * La validation côté client (HTML5 / JavaScript) n'est qu'un confort :
 * seule celle-ci fait foi, car une requête peut être forgée sans passer par le navigateur.
 */
final class Validator
{
    private array $errors = [];

    public function __construct(private array $data)
    {
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    private function value(string $field): string
    {
        return trim((string) ($this->data[$field] ?? ''));
    }

    private function fail(string $field, string $message): void
    {
        $this->errors[$field] ??= $message;
    }

    public function required(string $field, string $label): self
    {
        if ($this->value($field) === '') {
            $this->fail($field, "Le champ « $label » est obligatoire.");
        }
        return $this;
    }

    public function max(string $field, int $max, string $label): self
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->fail($field, "Le champ « $label » ne doit pas dépasser $max caractères.");
        }
        return $this;
    }

    public function email(string $field): self
    {
        $v = $this->value($field);
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->fail($field, "L'adresse e-mail n'est pas valide.");
        }
        return $this;
    }

    public function phone(string $field): self
    {
        $v = preg_replace('/[\s.\-]/', '', $this->value($field));
        if ($v !== '' && !preg_match('/^(\+33|0)[1-9]\d{8}$/', $v)) {
            $this->fail($field, 'Le numéro de téléphone doit être un numéro français valide (ex. 06 12 34 56 78).');
        }
        return $this;
    }

    public function postalCode(string $field): self
    {
        $v = $this->value($field);
        if ($v !== '' && !preg_match('/^\d{5}$/', $v)) {
            $this->fail($field, 'Le code postal doit comporter 5 chiffres.');
        }
        return $this;
    }

    /** Politique de mot de passe imposée par le cahier des charges. */
    public function strongPassword(string $field): self
    {
        $v = (string) ($this->data[$field] ?? '');
        if (!self::isStrongPassword($v)) {
            $this->fail($field, 'Le mot de passe doit contenir au moins 10 caractères, dont une majuscule, une minuscule, un chiffre et un caractère spécial.');
        }
        return $this;
    }

    public static function isStrongPassword(string $v): bool
    {
        return mb_strlen($v) >= 10
            && preg_match('/[A-Z]/', $v)
            && preg_match('/[a-z]/', $v)
            && preg_match('/\d/', $v)
            && preg_match('/[^A-Za-z0-9]/', $v);
    }

    public function confirmed(string $field, string $confirmation): self
    {
        if (($this->data[$field] ?? '') !== ($this->data[$confirmation] ?? '')) {
            $this->fail($confirmation, 'Les deux mots de passe ne correspondent pas.');
        }
        return $this;
    }

    public function integer(string $field, string $label, ?int $min = null, ?int $max = null): self
    {
        $v = $this->value($field);
        if ($v === '') {
            return $this;
        }
        if (filter_var($v, FILTER_VALIDATE_INT) === false) {
            $this->fail($field, "Le champ « $label » doit être un nombre entier.");
            return $this;
        }
        if ($min !== null && (int) $v < $min) {
            $this->fail($field, "Le champ « $label » doit être supérieur ou égal à $min.");
        }
        if ($max !== null && (int) $v > $max) {
            $this->fail($field, "Le champ « $label » doit être inférieur ou égal à $max.");
        }
        return $this;
    }

    public function decimal(string $field, string $label, float $min = 0): self
    {
        $v = str_replace(',', '.', $this->value($field));
        if ($v !== '' && (!is_numeric($v) || (float) $v < $min)) {
            $this->fail($field, "Le champ « $label » doit être un nombre positif.");
        }
        return $this;
    }

    public function date(string $field, string $label): self
    {
        $v = $this->value($field);
        $d = \DateTime::createFromFormat('Y-m-d', $v);
        if ($v !== '' && (!$d || $d->format('Y-m-d') !== $v)) {
            $this->fail($field, "Le champ « $label » doit être une date valide.");
        }
        return $this;
    }

    public function time(string $field, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && !preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $v)) {
            $this->fail($field, "Le champ « $label » doit être une heure valide.");
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label): self
    {
        $v = $this->value($field);
        if ($v !== '' && !in_array($v, $allowed, true)) {
            $this->fail($field, "La valeur du champ « $label » n'est pas autorisée.");
        }
        return $this;
    }

    public function accepted(string $field, string $message): self
    {
        if (empty($this->data[$field])) {
            $this->fail($field, $message);
        }
        return $this;
    }

    public function addError(string $field, string $message): self
    {
        $this->fail($field, $message);
        return $this;
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
