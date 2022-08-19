<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\Roles;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;

class RoleService
{

    public function storeRole(Roles $role)
    {
        $result = new ViewResult();
        try {
            if($role->save()){
                $result->success();
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function getRoles(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $roles = new Roles();

            if ($criteria->relationships && is_array($criteria->relationships)) {
                foreach ($criteria->relationships as $relationship) {
                    $roles = $roles->with($relationship);
                }
            }
            try {
                if (isset($criteria->details['title'])) {
                    $roles = $roles->where('title', 'LIKE', "%{$criteria->details['title']}%");
                }
                $result->details = $roles->paginate(Utility::$PAGINATION_COUNT);
                $result->success();
            } catch (RelationNotFoundException $e) {
                $result->error($e);
                $result->message = "'" . $e->relation . "' relation does not exists";
            }
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
  
}
