<?php

namespace App\Services;

use App\Models\Criteria;

interface RolebasedAccessControl{
    public function storeRole(Criteria $criteria);
    public function updateRole(Criteria $criteria);
    public function getRoles(Criteria $criteria);
    public function getUserRolesSetup($userID);
    public function saveUserRoles(Criteria $criteria);
    public function getTaskByRoleID($roleID);
    public function getTasks();

}