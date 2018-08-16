<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_member_log extends Model
{
    protected $table = 'tbl_member_log';
	protected $primaryKey = "member_log_id";
	public $timestamps = false;

	public function scopeMemberAddress($query)
	{
		$query->join("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
	}

	public function scopeProof($query)
	{
		$query
		->leftJoin("tbl_cash_in_proof", "tbl_cash_in_proof.member_log_id", "=", "tbl_member_log.member_log_id");
	}

	public function scopeEth($query)
	{
		$query
		->leftJoin("tbl_cash_in_eth", "tbl_cash_in_eth.member_log_id", "=", "tbl_member_log.member_log_id");
	}

	public function scopeMethod($query)
	{
		$query
		->leftJoin("tbl_cash_in_method", "tbl_cash_in_method.cash_in_method_id", "=", "tbl_cash_in_proof.cash_in_method_id");
	}

	public function scopeMember($query)
    {
    	$query->join("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
        $query->join("users", "users.id", "=", "tbl_member_address.member_id");
        $query->join("tbl_coin", "tbl_coin.coin_id", "=", "tbl_member_address.coin_id");
    }

	public function scopeMemberEth($query)
	{
		$query
		->leftJoin("users", "users.id", "=", "tbl_cash_in_eth.cash_in_by");
	}

	public function scopeCoin($query)
	{
		$query
		->leftJoin("tbl_member_address", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")
		->leftJoin("tbl_coin", "tbl_coin.coin_id", "=", "tbl_member_address.coin_id");
	}

	public static function scopeJoinBitcoinCashIn($query, $member_address_id = 0, $log_status = '', $log_message = '')
	{
		$query->join('tbl_bitcoin_cash_in', 'tbl_bitcoin_cash_in.member_log_id', '=', 'tbl_member_log.member_log_id');
		
		if($member_address_id != 0)
		{
			$query->where('member_address_id', $member_address_id);
		}
		
		if($log_status != '')
		{
			$query->where('log_status', $log_status);
		}
		
		if($log_message != '')
		{
			$query->where('log_method', $log_message);
		}
	}

	public static function scopeJoinAutomaticCashIn($query, $member_address_id = 0, $log_status = '', $log_message = '')
	{
		$query->join('tbl_automatic_cash_in', 'tbl_automatic_cash_in.member_log_id', '=', 'tbl_member_log.member_log_id');
		
		if($member_address_id != 0)
		{
			$query->where('member_address_id', $member_address_id);
		}
		
		if($log_status != '')
		{
			$query->where('log_status', $log_status);
		}
		
		if($log_message != '')
		{
			$query->where('log_method', $log_message);
		}
	}
	protected $hidden = [
        'password', 'remember_token',
    ];
}
