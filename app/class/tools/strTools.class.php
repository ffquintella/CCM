<?php
/**
 * Created by PhpStorm.
 * User: Felipe
 * Date: 09/03/14
 * Time: 00:08
 */

namespace gcc\tools;


class strTools
{
    static function startsWith($haystack, $needle)
    {
        return $needle === "" || strpos($haystack, $needle) === 0;
    }

    static function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }

    static function removeSpaces($string)
    {
        return str_replace(" ", "_", $string);
    }
} 