<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AddCommentTrelloJob;
use App\Models\TaskLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LogTaskController extends Controller {
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
		try {
			$input = $request->all();
			//Check log others are the same id_task
			$old_time_total = TaskLog::where('id_task', $input['id_task'])->sum('time_work_per_day');
			$time_total = (int) $old_time_total + (int) $input['time_work_per_day'];
			$task_log = TaskLog::create($input);
			$update_task = $task_log->task->update(['time_work_total' => $time_total]);
			$job_arr = [
				'key' => $input['key'],
				'token' => $input['token'],
				'url' => ENV('TRELLO_URL') . "cards/" . $input['id_trello'] . "/actions/comments",
				'text' => $input['comment'],
			];
			$update_trello = (new AddCommentTrelloJob($job_arr))->delay(Carbon::now()->addSeconds(10));
			dispatch($update_trello);

			return response()->json(['status' => 'success', 'message' => 'Add Log Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Add Log Failed!']);
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
			$logs = TaskLog::where('id_task_detail', $id)->latest()->get();
			return response()->json($logs);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Log Failed!']);
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
}
