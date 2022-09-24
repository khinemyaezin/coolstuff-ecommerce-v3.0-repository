<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\Criteria;
use App\Models\Images;
use App\Models\UserPrivileges;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\UserService;
use App\Services\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsersApiController extends Controller
{
    function __construct(protected UserService $userService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result = null;
        $validator = validator($request->all(), [
            'relationships' => 'array',
            'details.*' => [
                'first_name' => 'string|nullable',
            ]
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            //$result->error(new InvalidRequest(), $validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request['relationships'];
            $criteria->details = $request['details'];
            $result = $this->userService->getUsers($criteria);
        }
        return response()->json($result, $result->getHttpStatus());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return $request;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Http\Response
     */
    public function show(Users $users)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Http\Response
     */
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Users  $users
     * @return \Illuminate\Http\Response
     */
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
