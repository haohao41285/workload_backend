<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtendTask extends Model {
	protected $table = "extend_tasks";
	protected $fillable = [
		'expired',
		'id_task', // from tasks
		'id_detail_task', // from task_detail
		'note',
		'status', // 0: New, 1: Active, 2: Cancel
		'token',
	];

	public function task() {
		return $this->belongsTo(task::class, 'id_task', 'id');
	}
	public function detail_task() {
		return $this->belongsTo(TaskDetail::class, 'id_detail_task', 'id');
	}
}
