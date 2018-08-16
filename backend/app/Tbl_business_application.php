<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_business_application extends Model
{
    protected $table = 'tbl_business_application';
	protected $primaryKey = "business_application_id";
	public $timestamps = false;

	public function scopeGetList($query,$name = null,$date_from = null, $date_to = null)
	{
		$data = Tbl_business_application::select('*');
		if($name != false)
		{
			$data = $data->where('business_director_name',"like","%".$name."%");
		}
		if($date_from != false)
		{
			$data = $data->whereDate("business_date_submitted", ">=" ,$date_from);
		}
		if($date_to != false)
		{
			$data = $data->whereDate("business_date_submitted", "<=" ,$date_to);
		}
		return $data->get();
	}
}
