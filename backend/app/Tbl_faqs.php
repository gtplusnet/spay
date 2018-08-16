<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_faqs extends Model
{
    protected $table = 'tbl_faqs';
	protected $primaryKey = "faq_id";
	public $timestamps = false;
}
