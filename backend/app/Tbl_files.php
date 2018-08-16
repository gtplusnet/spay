<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_files extends Model
{
    protected $table = 'tbl_files';
	protected $primaryKey = "file_id";
	public $timestamps = false;
}
