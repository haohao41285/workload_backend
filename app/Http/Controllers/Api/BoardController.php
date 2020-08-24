<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BoardTrello;
use App\Models\Project;
use App\Models\TableTrello;
use App\User;
use DB;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class BoardController extends Controller {
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		// return 'ok';
		try {
			$boards = BoardTrello::with('list')->get();
			$lists = [];
			foreach ($boards as $key => $board) {
				foreach ($board->list as $list) {
					$lists[$board->id][] = [
						'name' => $list->name,
						'idList' => $list->idList,
						'id' => $list->id,
					];
				}
			}
			$projects = Project::where(function ($query) {
				$query->where('status', 1)
					->orWhere('status', 2);
			})
				->latest()->get();
			$users = User::where('id_trello', '!=', '')->where('id_trello', '!=', null)->get();
			return response()->json(['lists' => $lists, 'boards' => $boards, 'users' => $users, 'projects' => $projects]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get boards Failed!']);
		}

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
			//Check duplicate url
			$check = BoardTrello::where('url', $request->url)->count();
			if ($check > 0) {
				return response()->json(['status' => 'warning', 'message' => 'This Url has Existed!']);
			}
			$key = $request->key;
			$token = $request->token;
			//Find idBoard Trello
			$url = $request->url;
			$str_rev = strrev($url);
			$pos = strpos($str_rev, '/');
			$new_string = strrev(substr($str_rev, $pos)) . "reports.json";

			$client = new Client;
			$response = $client->request('GET', $new_string . "?key=" . $key . "&token=" . $token);
			$body = (string) $response->getBody();
			$board_info = json_decode($body);

			//Save info to database
			$board_arr = [
				'id_board' => $board_info->id,
				'name' => $board_info->name,
				'url' => $url,
			];
			$board = BoardTrello::create($board_arr);

			//Get Member of Board
			// $members = $this->getMemberTrello($board_info->id, $key, $token);
			// //Get users database
			// $old_users = User::all();
			// $old_user_arr = [];
			// foreach ($old_users as $user) {
			// 	$old_user_arr[] = $user->id_trello;
			// }
			// $member_arr = [];
			// //Compare to get new members
			// foreach ($members as $key => $member) {
			// 	if (in_array($member->id, $old_user_arr)) {} else {
			// 		$member_arr[] = [
			// 			'name' => $member->username,
			// 			'id_trello' => $member->id,
			// 			'full_name' => $member->fullName,
			// 			'email' => $member->username . '@vietguys.biz',
			// 			'password' => Hash::make(123456),
			// 		];
			// 	}

			// }
			// User::insert($member_arr);
			//End get member trello

			//Get List of Board
			$lists = $this->getListTrello($board_info->id, $key, $token);
			$list_arr = [];
			foreach ($lists as $key => $list) {
				$list_arr[] = [
					'name' => $list->name,
					'idList' => $list->id,
					'id_board' => $board->id,
				];
			}
			//Save all list to datatabse
			TableTrello::insert($list_arr);
			DB::commit();
			$boards = BoardTrello::all();

			// return $pos;
			return response()->json(['status' => 'Success', 'board' => $board, 'lists' => $list_arr, 'boards' => $boards]);
		} catch (\Exception $e) {
			\Log::info($e);
			DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Add Board Falied!']);
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
			$board = BoardTrello::find($id);
			$tasks = $board->tasks;
			if ($tasks->count() > 0) {
				return response()->json(['status' => 'error', 'message' => 'Can Not Delete! This Board contain tasks']);
			}
			$board->delete();
			$boards = BoardTrello::latest()->get();
			return response()->json(['status' => 'success', 'message' => 'Delete Successfully!', 'boards' => $boards]);
		} catch (\Exception $e) {
			return response()->json(['status' => 'error', 'message' => 'Delete Failed!']);
		}
	}

	public function search(Request $request) {
		try {
			//get all board
			$boards = BoardTrello::latest()->get();

			//Get id Board to get list card in trello
			if ($request->id_board && $request->id_board != "") {
				$idBoard = $request->id_board;
			} else {
				$idBoard = $boards->first()->id_board;
			}

			$lists = BoardTrello::where('id_board', $idBoard)->first()->list;

			return response()->json(['lists' => $lists, 'boards' => $boards]);

		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get List, Board Trello Failed!']);
		}
	}
	public function update(Request $request) {
		try {
			$idBoard = $request->idBoard;
			//Get old list
			$board = BoardTrello::where('id_board', $idBoard)->first();
			$old_list = $board->list;
			$old_list_arr = [];
			foreach ($old_list as $key => $list) {
				$old_list_arr[] = $list->idList;
			}
			$lists = $this->getListTrello($request->idBoard);

			$list_arr = [];
			foreach ($lists as $key => $list) {
				// return $list->id;
				if (in_array($list->id, $old_list_arr)) {} else {
					$list_arr[] = [
						'name' => $list->name,
						'idList' => $list->id,
						'id_board' => $board->id,
					];
				}
			}
			TableTrello::insert($list_arr);

			return response()->json(['status' => 'success', 'message' => 'Updated ' . count($list_arr) . " List"]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => "error", 'message' => 'Failed!']);
		}
	}
	public static function getListTrello($idBoard, $key, $token) {
		$url_trello = 'https://api.trello.com/1/boards/' . $idBoard . "/lists";
		$client_list = new Client;
		$response_list = $client_list->request('GET', $url_trello . "?key=" . $key . "&token=" . $token);
		$body_list = (string) $response_list->getBody();

		$lists = json_decode($body_list);
		return $lists;
	}
	public static function getMemberTrello($idBoard, $key, $token) {
		$url = 'https://api.trello.com/1/boards/' . $idBoard . '/members';
		$client = new Client;
		$response = $client->request('GET', $url . "?key=" . $key . "&token=" . $token);
		$body = (string) $response->getBody();

		$members = json_decode($body);

		return $members;
	}
	public function show(Request $r) {
		try {
			$board = BoardTrello::find($r->id);
			$list_database = [];
			foreach ($board->list as $list) {
				$list_database[] = $list->idList;
			}
			$idBoard = $board->id_board;
			//get trello board's list
			$lists = $this->getListTrello($board->id_board, $r->key, $r->token);
			$list_arr = [];

			foreach ($lists as $key => $list) {
				if (in_array($list->id, $list_database)) {} else {
					$list_arr[] = [
						'name' => $list->name,
						'idList' => $list->id,
						'id_board' => $board->id,
					];
				}
			}
			TableTrello::insert($list_arr);
			$lists = TableTrello::where('id_board', $r->id)->latest()->get();
			return response()->json(['list' => $lists, 'message' => count($list_arr) . " New List"]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get List Failed!']);
		}
	}
	public function users(Request $r) {
		try {
			$members = $this->getMemberTrello($r->id, $r->key, $r->token);
			$users = User::orderBy('name', 'desc')->get();
			return response()->json(['members' => $members, 'users' => $users]);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Get Users Failed!']);
		}
	}
	public function updateIdTrelloToUser(Request $request) {
		try {
			$user = User::find($request->id_user);
			$user->update(['id_trello' => $request->id_trello]);
			return response()->json(['status' => 'success', 'message' => 'Update Successfully!']);
		} catch (\Exception $e) {
			\Log::info($e);
			return response()->json(['status' => 'error', 'message' => 'Updated Failed!']);
		}
	}
}
