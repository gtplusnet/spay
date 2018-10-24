<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_kyc_proof_v2 extends Model
{
    protected $table = 'tbl_kyc_proof_v2';
	protected $primaryKey = "proof_id";
	public $timestamps = false;

	// public function scopeMemberLog($query){
	// 	$query
	// 	->leftJoin("tbl_member_log", "tbl_member_log.member_log_id", "=", "tbl_bitcoin_cash_in.member_log_id");
	// }
}