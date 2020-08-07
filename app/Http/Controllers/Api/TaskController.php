<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaskDetail;
use App\User;
use Illuminate\Http\Request;
use DB;
use App\Models\task;

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
		try{
			$input = $request->all();
			$input['id_board'] = $request->idBoard;
			$input['des'] = $request->desc;
			$task = task::create($input);
			$detail_arr = [
				'id_task' => $task->id,
				'user_id' => $input['user_id'],
				'status' => 1
			];
			// return $input;
			TaskDetail::create($detail_arr);
			DB::commit();
			return response()->json(['status'=>'success','message'=>'Successfully!']);
		}catch(\Exception $e){
			\Log::info($e);
			DB::rollBack();
			return response()->json(['status'=>'error','message'=>'Failed!']);
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
		try{
			$data = $request->all();

			//Check token_api's User
			$count_user = User::where([['id', $data['user_id'], ['_token_api', $data['token']]]])->count();
			if ($count_user == 0) {
				return response()->json(['status' => 'error', 'message' => 'Failed!']);
			}
			$tasks = TaskDetail::where('user_id', $data['user_id'])->with('task');
			if ($data['status'] == 'all') {} else {
				if($data['status'] == '3'){
					$tasks = $tasks->whereDate('due', '>' ,today());
				}else{
					$tasks = $tasks->where('status', $data['status']);
				}
			}
			if($data['name'] != ""){
				$name = "%".str_replace(" ", "%", $data['name'])."%";
				$tasks = $tasks->whereHas('task', function ($query) use ($name) {
					$query->where('name','like',$name);
				});
			}
			if($data['from'] != "" && $data['to'] != ""){
				$tasks = $tasks->whereHas('task', function ($query) use ($data) {
					$query->whereBetween('date_start', [$data['from'], $data['to']]);
				});
			}
				
			$tasks = $tasks->latest()->get();

			return response()->json($tasks);

		}catch(\Exception $e){
			\Log::info($e);
			return response()->json(['status'=>'error','message'=>'Get Tasks Failed!']);
		}
	}
	public function searchTotal(Request $request){
		try{
			$data = $request->all();
			$tasks = task::with('tasks_details');
			$tasks = $tasks->whereHas('tasks_details', function($q) use ($data){
				$q->where('user_id',$data['user_id']);
			});
			if ($data['status'] == 'all') {} else {
				$tasks = $tasks->where('status', $data['status']);
			}
			if ($data['from'] != "" && $data['to'] != ""){
				$tasks = $tasks->whereBetween('date_start', [$data['from'],$data['to']]);
			}
			if($data['name'] != ""){
				$name = "%".str_replace(" ", "%", $data['name'])."%";
				$tasks = $tasks->where('name','like', $name);
			}
			$tasks_list = $tasks->latest()->get()->toArray();

			$status =[
				[
					'class' => 'success',
					'count' => $tasks->where('status',1)->count(),
					'status' => 'New Tasks',
					'icon' => 'fa fa-envelope'
				],
				[
					'class' => 'warning',
					'count' => $tasks->where('status',2)->count(),
					'status' => 'Pending Tasks',
					'icon'=>'fa fa-envelope-open'
				],
				[
					'class' => 'danger',
					'count' => $tasks->whereDate('due' ,'>',today())->count(),
					'status' => 'Expired Tasks',
					'icon' => 'fa fa-hourglass-end'
				],
				[
					'class' => 'secondary',
					'count' => $tasks->where('status',4)->count(),
					'status' => 'Reopen Tasks',
					'icon' => 'fa fa-envelope-open-o'
				]
			]; 

			return response()->json(['tasks'=>$tasks_list,'status'=>$status]);
		}catch(\Exception $e){
			\Log::info($e);
			return response()->json(['status'=>'error','message'=>'Get Tasks Failed!']);
		}
	}
}
