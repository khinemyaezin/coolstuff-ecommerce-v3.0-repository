<?php

namespace App\Services;

use App\Models\Criteria;
use App\Models\Images;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\RelationNotFoundException;


class UserService
{

    public function getUsers(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $users = new Users();
            $users = Utility::prepareRelationships($criteria, $users);
            
            try {
                if (isset($criteria->details['first_name'])) {
                    $users = $users->where('first_name', 'LIKE', "%{$criteria->details['first_name']}%");
                }
                if (isset($criteria->details['last_name'])) {
                    $users = $users->where('last_name', 'LIKE', "%{$criteria->details['last_name']}%");
                }
                $result->details = $users->paginate(Utility::$PAGINATION_COUNT);

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
    public function register(Users $user)
    {
        $result = new ViewResult();
        try {
            $result->details =  $user->save();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function updateUser(array $param, $id)
    {
        $result = new ViewResult();
        try {
            $userImage = new Images($param['image_url'], Utility::$IMAGE_AVATARS);
            $user = Users::find($id);
            $user->first_name   = $param['first_name'];
            $user->last_name    = $param['last_name'];
            $user->image_url    = $userImage->getPath($user->getRawOriginal('image_url'));
            $user->email        = $param['email'];
            $user->phone        = $param['phone'];
            $user->address      = $param['address'];

            $result->complete($user->save());
            $userImage->save();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
    public function saveUserPrivileges($data)
    {
        $result = new ViewResult();
        try {
            if (is_array($data)) {
                foreach ($data as $role) {

                    if (!$role->save()) {
                        throw new Exception();
                    }
                }
            } else {
                if (!$data->save()) {
                    throw new Exception();
                }
            }
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
