<?php

namespace App\Services;

use App\Enums\UserTypes;
use App\Models\Criteria;
use App\Models\Images;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\UserTypes as ModelsUserTypes;
use App\Models\ViewResult;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{

    public function getUsers(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $users = new Users();
            $users = Common::prepareRelationships($criteria, $users);

            try {
                if (isset($criteria->details['first_name'])) {
                    $users = $users->where('first_name', 'LIKE', "%{$criteria->details['first_name']}%");
                }
                if (isset($criteria->details['last_name'])) {
                    $users = $users->where('last_name', 'LIKE', "%{$criteria->details['last_name']}%");
                }
                $result->details = $users->paginate(config('constants.PAGINATION_COUNT'));

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

    public function getUserById(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {
            $user = new Users();
            $user = Common::prepareRelationships($criteria, $user);
            $user = $user->findOrFail($id);
            $result->details = $user;
            $result->success();
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
    public function updateUser(Criteria $criteria, $id)
    {
        $result = new ViewResult();
        try {

            $user = Users::find($id);
            if (!$user) throw new ModelNotFoundException();

            $user->first_name   = $criteria->details['first_name'];
            $user->last_name    = $criteria->details['last_name'];
            $user->profile_image    = $criteria->details['profile_image'];
            $user->email        = $criteria->details['email'];
            $user->phone        = $criteria->details['phone'];
            $user->address      = $criteria->details['address'];
            $user->save();
            $result->success();
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

    public function getUsersByUserTypes()
    {
        $result = new ViewResult();
        try {
            $currentUser = (object) Auth::user();
            $brand = $currentUser->brand;

            $records = ModelsUserTypes::with(['users' => function ($query) use ($brand) {
                $query->with('profileImage');
                $query->where('fk_brand_id', '=', $brand->id);
                $query->select([
                    'id',
                    'first_name',
                    'last_name',
                    'fk_brand_id',
                    'email',
                    'phone',
                    'fk_usertype_id',
                    'profile_image'
                ]);
            }])->where('title', '=', UserTypes::BRAND_OWNER->value)
                ->orWhere('title', '=', UserTypes::STAFF->value)->get();
            $result->details = $records;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
