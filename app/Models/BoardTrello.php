<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoardTrello extends Model
{
    protected $table = "board_trellos";
    protected $fillable = [
    	'url','id_board','name'
    ];
}
