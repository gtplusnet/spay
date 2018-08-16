<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_forget_password extends Model
{
    protected $table = 'tbl_forget_password';
	protected $primaryKey = "forget_pass_id";
	public $timestamps = false;
}
