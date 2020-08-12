<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Team;
use App\User;
use Illuminate\Http\Request;

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
			$users = User::with('team')->get();
			return response()->json(['users' => $users, 'teams' => $team_tree]);
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
	public function update(UpdateUserRequest $request, $id) {
		return $request->all();
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
}
