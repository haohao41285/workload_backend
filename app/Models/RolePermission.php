<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model {
	protected $tale = 'role_permissions';
	protected $fillable = [
		'id_role',
		'permissions',
		'active',
	];

	public function role() {
		return $this->belongsTo(Role::class, 'id_role', 'id');
	}
}
