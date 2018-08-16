<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_member_position extends Model
{
    protected $table = 'tbl_member_position';
	protected $primaryKey = "member_position_id";
	public $timestamps = false;
}
