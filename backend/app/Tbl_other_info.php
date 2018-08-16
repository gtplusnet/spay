<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_other_info extends Model
{
    protected $table = 'tbl_other_info';
	protected $primaryKey = "info_id";
	public $timestamps = false;

	public function scopeJoinDetails($query)
    {
        $query
        ->join("users", "users.id", "=", "tbl_other_info.user_id")
        ->leftJoin("tbl_referral", "tbl_referral.referral_id", "=", "tbl_other_info.referrer_id")
        ->leftJoin("tbl_member_position", "tbl_member_position.member_position_id", "=", "tbl_other_info.member_position_id");
    }

    
    protected $hidden = [
        'password', 'remember_token',
    ];
}
