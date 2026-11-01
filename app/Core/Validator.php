<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];
    /** @var array<string,mixed> */
    private array $data = [];

    public function __construct(array $data, private array $rules)
    {
        $this->data = $data;
    }

    public function fails(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $ruleString) {
            $rules = is_array($ruleString) ? $ruleString : explode('|', (string) $ruleString);
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $isNullable = in_array('nullable', $rules, true) && ($value === null || $value === '');

            if (in_array('required', $rules, true) && ($value === null || $value === '')) {
                $this->errors[$field] = $this->label($field) . ' is required.';
                continue;
            }

            if ($isNullable) {
                continue;
            }

            foreach ($rules as $rule) {
                if (in_array($rule, ['required', 'nullable'], true)) {
                    continue;
                }
                $message = $this->checkRule($field, $value, $rule);
                if ($message !== null) {
                    $this->errors[$field] = $message;
                    break;
                }
            }
        }

        return $this->errors !== [];
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors === [] ? null : (string) reset($this->errors);
    }

    /** @return array<string,mixed> */
    public function validated(): array
    {
        return array_intersect_key($this->data, $this->rules);
    }

    private function checkRule(string $field, mixed $value, string $rule): ?string
    {
        $label = $this->label($field);
        [$name, $parameter] = array_pad(explode(':', $rule, 2), 2, null);

        return match ($name) {
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : $label . ' must be a valid email address.',
            'url' => filter_var($value, FILTER_VALIDATE_URL) ? null : $label . ' must be a valid URL.',
            'numeric' => is_numeric($value) ? null : $label . ' must be a number.',
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false ? null : $label . ' must be a whole number.',
            'min' => mb_strlen((string) $value) >= (int) $parameter ? null : $label . ' must be at least ' . $parameter . ' characters.',
            'max' => mb_strlen((string) $value) <= (int) $parameter ? null : $label . ' may not be longer than ' . $parameter . ' characters.',
            'between' => (function () use ($value, $parameter, $label) {
                [$min, $max] = array_pad(explode(',', (string) $parameter), 2, null);
                $length = mb_strlen((string) $value);
                return ($length >= (int) $min && $length <= (int) $max)
                    ? null
                    : sprintf('%s must be between %s and %s characters.', $label, $min, $max);
            })(),
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null) ? null : $label . ' confirmation does not match.',
            'alpha_dash' => preg_match('/^[A-Za-z0-9_\-\.]+$/', (string) $value) ? null : $label . ' may only contain letters, numbers, dots, dashes and underscores.',
            'in' => in_array((string) $value, explode(',', (string) $parameter), true) ? null : $label . ' is invalid.',
            'regex' => preg_match((string) $parameter, (string) $value) ? null : $label . ' format is invalid.',
            'date' => strtotime((string) $value) !== false ? null : $label . ' must be a valid date.',
            'unique' => $this->checkUnique($value, (string) $parameter, $label),
            'strong_password' => $this->checkStrongPassword((string) $value, $label),
            default => null,
        };
    }

    private function checkUnique(mixed $value, string $parameter, string $label): ?string
    {
        // unique:table,column,ignoreId
        [$table, $column, $ignoreId] = array_pad(explode(',', $parameter), 3, null);
        if (!$table || !$column) {
            return null;
        }
        $sql = sprintf('SELECT COUNT(*) FROM `%s` WHERE `%s` = ?', preg_replace('/[^a-z0-9_]/i', '', $table), preg_replace('/[^a-z0-9_]/i', '', $column));
        $params = [$value];
        if ($ignoreId !== null && $ignoreId !== '') {
            $sql .= ' AND id <> ?';
            $params[] = (int) $ignoreId;
        }
        $count = (int) Database::value($sql, $params, 0);
        return $count === 0 ? null : $label . ' is already taken.';
    }

    private function checkStrongPassword(string $value, string $label): ?string
    {
        if (mb_strlen($value) < 8) {
            return $label . ' must be at least 8 characters long.';
        }
        if (!preg_match('/[A-Za-z]/', $value) || !preg_match('/\d/', $value)) {
            return $label . ' must include both letters and numbers.';
        }
        return null;
    }

    private function label(string $field): string
    {
        $label = str_replace(['_', '-'], ' ', $field);
        return ucfirst(trim($label));
    }
}
