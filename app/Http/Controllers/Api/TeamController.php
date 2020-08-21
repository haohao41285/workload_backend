<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamRequest;
use App\Models\Team;
use App\User;
use DB;
use Illuminate\Http\Request;

class TeamController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		try {
			$teams = Team::active()->with(['teamParent', 'leader'])->latest()->get();
			$team_tree = getTeamTree(Team::all());
			return response()->json(['teams' => $teams, 'team_tree' => $team_tree]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get teams failed!']);
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
	public function store(TeamRequest $request) {
		DB::beginTransaction();
		try {
			$input = $request->all();
			$team = Team::create($input);
			if ($input['id_leader']) {
				User::find($input['id_leader'])->update(['team_id' => $team->id]);
			}

			DB::commit();
			$teams = Team::with(['teamParent', 'leader'])->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Add New Team Successfully!', 'teams' => $teams]);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Add New Team Failed!']);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		try {
			$teams_user = User::select('id', 'name', 'email')->where('team_id', $id)->get();
			$users = User::select('id', 'name', 'email')->whereNull('team_id')->get();
			return response()->json(['users' => $users, 'teams_user' => $teams_user, 'status' => "success", 'message' => 'Team\'s user below!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'Get Team\'s User Failed!']);
		}
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
			$input = $request->all();
			Team::find($id)->update($input);
			$teams = Team::active()->with(['teamParent', 'leader'])->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!', 'teams' => $teams]);
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
			$team = Team::find($id);
			if ($team->teams->count() > 0 || $team->users->count() > 0) {
				return response()->json(['status' => 'error', 'message' => 'Delete Failed! This Team contain user or other team!']);
			}
			$team->delete();
			return response()->json(['status' => 'success', 'message' => 'Delete Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'Delete Failed!']);
		}
	}
	public function search(Request $request) {
		$input = $request->all();
		try {
			$teams = Team::with(['teamParent', 'leader']);
			if ($input['name'] != "") {
				$name = "%" . str_replace(" ", "%", $input['name']) . "%";
				$teams = $teams->where('name', 'like', $name);
			}
			if ($input['from'] != "" && $input['to'] != "") {
				$teams = $teams->whereBetween('created_at', [$input['from'], $input['to']]);
			}
			if ($input['team_id'] == "all") {} elseif ($input['team_id'] != "" && $input['team_id'] != "all") {
				$team_list = Team::all();
				$team_arr = getIdChildTeam($team_list, $input['team_id']);
				// return $team_arr;
				$teams = $teams->whereIn('id', $team_arr);

			}
			$teams = $teams->get();
			return response()->json($teams);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Search Team Failed!']);
		}
	}
	public function teamsLeader() {
		try {
			$users = User::whereNull('team_id')->get();
			$teams = Team::active()->get();
			$teams = getTeamTree($teams);
			return response()->json(['users' => $users, 'teams' => $teams]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Teams, Leader Failed!']);
		}
	}
	public function addUserToTeam(Request $request) {
		try {
			$id_user = $request->id;
			$id_team = $request->id_team;

			//Check User
			$user = User::find($id_user);
			if ($user->team_id != null) {
				return response()->json(['status' => 'error', 'message' => 'Add User to Team Failed! This User is inside other team!']);
			}
			$user->update(['team_id' => $id_team]);

			$teams_user = User::select('name', 'email', 'id')->where('team_id', $id_team)->latest()->get();
			$user_list = User::select('name', 'email', 'id')->whereNull('team_id')->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Add User to Team Successfully!', 'teams_user' => $teams_user, 'user_list' => $user_list]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Add User to Team Failed!']);
		}
	}

	public function removeUserOutTeam(Request $request) {
		try {
			$id_user = $request->id;
			$id_team = $request->id_team;

			$user = User::find($id_user);
			if ($user->team_id != $id_team) {
				return response()->json(['status' => 'error', 'message' => 'Remove User Failed!']);
			}
			$user->update(['team_id' => Null]);

			$teams_user = User::select('name', 'email', 'id')->where('team_id', $id_team)->latest()->get();
			$user_list = User::select('name', 'email', 'id')->whereNull('team_id')->latest()->get();

			return response()->json(['status' => 'success', 'message' => 'Remove User Successfully!', 'teams_user' => $teams_user, 'user_list' => $user_list]);
		} catch (\Exception $e) {
			\log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Remove User Failed!']);
		}
	}
	public function getLeader($id) {
		$team = User::find($id);
		return response()->json($team);
	}
}
