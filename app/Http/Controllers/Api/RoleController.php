<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Validator;

class RoleController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		try {
			$roles = Role::with('createdBy')->latest()->get();
			return response()->json($roles);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Roles Failed!']);
		}
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		try {
			$rule = [
				'name' => 'required|unique:roles,name',
			];
			$validator = Validator::make($request->all(), $rule);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors(),
				]);
			}
			$input = $request->all();
			Role::create($input);
			$roles = Role::latest()->with('createdBy')->get();
			return response()->json(['status' => 'success', 'message' => 'Add Role Successfully!', 'roles' => $roles]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Add Role Failed!']);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
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
			if (isset($request->name)) {
				$rule = [
					'name' => 'required|unique:roles,name,' . $id,
				];
				$validator = Validator::make($request->all(), $rule);
				if ($validator->fails()) {
					return response()->json([
						'status' => 'error',
						'message' => $validator->errors(),
					]);
				}
			}
			$input = $request->all();
			$role = Role::find($id);
			$role->update($input);
			$roles = Role::with('createdBy')->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!', 'roles' => $roles]);
		} catch (\Exception $e) {
			\Log::info($e);
			$roles = Role::with('createdBy')->latest()->get();
			return response()->json(['status' => 'error', 'message' => 'Update Failed!', 'roles' => $roles]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//
	}
	public function search(Request $request) {
		try {
			$input = $request->all();
			$roles = Role::with('createdBy');
			if ($input['name'] != "") {
				$name = "%" . str_replace(" ", "%", $input['name']) . "%";
				$roles = $roles->where('name', 'like', $name);
			}
			if ($input['from'] != "" && $input['to'] != "") {
				$roles = $roles->whereBetween('created_at', [$input['from'], $input['to']]);
			}
			$roles = $roles->latest()->get();
			return response()->json($roles);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => ' Search Failed!']);
		}
	}
}
