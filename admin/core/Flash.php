<?php
/**
 * Flash Messages Class
 * Displays one-time messages to users (success, error, warning, info)
 */

class Flash
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';

    /**
     * Add a flash message
     */
    public static function add(string $type, string $message): void
    {
        Session::start();

        if (!isset($_SESSION['_flash_messages'])) {
            $_SESSION['_flash_messages'] = [];
        }

        $_SESSION['_flash_messages'][] = [
            'type' => $type,
            'message' => $message,
        ];
    }

    /**
     * Add a success message
     */
    public static function success(string $message): void
    {
        self::add(self::SUCCESS, $message);
    }

    /**
     * Add an error message
     */
    public static function error(string $message): void
    {
        self::add(self::ERROR, $message);
    }

    /**
     * Add a warning message
     */
    public static function warning(string $message): void
    {
        self::add(self::WARNING, $message);
    }

    /**
     * Add an info message
     */
    public static function info(string $message): void
    {
        self::add(self::INFO, $message);
    }

    /**
     * Get all flash messages and clear them
     */
    public static function get(): array
    {
        Session::start();

        $messages = $_SESSION['_flash_messages'] ?? [];
        unset($_SESSION['_flash_messages']);

        return $messages;
    }

    /**
     * Check if there are any flash messages
     */
    public static function has(): bool
    {
        Session::start();
        return !empty($_SESSION['_flash_messages']);
    }

    /**
     * Get messages by type
     */
    public static function getByType(string $type): array
    {
        $allMessages = self::get();

        return array_filter($allMessages, function ($msg) use ($type) {
            return $msg['type'] === $type;
        });
    }

    /**
     * Render flash messages as HTML
     */
    public static function render(): string
    {
        $messages = self::get();

        if (empty($messages)) {
            return '';
        }

        $html = '<div class="flash-messages">';

        foreach ($messages as $msg) {
            $type = htmlspecialchars($msg['type']);
            $message = htmlspecialchars($msg['message']);

            $html .= sprintf(
                '<div class="alert alert-%s">
                    <span class="alert-message">%s</span>
                    <button type="button" class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>',
                $type,
                $message
            );
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Store form errors (for validation)
     */
    public static function setErrors(array $errors): void
    {
        Session::start();
        $_SESSION['_form_errors'] = $errors;
    }

    /**
     * Get form errors
     */
    public static function getErrors(): array
    {
        Session::start();
        $errors = $_SESSION['_form_errors'] ?? [];
        unset($_SESSION['_form_errors']);
        return $errors;
    }

    /**
     * Alias for getErrors()
     */
    public static function errors(): array
    {
        return self::getErrors();
    }

    /**
     * Check if there are form errors
     */
    public static function hasErrors(): bool
    {
        Session::start();
        return !empty($_SESSION['_form_errors']);
    }

    /**
     * Store old input (for form repopulation)
     */
    public static function setOldInput(array $input): void
    {
        Session::start();
        // Remove sensitive fields
        unset($input['password'], $input['password_confirmation'], $input['_token']);
        $_SESSION['_old_input'] = $input;
    }

    /**
     * Get old input value
     */
    public static function old(string $key, $default = '')
    {
        Session::start();
        $value = $_SESSION['_old_input'][$key] ?? $default;
        return $value;
    }

    /**
     * Get all old input values and clear them
     */
    public static function oldInput(): array
    {
        Session::start();
        $input = $_SESSION['_old_input'] ?? [];
        unset($_SESSION['_old_input']);
        return $input;
    }

    /**
     * Clear old input
     */
    public static function clearOldInput(): void
    {
        Session::start();
        unset($_SESSION['_old_input']);
    }
}
