<?php

namespace App\Services;


use App\Models\Criteria;

interface UserService{
    public function getUsers(Criteria $criteria);
    public function getUserById(Criteria $criteria, $id);
    public function saveUser(Criteria $criteria);
    public function updateUser(Criteria $criteria);
    public function getUsersByUserTypes();

}