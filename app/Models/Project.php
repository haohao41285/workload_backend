<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {
	protected $table = "projects";
	protected $fillable = [
		'name', 'desc', 'id_team', 'created_by',
		'status', // 1: New, 2: Progressing, 3: Done, 4 : Cancel
	];

	public function createdBy() {
		return $this->belongsTo('App\User', 'created_by', 'id');
	}
	public function team() {
		return $this->belongsTo(Team::class, 'id_team', 'id');
	}
	public function tasks() {
		return $this->hasMany(task::class, 'id_project', 'id');
	}
}
