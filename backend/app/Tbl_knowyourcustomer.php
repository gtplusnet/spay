<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_knowyourcustomer extends Model
{
    protected $table = 'tbl_knowyourcustomer';
	protected $primaryKey = "kyc_id";
    public $timestamps = false;

	public function scopeJoinDetails($query)
    {
        $query
        ->join("users", "users.id", "=", "tbl_knowyourcustomer.kyc_member_id");
    }

    public function scopeFilterKycDetails($query, $level = 0, $name = null, $date_from = null, $date_to = null, $status = null)
    {
    	$data = Tbl_knowyourcustomer::select('kyc_upload_date')->groupBy('kyc_upload_date');
    	if($level != 0)
    	{
    		$data = $data->where('kyc_level',$level);
    	}
    	if($date_from != false)
    	{
    		$data = $data->whereDate("kyc_upload_date", ">=" ,$date_from);
    	}
        if($date_to != false)
        {
            $data = $data->whereDate("kyc_upload_date","<=", $date_to);
        }
        if($name != false)
        {
            $data = $data->join("users", "users.id", "=", "tbl_knowyourcustomer.kyc_member_id");
            $data
            ->where("first_name","like","%".$name."%")
            ->orWhere("last_name","like","%".$name."%");
            if(strpos($name, ' ') !== false )
            {
                $surname_name = substr($name, strpos($name, " ") + 1);
                $data = $data->orWhere("last_name","LIKE","%".$surname_name."%"); 
            }
        }
        if($status != "all")
        {
            $data = $data->where("kyc_status",$status);
        }
    	return $data->get();
    }
}
