<?php

namespace App\ViewModels;


class LoginData
{
    public function __construct(
        public $userId,
        public $userAuthMethodValue,
        public $bizId,
        public $userType
    ) {
    }
}
