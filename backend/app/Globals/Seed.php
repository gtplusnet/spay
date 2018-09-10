<?php
namespace App\Globals;
use App\Tbl_audit;
use App\Tbl_country_codes;
use Carbon\Carbon;
use DB;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Hash;
use App\Globals\Coin;

class Seed
{
	public static function test_seed()
	{
		Self::coin();
		Self::member_position();
		Self::country_codes();
		
        /* Get Coin List */
        $data["_coin"] = Coin::getListWithLOKConversion();
       
        /* Get ABA Coin ID */
        $data["aba_id"] = Coin::getLOKId();

        /* Get Sale Stage List */
        $data["_sale_stage"] = Coin::getSaleStageList();
       
        /* Get Bonus Coin per Sale Stage List */
		$data["_sale_stage_bonus"] = Coin::getBonusSaleStageList();
		
		Self::dev_account_seed();
        
	}

	public static function coin()
	{
		if (!DB::table("tbl_coin")->first()) 
		{
			$seed[0]["coin_name"]  = "peso";
			$seed[0]["coin_abb"]   = "PHP";
			$seed[0]["created_at"] = Carbon::now();
			$seed[0]["updated_at"] = Carbon::now();

			$seed[1]["coin_name"]  = "ethereum";
			$seed[1]["coin_abb"]   = "ETH";
			$seed[1]["created_at"] = Carbon::now();
			$seed[1]["updated_at"] = Carbon::now();

			$seed[2]["coin_name"]  = "bitcoin";
			$seed[2]["coin_abb"]   = "BTC";
			$seed[2]["created_at"] = Carbon::now();
			$seed[2]["updated_at"] = Carbon::now();

			$seed[3]["coin_name"]  = "ahm";
			$seed[3]["coin_abb"]   = "AHM";
			$seed[3]["created_at"] = Carbon::now();
			$seed[3]["updated_at"] = Carbon::now();

			$seed[4]["coin_name"]  = "dollar";
			$seed[4]["coin_abb"]   = "USD";
			$seed[4]["created_at"] = Carbon::now();
			$seed[4]["updated_at"] = Carbon::now();

			foreach ($seed as $key => $insert) 
			{
				DB::table("tbl_coin")->insert($insert);
			}
		}
		
		return true;
	}

	public static function member_position()
	{
		if (!DB::table("tbl_member_position")->first()) 
		{
			$seed[0]["member_position_name"]  = "Member";

			$seed[1]["member_position_name"]  		= "Community Manager";
			$seed[1]["token_release"]  		  		= 25000;
			$seed[1]["initial_release_percentage"]  = 50;
			$seed[1]["commission"]  		  		= 5.5;

			$seed[2]["member_position_name"]  		= "Marketing Director";
			$seed[2]["token_release"]  		  		= 30000;
			$seed[2]["initial_release_percentage"]  = 50;
			$seed[2]["commission"]  		  		= 7;

			$seed[3]["member_position_name"]  		= "Ambassador";
			$seed[3]["token_release"]  		  		= 50000;
			$seed[3]["initial_release_percentage"]  = 50;
			$seed[3]["commission"]  		  		= 6;

			$seed[4]["member_position_name"]  		= "Advisor";
			$seed[4]["token_release"]  		  		= 100000;
			$seed[4]["initial_release_percentage"]  = 50;
			$seed[4]["commission"]  		  		= 6;


			foreach ($seed as $key => $insert) 
			{
				DB::table("tbl_member_position")->insert($insert);
			}
		}
		
		return true;
	}

	public static function country_codes()
	{
		$string = public_path().'/assets/phone.json';

    	$string = json_decode(file_get_contents($string));
        $string_count = count($string);


        foreach($string as $key => $value)
    	{
			 $seed[$key]["country_code_abbr"] = $value->code;
	         $seed[$key]["country_code"] = $value->number;
	         $seed[$key]["country"] = $value->country;
	         
	         // DB::table("tbl_country_codes")->insert($seed);
    	}
    	
    	if(!Tbl_country_codes::first())
        {
            foreach ($seed as $key => $value) 
            {
                $insert["country_code_abbr"] = $value["country_code_abbr"];
                $insert["country_code"] = $value["country_code"];
                $insert["country"] = $value["country"];
                DB::table("tbl_country_codes")->insert($insert);
            }

        }
	}

	public static function dev_account_seed()
	{
		$insert["first_name"]           = "Chicharon Ni";
		$insert["last_name"]            = "Mang Juan";
		$insert["email"]                = "chicharon@mang.juan";
		$insert["country_code_id"]      = 1;
		$insert["phone_number"]         = "9112234456";
		$insert["username"]             = "developer";   
		$insert["password"]             = Hash::make("water123");
		$insert["sponsor"]              = 1;
		$insert["create_ip_address"]    = $_SERVER['REMOTE_ADDR'];
		$insert["email_token"]          = base64_encode('chicharon@mang.juan');
		$insert["created_at"]           = Carbon::now();
		$insert["verified_mail"]        = 1;
		$insert["status_account"]       = 1;
		$insert["is_admin"]             = 1;
		$insert["entity"]               = "Individual";
		$insert["birth_date"]           = Carbon::now();
		$insert["company_name"]         = "DIGIMA";
		$insert["platform"]          	= "system";
		$insert["first_time_login"]     = 0;
		$insert["crypto_purchaser"]     = null;

		$member_id                      = DB::table("users")->insertGetId($insert);

		$ref_insert["referral_link"] 		  = substr(md5(Carbon::now()."AHMTOKEN"), 0, 7);
		$ref_insert["referral_user_id"]       = $member_id;
		$referral_id = DB::table("tbl_referral")->insertGetId($ref_insert);

		$google2fa = new Google2FA();
        $secret_key = $google2fa->generateSecretKey();
		
		$info_insert["referrer_id"] = null;
		$info_insert["member_position_id"]    = 1;
		$info_insert["registration_stage_id"] = 1;
		$info_insert["user_id"] = $member_id;
		$info_insert["google2fa_secret_key"] = $secret_key;

		$other_info = DB::table("tbl_other_info")->insert($info_insert);

		$career = DB::table("tbl_member_position")->where("member_position_id", 1)->first();

		$career_insert["member_id"] = $member_id;
		$career_insert["token_release"] = $career->token_release;
		$career_insert["initial_release_percentage"] = $career->initial_release_percentage;
		$career_insert["commission"] = $career->commission;
		$career_insert["after_purchase_commission"] = $career->commission;
		$career_insert["needed_member"] = $career->needed_member;
		$career_insert["needed_ambassador"] = $career->needed_ambassador;
		$career_insert["needed_advisor"] = $career->needed_advisor;
		$career_insert["needed_marketing_director"] = $career->needed_marketing_director;
		$career_insert["needed_community_manager"] = $career->needed_community_manager;
		$career_insert["date_created"] = Carbon::now();

		$careerInsert = DB::table("tbl_position_requirements")->insert($career_insert);

		$member_position_log_insert["member_position_id"] = 1;
		$member_position_log_insert["member_id"] = $member_id;
		$member_position_log_insert["created_at"] = Carbon::now();
		$member_position_log = DB::table("tbl_member_position_logs")->insert($member_position_log_insert);
	}
}