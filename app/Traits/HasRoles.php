<?php

namespace App\Traits;

trait HasRoles
{
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
