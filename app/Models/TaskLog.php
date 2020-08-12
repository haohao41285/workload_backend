<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model {
	protected $table = "task_logs";
	protected $fillable = [
		'id_task_detail',
		'time_work_per_day',
		'comment',
		'date_work',
		'id_user',
		'id_task',
	];

	public function task() {
		return $this->belongsTo(task::class, 'id_task', 'id');
	}
}
