<?php
/**
 * CSRF Protection Class
 * Prevents Cross-Site Request Forgery attacks
 */

class CSRF
{
    const TOKEN_NAME = '_csrf_token';
    const TOKEN_LENGTH = 32;

    /**
     * Generate a new CSRF token
     */
    public static function generate(): string
    {
        Session::start();

        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));
        $_SESSION[self::TOKEN_NAME] = $token;
        $_SESSION['_csrf_time'] = time();

        return $token;
    }

    /**
     * Get the current CSRF token (generate if not exists)
     */
    public static function token(): string
    {
        Session::start();

        if (!isset($_SESSION[self::TOKEN_NAME])) {
            return self::generate();
        }

        // Regenerate token if older than 1 hour
        if (isset($_SESSION['_csrf_time']) && (time() - $_SESSION['_csrf_time']) > 3600) {
            return self::generate();
        }

        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Verify a CSRF token
     */
    public static function verify(?string $token): bool
    {
        Session::start();

        if (empty($token) || !isset($_SESSION[self::TOKEN_NAME])) {
            return false;
        }

        // Use timing-safe comparison
        $valid = hash_equals($_SESSION[self::TOKEN_NAME], $token);

        return $valid;
    }

    /**
     * Verify and regenerate token (for single-use tokens)
     */
    public static function verifyAndRegenerate(?string $token): bool
    {
        $valid = self::verify($token);

        if ($valid) {
            self::generate();
        }

        return $valid;
    }

    /**
     * Get the token from the request
     */
    public static function getFromRequest(): ?string
    {
        // Check POST data first
        if (isset($_POST['_token'])) {
            return $_POST['_token'];
        }

        // Check header (for AJAX requests)
        $headers = getallheaders();
        if (isset($headers['X-CSRF-Token'])) {
            return $headers['X-CSRF-Token'];
        }

        // Check alternative header names
        if (isset($headers['X-Csrf-Token'])) {
            return $headers['X-Csrf-Token'];
        }

        return null;
    }

    /**
     * Generate a hidden input field with the CSRF token
     */
    public static function field(): string
    {
        return sprintf(
            '<input type="hidden" name="_token" value="%s">',
            htmlspecialchars(self::token())
        );
    }

    /**
     * Generate a meta tag for AJAX requests
     */
    public static function meta(): string
    {
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars(self::token())
        );
    }

    /**
     * Middleware-style check - throws exception if invalid
     */
    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return; // GET requests don't need CSRF protection
        }

        $token = self::getFromRequest();

        if (!self::verify($token)) {
            http_response_code(403);
            throw new Exception('CSRF token validation failed');
        }
    }

    /**
     * Remove the CSRF token
     */
    public static function remove(): void
    {
        Session::start();
        unset($_SESSION[self::TOKEN_NAME], $_SESSION['_csrf_time']);
    }
}
