<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_referral extends Model
{
    protected $table = 'tbl_referral';
	protected $primaryKey = "referral_id";
	public $timestamps = false;

	public function scopeMember($query)
    {
        $query
        ->join("users", "users.id", "=", "tbl_referral.referral_user_id");
    }

}
