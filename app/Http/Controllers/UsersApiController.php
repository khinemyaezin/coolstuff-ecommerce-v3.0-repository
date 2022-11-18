<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetUserByIdRequest;
use App\Http\Requests\GetUsersRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Criteria;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersApiController extends Controller
{
    function __construct(protected UserService $userService)
    {
    }


    public function index(GetUsersRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->userService->getUsers($criteria);
        return response()->json($result, $result->getHttpStatus());
    }


    public function store(Request $request)
    {
        return $request;
    }


    public function show(GetUserByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->userService->getUserById($criteria, $request->route('id'));
        return response()->json($result, $result->getHttpStatus());
    }


    public function update(UserUpdateRequest $request)
    {
        DB::beginTransaction();
        $result = null;
        $id = $request->route('id');
        $criteria = new Criteria();
        $criteria->details = $request->validated();

        $result = $this->userService->updateUser($criteria, $id);

        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }


    public function destroy(Users $users)
    {
        //
    }

    public function savePrivileges(Request $request)
    {
        DB::beginTransaction();
        $result = null;
        $validator = validator($request->all(), [
            'roles' => 'array|required',
            'roles.*' => 'string|exists:roles,id',

        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            //$result->error(new InvalidRequest(), $validator->errors());
        } else {
            $userPrivileges = [];
            foreach ($request->roles as $role) {
                array_push($userPrivileges, new UserPrivileges([
                    'title' => 'UserPrivileges',
                    'fk_user_id' => $request->userid,
                    'fk_role_id' => $role
                ]));
            }

            $result = $this->userService->saveUserPrivileges($userPrivileges);
        }
        $result->completeTransaction();
        return response()->json($result, $result->getHttpStatus());
    }
}
