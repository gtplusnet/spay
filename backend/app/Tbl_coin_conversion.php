<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_coin_conversion extends Model
{
    protected $table = 'tbl_coin_conversion';
	protected $primaryKey = "coin_conversion_id";
	public $timestamps = false;
}
