<?php
namespace App\Globals;
use Carbon\Carbon;
use App\Tbl_User;
use Validator;

class Members
{
	
	public static function rules()
	{
		$rules["cash_in_method_name"]         = "required";
		$rules["cash_in_method_fee"]          = "required|numeric";
		$rules["cash_in_method_header"]       = "required";
		$rules["cash_in_account_name"]        = "required";
		$rules["cash_in_account_number"]      = "required";
		$rules["cash_in_method_payment_rule"] = "required";

		return $rules;
	}

	public static function update($update_id, $status)
	{


    	$data = Tbl_User::where("id", $update_id);

    	if($status == 0)
    	{
    		$data->update(["verified_mail" => 1])
    	}
    	else
    	{
    		$data->update(["verified_mail" => 0])
    	}

    	$result["message"] = "Successfully Updated";
    	$result["status"] = "success";

        return $result;
	}
}