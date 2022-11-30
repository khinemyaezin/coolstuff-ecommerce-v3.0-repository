<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetUserByIdRequest;
use App\Http\Requests\GetUsersRequest;
use App\Http\Requests\RoleUserSaveRequest;
use App\Http\Requests\UserSaveRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Criteria;
use App\Models\Users;
use App\Services\RoleBasedAccessControl;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsersApiController extends Controller
{
    function __construct(protected UserService $userService,protected RoleBasedAccessControl $rbac)
    {
    }


    public function index(GetUsersRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->userService->getUsers($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function store(UserSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->userService->saveUser($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function show(GetUserByIdRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->userService->getUserById($criteria, $request->route('id'));
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function update(UserUpdateRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->userService->updateUser($criteria);

        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function destroy(Users $users)
    {
        //
    }

    public function getAvaliableRoles(Request $request)
    {
        $result = $this->rbac->getUserRolesSetup($request->route('id'));
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function saveRoleUser(RoleUserSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->rbac->saveUserRoles($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
}
