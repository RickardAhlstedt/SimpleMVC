<?php

namespace SimpleMVC\Core\HTTP;

class Session
{
    /**
     * Start the session if not already started.
     */
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * Get a value from the session.
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a value in the session.
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Remove a value from the session.
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destroy the session and clear all data.
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Regenerate the session ID for security.
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Check if a key exists in the session.
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Get all session data.
     */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }
}
