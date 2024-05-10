<?php

namespace App\Config;

class Configuration
{
    private array $config;

    /**
     * Initialize the configuration.
     * Accepts an array of configurations directly.
     *
     * @param array $config Configuration data
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves a configuration value by key, with an optional default.
     *
     * @param string $key The configuration key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The configuration value or default
     */
    public function get(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
