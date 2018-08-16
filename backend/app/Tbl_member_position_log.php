<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_member_position_log extends Model
{
    protected $table = 'tbl_member_position_logs';
	protected $primaryKey = "member_position_log_id";
	public $timestamps = false;
}
