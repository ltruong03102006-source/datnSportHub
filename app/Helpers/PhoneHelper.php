<?php

if (!function_exists('maskPhone')) {
    function maskPhone(?string $phone): ?string
    {
        if (!$phone || strlen($phone) < 4) {
            return $phone;
        }

        $prefix = substr($phone, 0, 3);
        $suffix = substr($phone, -2);
        $masked = str_repeat('*', strlen($phone) - 5);

        return $prefix . $masked . $suffix;
    }
}
