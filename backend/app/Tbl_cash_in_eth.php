<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_cash_in_eth extends Model
{
    protected $table = 'tbl_cash_in_eth';
	protected $primaryKey = "cash_in_eth_id";
	public $timestamps = false;
}
