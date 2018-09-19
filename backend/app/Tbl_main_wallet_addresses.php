<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_main_wallet_addresses extends Model
{
    protected $table = 'tbl_main_wallet_addresses';
	protected $primaryKey = "mwallet_id";
	public $timestamps 	= false;
}
