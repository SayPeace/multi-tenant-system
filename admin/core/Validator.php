<?php
/**
 * Input Validation Class
 * Validates and sanitizes user input
 */

class Validator
{
    private array $data = [];
    private array $rules = [];
    private array $errors = [];
    private array $messages = [];

    // Custom error messages
    private array $defaultMessages = [
        'required' => 'The :field field is required.',
        'email' => 'The :field must be a valid email address.',
        'min' => 'The :field must be at least :param characters.',
        'max' => 'The :field must not exceed :param characters.',
        'numeric' => 'The :field must be a number.',
        'integer' => 'The :field must be an integer.',
        'alpha' => 'The :field may only contain letters.',
        'alpha_num' => 'The :field may only contain letters and numbers.',
        'alpha_dash' => 'The :field may only contain letters, numbers, dashes, and underscores.',
        'url' => 'The :field must be a valid URL.',
        'date' => 'The :field must be a valid date.',
        'confirmed' => 'The :field confirmation does not match.',
        'in' => 'The :field must be one of: :param.',
        'not_in' => 'The :field must not be one of: :param.',
        'unique' => 'The :field has already been taken.',
        'exists' => 'The selected :field is invalid.',
        'regex' => 'The :field format is invalid.',
        'same' => 'The :field and :param must match.',
        'different' => 'The :field and :param must be different.',
        'between' => 'The :field must be between :param.',
        'password' => 'The :field does not meet the password requirements.',
    ];

    /**
     * Create a new validator instance
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->messages = array_merge($this->defaultMessages, $messages);
    }

    /**
     * Static factory method
     */
    public static function make(array $data, array $rules, array $messages = []): self
    {
        return new self($data, $rules, $messages);
    }

    /**
     * Run validation
     */
    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $rules) {
            $rules = is_string($rules) ? explode('|', $rules) : $rules;
            $value = $this->getValue($field);

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return $this->validate();
    }

    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !$this->validate();
    }

    /**
     * Get validation errors
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Get first error for a field
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get validated data
     */
    public function validated(): array
    {
        $validated = [];
        foreach (array_keys($this->rules) as $field) {
            if (!isset($this->errors[$field])) {
                $validated[$field] = $this->getValue($field);
            }
        }
        return $validated;
    }

    /**
     * Get a value from the data array (supports dot notation)
     */
    private function getValue(string $field)
    {
        $keys = explode('.', $field);
        $value = $this->data;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return null;
            }
            $value = $value[$key];
        }

        return $value;
    }

    /**
     * Apply a validation rule
     */
    private function applyRule(string $field, $value, string $rule): void
    {
        // Parse rule and parameter
        $parts = explode(':', $rule, 2);
        $ruleName = $parts[0];
        $param = $parts[1] ?? null;

        // Skip validation if field is empty and not required
        if ($ruleName !== 'required' && ($value === null || $value === '')) {
            return;
        }

        $method = 'validate' . str_replace('_', '', ucwords($ruleName, '_'));

        if (method_exists($this, $method)) {
            $isValid = $this->$method($value, $param, $field);

            if (!$isValid) {
                $this->addError($field, $ruleName, $param);
            }
        }
    }

    /**
     * Add an error message
     */
    private function addError(string $field, string $rule, ?string $param = null): void
    {
        $message = $this->messages[$rule] ?? "The $field is invalid.";
        $message = str_replace(':field', str_replace('_', ' ', $field), $message);
        $message = str_replace(':param', $param ?? '', $message);

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    // Validation Rules

    private function validateRequired($value): bool
    {
        if (is_null($value)) return false;
        if (is_string($value) && trim($value) === '') return false;
        if (is_array($value) && empty($value)) return false;
        return true;
    }

    private function validateEmail($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateMin($value, $param): bool
    {
        if (is_string($value)) {
            return strlen($value) >= (int) $param;
        }
        if (is_numeric($value)) {
            return $value >= (float) $param;
        }
        if (is_array($value)) {
            return count($value) >= (int) $param;
        }
        return false;
    }

    private function validateMax($value, $param): bool
    {
        if (is_string($value)) {
            return strlen($value) <= (int) $param;
        }
        if (is_numeric($value)) {
            return $value <= (float) $param;
        }
        if (is_array($value)) {
            return count($value) <= (int) $param;
        }
        return false;
    }

    private function validateNumeric($value): bool
    {
        return is_numeric($value);
    }

    private function validateInteger($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    private function validateAlpha($value): bool
    {
        return preg_match('/^[\pL\pM]+$/u', $value);
    }

    private function validateAlphaNum($value): bool
    {
        return preg_match('/^[\pL\pM\pN]+$/u', $value);
    }

    private function validateAlphaDash($value): bool
    {
        return preg_match('/^[\pL\pM\pN_-]+$/u', $value);
    }

    private function validateUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    private function validateDate($value): bool
    {
        return strtotime($value) !== false;
    }

    private function validateConfirmed($value, $param, $field): bool
    {
        $confirmationField = $field . '_confirmation';
        return $value === ($this->data[$confirmationField] ?? null);
    }

    private function validateIn($value, $param): bool
    {
        $allowed = explode(',', $param);
        return in_array($value, $allowed, true);
    }

    private function validateNotIn($value, $param): bool
    {
        $disallowed = explode(',', $param);
        return !in_array($value, $disallowed, true);
    }

    private function validateRegex($value, $param): bool
    {
        return preg_match($param, $value) === 1;
    }

    private function validateSame($value, $param): bool
    {
        return $value === ($this->data[$param] ?? null);
    }

    private function validateDifferent($value, $param): bool
    {
        return $value !== ($this->data[$param] ?? null);
    }

    private function validateBetween($value, $param): bool
    {
        list($min, $max) = explode(',', $param);
        $size = is_string($value) ? strlen($value) : (is_numeric($value) ? $value : count($value));
        return $size >= (float) $min && $size <= (float) $max;
    }

    private function validatePassword($value): bool
    {
        $config = require __DIR__ . '/../config/admin.php';
        $rules = $config['password'] ?? [];

        if (strlen($value) < ($rules['min_length'] ?? 8)) {
            return false;
        }

        if (($rules['require_uppercase'] ?? false) && !preg_match('/[A-Z]/', $value)) {
            return false;
        }

        if (($rules['require_lowercase'] ?? false) && !preg_match('/[a-z]/', $value)) {
            return false;
        }

        if (($rules['require_number'] ?? false) && !preg_match('/[0-9]/', $value)) {
            return false;
        }

        if (($rules['require_special'] ?? false) && !preg_match('/[^A-Za-z0-9]/', $value)) {
            return false;
        }

        return true;
    }

    // Sanitization helpers

    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeEmail(string $value): string
    {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeInt($value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function sanitizeFloat($value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function sanitizeUrl(string $value): string
    {
        return filter_var(trim($value), FILTER_SANITIZE_URL);
    }
}
