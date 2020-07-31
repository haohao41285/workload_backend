<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class task extends Model {
	protected $table = "tasks";
	protected $fillable = [
		'name', 'des', 'date_start', 'due', 'idList', 'progressing', 'time_work_total',
		'status', // 1: New, 2: Progressing, 3: Done, 4: Reopen
	];

	public function tasks_details() {
		return $this->hasMany(TaskDetail::class, 'id_task', 'id');
	}
}
