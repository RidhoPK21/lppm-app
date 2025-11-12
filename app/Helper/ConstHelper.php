<?php

namespace App\Helper;

class ConstHelper
{
    const OPTION_ROLES = [
        'Admin',
        'Editor',
        'Viewer',
    ];

    public static function getOptionRoles()
    {
        $roles = self::OPTION_ROLES;
        sort($roles);

        return $roles;
    }
}
