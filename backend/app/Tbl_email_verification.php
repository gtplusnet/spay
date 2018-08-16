<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_email_verification extends Model
{
    protected $table = 'tbl_email_verification';
	protected $primaryKey = "verification_id";
	public $timestamps = false;
}
