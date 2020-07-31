<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskDetail;
use App\User;
use Illuminate\Http\Request;

class TaskController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {

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

		try {
			$tasks = TaskDetail::where('user_id', $id)->with('task')->get();
			return response()->json($tasks);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Task Failed']);
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
		//
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

		$data = $request->all();

		//Check token_api's User
		$count_user = User::where([['id', $data['user_id'], ['_token_api', $data['token']]]])->count();
		if ($count_user == 0) {
			return response()->json(['status' => 'error', 'message' => 'Failed!']);
		}
		$tasks = TaskDetail::where('user_id', $data['user_id']);
		if ($data['status'] == 'all') {} else {
			$tasks = $tasks->where('status', $data['status']);
		}
		// $tasks = $tasks->whereHas('task', function ($query) use ($data) {
		// 	$query->whereBetween('date_start', [$data['from'], $data['to']]);
		// });
		$tasks = $tasks->get();

		return $tasks;

		return \Session::get('_token');
		return response()->json($request->all());

		// $tasks = TaskDetail::where('user_id',$)
	}
}
