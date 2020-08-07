<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableTrello extends Model
{
    protected $table = "table_trellos";
    protected $fillable = [
    	'name','idList',
    	'id_board', // id board_trellos table
    ];
}
