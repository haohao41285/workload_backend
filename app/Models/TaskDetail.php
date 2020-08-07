<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskDetail extends Model {
	protected $table = 'task_details';
	protected $fillable = [
		'id_task', 'user_id',
		'status', // 1: New,2: Progressing,3: Done, 4: Reopen
		'note',
		'progressing',
	];

	public function task() {
		return $this->belongsTo(task::class, 'id_task', 'id');
	}

	public function user(){
		return $this->belongsTo(User::class,'user_id','id');
	}
}
