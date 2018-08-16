<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_country_codes extends Model
{
    protected $table = 'tbl_country_codes';
	protected $primaryKey = "country_code_id";
	public $timestamps = false;
}
