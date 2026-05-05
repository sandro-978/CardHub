<?php
class Validator
{
    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isPositivePrice(float $price): bool
    {
        return $price > 0;
    }

    public static function hasMinLength(string $value, int $min): bool
    {
        return strlen(trim($value)) >= $min;
    }
}
?>
