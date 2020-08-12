<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class task extends Model {
	protected $table = "tasks";
	protected $fillable = [
		'name', 'des', 'date_start', 'due', 'idList', 'progressing', 'time_work_total',
		'status', // 1: New, 2: Progressing, 3: Done, 4: Reopen,
		'idBoard', // id from board_trellos table
		'id_trello', // id card from Trello
		'created_by',

	];

	public function tasks_details() {
		return $this->hasMany(TaskDetail::class, 'id_task', 'id');
	}
	public function board() {
		return $this->belongsTo(BoardTrello::class, 'id_board', 'idBoard');
	}
	public function user() {
		return TaskDetail::where('id_task', $this->id)->with('user')->get();
	}
	public function createdBy() {
		return $this->belongsTo('App\User', 'created_by', 'id');
	}
}
