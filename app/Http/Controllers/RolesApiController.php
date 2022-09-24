<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequest;
use App\Models\Criteria;
use App\Models\Roles;
use App\Models\ViewResult;
use App\Services\RoleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolesApiController extends Controller
{
    function __construct(protected RoleService $roleService)
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
                'title' => 'string|nullable',
            ]
        ]);
        if ($validator->fails()) {
            $result = new ViewResult();
            //$result->error(new InvalidRequest(),$validator->errors());
        } else {
            $criteria = new Criteria();
            $criteria->relationships = $request->relationships;
            $criteria->details = $request->details;
            $result = $this->roleService->getRoles($criteria);
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
        $result = null;
        DB::beginTransaction();
        try {
            $validator = validator(request()->all(), [
                'title' => 'string|required|max:100',
            ]);
            if ($validator->fails()) {
                $result = new ViewResult();
                //$result->error(new InvalidRequest(),$validator->errors());
            } else {
                $roles = new Roles([
                    'title'=> $request['title']
                ]);
                $result = $this->roleService->storeRole($roles);
            }
        } catch (Exception $e) {
            $result = new ViewResult();
            $result->error($e);
        }

        $result->completeTransaction();
        return response([
            $result
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
