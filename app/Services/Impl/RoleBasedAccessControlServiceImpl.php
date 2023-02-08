<?php

namespace App\Services\Impl;

use App\Enums\BizStatus;
use App\Models\Criteria;
use App\Models\Roles;
use App\Models\Tasks;
use App\Models\Users;
use App\Models\ViewResult;
use App\Services\Common;
use App\Services\RolebasedAccessControlService;
use Exception;
use Illuminate\Support\Facades\DB;

class RoleBasedAccessControlServiceImpl implements RolebasedAccessControlService
{

    public function storeRole(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $role = Roles::create([
                "title" => $criteria->details['title'],
                "code" => Common::toCode($criteria->details['title'])
            ]);
            $tasks = [];
            foreach ($criteria->details['tasks'] as $task) {
                $tasks[$task['id']] = [
                    'fk_role_id' => $role->id
                ];
            }
            $role->tasks()->sync($tasks);
            $result->details = $role->id;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function updateRole(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $role = Roles::findOrFail($criteria->request->route('id'));
            $tasks = [];
            foreach ($criteria->details['tasks'] as $task) {
                $tasks[$task['id']] = [
                    'fk_role_id' => $role->id
                ];
            }
            $role->tasks()->sync($tasks);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getRoles(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $roles = Common::prepareRelationships($criteria, new Roles());
            if (isset($criteria->details['title'])) {
                $roles = $roles->where('title', 'LIKE', "%{$criteria->details['title']}%");
            }
            $result->details = $roles->paginate(config('constants.PAGINATION_COUNT'));
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getUserRolesSetup($userID)
    {
        $result = new ViewResult();
        try {
            $records = DB::table('roles')
                ->leftJoin('user_privileges', function ($query) use ($userID) {
                    $query->on('user_privileges.fk_role_id', '=', 'roles.id');
                    $query->where('user_privileges.biz_status', '=', BizStatus::ACTIVE);
                    $query->where('user_privileges.fk_user_id', '=', $userID);
                })
                ->select([
                    'roles.*',
                    'user_privileges.fk_user_id'
                ])->get();

            $result->details = $records;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function saveUserRoles(Criteria $criteria)
    {
        $result = new ViewResult();
        try {
            $user = Users::find($criteria->request->route('id'));
            $roles = [];
            foreach ($criteria->details['roles'] as $role) {
                $roles[$role['id']] = [
                    'title' => '',
                    'fk_user_id' => $user->id
                ];
            }
            $user->roles()->sync($roles);
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getTaskByRoleID($roleID)
    {
        $result = new ViewResult();
        try {
            $records = DB::table('tasks')
                ->leftJoin('roles_privileges', function ($q) use ($roleID) {
                    $q->on('roles_privileges.fk_task_id', '=', 'tasks.id');
                    $q->where('roles_privileges.fk_role_id', '=', $roleID);
                })
                ->orderByDesc('tasks.id')
                ->select([
                    'tasks.id as task_id',
                    'roles_privileges.fk_role_id as role_id'
                ])->get();

            $result->details = $records;
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }

    public function getTasks()
    {
        $result = new ViewResult();
        try {
            $result->details = Tasks::where('biz_status', '=', BizStatus::ACTIVE)->get();
            $result->success();
        } catch (Exception $e) {
            $result->error($e);
        }
        return $result;
    }
}
