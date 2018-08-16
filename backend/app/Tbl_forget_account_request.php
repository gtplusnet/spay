<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_forget_account_request extends Model
{
    protected $table = 'tbl_forget_account_request';
	protected $primaryKey = "forget_account_request_id";
	public $timestamps 	= false;
}
