<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExtendTask;
use App\Models\TableTrello;
use App\Models\task;
use App\Models\TaskDetail;
use App\User;
use DB;
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

		DB::beginTransaction();
		try {
			$input = $request->all();
			$input['id_board'] = $request->idBoard;
			$input['des'] = $request->desc;
			$input['created_by'] = $input['user_id'];
			$task = task::create($input);

			//get id user assign
			$assign_arr = $input['idMembers'];
			$users = User::whereIn('id_trello', $assign_arr)->get();

			$user_name_arr = [];

			foreach ($users as $user) {
				$detail_arr[] = [
					'id_task' => $task->id,
					'user_id' => $user->id,
					'status' => 1,
					'created_at' => now(),
				];
				$user_name_arr[] = $user->name;
			}
			$user_name_str = implode('|', $user_name_arr);
			$values = [
				[
					"", '', $input['name'], $user_name_str, 'NEW', '', $input['due'], '', '', '', $input['id_trello'],
				],
			];
			// Add data to google sheet
			appendRow($values);
			// return $input;
			TaskDetail::insert($detail_arr);
			DB::commit();
			return response()->json(['status' => 'success', 'message' => 'Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Failed!']);
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
			$tasks = TaskDetail::where('user_id', $id)->with('task')->latest()->get();
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

	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		DB::beginTransaction();
		try {
			$input = $request->all();
			$task_detail = TaskDetail::find($id);
			$detail_arr = [
				'progressing' => $input['detail_progressing'],
				'status' => $input['status'],
			];

			//Calculate Progressing Main Task
			$arr = [
				'id_task' => $input['id'],
				'main_task' => $input['main_id'],
				'progressing' => $input['detail_progressing'],
			];
			$main_progressing = self::calculation($arr);
			$input['progressing'] = $main_progressing;
			if ((int) $main_progressing == 100) {
				$input['status'] = 3;
			}

			// Detail Task Update
			$update_detail = $task_detail->update($detail_arr);
			$main_task_update = $task_detail->task->update($input);

			//Update google sheet
			$position = 0;
			$sheet_rows = getAllRows();
			foreach ($sheet_rows['values'] as $key => $row) {
				if (isset($row[10]) && $row[10] == $task_detail->task->id_trello) {
					$position = $key;
					break;
				}
			}
			//Update google sheet
			$task_info = task::find($input['id']);
			$user_arr = [];
			foreach ($task_info->tasks_details as $detail) {
				$user_arr[] = " " . $detail->user->name . " ";
			}
			$user_name_str = implode(';', $user_arr);
			if ($input['status'] == 3) {
				$finish_date = now()->format('Y-m-d H:i:s');
				$due = \Carbon\Carbon::parse($task_info->due);
				$different_date = \Carbon\Carbon::parse($finish_date)->diffInDays($due);
			} else {
				$finish_date = "";
				$different_date = "";
			}
			$values = [
				[$input['name'], $user_name_str, statusTask()[$input['status']], $input['progressing'], $task_info->due, $finish_date, $different_date],
			];
			$position = (int) $position + 1;

			$range = "C" . $position . ":" . "I" . $position;
			updateRow($values, $position, $range);

			$tasks = TaskDetail::where('user_id', $input['user_id'])->with('task')->latest()->get();
			DB::commit();
			return response()->json(['status' => 'success', 'message' => "Update Successfully!", 'tasks' => $tasks]);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
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
		//
	}

	public function search(Request $request) {
		try {
			$data = $request->all();

			//Check token_api's User
			$count_user = User::where('id', $data['user_id'])->count();
			if ($count_user == 0) {
				return response()->json(['status' => 'error', 'message' => 'User Not Existed!']);
			}
			$tasks = TaskDetail::where('user_id', $data['user_id'])->with('task');
			if ($data['status'] == 'all') {} else {
				if ($data['status'] == '3') {
					$tasks = $tasks->whereDate('due', '>', today());
				} else {
					$tasks = $tasks->where('status', $data['status']);
				}
			}
			if ($data['name'] != "") {
				$name = "%" . str_replace(" ", "%", $data['name']) . "%";
				$tasks = $tasks->whereHas('task', function ($query) use ($name) {
					$query->where('name', 'like', $name);
				});
			}
			if ($data['from'] != "" && $data['to'] != "") {
				$tasks = $tasks->whereHas('task', function ($query) use ($data) {
					$query->whereBetween('date_start', [$data['from'], $data['to']]);
				});
			}

			$tasks = $tasks->latest()->get();

			return response()->json($tasks);

		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Tasks Failed!']);
		}
	}
	public function searchTotal(Request $request) {
		try {
			$data = $request->all();
			$tasks = task::with('tasks_details');
			$tasks = $tasks->whereHas('tasks_details', function ($q) use ($data) {
				$q->where('user_id', $data['user_id']);
			});
			if ($data['status'] == 'all') {} else {
				$tasks = $tasks->where('status', $data['status']);
			}
			if ($data['from'] != "" && $data['to'] != "") {
				$tasks = $tasks->whereBetween('date_start', [$data['from'], $data['to']]);
			}
			if ($data['name'] != "") {
				$name = "%" . str_replace(" ", "%", $data['name']) . "%";
				$tasks = $tasks->where('name', 'like', $name);
			}
			$tasks_list = $tasks->latest()->get()->toArray();

			$status = [
				[
					'class' => 'success',
					'count' => $tasks->where('status', 1)->count(),
					'status' => 'New Tasks',
					'icon' => 'fa fa-envelope',
				],
				[
					'class' => 'warning',
					'count' => $tasks->where('status', 2)->count(),
					'status' => 'Pending Tasks',
					'icon' => 'fa fa-envelope-open',
				],
				[
					'class' => 'danger',
					'count' => $tasks->whereDate('due', '>', today())->count(),
					'status' => 'Expired Tasks',
					'icon' => 'fa fa-hourglass-end',
				],
				[
					'class' => 'secondary',
					'count' => $tasks->where('status', 4)->count(),
					'status' => 'Reopen Tasks',
					'icon' => 'fa fa-envelope-open-o',
				],
			];

			return response()->json(['tasks' => $tasks_list, 'status' => $status]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Tasks Failed!']);
		}
	}
	public function detail_task($id_board) {
		try {
			$lists = TableTrello::where('id_board', $id_board)->get();
			return $lists;
		} catch (\Exceoption $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get detail task Failed!']);
		}
	}
	public function calculate(Request $request) {
		try {
			$input = $request->all();
			$main_progressing = self::calculation($input);
			return response()->json($main_progressing);
		} catch (\Exception $e) {
			\log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Calculate progressing Failed!']);
		}
	}
	public static function calculation($input) {
		$id_task = $input['id_task'];
		$progressing = $input['progressing'];
		$main_task = $input['main_task'];

		$other_tasks = TaskDetail::where([['id_task', $main_task], ['id', '!=', $id_task]])->get();
		$sum_progressing = (int) $other_tasks->sum('progressing') + (int) $progressing;
		$count_tasks = $other_tasks->count() + 1;
		$main_progressing = round($sum_progressing / $count_tasks, 2);

		return $main_progressing;
	}
	public function extendTask(Request $request) {
		DB::beginTransaction();
		try {
			$input = $request->all();
			$input['expired'] = $input['old_deadline'];
			$extend = ExtendTask::create($input);
			$extend->task->update(['due' => $input['expired_date']]);
			$tasks = TaskDetail::where('user_id', $input['user_id'])->with('task')->get();
			DB::commit();
			return response()->json(['status' => 'success', 'message' => 'Extend Task Successfully!', 'tasks' => $tasks]);
		} catch (\Exception $e) {
			DB::rollBack();
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Extend Task Failed!']);
		}
	}
}
