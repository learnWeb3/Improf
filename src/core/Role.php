<?php

namespace Application\Core;

class Role
{
    private const ROLE_NAMES = [
        0 => "user",
        1 => "admin"
    ];
    public static function getRoleName(int $role)
    {
        return self::ROLE_NAMES[$role];
    }
}
