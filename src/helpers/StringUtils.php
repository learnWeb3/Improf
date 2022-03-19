<?php

namespace Application\Helper;

class StringUtils
{
    public static function plurialize(string $string)
    {
        $pattern = "/y$/";
        $replacement = "ie";
        return preg_replace($pattern, $replacement, $string) . "s";
    }

    public static function singularize(string $string)
    {
        $pattern = ["/ies$/", "/s$/"];
        $replacement = ["y", ""];
        return preg_replace($pattern, $replacement, $string);
    }

    public static function toTableName(string $string)
    {
        $string = preg_replace("/.+?\\\/", "", $string);
        preg_match_all("/[A-Za-z][a-z]+/", $string, $match);
        if (isset($match[0])) {
            $string = $match[0];
            $string = array_map(function ($el) {
                return strtolower($el);
            }, $string);

            $string = implode("_", $string);
        }
        return self::plurialize(self::singularize($string));
    }

    public static function toModelName(string $string)
    {
        $string = preg_replace("/.+?\\\/", "", $string);
        $pattern = "/_/";
        preg_match_all($pattern, $string, $matches);
        if (isset($matches[0])) {
            $string = explode("_", $string);
            $string = implode("", array_map(function ($el) {
                return ucfirst($el);
            }, $string));
        }
        return self::singularize($string);
    }

    public static function toControllerName(string $string)
    {
        $string = preg_replace("/.+?\\\/", "", $string);
        return ucfirst($string) . "Controller";
    }

    public static function toForeignKey(string $string)
    {
        $string = preg_replace("/.+?\\\/", "", $string);
        return 'id_' . strtolower($string);
    }

    public static function toQueryString(array $query_params)
    {
        $formatted_params = [];
        foreach ($query_params as $key => $value) {
            $formatted_params[] = "$key=$value";
        }
        $formatted_params = implode("&", $formatted_params);
        return $formatted_params;
    }
}
