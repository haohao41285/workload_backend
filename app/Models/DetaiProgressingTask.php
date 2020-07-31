<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetaiProgressingTask extends Model {
	protected $table = "detai_progressing_tasks";
	protected $fillable = [
		'id_task_detail', 'title', 'user_id',
		'status', // 0: New, 1: Done
	];
}
