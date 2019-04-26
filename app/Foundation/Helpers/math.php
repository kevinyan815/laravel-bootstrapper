<?php

if (!function_exists('base62_encode')) {
    /**
     * Convert a 10 base numeric string to a 62 base string
     *
     * @param  int $value
     * @return string
     */
    function base62_encode($value)
    {
        return to_base($value, 62);
    }
}

if (!function_exists('base62_decode')) {
    /**
     * Convert a string from base 62 to base 10 numeric string
     *
     * @param  string $value
     * @return int
     */
    function base62_decode($value)
    {
        return to_base10($value, 62);
    }
}

if (!function_exists('to_base')) {

    /**
     * Convert a numeric string from base 10 to another base.
     *
     * @param $value  decimal string
     * @param int $b base , max is 62
     * @return string
     */
    function to_base($value, $b = 62)
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $r = $value % $b;
        $result = $base[$r];
        $q = floor($value / $b);

        while ($q) {
            $r = $q % $b;
            $q = floor($q / $b);
            $result = $base[$r] . $result;
        }

        return $result;
    }
}

if (!function_exists('to_base10')) {
    /**
     * Convert a string from a given base to base 10.
     *
     * @param  string $value string from given base
     * @param  int $b base, max is 62
     * @return string
     */
    function to_base10($value, $b = 62)
    {
        $base = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $limit = strlen($value);
        $result = strpos($base, $value[0]);

        for ($i = 1; $i < $limit; $i++) {
            $result = $b * $result + strpos($base, $value[$i]);
        }

        return $result;
    }
}