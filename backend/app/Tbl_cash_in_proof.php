<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_cash_in_proof extends Model
{
    protected $table = 'tbl_cash_in_proof';
	protected $primaryKey = "cash_in_proof_id";
	public $timestamps = false;

	public function scopeJoinMember($query)
	{
		$query
		->leftJoin("users", "users.id", "=", "tbl_cash_in_proof.cash_in_by")
		->leftJoin("tbl_cash_in_method", "tbl_cash_in_method.cash_in_method_id", "=", "tbl_cash_in_proof.cash_in_method_id")
		->select("tbl_cash_in_proof.*", "users.first_name", "users.last_name", "tbl_cash_in_method.cash_in_method_name");
	}
}
