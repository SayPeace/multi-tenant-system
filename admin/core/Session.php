<?php
/**
 * Session Management Class
 * Handles session operations for the admin dashboard
 */

class Session
{
    private static bool $started = false;

    /**
     * Start the session with secure settings
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $config = require __DIR__ . '/../config/admin.php';
        $sessionConfig = $config['session'] ?? [];

        // Set session name
        session_name($sessionConfig['name'] ?? 'ADMIN_SESSION');

        // Configure session settings
        session_set_cookie_params([
            'lifetime' => 0, // Session cookie (expires when browser closes)
            'path' => '/',
            'domain' => '',
            'secure' => $sessionConfig['cookie_secure'] ?? false,
            'httponly' => $sessionConfig['cookie_httponly'] ?? true,
            'samesite' => $sessionConfig['cookie_samesite'] ?? 'Lax',
        ]);

        // Set garbage collection lifetime
        ini_set('session.gc_maxlifetime', $sessionConfig['lifetime'] ?? 7200);

        session_start();
        self::$started = true;

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['_last_regenerate'])) {
            $_SESSION['_last_regenerate'] = time();
        } elseif (time() - $_SESSION['_last_regenerate'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['_last_regenerate'] = time();
        }
    }

    /**
     * Set a session value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a session key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session key
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public static function all(): array
    {
        self::start();
        return $_SESSION ?? [];
    }

    /**
     * Clear all session data (but keep session alive)
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }

    /**
     * Destroy the session completely
     */
    public static function destroy(): void
    {
        self::start();

        // Clear session data
        $_SESSION = [];

        // Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Destroy the session
        session_destroy();
        self::$started = false;
    }

    /**
     * Regenerate session ID
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['_last_regenerate'] = time();
    }

    /**
     * Get the session ID
     */
    public static function id(): string
    {
        self::start();
        return session_id();
    }

    /**
     * Flash data - available only for next request
     */
    public static function flash(string $key, $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash data
     */
    public static function getFlash(string $key, $default = null)
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if flash data exists
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }
}
