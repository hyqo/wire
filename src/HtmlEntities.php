<?php

namespace Hyqo\Wire;

class HtmlEntities
{
    protected static $list = [
        'nbsp',
        'amp',
        'minus',
        'plus',
        'quot',
    ];

    public static function wrapRegex(): string
    {
        return sprintf('/\&(%s)\;/i', implode('|', self::$list));
    }

    public static function unwrapRegex(): string
    {
        return sprintf('/\&_(%s)_\;/i', implode('|', self::$list));
    }
}
