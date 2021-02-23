<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{

    const ROLE_SUPPER_ADMIN = 1;
    const ROLE_ADMIN = 2;

    public function isSuperAdmin()
    {
        if ($this->user_type == self::ROLE_SUPPER_ADMIN) {
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        if ($this->user_type == self::ROLE_ADMIN) {
            return true;
        }
        return false;
    }
}
