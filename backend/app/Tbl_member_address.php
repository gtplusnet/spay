<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_member_address extends Model
{
    protected $table = 'tbl_member_address';
	protected $primaryKey = "member_address_id";
	public $timestamps = false;

	public function scopeJoinCoin($query, $member_id = 0, $coin_name = "")
	{
		$query->join('users', 'users.id', '=', 'tbl_member_address.member_id')
			  ->join('tbl_coin', 'tbl_coin.coin_id', '=', 'tbl_member_address.coin_id');

		if ($member_id != 0) 
		{
			$query->where('tbl_member_address.member_id', $member_id);
		}

		if ($coin_name != "") 
		{
			$query->where('tbl_coin.coin_name', $coin_name);
		}
	}

	public function scopeJoinCoinOnly($query, $member_id = 0, $coin_name = "")
	{
		$query->join('tbl_coin', 'tbl_coin.coin_id', '=', 'tbl_member_address.coin_id');

		if ($member_id != 0) 
		{
			$query->where('tbl_member_address.member_id', $member_id);
		}

		if ($coin_name != "") 
		{
			$query->where('tbl_coin.coin_name', $coin_name);
		}
	}

	public function scopeJoinOther($query)
	{
		$query->join('tbl_other_info', 'tbl_other_info.user_id', '=', 'tbl_member_address.member_id');
	}
}
