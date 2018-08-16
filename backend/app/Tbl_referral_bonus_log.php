<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_referral_bonus_log extends Model
{
    protected $table = 'tbl_referral_bonus_log';
	protected $primaryKey = "referral_bonus_log_id";
	public $timestamps = false;
}
