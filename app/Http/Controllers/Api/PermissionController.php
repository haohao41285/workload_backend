<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;

class PermissionController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		try {
			$permissions = Permission::all();
			$roles = Role::with('permissions')->where('id', '!=', 1)->get();
			return response()->json(['roles' => $roles, 'permissions' => $permissions]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Role Permission Failed!']);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		try {
			$role_permission = RolePermission::where('id_role', $id)->first();

			if (!isset($role_permission->permissions)) {
				$r_p_arr = [
					'id_role' => $id,
					'permissions' => $request->id_permission,
					'active' => 1,
				];
				RolePermission::create($r_p_arr);
			} else {
				$permissions = $role_permission->permissions;
				$permision_arr = explode(";", $permissions);
				//Check permisson in array
				if (($key = array_search($request->id_permission, $permision_arr)) !== false) {
					unset($permision_arr[$key]);
				} else {
					$permision_arr[] = $request->id_permission;
				}
				$permissions = implode(';', $permision_arr);
				$role_permission->update(['permissions' => $permissions]);
			}
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Failed!']);
		}
	}
}
