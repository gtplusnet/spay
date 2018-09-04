<?php
namespace App\Globals;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Tbl_User;
use App\Tbl_login;
use App\Tbl_member_address;
use App\Tbl_cash_in_proof;
use App\Tbl_member_log;
use App\Globals\Mails;
use App\Tbl_email_verification;
use Validator;

class User
{
	public static function getList($params = null)
	{
		$query = Tbl_User::leftJoin("tbl_country_codes", "tbl_country_codes.country_code_id", "=", "users.country_code_id");
		$query = $query->leftJoin("tbl_other_info", "tbl_other_info.user_id", "=", "users.id");
		$query = $query->leftJoin("tbl_member_position", "tbl_member_position.member_position_id", "=", "tbl_other_info.member_position_id");

		if($params != null)
		{
			if($params["search_name"] != null || $params["search_name"] != "")
			{
				$name = $params["search_name"];
				$query = $query
				->where( function($query) use ($name){
	                        $query
	                        ->where('first_name', 'like', '%' . $name . '%')
	                        ->orWhere('last_name', 'like', '%'. $name . '%');
	                    });
			}
			if($params["register_platform"] != "all")
			{
				$query = $query->where("platform",$params["register_platform"]);
			}
			if($params["search_status"] != "all")
			{
				$query = $query->where("status_account", $params["search_status"]);
			}
			if($params["search_career"] != "all")
			{
				$query = $query->where("tbl_member_position.member_position_id", $params["search_career"]);
			}

			if($params["search_email_status"] != "all")
			{
				$query = $query->where("verified_mail", $params["search_email_status"]);
			}

			if($params["search_roles"] != "all")
			{
				$query = $query->where("is_admin", $params["search_roles"]);
			}

			if($params["search_date_from"])
			{
				$query = $query->whereDate("created_at", ">=", $params["search_date_from"]);
			}

			if($params["search_date_to"])
			{
				$query = $query->whereDate("created_at", "<=", $params["search_date_to"]);
			}
		}
		

		$query = $query->get();

		foreach ($query as $key => $value) 
		{
			$member_address = Tbl_member_address::joincoinonly()->where("tbl_coin.coin_name", "!=" ,"peso")->where("member_id", $value->id)->get();
			foreach ($member_address as $key2 => $value2) 
			{
				$query[$key][$value2["coin_name"]] = $value2;
			}

		}

		return $query;
	}

	public static function updateUserPassword($params = null)
	{
		$new_pass = $params["new_password"];
		$c_new_pass = $params["confirm_new_password"];
		$id = $params["id"];

		if(strlen($new_pass) >= 6)
		{
			if($new_pass == $c_new_pass)
			{
				$password = Hash::make($new_pass);
				$_data = Tbl_User::where("id", $params["id"])->update(["password" => $password]);
				$return["message"] = "Password successfully changed.";
				$return["type"] = "success";
			}
			else
			{
				$return["message"] = "New Password and Confirm New Password does not match";
				$return["type"] = "error";
			}
		}
		else
		{
			$return["message"] = "Password cannot be less than 6 characters.";
			$return["type"] = "error";
		}

		return json_encode($return);
		
	}


	public static function rules()
	{
		$rules["first_name"]        = "required";
		$rules["last_name"]         = "required";
		$rules["phone_number"]     	= "required";
		$rules["email"]     		= "required|email";

		return $rules;
	}

	public static function updatePassword($insert)
	{
        Tbl_User::where("id", $insert["id"])->update($insert);

        return true;
	}

	public static function send_email_verification_link($email, $id)
    {
        $email_verification["verification_email"]     = $email;
        $email_verification["verification_user_id"]   = $id;
        $email_verification["expiration_date"]        = Carbon::now()->addHours(12);
        $email_verification["date_generated"]         = Carbon::Now();
        $email_verification["verification_code"]      = md5(Carbon::now());

        $v_id = Tbl_email_verification::insertGetId($email_verification);
        $data["email"] = Tbl_email_verification::where("verification_id",$v_id)->first();
        $data["member"] = Tbl_User::where("email",$data["email"]->verification_email)->first();

        Mails::send_register_verification($data);
        
        return "success"; //kyc
        

    }

}