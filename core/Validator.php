<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    public function validate(array $data, array $rules, array $messages = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $ruleList = is_array($ruleSet) ? $ruleSet : explode('|', (string) $ruleSet);

            foreach ($ruleList as $rule) {
                $rule = (string) $rule;
                if ($rule === '') {
                    continue;
                }

                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $name = trim($name);
                $arg = $arg !== null ? trim($arg) : null;

                if ($name === 'required' && $this->isEmpty($value)) {
                    $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Ce champ est obligatoire.');
                    break;
                }

                if ($this->isEmpty($value)) {
                    continue;
                }

                if ($name === 'max' && $arg !== null && mb_strlen((string) $value) > (int) $arg) {
                    $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Ce champ est trop long.');
                    break;
                }

                if ($name === 'min' && $arg !== null && mb_strlen((string) $value) < (int) $arg) {
                    $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Ce champ est trop court.');
                    break;
                }

                if ($name === 'email' && filter_var((string) $value, FILTER_VALIDATE_EMAIL) === false) {
                    $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Email invalide.');
                    break;
                }

                if ($name === 'in' && $arg !== null) {
                    $allowed = array_map('trim', explode(',', $arg));
                    if (!in_array((string) $value, $allowed, true)) {
                        $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Valeur invalide.');
                        break;
                    }
                }

                if ($name === 'regex' && $arg !== null && @preg_match($arg, '') !== false) {
                    if (preg_match($arg, (string) $value) !== 1) {
                        $errors[$field] = $this->resolveMessage($field, $name, $messages, 'Format invalide.');
                        break;
                    }
                }
            }
        }

        return $errors;
    }

    private function resolveMessage(string $field, string $rule, array $messages, string $fallback): string
    {
        if (isset($messages[$field . '.' . $rule])) {
            return (string) $messages[$field . '.' . $rule];
        }
        if (isset($messages[$field])) {
            return (string) $messages[$field];
        }
        return $fallback;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }
        return trim((string) $value) === '';
    }
}
