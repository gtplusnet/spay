<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_referral_bonus_log extends Model
{
    protected $table = 'tbl_referral_bonus_log';
	protected $primaryKey = "referral_bonus_log_id";
	public $timestamps = false;

	public function scopeMemberTo($query)
	{
		$query->join('users', 'users.id', '=', 'tbl_referral_bonus_log.member_to');
	}

	public function scopeMemberFrom($query)
	{
		$query->join('users', 'users.id', '=', 'tbl_referral_bonus_log.member_from');
	}

	public function scopeLogTo($query)
	{
		$query->join('tbl_member_log', 'tbl_member_log.member_log_id', '=', 'tbl_referral_bonus_log.member_log_to');
	}

	public function scopeLogFrom($query)
	{
		$query->join('tbl_member_log', 'tbl_member_log.member_log_id', '=', 'tbl_referral_bonus_log.member_log_from');
	}
}
