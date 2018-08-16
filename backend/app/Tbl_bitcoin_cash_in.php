<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_bitcoin_cash_in extends Model
{
    protected $table = 'tbl_bitcoin_cash_in';
	protected $primaryKey = "bitcoin_cash_in_id";
	public $timestamps = false;

	public function scopeMemberLog($query){
		$query
		->leftJoin("tbl_member_log", "tbl_member_log.member_log_id", "=", "tbl_bitcoin_cash_in.member_log_id");
	}

	public function scopeMemberAddress($query){
		$query
		->leftJoin("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
	}


	public function scopeMember($query){
		$query
		->leftJoin("users", "users.id", "=", "tbl_member_address.member_id");
	}
}