<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_position_requirements extends Model
{
    protected $table = 'tbl_position_requirements';
	protected $primaryKey = "requirement_id";
	public $timestamps = false;

	public function scopeJoinMember($query)
    {
        $query->join("users", "users.id", "=", "tbl_position_requirements.member_id");
        $query->join("tbl_other_info","tbl_other_info.user_id","=", "id");
        $query->join("tbl_member_position","tbl_member_position.member_position_id","=","tbl_other_info.member_position_id")->select("tbl_member_position.member_position_name","tbl_member_position.member_position_id","users.*","tbl_position_requirements.*");
        // $query->join("tbl_member_position","tbl_member_position.member_position_id","=","id")->select("tbl_member_position.member_position_name","users.*","tbl_position_requirements.*");
    }
    
    protected $hidden = [
        'password', 'remember_token',
    ];
}
