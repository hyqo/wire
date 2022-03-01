<?php

namespace Hyqo\Wire;

class Utils
{
    public static function pack($value): string
    {
        return htmlspecialchars(json_encode($value), ENT_QUOTES);
    }

    public static function unpack(string $string): string
    {
        return preg_replace(
            "/\n */",
            '',
            var_export(json_decode(htmlspecialchars_decode($string), true), true)
        );
    }

    public static function addStyle(string $string, string $key, string $value): string
    {
        $styles = explode(';', $string);
        $styles = array_filter($styles, static function (string $style): bool {
            return trim($style);
        });
        $styles[] = sprintf('%s: %s', $key, $value);

        return implode('; ', $styles);
    }
}
