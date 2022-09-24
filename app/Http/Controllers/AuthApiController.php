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
            $criteria = new Criteria();
            $criteria->relationships = Common::splitToArray('brand,userType,profileImage');
            $criteria->httpParams = [
                'brand' => 'profileImage,coverImage'
            ];
            $user = Common::prepareRelationships($criteria, new Users());
            $user = $user->find(Auth::id());

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
    // public function register(Request $request)
    // {
    //     $result = null;
    //     DB::beginTransaction();
    //     try {
    //         $validator = validator(request()->all(), [
    //             'first_name' => 'string|required',
    //             'last_name' => 'string|required',
    //             'usertype_id' => 'string|required|exists:user_types,id',
    //             'email' => 'string|required|email|unique:users,email',
    //             'password' => 'string|required',
    //         ]);
    //         if ($validator->fails()) {
    //             $result = new ViewResult();
    //             $result->error(new InvalidRequest(),$validator->errors());
    //         } else {
    //             $user =  new Users();
    //             $user->first_name = $request['first_name'];
    //             $user->last_name = $request['last_name'];
    //             $user->fk_usertype_id = $request['usertype_id'];
    //             $user->email = $request['email'];
    //             $user->password = Hash::make($request['password']);
    //             $result = $this->userService->register($user);
    //         }
    //     } catch (Exception $e) {
    //         $result = new ViewResult();
    //         $result->error($e);
    //     }

    //     $result->completeTransaction();
    //     return response([
    //         $result
    //     ]);
    // }
    public function logout()
    {
        $currentUser = (object) Auth::user();
        $currentUser->tokens()->where('id', $currentUser->currentAccessToken()->id)->delete();
        $cookie = Cookie::forget(config('constants.TOKEN.NAME'));
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
        $criteria = new Criteria();
        $criteria->relationships = Common::splitToArray('brand,userType,profileImage');
        $criteria->httpParams = [
            'brand' => 'profileImage,coverImage'
        ];
        $user = Common::prepareRelationships($criteria, new Users());
        $user = $user->find(Auth::id());
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
