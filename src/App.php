<?php

namespace Undefined\Core;

/**
 * Base App class with Singleton pattern
 *
 * @name App
 * @since 2.1.0
 * @package Undefined\Core
 */
abstract class App
{
    /**
     * Singleton instance
     *
     * @var static|null
     */
    private static ?self $instance = null;

    /**
     * Get the singleton instance
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Reset the singleton instance (useful for testing)
     *
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Prevent cloning
     */
    private function __clone(): void
    {
    }

    /**
     * Prevent unserialization
     *
     * @throws \Exception
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
