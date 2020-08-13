<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;

class ProjectController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		try {
			$project = Project::with(['team', 'createdBy'])->get();
			return response()->json($project);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => "Get Project Failed!"]);
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
			$validator = \Validator::make($request->all(), [
				'name' => 'required|unique:projects,name',
				'id_team' => 'required',
			]);
			if ($validator->fails()) {
				return response()->json([
					'status' => 'error',
					'message' => $validator->errors(),
				]);
			}
			$input = $request->all();

			Project::create($input);
			$projects = Project::with(['team', 'createdBy'])->latest()->get();
			return response()->json($projects);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Add New Project Failed!']);
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
			$project = Project::find($id);
			$project->update(['status' => $request->status]);
			$projects = Project::with(['createdBy', 'team'])->latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Update Status Successfully!', 'projects' => $projects]);
		} catch (\Exception $e) {
			$projects = Project::with(['createdBy', 'team'])->latest()->get();
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Update Status Failed!', 'projects' => $projects]);
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
			$project = Project::find($id);
			//Check Task
			if ($project->tasks->count() > 0) {
				return response()->json(['status' => 'error', 'message' => 'Delete Fail! Project may contain task']);
			}
			$project->delete();
			$projects = Project::all();
			return response()->json(['status' => 'success', 'message' => 'Delete Successfully!', 'projects' => $projects]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Delete project Failed!']);
		}
	}

	public function search(Request $request) {
		try {
			$input = $request->all();
			$projects = Project::with(['team', 'createdBy']);
			// return response()->json($projects->get());
			if ($input['name'] != "") {
				$name = "%" . str_replace(" ", "%", $input['name']) . "%";
				$projects = $projects->where('name', 'like', $name);
			}
			if ($input['from'] != "" && $input['to'] != "") {
				$projects = $projects->whereBetween('created_at', [$input['from'], $input['to']]);
			}
			if ($input['id_team'] != "" & $input['id_team'] != "all") {
				//Get all child teams
				$teams = Team::all();
				$team_childs = getIdChildTeam($teams, $input['id_team']);
				$team_childs[] = $input['id_team'];
				$projects = $projects->whereIn('id_team', $team_childs);
			}
			if ($input['status'] != "" && $input['status'] != "all") {
				$projects = $projects->where('status', $input['status']);
			}
			$projects = $projects->latest()->get();
			return response()->json($projects);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Project Faield!']);
		}
	}
}
