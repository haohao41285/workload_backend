<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\AddRowSpreadSheetJob;
use App\Jobs\MailRequestExtendTaskJob;
use App\Jobs\ResponseExtendTaskJob;
use App\Jobs\UpdateTrelloJob;
use App\Models\ExtendTask;
use App\Models\Project;
use App\Models\TableTrello;
use App\Models\task;
use App\Models\TaskDetail;
use App\User;
use Carbon\Carbon;
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
			//get follower task
			$follower = User::where('id_trello', $input['follower'])->first()->id;
			$input['follower'] = $follower;

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

			$project = Project::find($input['id_project'])->name;
			$values = [
				[
					$input['team'], $project, $input['name'], $user_name_str, 'NEW', '', $input['due'], '', '', '', $input['id_trello'],
				],
			];
			// Add data to google sheet
			// appendRow($values);
			$addSpreadSheetJob = (new AddRowSpreadSheetJob($values))->delay(Carbon::now()->addSeconds(10));
			dispatch($addSpreadSheetJob);

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
			$task_info = task::find($input['main_id']);
			$user_arr = [];
			foreach ($task_info->tasks_details as $detail) {
				$user_arr[] = " " . $detail->user->name . " ";
			}
			$user_name_str = implode(';', $user_arr);
			if ($input['status'] == 3) {
				$finish_date = now()->format('Y-m-d H:i:s');
				$due = \Carbon\Carbon::parse($task_info->due);
				$different_date = \Carbon\Carbon::parse($finish_date)->diffInDays($due);
				$task_detail->task->update(['date_finish' => $finish_date]);
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

			//Update trello
			$idList = $task_detail->task->idList;
			$job_arr = [
				'key' => $input['key'],
				'token' => $input['token'],
				'url' => ENV('TRELLO_URL') . "cards/" . $task_detail->task->id_trello,
				'name' => $input['name'],
				'desc' => $input['desc'],
				'dueComplete' => $input['status'] == 3 ? true : false,
				'idList' => $task_detail->task->idList,
			];
			$update_trello = (new UpdateTrelloJob($job_arr))->delay(Carbon::now()->addSeconds(10));
			dispatch($update_trello);

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
			if ($data['id_project'] != "") {
				$tasks = $tasks->whereHas('task', function ($query) use ($data) {
					$query->where('id_project', $data['id_project']);
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
			$tasks = task::with('tasks_details')->where('status', '!=', 3);
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
			$task = task::find($input['id_task']);

			if ($input['user_id'] == $task->follower) {
				$input['expired'] = $input['old_deadline'];
				$input['status'] = 1;
				$extend = ExtendTask::create($input);
				$extend->task->update(['due' => $input['expired_date']]);

				$tasks = TaskDetail::where('user_id', $input['user_id'])->with('task')->get();

				DB::commit();
				return response()->json(['status' => 'success', 'message' => 'Extend Task Successfully!', 'mail' => '0', 'tasks' => $tasks]);
			} else {
				$input['expired'] = $input['expired_date'];
				$input['token'] = Hash::make('Vietguys' . $input['expired']);
				$extend = ExtendTask::create($input);
				$follower_id = $extend->task->follower;
				$follower = User::find($follower_id);
				$job_arr = [
					'reciever_email' => $follower->email,
					'name' => $follower->name,
					'subject' => '[INTERNAL]-Yêu cầu gia hạn Task' . $extend->task->name,
					'view' => 'mail.request_extend',
					'link' => ENV('REAL_DOMAIL') . "task-request?token=" . $input['token'],
				];
				// \Mail::send($job_arr['view'], $job_arr, function ($m) use ($job_arr) {
				// 	$m->from(env('MAIL_USERNAME'));
				// 	$m->to($job_arr['reciever_email'], $job_arr['name'])->subject($job_arr['subject']);
				// });
				$addSpreadSheetJob = (new MailRequestExtendTaskJob($job_arr))->delay(Carbon::now()->addSeconds(10));
				dispatch($addSpreadSheetJob);

				DB::commit();
				return response()->json(['status' => 'success', 'message' => "Send Request Successfully", 'tasks' => $tasks, 'mail' => 1]);
			}

		} catch (\Exception $e) {
			DB::rollBack();
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Extend Task Failed!']);
		}
	}
	public function byToken($token) {
		try {
			$extend_task = TaskDetail::join('extend_tasks', function ($join) {
				$join->on('task_details.id', 'extend_tasks.id_detail_task');
			})
				->join('tasks', function ($join) {
					$join->on('task_details.id_task', 'tasks.id');
				})
				->join('users', function ($join) {
					$join->on('task_details.user_id', 'users.id');
				})
				->where('extend_tasks.token', $token)
				->select('task_details.progressing', 'task_details.id as detail_id', 'users.name', 'users.email', 'users.full_name', 'tasks.date_start', 'tasks.id as task_id', 'due', 'tasks.idList', 'tasks.id_trello', 'tasks.progressing as main_progressing', 'tasks.name as task_name', 'extend_tasks.expired', 'extend_tasks.note', 'extend_tasks.status', 'extend_tasks.id as extend_task_id')
				->first();

			if ($extend_task->status == 1) {
				return response()->json(['status' => 'error', 'message' => 'This request has been ACCEPTED']);
			} elseif ($extend_task->status == 2) {
				return response()->json(['status' => 'error', 'message' => 'This request has been CANCEL']);
			}
			return response()->json($extend_task);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Extend Task Failed!']);
		}
	}
	public function responseExtend(Request $request) {
		DB::beginTransaction();
		try {
			$input = $request->all();

			$tasks = task::find($input['id_task']);
			$detail_task = TaskDetail::with('user')->find($input['id_detail']);
			$extend_task = ExtendTask::find($input['id_extend']);

			$old_expired = $tasks->due;
			$requested_expired = $extend_task->expired;

			if ($input['status'] == '1') {

				if ($input['expired'] == "") {
					$new_expired = $extend_task->expired;
				} else {
					$new_expired = $input['expired_time'];
				}

				//Update expired request
				$status = 'Chấp nhận';
				$tasks->update(['due' => $new_expired]);
				$extend_task->update(['expired' => $old_expired, 'status' => 1]);

			} else {
				// Cancel Extend Task
				$status = "Không được chấp nhận";
				$new_expired = "";
				ExtendTask::find($input['id_extend'])->update(['status' => 2]);
			}

			//Send Mail to response
			$job_arr = [
				'reciever_email' => $detail_task->user->email,
				'name' => $detail_task->user->name,
				'subject' => '[INTERNAL]-Yêu cầu gia hạn Task' . $tasks->name,
				'view' => 'mail.response_extend_task',
				'task_name' => $tasks->name,
				'old_expired' => $old_expired,
				'requested_expired' => $requested_expired,
				'response_expired' => $new_expired,
				'status' => $status,
				'note' => $input['message'],
			];

			$response_extend_task = (new ResponseExtendTaskJob($job_arr))->delay(Carbon::now()->addSeconds(10));
			dispatch($response_extend_task);
			DB::commit();

			return response()->json(['status' => 'success', 'message' => 'Response Extend Task Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Response Extend Task Failed!']);
		}
	}
}
