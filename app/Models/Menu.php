<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model {
	protected $table = 'menus';
	protected $fillable = [
		'name', 'url', 'icon', 'id_parent',
	];

	public function menuParent() {
		return $this->belongsTo(self::class, 'id_parent', 'id');
	}
}
