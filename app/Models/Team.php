<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model {
	protected $table = "teams";
	protected $fillable = [
		'name', 'id_leader', // id from users table
		'slogan', 'id_parent',
	];

	public function leader() {
		return $this->belongsTo('App\User', 'id_leader', 'id');
	}

	public function users() {
		return $this->hasMany('App\User', 'team_id', 'id');
	}
	public function teamParent() {
		return $this->belongsTo(self::class, 'id_parent', 'id');
	}
	public function teams() {
		return $this->hasMany(self::class, 'id_parent', 'id');
	}
	public function scopeActive() {
		return $this->where('active', 1);
	}
}
