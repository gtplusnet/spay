<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_release_logs extends Model
{
    protected $table = 'tbl_release_logs';
	protected $primaryKey = "release_log_id";
	public $timestamps = false;

	public function scopeJoinMember($query)
    {
        $query->join("tbl_member_address", "tbl_member_address.member_address", "=", "tbl_release_logs.released_from");
        $query->join("users", "users.id", "=", "tbl_member_address.member_id");
    }
}
