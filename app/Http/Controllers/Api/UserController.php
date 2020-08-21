<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		try {
			$teams = Team::all();
			$team_tree = getTeamTree($teams);
			$users = User::with('team')->latest()->get();
			$projects = Project::latest()->get();
			return response()->json(['users' => $users, 'teams' => $team_tree, 'projects' => $projects]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get users Failed!']);
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
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		// $user = User::find($id);
		// return response()->json($user);
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
			$rule = [
				'name' => 'required|unique:users,name,' . $id,
			];
			$validator = \Validator::make($request->all(), $rule);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors(),
				]);
			}
			$user_arr = [
				'name' => $request->name,
				'key' => $request->key,
				'token' => $request->token,
				'id_role' => $request->id_role,
			];
			// $input = $request->all();
			$user_update = User::find($id)->update($user_arr);
			$users = User::with('team')->get();
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!', 'users' => $users]);

		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Failed!']);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		try {
			$user = User::find($id);
			//check user's tasks
			if ($user->tasks->count() > 0) {
				return response()->json(['status' => 'error', 'message' => 'Delete Failed! This user contain task']);
			}
			$user->delete();
			$users = User::all();
			return response()->json(['status' => 'success', 'message' => 'Delete Successfully!', 'users' => $users]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Delete Failed!']);
		}
	}

	public function search(Request $request) {
		try {
			$input = $request->all();
			$users = User::where('name', '!=', "");
			if ($input['from'] != "" && $input['to'] != "") {
				$users = $users->whereBetween('created_at', [$input['from'], $input['to']]);
			}
			if ($input['name'] != "") {
				$name = "%" . str_replace(" ", '%', $input['name']) . "%";
				$users = $users->where('name', 'like', $name);
			}
			if ($input['team_id'] == "all") {} else {
				//get all team child
				$teams = Team::all();
				$team_child = getIdChildTeam($teams, $input['team_id']);
				$team_child[] = $input['team_id'];
				$users = $users->whereIn('team_id', $team_child);
			}
			$users = $users->latest()->get();

			return response()->json($users);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Search User Failed!']);
		}
	}

	public function changePassword(Request $request, $id) {
		try {
			$rules = [
				're_new_password' => 'required|
				                min:9|
				                regex:/[a-z]/|
				                regex:/[A-Z]/|
				                regex:/[0-9]/|
				                regex:/[@$!%*#?&]/',
			];
			$messages = [
				're_new_password.required' => 'Password been required',
				're_new_password.min' => 'must be at least 9 characters in length',
				're_new_password.regex' => 'Must contain at least one lowercase letter, one uppercase, one digit, one special character',
			];
			$validator = \Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors(),
				]);
			}

			$user = User::find($id);
			$user_update = $user->update(['password' => \Hash::make($request->re_new_password)]);
			return response()->json(['status' => 'success', 'message' => 'Update Password Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Password Failed!']);
		}
	}
	public function updateStatus(Request $request, $id) {
		try {
			$active = $request->active == 1 ? 0 : 1;
			$user = User::find($id);
			$user->update(['active' => $active]);
			$users = User::with('team')->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!', 'users' => $users]);
		} catch (\Exception $e) {
			\Log::info($e);
			$users = User::with('team')->latest()->get();
			return response()->json(['status' => 'error', 'message' => 'Update Failed!', 'users' => $users]);
		}
	}
	public function getOne($id) {
		try {
			$user = User::find($id);
			return response()->json($user);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get User Failed!']);
		}
	}
	public function updateOne(Request $request, $id) {
		try {
			$user = User::find($id);
			$input = $request->all();
			$user->update($input);
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Failed!']);
		}
	}
	public function updatePassword(Request $request, $id) {
		// return response()->json($request->all());
		try {
			$rules = [
				'password' => 'required',
				'new_password' => 'required|
						                min:9|
						                regex:/[a-z]/|
						                regex:/[A-Z]/|
						                regex:/[0-9]/|
						                regex:/[@$!%*#?&]/',
				're_new_password' => 'required| same:new_password',
			];
			$messages = [
				'password.required' => 'Password been required',
				'new_password.required' => 'Password been required',
				'new_password.min' => 'Must be at least 9 characters in length',
				'new_password.regex' => 'Must contain at least one lowercase letter, one uppercase, one digit, one special character',
				're_new_password.required' => 'Repeat password been required',
				're_new_password.same' => 'New pass word not match',
			];
			$validator = \Validator::make($request->all(), $rules, $messages);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors(),
				]);
			}
			$user = User::find($id);
			//Check Current User Password
			if (!($token = JWTAuth::attempt(['email' => $user->email, 'password' => $request->password]))) {
				return response()->json([
					'status' => 'error',
					'message' => 'Current Password Incorret!',
				]);
			}

			$user->update(['password' => \Hash::make($request->new_password)]);
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Failed!']);
		}
	}
}
