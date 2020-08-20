<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {
	protected $table = "roles";
	protected $fillable = [
		'name', 'active',
		'created_by', // in form users
	];

	public function createdBy() {
		return $this->belongsTo('App\User', 'created_by', 'id');
	}
	public function permissions() {
		return $this->hasOne(RolePermission::class, 'id_role', 'id');
	}
}
