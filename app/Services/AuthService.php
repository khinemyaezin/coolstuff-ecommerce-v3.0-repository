<?php

namespace App\Services;

interface AuthService {
    public function getUserInfoAfterLogin();
    public function mergeOrCreateToken($user,$roles);
}