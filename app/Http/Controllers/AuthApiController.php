<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Http\Requests\ChangeUserPasswordRequest;
use App\Models\Criteria;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\UserService;
use App\Services\Common;
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
    function __construct(protected UserService $userService)
    {
    }
    public function login(Request $request)
    {
        $result = null;
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                $resp = new ViewResult();
                $resp->error(throw new AuthenticationException());
                return response(new ViewResult(), Response::HTTP_UNAUTHORIZED);
            }
            $user = Users::with([
                'userType',
                'profileImage',
                'brand' => function ($query) {
                    $query->with(['profileImage', 'coverImage','region','defaultCurrency']);
                    $query->select([
                        'id',
                        'public_id',
                        'profile_image',
                        'cover_image',
                        'fk_region_id',
                        'def_currency_id'
                    ]);
                }
            ])->find(Auth::id(), [
                'id',
                'first_name',
                'last_name',
                'fk_usertype_id',
                'fk_brand_id',
                'profile_image',
            ]);
            $privileges = [];
            foreach ($user->roles as $role) {
                foreach ($role->tasks as $task) {
                    array_push($privileges, $task->title);
                }
            }
            $token = $user->createToken(config('constants.TOKEN.NAME'), $privileges)->plainTextToken;
            $cookie = cookie(config('constants.TOKEN.NAME'), $token, config('constants.TOKEN.DURATION'));

            $result = new ViewResult();
            $result->success();
            $result->details = [
                "roles" => $privileges,
                "user" => $user
            ];
            return response()->json($result)->withCookie($cookie);
        } catch (Exception $e) {
            $result = new ViewResult();
            $result->error($e);
            return response()->json($result, $result->getHttpStatus());
        }
    }
 
    public function logout(Request $request)
    {
        $currentUser = (object) Auth::user();

        $currentUser->tokens()->where('id', $currentUser->currentAccessToken()->id)->delete();
        $cookie = Cookie::forget(config('constants.TOKEN.NAME'));
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response(["message" => "success"])->withCookie($cookie);
    }
    public function revokeSessions()
    {
        $result = new ViewResult();
        $currentUser = (object) Auth::user();
        $currentUser->tokens()->delete();
        $cookie = Cookie::forget(config('constants.TOKEN.NAME'));
        $result->success();
        return response()->json($result)->withCookie($cookie);
    }
    public function getCurrentUserFromCookie()
    {
        $result = new ViewResult();
        $user = Users::with([
            'userType',
            'profileImage',
            'brand' => function ($query) {
                $query->with(['profileImage', 'coverImage','region','defaultCurrency']);
                $query->select([
                    'id',
                    'public_id',
                    'profile_image',
                    'cover_image',
                    'fk_region_id',
                    'def_currency_id'
                ]);
            }
        ])->find(Auth::id(), [
            'id',
            'first_name',
            'last_name',
            'fk_usertype_id',
            'fk_brand_id',
            'profile_image',
        ]);
        $privileges = [];
        foreach ($user->roles as $role) {
            foreach ($role->tasks as $task) {
                array_push($privileges, $task->title);
            }
        }
        $result->details = [
            "roles" => $privileges,
            "user" => $user
        ];
        $result->success();
        return response()->json($result, $result->getHttpStatus());
    }

    public function changePassword(ChangeUserPasswordRequest $request)
    {
        $result = new ViewResult();
        $request = request();
        DB::beginTransaction();
        try {
            $result->details = Users::find(Auth::id())->update([
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
}
