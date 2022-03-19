<?php
namespace Application\Helper;

class ObjectUtils
{

    public static function getPublicVars(object $element, $public_vars = []): array
    {
        $all_vars = (array) $element;
        foreach ($all_vars as $key => $value) {
            $pattern = "/[A-Za-z_]+/";
            preg_match_all($pattern, $key, $matches);
            if ($matches[0][0] === $key) {
                if (gettype($value) === "object") {
                    $public_vars = array_merge(
                        $public_vars,
                        [
                            $key => self::getPublicVars($value)
                        ]
                    );
                } else {
                    $public_vars[$key] = $value;
                }
            }
        }
        return $public_vars;
    }
}
