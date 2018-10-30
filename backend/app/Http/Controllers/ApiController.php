<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;

use App\Tbl_faqs;
use App\Tbl_sale_stage;
use App\Tbl_sale_stage_bonus;
use App\Tbl_coin_conversion;
use App\Tbl_member_address;
use Carbon\Carbon;
use App\Globals\User;
use App\Globals\Audit;
use App\Globals\Authenticator;
use App\Globals\Seed;
use App\Globals\Coin;
use App\Globals\Mails;
use App\Globals\Wallet;
use App\Globals\Helper;
Use App\Globals\Google;
Use App\Globals\Tree;
use Jenssegers\Agent\Agent;
use Mail;
use App\Tbl_User;
use App\Tbl_forget_account_request;
use App\Tbl_email_verification;
use App\Tbl_country_codes;
use App\Tbl_referral;
use App\Tbl_other_info;
use App\Tbl_business_application;
use App\Tbl_member_position;
use App\Tbl_position_requirements;
use App\Tbl_member_position_log;
use App\Tbl_files;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Http\Request as Request2;

use Crypt;
use SSH;

class ApiController extends Controller
{
    function __construct()
    {
        sleep(1);
        Seed::coin();
        Seed::member_position();
        
        /* Get Coin List */
        $data["_coin"] = Coin::getListWithLOKConversion();
       
        /* Get ABA Coin ID */
        $data["aba_id"] = Coin::getLOKId();

        /* Get Sale Stage List */
        $data["_sale_stage"] = Coin::getSaleStageList();
       
        /* Get Bonus Coin per Sale Stage List */
        $data["_sale_stage_bonus"] = Coin::getBonusSaleStageList();

        Validator::extend('alpha_spaces', function ($attribute, $value) {

            // This will only accept alpha and spaces. 
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[\pL\s]+$/u', $value); 

        });
    }

    public function other_info()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        // $return["_conversion"]            = Tbl_coin_conversion::get();

        $_data_from = Tbl_coin_conversion::join("tbl_coin", "tbl_coin.coin_id", "=", "tbl_coin_conversion.coin_from")
        ->whereColumn("tbl_coin_conversion.coin_from", "!=", "tbl_coin_conversion.coin_to")
        ->where("tbl_coin_conversion.coin_to", "!=", 1)
        ->orderBy("tbl_coin_conversion.coin_from", "asc")
        ->get();
        
        
        foreach($_data_from as $key => $data){
            $_data_to = Tbl_coin_conversion::join("tbl_coin", "tbl_coin.coin_id", "=", "tbl_coin_conversion.coin_to")->where("coin_conversion_id", $data->coin_conversion_id)->orderBy("tbl_coin_conversion.coin_from", "asc")->first();
            
            $_data_from[$key]["coin_name_to"] = $_data_to["coin_abb"];
        }
        return $_data_from;
    }
    
    function index(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        return json_encode("");
    }

    function checkBalance($address)
    {
        $response = json_decode(file_get_contents('https://blockexplorer.com/api/addr/'.$address));
        
        $balance = 0;
        foreach ($response->transactions as $key => $transaction) 
        {
            
            $transaction_info = json_decode(file_get_contents('https://blockexplorer.com/api/tx/'.$transaction));
          
            if (isset($transaction_info->valueOut)) 
            {
                $balance -= $transaction_info->valueOut;
            }
           
            else // if(isset($transaction_info->valueIn)) 
            {

                $balance += $transaction_info->valueIn;

            }
             dd($transaction_info->valueIn, $transaction_info->valueOut);
        }
        dd($response, $balance);
        echo $response->balanceSat;
    }

	function login(Request $request)
    {

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
         
        $rules["username"]                  = array("required");
        $rules["password"]                  = array("required", "min:6");
        // $rules["captcha"]                   = array("required");
        $validator                          = Validator::make($request->all(), $rules);

        /* VALIDATE LOGIN */
        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else
        {
            /* AUTHENTICATE LOGIN */
            $username_email                 = $request->username;

            $check_member                   = Tbl_User::where("username", $username_email)->first();

            if(!$check_member)
            {
                $check_member               = Tbl_User::where("email", $username_email)->first();
            }

            if($check_member)
            {
                /*Check if account was verified*/
                if($check_member['status_account'] == 0) 
                {
                    $return["message"]  = "Your account is not activated yet. We will notify you through Email once your account activation is already approved.";
                    $return["status"]   = "fail";  
                }
                else if (Hash::check($request->password, $check_member->password))
                {
                    $login_2fa = Tbl_other_info::where("user_id", $check_member->id);
                    $first_2fa = $login_2fa->first();
                    if($first_2fa->google2fa_enabled == 1)
                    {
                        $return["status"] = "google2fa_enabled";
                        $return["member_id"] = $check_member->id;
                    }
                    else
                    {
                        $login_key          = Authenticator::login($check_member->id, $check_member->password);
                        $return["message"]  = $login_key;  
                        $return["status"]   = "success";
                        $login_info         = Authenticator::checkLogin($login_key);
                        $return["name"]     = $login_info->first_name . " " . $login_info->last_name;
                    }
                }
                else
                {
                    $return["message"]  = "You entered an invalid account.";
                    $return["status"]   = "fail";
                }
            }
            else
            {
                $return["message"]  = "You entered an invalid account.";
                $return["status"]   = "fail";
            }
        }

        return json_encode($return);
    }

    function new_login(Request $request)
    {

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
         

        /* AUTHENTICATE LOGIN */
        $username_email                 = $request->email;
        $facebook_id                    = $request->id;

       
        $check_member               = Tbl_User::where("email", $username_email)->first();

        if(!$check_member)
        {
            $check_member               = Tbl_User::where("facebook_id", $facebook_id)->first();
        }
        if($check_member)
        {
            /*Check if account was verified*/
            if($check_member['status_account'] == 0) 
            {
              $return["message"]  = "Your account is not activated yet.";
              $return["status"]   = "fail";  
            }
            else
            {
                if($check_member->platform == "system")
                {
                    if(Hash::check($request->password, $check_member->password))
                    {
                        /* CHECK EMAIL VERIFICATION */
                       
                        $login_2fa = Tbl_other_info::where("user_id", $check_member->id);
                        $first_2fa = $login_2fa->first();
                        if($first_2fa->google2fa_enabled == 1)
                        {
                            $return["status"] = "google2fa_enabled";
                            $return["member_id"] = $check_member->id;
                        }
                        else
                        {
                            $login_key          = Authenticator::login($check_member->id, $check_member->password);
                            $return["message"]  = $login_key;  
                            $return["status"]   = "success";
                            $login_info         = Authenticator::checkLogin($login_key);
                            $return["name"]     = $login_info->first_name . " " . $login_info->last_name;
                        }
                    }
                }
                else
                {
                    $login_2fa = Tbl_other_info::where("user_id", $check_member->id);
                    $first_2fa = $login_2fa->first();
                    if($first_2fa->google2fa_enabled == 1)
                    {
                        $return["status"] = "google2fa_enabled";
                        $return["member_id"] = $check_member->id;
                    }
                    else
                    {
                        if(!$check_member->password)
                        {
                            $login_key          = Authenticator::login($check_member->id);
                        }
                        else
                        {
                            $login_key          = Authenticator::login($check_member->id, $check_member->password);
                        }
                        $return["message"]  = $login_key;  
                        $return["status"]   = "success";
                        $login_info         = Authenticator::checkLogin($login_key);
                        $return["name"]     = $login_info->first_name . " " . $login_info->last_name;
                    }
                }
            }
        }
        else
        {
            $return["message"]  = "You entered an invalid account.";
            $return["status"]   = "fail";
        }

        return json_encode($return);
    }

    function forget_account_request(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

         /*get account info in member*/
         $query = Tbl_User::where('email', $request->email);
         $member = $query->first();
         $check_if_verified = $query->where("verified_mail", 1);
         $verified = $check_if_verified->count();
         
        /*get account if exist*/
        if($member)
        {
            if($verified == 1)
            {
                /*update all request to used*/
                $update['used'] = 1;
                Tbl_forget_account_request::where('member_id', $member->id)->update($update);
                $insert["member_id"]                        = $member->id;
                $insert["verification_code"]                = rand(100000,999999);
                $insert["create_ip_address_request"]        = Authenticator::get_ip_address();
                $insert["created_at"]                       = Carbon::now();

                $request_id = Tbl_forget_account_request::insertGetId($insert);
                
                $data['member']     = $member;
                $data['request']    = Tbl_forget_account_request::where('forget_account_request_id', $request_id)->first();
                
                Mails::send_reset_password_request($data);

                $return["message"]  = "no-message";
                $return["status"]   = "success";
            }
            else
            {
                $return["message"]  = "email is not yet verified";
                $return["status"]   = "fail";
            }
        }
        else
        {
            $return["message"]  = "wrong email";
            $return["status"]   = "fail";
        }

        return json_encode($return);

    }

    function change_member_password(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $request_id                     = $request->request_id;
        $verification_code              = $request->verification_code;
        $password                       = $request->new_password;

        $rules["new_password"]      = array("required","min:6");
        $rules["verification_code"] = array("required");
        $validator                  = Validator::make($request->all(), $rules);

        if(strpos($request->new_password, ' ') !== false )//white space detection
        {
            $return["message"] = "Password contain white space.";
            $return["status"] = "fail";
        }
        else if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else
        {
            $request  = Tbl_forget_account_request::where('forget_account_request_id', $request_id)->where('verification_code', $verification_code)->first();

            if ($request) 
            {
                $update['password'] = Hash::make($password);

                Tbl_User::where('id', $request->member_id)->update($update);

                $update_request['used'] = 1;

                Tbl_forget_account_request::where('forget_account_request_id', $request_id)->update($update_request);

                $return["message"]  = "no-message";
                $return["status"]   = "success";
            }
            else
            {
                $return["message"]  = "Wrong Verification Code";
                $return["status"]   = "fail";
            }
        }
        return json_encode($return);
    }

    function get_forget_account_request(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

         $request_id         = $request->request_id;
         $request            = Tbl_forget_account_request::where('forget_account_request_id', $request_id)->first();

         $expiration_date = Carbon::parse($request->created_at)->addDays(2);
         if($expiration_date <= Carbon::now())
         {
            $return["message"]  = "Link is already expired.";
            $return["status"]   = "fail";
         }
         else if ($request) 
         {
            $return["data"] = $request;
            $return["message"]  = "no-message";
            $return["status"]   = "success";
            
         }
         else
         {
            $return["message"]  = "no-data";
            $return["status"]   = "fail";
          
         }
         return json_encode($return);
    }

    function sample_file()
    {

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $json_url = "http://localhost:3000/api/v2/create?password=water123&api_code=1da456d5-f176-%E2%80%8E4997-8105-2f95a4f95cfd";

        $json_data = file_get_contents($json_url);
        $json_feed = json_decode($json_data);
        die(var_dump($json_feed));
    }

    function get_country_codes()
    {

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        Seed::country_codes();

        $data = Tbl_country_codes::where("country_code_id", "!=", 0)->orderBy('country', 'asc')->get();
        // dd(json_encode($data));
        return json_encode($data);
    }

    function register(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        // dd(request()->all());
        /* INITIALIZE RULES */
        $rules["first_name"]              = array("required", "alpha");
        $rules["last_name"]               = array("required", "alpha");

        $rules["email"]                   = array("required", "email", "unique:users");
        $rules["username"]                = array("required", "alpha_dash", "unique:users", "min:6");
        $rules["country_code"]            = array("required", "string");
        $rules["phone_number"]            = array("required", "integer", "unique:users");
        $rules["birth_date"]              = array("required", "string");
        $rules["password"]                = array("required", "confirmed", "min:6");


        $validator = Validator::make($request->all(), $rules);

        /* ADD MANUAL VALIDATION FOR SPONSOR */
        if(request("sponsor") != "")
        {
            $check_sponsor = Tbl_User::where("username", request("sponsor"))->first();


            if($check_sponsor)
            {
                if($check_sponsor->membership_id == 0)
                {
                    $sponsor = -2;
                }
                else
                {
                    $sponsor = $check_sponsor->member_id; 
                }
            }
            else
            {
                $sponsor = -1;
            }
        }
        else
        {
            $sponsor = 0;
        }

        /* VALIDATE REGISTRATION */
        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        elseif($sponsor < 0)
        {
            $return["message"]  = "The sponsor you entered does not exist. Leave this blank if no one provided a sponsor for you.";
            $return["status"]   = "fail";
        }
        else
        {
            /* INSERT ACCOUNT TO DATABASE */
            $insert["first_name"]           = $request->first_name;
            $insert["last_name"]            = $request->last_name;
            $insert["email"]                = $request->email;
            $insert["country_code_id"]      = $request->country_code;
            $insert["phone_number"]         = $request->phone_number;
            $insert["username"]             = $request->username;   
            $insert["password"]             = Hash::make($request->password);
            $insert["sponsor"]              = $sponsor;
            $insert["create_ip_address"]    = $_SERVER['REMOTE_ADDR'];
            $insert["email_token"]          = base64_encode($request->email);
            $insert["created_at"]           = Carbon::now();
            $insert["verified_mail"]        = 0;
            $insert["status_account"]       = 0;
            $insert["is_admin"]             = 0;
            $insert["entity"]               = $request->entity == 0 ? "Individual" : $request->entity;
            $insert["birth_date"]           = $request->birth_date;
            $insert["company_name"]         = $request->company_name;
            $insert["desired_btc"]          = $request->desired_btc;
            $insert["desired_eth"]          = $request->desired_eth;


            $member_id                      = Tbl_User::insertGetId($insert);
            $email_verification["verification_email"]     = $request->email;
            $email_verification["verification_user_id"]   = $member_id;
            $email_verification["expiration_date"]        = Carbon::now()->addHours(12);
            $email_verification["date_generated"]         = Carbon::Now();
            $email_verification["verification_code"]      = md5(Carbon::now());

            $id = Tbl_email_verification::insertGetId($email_verification);
            $data["email"] = Tbl_email_verification::where("verification_id",$id)->first();

            $ref_insert["referral_link"] = substr(md5(Carbon::now()."XSTOKEN"), 0, 7);
            $ref_insert["referral_user_id"]       = $member_id;
            $referral_id = Tbl_referral::insertGetId($ref_insert);


            if($request->referral_link != null)
            {

                $info_ref = Tbl_referral::where("referral_link", $request->referral_link);
                $count = $info_ref->get();
                $info_r = $info_ref->first();
                $info_insert["referrer_id"] = count($count) != 0 ? $info_r->referral_id : null;
            }
            else
            {
                $info_insert["referrer_id"] = null;
            }

            $google2fa = new Google2FA();
            $secret_key = $google2fa->generateSecretKey();

            $info_insert["member_position_id"]    = $request->career_id;
            $info_insert["registration_stage_id"] = $request->sale_stage_id != null ? $request->sale_stage_id : 1;
            $info_insert["user_id"] = $member_id;
            $info_insert["google2fa_secret_key"] = $secret_key;


            $other_info = Tbl_other_info::insert($info_insert);

            $career = Tbl_member_position::where("member_position_id", $request->career_id)->first();

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

            $careerInsert = Tbl_position_requirements::insert($career_insert);

            $member_position_log_insert["member_position_id"] = $request->career_id;
            $member_position_log_insert["member_id"] = $member_id;
            $member_position_log_insert["created_at"] = Carbon::now();
            $member_position_log = Tbl_member_position_log::insert($member_position_log_insert);

            Mails::send_register_verification($data);

            $return["message"]  = "no-message";
            $return["status"]   = "success";
        }

        return json_encode($return);
    }

    function new_register(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


        /* INITIALIZE RULES */
        $rules["first_name"]              = array("required", "alpha_spaces", "min:2");
        $rules["last_name"]               = array("required", "alpha_spaces", "min:2");
        $rules["selfie_verification"]     = array("required");


        if($request->primary_id)
        {   
            $rules["primary_id"]               = array("required");
        }
        else
        {
            $rules["secondary_id_1"]               = array("required");
            $rules["secondary_id_2"]               = array("required");
        }


        // dd(Hash::make($request->first_name));

        if($request->platform == "system")
        {
            $rules["email"]                   = array("required", "email", "unique:users");
            // $rules["captcha"]                 = array("required");
        }


        $validator = Validator::make($request->all(), $rules);

        /* VALIDATE REGISTRATION */
        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else
        {
            /* INSERT ACCOUNT TO DATABASE */
            $insert["first_name"]           = $request->first_name;
            $insert["last_name"]            = $request->last_name;
            if($request->platform == "system")
            {
                $insert["email"]                        = $request->email;
                $insert["country_code_id"]              = $request->country_code_id;
                $insert["phone_number"]                 = $request->phone_number;
                $insert["gender"]                       = $request->gender;
                $insert["nationality"]                  = $request->nationality;
                $insert["address_line1"]                = $request->address_line1;
                $insert["address_line2"]                = $request->address_line2;
            }

            $insert["facebook_id"]          = $request->id;
            $insert["create_ip_address"]    = $_SERVER['REMOTE_ADDR'];
            $insert["email_token"]          = base64_encode($request->email);
            $insert["created_at"]           = Carbon::now();
            $insert["platform"]             = $request->platform;
            $insert["verified_mail"]        = 0;
            $insert["status_account"]       = $request->platform == "system" ? 0 : 1;
            $insert["is_admin"]             = 0;
            $insert["password"]             = null;

            $member_id                      = Tbl_User::insertGetId($insert);

            $ref_insert["referral_link"] = substr(md5(Carbon::now()."XSTOKEN"), 0, 7);
            $ref_insert["referral_user_id"]       = $member_id;
            $referral_id = Tbl_referral::insertGetId($ref_insert);

            if($request->referral_link != null)
            {
                $user_info = Tbl_User::where("id", $member_id)->first();
                $sponsor_target = Tbl_referral::where("referral_link", $request->referral_link)->member()->first();
                if($user_info)
                {
                    Tree::place_sponsor($user_info, $sponsor_target);
                }

                // $info_ref = Tbl_referral::where("referral_link", $request->referral_link);
                // $count = $info_ref->get();
                // $info_r = $info_ref->first();
                // $info_insert["referrer_id"] = count($count) != 0 ? $info_r->referral_id : null;
                
            }
            else
            {
                $info_insert["referrer_id"] = null;
            }

            $google2fa = new Google2FA();
            $secret_key = $google2fa->generateSecretKey();

            $info_insert["member_position_id"]    = $request->career_id;
            $info_insert["registration_stage_id"] = $request->sale_stage_id != null ? $request->sale_stage_id : 1;
            $info_insert["user_id"] = $member_id;
            $info_insert["google2fa_secret_key"] = $secret_key;


            $other_info = Tbl_other_info::insert($info_insert);

            $career = Tbl_member_position::where("member_position_id", $request->career_id)->first();

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

            $careerInsert = Tbl_position_requirements::insert($career_insert);
            
            $member_position_log_insert["member_position_id"] = $request->career_id;
            $member_position_log_insert["member_id"] = $member_id;
            $member_position_log_insert["created_at"] = Carbon::now();
            $member_position_log = Tbl_member_position_log::insert($member_position_log_insert);

            $return["message"]  = "no-message";
            $return["status"]   = "success";

            if($request->platform == "system")
            {
                // $email_verification["verification_email"]     = $request->email;
                // $email_verification["verification_user_id"]   = $member_id;
                // $email_verification["expiration_date"]        = Carbon::now()->addHours(12);
                // $email_verification["date_generated"]         = Carbon::Now();
                // $email_verification["verification_code"]      = md5(Carbon::now());

                // $id = Tbl_email_verification::insertGetId($email_verification);
                // $data["email"] = Tbl_email_verification::where("verification_id",$id)->first();
                // $data["member"] = Tbl_User::where("email",$data["email"]->verification_email)->first();
                
                // Mails::send_register_verification($data);
                $kyc_insert["user_id"]                  = $member_id;
                $kyc_insert["primary_id"]               = $request->primary_id;
                $kyc_insert["secondary_id_1"]           = $request->secondary_id_1;
                $kyc_insert["secondary_id_2"]           = $request->secondary_id_2;
                $kyc_insert["primary_id1"]              = $request->primary_id1;
                $kyc_insert["secondary_id1"]            = $request->secondary_id1;
                $kyc_insert["secondary_id2"]            = $request->secondary_id2;
                $kyc_insert["selfie_verification"]      = $request->selfie_verification;
                User::submit_kyc_proof($kyc_insert);
                User::send_email_verification_link($request->email, $member_id);
            }
        }

        return json_encode($return);
    }

    function faqs_list_homepage(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $data = Helper::getFaqs($request->search);
        // dd(123);
        return json_encode($data);
    }

    function get_conversion_rates(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        
        $_data_from = Tbl_coin_conversion::join("tbl_coin", "tbl_coin.coin_id", "=", "tbl_coin_conversion.coin_from")
        ->whereColumn("tbl_coin_conversion.coin_from", "!=", "tbl_coin_conversion.coin_to")
        ->orderBy("tbl_coin_conversion.coin_from", "asc")
        ->get();
        
        
        foreach($_data_from as $key => $data){
            $_data_to = Tbl_coin_conversion::join("tbl_coin", "tbl_coin.coin_id", "=", "tbl_coin_conversion.coin_to")->where("coin_conversion_id", $data->coin_conversion_id)->orderBy("tbl_coin_conversion.coin_from", "asc")->first();
            $_current_stage = Tbl_sale_stage::whereDate("sale_stage_start_date", ">=", Carbon::now())->orWhereDate("sale_stage_end_date", "<=", Carbon::now())->first();

            $_data_from[$key]["coin_name_to"]       = $_data_to["coin_abb"];
            $_data_from[$key]["sale_stage_id"]     = $_current_stage["sale_stage_id"];
            $_data_from[$key]["current_sale_stage"] = ucwords(str_replace("_", " ", $_current_stage["sale_stage_type"]));
            $_data_from[$key]["current_discount"]   = $_current_stage["sale_stage_discount"];
            $_data_from[$key]["end_date_stage"]     = $_current_stage["sale_stage_end_date"];

            // $_sale_stage_bonuses = Tbl_sale_stage_bonus::where("sale_stage_id", $_current_stage["sale_stage_id"])->get();

            //     $_data_from[$key]["buy_coin"] = $_sale_stage_bonuses;
        }

        return (json_encode($_data_from));

        // dd($_data_from);
        // foreach ($_data_from as $key => $data_from) {
        //     $__data[$key] = $data_from;
        //     $__data[$key]->coin_abb_from = $data_from->coin_abb;
        //     $__data[$key]->coin_abb_to = $_data_to->coin_abb;
        // }

        // dd($__data);

        // dd($_data_php_btc);
        // return json_encode($return);

    }
    function send_email_get_in_touch(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        
        $data["first_name"]           = $request->contact_first_name;
        $data["last_name"]            = $request->contact_last_name;
        $data["phone_number"]         = $request->contact_phone_number;
        $data["email"]                = $request->contact_email;
        $data["subject"]              = $request->contact_subject;
        $data["contact_message"]      = $request->contact_message;
        $message["success"]  = "success";

        Mails::send_mail_get_in_touch($data);

        return $message;
    }

    function email_verification_check(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $data = Tbl_email_verification::where("verification_code", $request->verify);

        if($data->count() != 0)
        {
            $_data["data"] = $data->get();
            foreach($_data["data"] as $data)
            {
                if($data->status == 1)
                {
                    if($data->expiration_date > Carbon::now())
                        {
                            $update_status = Tbl_email_verification::where("verification_id", $data->verification_id)->update(["status" => 0]);

                            $update_status = Tbl_User::where("id", $data->member_id)->update(["verified_mail" => 0]);

                            $_data["message"] = "success";
                            return json_encode($_data);
                        }
                        else
                        {
                            $_data["message"] = "error_expired";
                            return json_encode($_data); 
                        }
                }
                else
                {
                    $_data["message"] = "error_used";
                    return json_encode($_data);      
                }
                
            }
            
        }
        else
        {
            $_data["message"] = "error_not_existing";
            return json_encode($_data);
        }
    }

    function resend_verification(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $data = Tbl_User::where("email", $request->email);


        if($data->count() != 0)
        {
            $_data = $data->first();
            if($_data->verified_mail == 0)
            {
                // $send_new = Tbl_email_verification::insert([
                //     "member_email" => $request->member_email,
                //     "member_id" => $_data->member_id;
                //     "verification_code" => md5(Carbon::now()),
                //     "expiration_date" => Carbon::now()->addHours(12)
                // ]);
                $email_verification["verification_email"] = $request->email;
                $email_verification["verification_user_id"] = $_data->id;
                $email_verification["verification_code"] = md5(Carbon::now());
                $email_verification["date_generated"] = Carbon::now();
                $email_verification["expiration_date"] = Carbon::now()->addHours(12);

                $check_if_verified = Tbl_email_verification::where("verification_email", $request->email);

                if($check_if_verified->count() != 0)
                {
                    $check_if_verified = $check_if_verified->first();
                    //dd($check_if_verified);
                    if($check_if_verified->is_used == 1)
                    {
                        $email_request = Tbl_email_verification::insertGetId($email_verification);
                        $datas["email"] = Tbl_email_verification::where("verification_id", $email_request)->first();
                        Mails::send_email_email_verify($datas);

                        $message["status"] = "success";
                        $message["message"] = "Re-send Verification Success";
                        return json_encode($message); 
                    }
                    else
                    {
                        $message["status"] = "fail";
                        $message["message"] = "Email Already Verified";
                        return json_encode($message); 
                    }
                }
                else
                {
                        $email_request = Tbl_email_verification::insertGetId($email_verification);
                        $datas["email"] = Tbl_email_verification::where("verification_id", $email_request)->first();
                        Mails::send_email_email_verify($datas);
                        $message["status"]  = "success";
                        $message["message"] = "Re-send Verification Success"; 
                        return json_encode($message); 
                }   
            }
            else
            {
                $message["status"] = "fail";
                $message["message"] = "Email Already Verified";
                return json_encode($message); 
            }
        }
        else
        {
            $message["status"] = "fail";
            $message["message"] = "Email not found."; 
            return json_encode($message);
        }
    }

    function check_verify_code(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $email_data = Tbl_email_verification::where("verification_code",$request->verification_code)->first();
        $member = Tbl_User::where('email',$email_data->verification_email)->first();
        $position = Tbl_other_info::where('user_id', $member->id)->first();
        
        if($email_data)
        {
            if($member->verified_mail == 0)
            {
                if($email_data->is_used == 1)
                {
                    $return["message"] = "Link is used.";
                    $return["status"]  = "fail";
                }
                else if($email_data->expiration_date <= Carbon::now())
                {
                    Tbl_email_verification::where("verification_id", $email_data->verification_id)->update(["is_used" => 1]);
                    $return["message"] = "Link is already expired.";
                    $return["status"]  = "fail";
                }
                else
                {
                    $passkey = Wallet::randomPassword();

                    $data["member"] = Tbl_User::where('email', $email_data->verification_email)->first();
                    $data["passkey"] = $passkey;

                    $update_email_verification["is_used"] = 1;
                    $update_user["verified_mail"]   = 1;
                    // $update_user["status_account"]  = $position->member_position_id == 1 ? 1 : 0;
                    if($data["member"]->password == null) // remove platform ($data["member"]->platform == system)
                    {
                        $update_user["password"]        = Hash::make($passkey);
                        Mails::send_temp_pass($data);
                    }
                    Tbl_email_verification::where('verification_code', $email_data->verification_code)->update($update_email_verification);
                    Tbl_User::where('email', $email_data->verification_email)->update($update_user);

                    $return["message"] = $data["member"]->password == null ? "Your email address have been activated. Check your email address for the temporary password we generated for you!" : "Your email address have been activated you can now enjoy our website!";
                    $return["status"]  = "success";
                }
            }
            else
            {
                $return["message"] = "Email is already activated.";
                $return["status"]  = "fail";
            }
        }
        else
        {
            $return["message"] = "Invalid link.";
            $return["status"]  = "fail";
        }
        return json_encode($return);
    }

    public function upload_file_business_application(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $file = $request->file('document');
   
        $file_size = $request->file('document')->getClientSize();
        if($file_size < 26214400) // max 25mb
        {
            $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
            $path ="lokalize/business_proof";
            $storage_path = storage_path();

            if ($request->file('document')->isValid())
            {
                $full_path = Storage::putFile($path, $request->file('document'), "public");
                $return['message']      = "upload successful";
                $return['status']       = "success";
                $return['full_path']    = $full_path;
            }
            else
            {
                $return['message']      = "Error on uploading";
                $return['status']       = "fail";
            }
        }
        else
        {
            $return['message'] = "The maximum file size is 25mb.";
            $return['status'] = "fail";
        }
         return json_encode($return);
    }

    public function upload_system_files_documents(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $file = $request->file('document');
   
        $file_size = $request->file('document')->getClientSize();
        if($file_size < 26214400) // max 25mb
        {
            $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
            $path ="successmall/docs";
            $storage_path = storage_path();

            if ($request->file('document')->isValid())
            {
                $full_path = Storage::putFile($path, $request->file('document'), "public");
                $return['message']      = "Document Successfully Uploaded";
                $return['status']       = "success";
                $return['full_path']    = $full_path;
            }
            else
            {
                $return['message']      = "Error on uploading";
                $return['status']       = "fail";
            }
        }
        else
        {
            $return['message'] = "The maximum file size is 25mb.";
            $return['status'] = "fail";
        }
         return json_encode($return);
    }

    public function submit_business_registration(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $rules["name"]                         = array("required");
        $rules["country"]                      = array("required");
        $rules["position"]                     = array("required");
        $rules["pref_token"]                   = array("required");
        $rules["pref_ico_name"]                = array("required");
        $rules["contact_number"]               = array("required");
        $rules["contact_email"]                = array("required");
        $validator                             = Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $return['message']  = $validator->errors()->all();
            $return['status']   = "fail";
        }
        else
        {
            $insert["business_company_legal_name"]           = $request->company_name;
            $insert["business_director_name"]                = $request->name;
            $insert["business_country"]                      = $request->country;
            $insert["business_number_of_employees"]          = $request->number_of_employee;
            $insert["business_annual_revenue"]               = $request->annual_revenue;
            $insert["business_supporting_documents"]         = $request->supporting_document;
            $insert["business_pref_token_name"]              = $request->pref_token;
            $insert["business_contact_number"]               = $request->contact_number;
            $insert["business_contact_email"]                = $request->contact_email; 
            $insert["business_remarks"]                      = $request->remarks;
            $insert["business_date_submitted"]               = Carbon::now();
            $insert["position"]                              = $request->position;
            $insert["preferred_ico_name"]                    = $request->pref_ico_name;
           
            $business_application_id = Tbl_business_application::insertGetId($insert);
            if($business_application_id)
            {
                $request = Tbl_business_application::where('business_application_id',$business_application_id)->first();
                $data['request'] = $request;
                Mails::send_business_applicaiton_verification($data);
                $return['status'] = "success";
                $return['message'] = "Thank you for Applying with us, We will get back to you soon.";
            }
        }
         return json_encode($return);
    }

    public function contact_us(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $rules["name"]           = array("required");
        $rules["email"]          = array("required","email");
        $rules["message"]        = array("required");
        $validator               = Validator::make($request->all(), $rules);
        if($validator->fails())
        {
            $return['message']   = $validator->errors()->first();
            $return['status']    = "fail";
        }
        else
        {
            $data['request']    = $request->all();
            Mails::send_contact_us($data);
            $return['message']  = "Your message is successfully sent.";
            $return['status']   = "success";
        }
        return json_encode($return);
    }

    public function get_sale_stages()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $date_now = date("Y-m-d", strtotime(Carbon::now()));
       
        
        $_current_stage = Tbl_sale_stage::whereDate("sale_stage_start_date", "<=", $date_now)->whereDate("sale_stage_end_date", ">=", $date_now)->first();
        $_current_stage_count = Tbl_sale_stage::whereDate("sale_stage_start_date", "<=", $date_now)->whereDate("sale_stage_end_date", ">=", $date_now)->count();
        $bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $_current_stage_count != 0 ? $_current_stage->sale_stage_id : 1)->get();
        $_current_stage["bonus"] = $bonus;

        return $_current_stage;
    }

    public function validate_key(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $user = Tbl_other_info::where("user_id", $request->member_id)->join("users", "users.id", "=", "tbl_other_info.user_id");
        $first = $user->first();

        $user_code = $request->code;

        if($user->count() != 0)
        {
            $valid = Google::validateKey($request->member_id, $user_code);
            if($valid["message"] == "valid")
            {
                $login_key          = Authenticator::login($first->id, $first->password);
                $return["message"]  = $login_key;  
                $return["status"]   = "success";
                $login_info         = Authenticator::checkLogin($login_key);
                $return["name"]     = $login_info->first_name . " " . $login_info->last_name;
            }
            else
            {
                $return["message"] = "Invalid code. Please check and try again.";
                $return["status"] = "fail";
            }
        }

        return json_encode($return);
    }

    public function decrypt_passwords()
    {
        // $commands = ["cd btcutils/signer", "./signer 7556f7c2e19a9f32e2504558cff5304543003c7310a5c26cfa9aae4007609051 ab7cfc3b0e18dfdcfc4666127158c502e415df119fc5a747e7b36d3c254a22f7"];
        // SSH::into('production')->run($commands, function($line)
        // {
        //     // $line = str_replace($line, "/n", "");
        //     $line = str_replace("\n", "", $line);
        //     dd($line);
        // });
        dd(Hash::make('water123'));
        $string = "eyJpdiI6IjlwUms4a09YVUxqUkFUYW94Q0ErWWc9PSIsInZhbHVlIjoiWEUxaSs2Qlg3bXBrWFdyVkRabTNFQVBMUjVFZmg0V2kxTnZTeVRWRXBKcE9VdUVsc1FEZHg5N2lcLzhvZXNwUWNkR0pPZXl4RG5JWW9Zcjhmak4xN0ZYK2ZjYmJ3RDlOXC9jRFNERks3dnBXMD0iLCJtYWMiOiI2YzdiYWFlM2M1MDViYTkwZmQxZTRhNzdkNWZjYzUyYzk4MTgxOGNkNjZlOTAyM2U2NWJjMjFlZmJmM2ZkNTNjIn0=";
        $data = Crypt::decryptString($string);
        dd($data);
        $__btc = [];

        $btc = Tbl_member_address::where("coin_id", 3)->get();
        foreach ($btc as $key => $value) {
            $__btc["btc_passwords"] = Crypt::decryptString($value->address_api_password);
        }

        $eth = Tbl_member_address::where("coin_id", 2)->get();
        foreach ($eth as $key => $value) {
            $__btc["eth_passwords"] = Crypt::decryptString($value->address_api_password);
        }
        return $__btc;
    }

    public function get_faqs()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $data["list"] = Tbl_faqs::where("is_active",1)->get();

        return $data["list"];
    }

    public function verify_email_address(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $data = User::send_email_verification_link($request->email_address, $request->member_id);

        if($data == "success")
        {
            $return['status'] = "success";
        }
        else
        {
            $return['status'] = "fail";
        }

        return $return;
    }

    public function google_analytics_data()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        $agent = new Agent();

        $data["ip_address"] = $_SERVER["SERVER_ADDR"];
        $data["operating_system"] = $agent->platform();
        return json_encode($data);
    }

    public function upload_system_files(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $file = $request->file('document');
   
        $file_size = $request->file('document')->getClientSize();
        if($file_size < 26214400) // max 25mb
        {
            $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
            $path ="lokalize/business_proof";
            $storage_path = storage_path();

            if ($request->file('document')->isValid())
            {
                $full_path = Storage::putFile($path, $request->file('document'), "public");
                $return['message']      = "upload successful";
                $return['status']       = "success";
                $return['full_path']    = $full_path;
            }
            else
            {
                $return['message']      = "Error on uploading";
                $return['status']       = "fail";
            }
        }
        else
        {
            $return['message'] = "The maximum file size is 25mb.";
            $return['status'] = "fail";
        }
         return json_encode($return);
    }


    public function get_system_files()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        
        $data = Tbl_files::where("file_id", "!=", 0)->get();
        $_data = [];
        foreach ($data as $key => $value) {
            $file_name = strtolower(str_replace(array(" ", "-"), "_", $value->file_name));
            $_data[$file_name] = "http://aeolus-storage.sgp1.digitaloceanspaces.com/".$value->file;
        }

        return json_encode($_data);
    }

    public function verify_captcha(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $response   = isset($request->response) ? $request->response : null;
        $privatekey = "6LcawmMUAAAAAP9WKz8f2Gdt8fDRN0u28rQCyrB6";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'secret' => $privatekey,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ));

        $resp = json_decode(curl_exec($ch));
        curl_close($ch);

        if ($resp->success) {
            $return["status"] = "success";
        } else {
            $return["status"] = "error";
        }

        return json_encode($return);
    }

    public function upload_proof(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $file = $request->file('upload');

        $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
        $path = "successpay/".$request->input('folder');
        $storage_path = storage_path();

        if ($file->isValid())
        {
            $full_path = Storage::disk('s3')->putFile($path, $file, "public");
            $url = Storage::disk('s3')->url($full_path);
            return json_encode($url);
        }
    }
}