<?php

namespace App\Services\Impl;

use App\Enums\BizStatus;
use App\Enums\UserTypes;
use App\Models\Users;
use App\Services\AuthService;
use App\Services\RolebasedAccessControlService;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthServiceImpl implements AuthService
{
    public function __construct(protected RolebasedAccessControlService $accessControlService)
    {
    }
    /**
     * @return Users
     */
    public function getUserInfo()
    {

        return Users::with([
            'userType',
            'profileImage',
        ])->find(
            Auth::id(),
            [
                'id',
                'first_name',
                'last_name',
                'fk_usertype_id',
                'profile_image',
                'userable_type',
                'userable_id'
            ]
        );
    }
    public function getUserInfoByUserType(Users $user)
    {
        switch ($user->userType->id) {
            case UserTypes::BRAND_OWNER->value || UserTypes::STAFF->value: {
                    return $user->userable()->with([
                        'brand' => function ($query) {
                            $query->with(['profileImage', 'coverImage', 'region', 'defaultCurrency']);
                            $query->select([
                                'id',
                                'title',
                                'public_id',
                                'profile_image',
                                'cover_image',
                                'fk_region_id',
                                'def_currency_id',
                                'created_at',
                                'updated_at'
                            ]);
                        }
                    ])->first(['fk_brand_id']);
                };
                break;
        }
        return null;
    }

    public function getRoles(Users $user)
    {
        $privileges = [];
        if ($user->userType->id == UserTypes::SERVER_ADMIN->value || true) {
            $taskResult = $this->accessControlService->getTasks();
            if (!$taskResult->success) throw new Exception();
            foreach ($taskResult->details as $key => $task) {
                array_push($privileges, $task->id);
            }
        } else {
            foreach ($user->roles()->where('roles.biz_status', BizStatus::ACTIVE)->get() as $role) {
                foreach ($role->tasks()->where('tasks.biz_status', BizStatus::ACTIVE)->get() as $task) {
                    array_push($privileges, $task->id);
                }
            }
        }
        return $privileges;
    }

    public function mergeOrCreateToken($user, $roles)
    {
        $ip = request()->ip();
        $tokenName = config('constants.TOKEN.NAME');
        $recent = $user->tokens()->where('ip', '=', $ip)->first();
        if ($recent) {
            $recent->delete();
        }
        $token = $user->createToken($tokenName, $roles)->plainTextToken;
        $cookie = cookie(config('constants.TOKEN.NAME'), $token, config('constants.TOKEN.DURATION'));
        return $cookie;
    }
}
