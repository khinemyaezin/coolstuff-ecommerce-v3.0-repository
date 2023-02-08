<?php

namespace App\Services;

use App\Models\Users;

interface AuthService {
    public function getUserInfo();
    public function getUserInfoByUserType(Users $user);
    public function getRoles(Users $user);
    public function mergeOrCreateToken($user,$roles);
}