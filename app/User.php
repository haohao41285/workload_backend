<?php
namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject {
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password', '_token_api', 'team_id',
		'key', 'token', // Trello
		'full_name',
		'id_trello', // user id of trello
		'active',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token', '_token_api',
	];

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}

	public function team() {
		return $this->belongsTo('App\Models\Team', 'team_id', 'id');
	}
	public function tasks() {
		return $this->hasMany('App\Models\TaskDetail', 'user_id', 'id');
	}
}
