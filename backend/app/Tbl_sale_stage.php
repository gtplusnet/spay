<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_sale_stage extends Model
{
    protected $table = 'tbl_sale_stage';
	protected $primaryKey = "sale_stage_id";
	public $timestamps = false;
}
