<?php

namespace App\Services\Impl;

use App\Enums\BizStatus;
use App\Enums\UserTypes;
use App\Models\Users;
use App\Services\RolebasedAccessControl;
use App\Services\AuthService;
use Exception;
use Illuminate\Support\Facades\Auth;

class AuthServiceImpl implements AuthService
{
    public function __construct(protected RolebasedAccessControl $accessControl)
    {
    }
    public function getUserInfoAfterLogin()
    {
        $user = Users::with([
            'userType',
            'profileImage',
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
        ])->find(
            Auth::id(),
            [
                'id',
                'first_name',
                'last_name',
                'fk_usertype_id',
                'fk_brand_id',
                'profile_image',
            ]
        );
        $privileges = [];
        if ($user->userType->id == UserTypes::SERVER_ADMIN->value || true) {
            $taskResult = $this->accessControl->getTasks();
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
        return [
            "roles" => $privileges,
            "user" => $user
        ];
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