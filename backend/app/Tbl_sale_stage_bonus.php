<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_sale_stage_bonus extends Model
{
    protected $table = 'tbl_sale_stage_bonus';
	protected $primaryKey = "sale_stage_bonus_id";
	public $timestamps = false;
}