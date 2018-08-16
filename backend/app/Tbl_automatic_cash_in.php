<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_automatic_cash_in extends Model
{
    protected $table = 'tbl_automatic_cash_in';
	protected $primaryKey = "automatic_cash_in_id";
	public $timestamps = false;

	public function scopeJoinTransactions($query, $param)
	{
		$query = $query
		->join('tbl_member_log', 'tbl_automatic_cash_in.member_log_id', '=', 'tbl_member_log.member_log_id')
		->where(function ($query) use ($param)
		{
			$query->where('tbl_member_log.log_method', $param["log_method"])->orWhere('tbl_member_log.log_method', $param["log_method_accepted"]);
		})
		->join('tbl_member_address', 'tbl_member_log.member_address_id', '=', 'tbl_member_address.member_address_id')
		->where('tbl_member_address.coin_id', 4)
		->join('users', 'tbl_member_address.member_id', '=', 'users.id');

		
	}

	// public function scopeJoinMemberLog($query)
	// {
	// 	$query->join('tbl_member_address')
	// }
}
