<?php

if (!function_exists('env')) {
    /**
     * Get an environment variable with support for fallback to $_SERVER and getenv().
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false || $value === null) {
            return $default;
        }
        
        // Handle string booleans
        if (is_string($value)) {
            $lowerValue = strtolower($value);
            if ($lowerValue === 'true') return true;
            if ($lowerValue === 'false') return false;
            if ($lowerValue === 'null') return null;
        }
        
        return $value;
    }
}
