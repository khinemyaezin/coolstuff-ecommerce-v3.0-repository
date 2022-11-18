<?php

namespace App\Http\Controllers;

use App\Services\UserService;

class UserTypesApiController extends Controller
{
    function __construct(protected UserService $userService)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUsersByUserTypes()
    {
        $result = $this->userService->getUsersByUserTypes();
        return response()->json($result, $result->getHttpStatus());
    }

}
