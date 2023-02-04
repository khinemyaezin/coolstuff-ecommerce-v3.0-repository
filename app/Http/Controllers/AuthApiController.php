<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeUserPasswordRequest;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\RolebasedAccessControl;
use App\Services\AuthService;
use App\Services\UserService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthApiController extends Controller
{
    function __construct(
        private UserService $userService,
        private RolebasedAccessControl $acessControl,
        private AuthService $authService
    ) {
    }

    public function login(Request $request)
    {
        $result = null;
        try {
            //$accessToken->created_at->gt(now()->subMinutes($this->expiration)))

            if (!Auth::attempt($request->only('email', 'password'))) {
                $resp = new ViewResult();
                $resp->error(throw new AuthenticationException());
                return response(new ViewResult(), Response::HTTP_UNAUTHORIZED);
            }
            $info = $this->authService->getUserInfoAfterLogin(); 
            $cookie = $this->authService->mergeOrCreateToken($info['user'],$info['roles']);

            $result = new ViewResult();
            $result->success();
            $result->details = $info;

            return response()->json($result->nullCheckResp())->withCookie($cookie);
        } catch (Exception $e) {
            $result = new ViewResult();
            $result->error($e);
            return response()->json($result->nullCheckResp(), $result->getHttpStatus());
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $cookie = Cookie::forget(config('constants.TOKEN.NAME'));

        $result = new ViewResult();
        $result->success();

        return response()->json($result->nullCheckResp())->withCookie($cookie);
    }

    public function revokeSessions()
    {
        $result = new ViewResult();
        $currentUser = (object) Auth::user();
        $currentUser->tokens()->delete();
        $cookie = Cookie::forget(config('constants.TOKEN.NAME'));
        $result->success();
        return response()->json($result->nullCheckResp())->withCookie($cookie);
    }

    public function revokeUserSession(Request $request)
    {
        $result = new ViewResult();
        $currentUser = Users::findOrFail($request->route('userid'));
        $currentUser->tokens()->where('id', $request->route('sessionid'))->delete();
        $result->success();
        return response()->json($result->nullCheckResp());
    }

    public function getCurrentUserFromCookie()
    {
        $result = new ViewResult();
        $info = $this->authService->getUserInfoAfterLogin();
        $result->details = $info;
        $result->success();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function changePassword(ChangeUserPasswordRequest $request)
    {
        $result = new ViewResult();
        $request = request();
        DB::beginTransaction();
        try {
            $result->details = Users::findOrFail($request->route('id'))->update([
                'password' => Hash::make($request['password'])
            ]);
            $result->success();
        } catch (Exception $e) {
            $result = new ViewResult();
            $result->error($e);
        }
        $result->completeTransaction();
        return $this->revokeSessions();
    }

    public function getUserSessions($id)
    {
        $result = new ViewResult();
        $currentUser = Users::findOrFail($id);
        $sessions = $currentUser->tokens()->select([
            'id', 'user_agent', 'ip', 'tokenable_id', 'last_used_at', 'created_at'
        ])->get();
        $result->details = $sessions->transform(function ($data) {
            $data->current_device = $data->id === ((object) Auth::user())->currentAccessToken()->id;
            return $data;
        })->sortByDesc('current_device')->values()->all();

        $result->success();
        return response()->json($result->nullCheckResp());
    }
}
