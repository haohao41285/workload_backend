<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardTrello extends Model {
	protected $table = "board_trellos";
	protected $fillable = [
		'url',
		'id_board', // from Api trello
		'name',
	];
	public function list() {
		return $this->hasMany(TableTrello::class, 'id_board', 'id');
	}
	public function tasks() {
		return $this->hasMany(task::class, 'idBoard', 'id');
	}
}
