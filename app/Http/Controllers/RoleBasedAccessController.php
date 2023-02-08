<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetRolesRequest;
use App\Http\Requests\RoleSaveRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\Criteria;
use App\Services\RolebasedAccessControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleBasedAccessController extends Controller
{
    function __construct(protected RolebasedAccessControlService $service)
    {
    }

    public function getRoles(GetRolesRequest $request)
    {
        $criteria = new Criteria($request);
        $result = $this->service->getRoles($criteria);
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }


    public function saveRole(RoleSaveRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->storeRole($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function updateRole(RoleUpdateRequest $request)
    {
        DB::beginTransaction();
        $criteria = new Criteria($request);
        $result = $this->service->updateRole($criteria);
        $result->completeTransaction();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getTaskByRoleID(Request $request)
    {
        $result = $this->service->getTaskByRoleID($request->route('id'));
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }

    public function getTasks()
    {
        $result = $this->service->getTasks();
        return response()->json($result->nullCheckResp(), $result->getHttpStatus());
    }
}
