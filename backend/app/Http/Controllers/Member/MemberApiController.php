<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Globals\Audit;
use App\Globals\Authenticator;
use App\Globals\Wallet;
use App\Globals\Transactions;
use App\Globals\Blockchain;
use App\Globals\User;
use App\Globals\Google;
use Illuminate\Support\Facades\Crypt;
use App\Tbl_User;
use App\Tbl_coin;
use App\Tbl_coin_conversion;
use App\Tbl_cash_in_method;
use App\Tbl_sale_stage;
use App\Tbl_sale_stage_bonus;
use App\Tbl_bitcoin_cash_in;
use App\Tbl_knowyourcustomer;
use App\Tbl_member_log;
use App\Tbl_automatic_cash_in;
use App\Tbl_other_info;
use App\Tbl_member_position;
use App\Tbl_communication_board;
use App\Tbl_referral;
use App\Tbl_member_address;
use App\Tbl_referral_bonus_log;
use App\Globals\Member_log;
use Validator;
use Aws\S3\S3Client;
use PragmaRX\Google2FA\Google2FA;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Tbl_email_verification;
use App\Globals\Mails;
use Intervention\Image\Image;
use stdClass;
use Excel;

class MemberApiController extends Controller
{
    public $member;

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $member = Authenticator::checkLogin(request()->login_token);
        
        if($member)
        {  
            $this->member = $member;
            Wallet::setupWallet($member->id);
        }
        else
        {
            abort(404);
        }

        Validator::extend('num_spaces', function ($attribute, $value) {

            // This will only accept alpha and spaces. 
            // If you want to accept hyphens use: /^[\pL\s-]+$/u.
            return preg_match('/^[0-9 +-]+$/', $value); 

        });
    }

    function init()
    {
        
        if ($this->member) 
        {
            return json_encode('true');
        }
        else
        {
            $this->member = $member;
        }
    }

    function dashboard(Request $req)
    {

        return json_encode($this->member);
    }

    function member_info(Request $req)
    {

        $_member                           = new stdClass();
        $member                            = $this->member;

        $_member->member_id                = $member->id;
        $_member->first_name               = $member->first_name;
        $_member->last_name                = $member->last_name;
        $_member->email                    = $member->email;
        $_member->birth_date               = $member->birth_date;
        $_member->phone_number             = $member->phone_number;
        $_member->entity                   = $member->entity;
        $_member->country_code_id          = $member->country_code_id;
        $_member->desired_btc              = $member->desired_btc;
        $_member->desired_eth              = $member->desired_eth;
        $_member->company_name             = $member->company_name;
        $_member->verified_mail            = $member->verified_mail;
        $_member->status_account           = $member->status_account;
        $_member->username                 = $member->username;
        $_member->platform                 = $member->platform;
        $_member->is_admin                 = $member->is_admin;
        $_member->crypto_purchaser         = $member->crypto_purchaser;
        $_member->first_time_login         = $member->first_time_login == 1 ? true : false;
        $_member->created_at               = $member->created_at;
        $_member->btc_transaction_fee      = 0.00005;
        $_member->_wallet                  = Wallet::getWalletList($member->id);

        $_member->_transaction             = Wallet::getTransaction($member->id);

        $_member->_transaction_pending     = Wallet::getTransaction($member->id, "pending");
        $_member->_transaction_confirmed   = Wallet::getTransaction($member->id, "confirmed");
        $_member->_transaction_processing  = Wallet::getTransaction($member->id, "processing");

        $date_now = date("Y-m-d", strtotime(Carbon::now('Asia/Manila')));
        
        $_current_stage = Tbl_sale_stage::whereDate("sale_stage_start_date", "<=", $date_now)->whereDate("sale_stage_end_date", ">=", $date_now)->first();

        $salestage_id = $_current_stage ? $_current_stage->sale_stage_id : 0;
        Blockchain::checkBalanceBTC($member->id, $salestage_id);
        Blockchain::checkBalanceETH($member->id, $salestage_id);

       return json_encode($_member);
    }

    public function other_info()
    {
       
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

    public function get_sale_stages()
    {
        $date_now = date("Y-m-d", strtotime(Carbon::now('Asia/Manila')));
       
        
        $_current_stage = Tbl_sale_stage::whereDate("sale_stage_start_date", "<=", $date_now)->whereDate("sale_stage_end_date", ">=", $date_now)->first();
        $_current_stage_count = Tbl_sale_stage::whereDate("sale_stage_start_date", "<=", $date_now)->whereDate("sale_stage_end_date", ">=", $date_now)->count();
        $bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $_current_stage_count != 0 ? $_current_stage->sale_stage_id : 1)->get();
        $_current_stage["bonus"] = $bonus;

        return $_current_stage;
    }

    public function get_buy_bonus(Request $request)
    {
        $buy_bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $request->sale_stage_id)->where("buy_coin_bonus_from", "<=", $request->token_amount)->where("buy_coin_bonus_to", ">=", $request->token_amount)->first();
        // dd($buy_bonus);
        if($buy_bonus)
        {
            $return["percentage"] = $buy_bonus->buy_coin_bonus_percentage;
            $return["message"] = "within";
        }
        else
        {
            $buy_bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $request->sale_stage_id)->where("buy_coin_bonus_to", "<=", $request->token_amount)->orderBy("buy_coin_bonus_to", "desc")->first();
            if($buy_bonus)
            {
                $return["percentage"] = $buy_bonus->buy_coin_bonus_percentage;
                $return["message"] = "highest";
            }
            else
            {
                $return["percentage"] = 0;
                $return["message"] = "no bonus";
            }
        }
        // dd($return);
        return json_encode($return);
    }

    function member_update_password(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        $rules["current_password"]          = array("required");
        $rules["password"]                  = array("required", "min:6");
        $rules["password_confirmation"]     = array("required", "min:6", "same:password");

        $data = Tbl_User::where('id', $request->id)->first();
        $_data = [];
        $_data['id']        = $data->id;
        $_data['password']  = $data->password; 

        $match_password = Hash::check($request->current_password,$_data['password']); 
        $validator      = Validator::make($request->all(), $rules);

        if(strpos($request->password, ' ') !== false )//white space detection
        {
            $return["message"] = "Password contain white space.";
            $return["status"] = "fail";
        }
        /* VALIDATE LOGIN */
        else if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else
        {
            if($match_password)
            {
                $insert['id']               = $request->id;
                $insert['password']         = Hash::make($request->password);
                User::updatePassword($insert);
                $return["message"]          = "Password Successfully Changed";
                $return["status"]           = "success";
            }
            else
            {
                $return["message"]  = "Current password not match.";
                $return["status"]   = "fail";
            }  
        }
            return json_encode($return);
    }


    function update_contact_number(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
        if($request->crypto_purchaser == "none")
        {
            $request->crypto_purchaser = null;
        }
        $rules["phone_number"]      = array("required","unique:users");
        $validator_phone            = Validator::make($request->all(), $rules);
        if($validator_phone->fails())
        {
           $rules["username"]          = array("required","unique:users");
           $validator_user             = Validator::make($request->all(), $rules);
           if($validator_user->fails())
           {
                $update["entity"]           = $request->entity;
                $update["company_name"]     = $request->company_name;
                $update["desired_btc"]      = $request->desired_btc;
                $update["desired_eth"]      = $request->desired_eth;
                $update["country_code_id"]  = $request->country_code_id; 
                $update["crypto_purchaser"] = $request->crypto_purchaser;
           }
                $update["username"]         = $request->username;
                $update["entity"]           = $request->entity;
                $update["company_name"]     = $request->company_name;
                $update["desired_btc"]      = $request->desired_btc;
                $update["desired_eth"]      = $request->desired_eth;
                $update["country_code_id"]  = $request->country_code_id; 
                $update["crypto_purchaser"] = $request->crypto_purchaser; 
        }
        else
        {
            $update["username"]         = $request->username;
            $update["phone_number"]     = $request->phone_number;
            $update["entity"]           = $request->entity;
            $update["company_name"]     = $request->company_name;
            $update["desired_btc"]      = $request->desired_btc;
            $update["desired_eth"]      = $request->desired_eth;
            $update["country_code_id"]  = $request->country_code_id;
            $update["crypto_purchaser"] = $request->crypto_purchaser;
        }
        $data = Tbl_User::where('id', $request->id)->update($update);
            if($data)
            {
                $return["message"]      = "Successfully updated";
                $return["status"]       = "success";
                $return["phone_number"] = $request->phone_number;
                $return["crypto_purchaser"] = $request->crypto_purchaser;
            }
            else
            {
                $return["message"]      = "Error in updating.";
                $return["status"]       = "fail";
            }

        return json_encode($return);
    }

    public function record_transaction(Request $request)
    {
        // dd($request);
        if($request->token_amount > 0)
        {
            $member_id                          = $request->member_id;
            $coin_id                            = Tbl_coin::where("coin_abb", "AHM")->value("coin_id");
            $sale_stage_id                      = $request->sale_stage_id;
            $conversion_rate                    = $request->lok_exchange_rate;
            $amount                             = $request->amount_to_pay;
            $token_amount                       = $request->token_amount;
            $log_method                         = $request->payment_method;
            $cash_in_method                     = $request->cash_in_method;
            $cash_in_proof_img                  = $request->cash_in_proof_img;
            $cash_in_proof_tx                   = $request->cash_in_proof_tx;
            $log                                = "Buy <b>". $request->token_amount ." AHM Tokens</b> via <b>" . ucfirst($request->payment_method) . ".</b>";
            $member_log_id                      = Wallet::recordTransaction($member_id, $coin_id, $sale_stage_id, $conversion_rate, $amount, $token_amount, $log_method, $log, "pending" ,$cash_in_method, $cash_in_proof_img, $cash_in_proof_tx);        

            $return["type"] = "success";
            $return["message"] = "Successfully placed an order.";

        }
        else
        {
            $return["type"] = "fail";
            $return["message"] = "Token amount cannot be less than 0. ";
        }
        
        return $return;
    }

    function get_btc_transaction(Request $request)
    {
        $req = request()->all();
        $data["list"] = Transactions::getTransactions($req,null,$request->member_id);
        $data["address"] = Tbl_member_address::where("member_id",$request->member_id)->where("coin_id",3)->select('member_address')->get()->first();
        return $data;
    }

    function get_php_transaction(Request $request)
    {
        $req = request()->all();
        $data["list"] = Transactions::getTransactions($req,null,$request->member_id);
        $data["address"] = Tbl_member_address::where("member_id",$request->member_id)->where("coin_id",1)->select('member_address')->get()->first();
        return $data;
    }

    function get_eth_transaction(Request $request)
    {
        $req = request()->all();
        $data["list"] = Transactions::getTransactions($req,null,$request->member_id);
        $data["address"] = Tbl_member_address::where("member_id",$request->member_id)->where("coin_id",2)->select('member_address')->get()->first();
        return $data;
    }

    public function upload_file(Request $request)
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

        if($request->for == "banner")
        {
            $file_banner = $request->file('image_banner');
            $file_size = $file_banner->getClientSize();
            $file_ext = $file_banner->getClientOriginalExtension();
            $file_dimension = getimagesize($file_banner);
            $width = $file_dimension[0];
            $height = $file_dimension[1];
            if($width == 300 && $height == 140)
            {
                if($file_ext == "jpg" 
                || $file_ext == "png" || $file_ext == "JPG"
                || $file_ext == "PNG" || $file_ext == "jpeg"
                || $file_ext == "JPEG" || $file_ext == "gif"
                || $file_ext == "GIF")
                {
                    if($file_size < 26214400)
                    {
                        $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
                        $path ="ahm/kycphotos";
                        $storage_path = storage_path();
            
                        if ($request->file('image_banner')->isValid())
                        {
                            $full_path = Storage::putFile($path, $request->file('image_banner'), "public");
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
                }
                else
                {
                    $return['message'] = "The " .$file_ext. " file is not supported.";
                    $return['status'] = "fail";
                }
            }
            else
            {
                $return['message'] = "300 x 140 is required dimension for banner";
                $return['status'] = "fail";
            }
        }
        else
        {
            $file = $request->file('image');
            $file_size = $request->file('image')->getClientSize();
            $file_ext = $request->file('image')->getClientOriginalExtension();
            if($file_ext == "jpg" 
                || $file_ext == "png" || $file_ext == "JPG"
                || $file_ext == "PNG" || $file_ext == "jpeg"
                || $file_ext == "JPEG" || $file_ext == "gif"
                || $file_ext == "GIF")
            {
                if($file_size < 26214400)
                {
                    $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
                    $path ="ahm/kycphotos";
                    $storage_path = storage_path();
        
                    if ($request->file('image')->isValid())
                    {
                        $full_path = Storage::putFile($path, $request->file('image'), "public");
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
            }
            else
            {
                $return['message'] = "The " .$file_ext. " file is not supported.";
                $return['status'] = "fail";
            }
        }

        return json_encode($return);
    }
    
    public function submit_kyc_id_level_2(Request $request)
    {
        $rules["id_type"]           = array("required");
        $rules["id_number"]         = array("required");
        $validator                  = Validator::make($request->all(), $rules);
        if($validator->fails())
        {
            $return['message']  = $validator->errors()->first();
            $return['status']   = "fail";
        }
        else
        {
            $insert["kyc_member_id"]        = $request->member_id;
            $insert["kyc_proof"]            = $request->front_id_link;
            $insert["kyc_id_number"]        = $request->id_number;
            $insert["kyc_type"]             = $request->id_type;
            $insert["kyc_remarks"]          = "front";
            $insert["kyc_id_expiration"]    = $request->expiration_date;
            $insert["kyc_level"]            = $request->level;
            $insert["kyc_upload_date"]      = Carbon::now('Asia/Manila');
            if($request->front_id_link == null || $request->back_id_link == null)
            {
                $return['message']  = "Can't upload proof, Please try again";
                $return['status']   = "fail";
            }
            else
            {
                $front = Tbl_knowyourcustomer::insert($insert); // insert front
                if($front)
                {
                    $insert["kyc_proof"]        = $request->back_id_link;
                    $insert["kyc_remarks"]      = "back";
                    $back = Tbl_knowyourcustomer::insert($insert); // insert back
                    if($back)
                    {
                        $return['message']  = "All information saved, Please wait for confirmation.";
                        $return['status']   = "success";
                    }
                    else
                    {
                        $return['message']  = "Error";
                        $return['status']   = "fail";
                    }
                }
            }
        }
        return json_encode($return);
    }

    public function submit_kyc_seflie_level_2(Request $request)
    {
        
        $insert["kyc_member_id"]        = $request->member_id;
        $insert["kyc_proof"]            = $request->selfie_link;
        $insert["kyc_type"]             = "selfie";
        $insert["kyc_remarks"]          = "selfie";
        $insert["kyc_level"]            = $request->level;
        $insert["kyc_upload_date"]      = Carbon::now('Asia/Manila');
        $id = Tbl_knowyourcustomer::insertGetId($insert); // insert front
       
        if($id)
        {
            $success = Tbl_knowyourcustomer::where("kyc_id",$id)->update(["kyc_id_number" => $id]); //id number
            if($success)
            {
                $return['message']  = "Your selfie verification was sent, Please wait for confirmation.";
                $return['status']   = "success";
            }
            else
            {
                $return['message']  = "Error";
                $return['status']   = "fail";
            }
        }
        return json_encode($return);
    }

    function cancel_transaction(Request $request)
    {
        // dd(request()->all());
        $data = Tbl_member_log::where("member_log_id", $request->member_log_id)->where("log_method", $request->log_method)->update(["log_status" => "canceled"]);
    }
    function get_kyc_status(Request $request)
    {
        $member_id = $request->member_id;

        $kyc = Tbl_knowyourcustomer::where('kyc_member_id',$member_id);
        if($kyc->count() != 0)
        {
            $id_status = $kyc->where('kyc_remarks',"front");
            if($id_status->count() != 0)
            {   
                $id_status = $id_status->where('kyc_status',"!=","rejected")->first();
                if($id_status)
                {
                    if($id_status->kyc_status == "pending")
                    {
                        $return['kyc_status_id'] = $id_status->kyc_status;
                        $return['message_id'] = "Wait for confirmation";
                    }
                    else if($id_status->kyc_status == "completed")
                    {
                        $return['kyc_status_id'] = $id_status->kyc_status;
                        $return['message_id'] = "Completed";
                    }
                }
                else
                {
                     $return['kyc_status_id'] = "rejected";
                    $return['message_id'] = "Rejected";
                }
            }
            else
            {
                $return['kyc_status_id'] = "";
                $return['message_id'] = "Verify";
            }

           $selfie_status = Tbl_knowyourcustomer::where('kyc_member_id',$member_id)->where('kyc_type',"selfie");
            if($selfie_status->count() != 0)
            {
                $selfie_status = $selfie_status->where("kyc_status","!=","rejected")->first();
                if($selfie_status)
                {
                    if($selfie_status->kyc_status == "pending")
                    {
                        $return['kyc_status_selfie'] = $selfie_status->kyc_status;
                        $return['message_selfie'] = "Wait for confirmation";
                    }
                    else if($selfie_status->kyc_status == "completed")
                    {
                        $return['kyc_status_selfie'] = $selfie_status->kyc_status;
                        $return['message_selfie'] = "Completed";
                    }
                }
                else
                {
                    $return['kyc_status_selfie'] = "rejected";
                    $return['message_selfie'] = "Rejected";
                }
            }
            else
            {
                $return['kyc_status_selfie'] = "";
                $return['message_selfie'] = "Verify";
            }
        }
        else
        {
            $return['kyc_status_selfie'] = "";
            $return['message_selfie'] = "Verify";
            $return['kyc_status_id'] = "";
            $return['message_id'] = "Verify";
        }
        return json_encode($return);
    }


    public function get_referrals(Request $request)
    {
        $id = $request->id;
        if($request->career != null)
        {
            if($request->career != "all")
            {
                $data["list"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name",$request->career)->joinDetails()->get();
            }
            else
            {
                $data["list"] = Tbl_other_info::where("referral_user_id",$id)->joinDetails()->get();
            }
        }
        else
        {
            $data["list"] = Tbl_other_info::where("referral_user_id",$id)->joinDetails()->get();
            $data["count"] = $data["list"]->count();
        }

        return $data;
    }
    public function get_upcoming_event(Request $request)
    {
        $member_id = $request->member_id;
        $member_info = Tbl_other_info::where("user_id",$member_id)->joinDetails()->first();
        $position_name = $member_info->member_position_name;

        $position_name = Str::lower($position_name);
        $position_name = str_replace(' ', '_', $position_name);
        $column_name = "communication_board_career_".$position_name;

        $data = Tbl_communication_board::where($column_name,1)->whereDate("communication_board_end_date",">=",Carbon::now('Asia/Manila'));
        $upcoming_event["list"] = $data->orderBy("insert_date","desc")->get()->take(5);
        $upcoming_event["count"] = $data->count();
        return json_encode($upcoming_event);
    }

    public function get_upcoming_event_details(Request $request)
    {
        $id = $request->id;

        $data["list"] = Tbl_communication_board::where("communication_board_id",$id)->first();
        return $data["list"];
    }
    
    public function get_referral_info(Request $request)
    {
        $id = $request->id;
        $member_position = Tbl_other_info::where("user_id", $id)->first();

        if($request->auth == 'admin')
        {
            $data["list"] = Tbl_referral::where("referral_user_id",$id)->first();
            $data["refer"] = 1;
        }
        else
        {
            if($member_position->member_position_id != 1)
            {
                $data["list"] = Tbl_referral::where("referral_user_id",$id)->first();
                $data["refer"] = 1;
            }
            else
            {
                $data["refer"] = 0;
            }
        }
        

        return $data;
    }

    public function get_view_referral_info(Request $request)
    {   
        $member_log_id = Tbl_member_log::where("id",$request->to_id)->where("log_mode","referral bonus")->member();
        if($request->tx_date_from)
        {
            $member_log_id = $member_log_id->whereDate("tbl_member_log.log_time", ">=", $request->tx_date_from);
        }
        if($request->tx_date_to)
        {
            $member_log_id = $member_log_id->whereDate("tbl_member_log.log_time", "<=", $request->tx_date_to);
        }
        $member_log_id = $member_log_id->get();

        if(count($member_log_id) != 0)
        {
            foreach ($member_log_id as $key => $data) {
            $referral[$key] = Tbl_referral_bonus_log::where("member_log_to",$data->member_log_id)->select("member_log_from","member_log_to")->get();
            }
            foreach ($referral as $key => $data) {
                $referral_info[$key]["from"] = Tbl_member_log::where("id",$request->from_id)->where("member_log_id",$referral[$key][0]->member_log_from)->member()->get();

                // $referral_info[$key]["from"] = $referral_info[$key]["from"]->where("log_method","Ethereum Total")->orWhere(function($query)
                //     {
                //         $query->orWhere("log_method","Bitcoin Total");
                //     })->orderBy("log_time","DESC")->get();
           
                 $referral_info[$key]["to"] = Tbl_member_log::where("id",$request->to_id)->where("member_log_id",$referral[$key][0]->member_log_to)->where("log_mode","referral bonus")->member()->get();
            }
            foreach ($referral_info as $key => $value) {
                if($referral_info[$key]["from"]->isEmpty())
                {
                    unset($referral_info[$key]);
                }
            }
            $reindex = array_values($referral_info);
            $referral_info = $reindex;
            if(!$referral_info)
            {
                $referral_info = "";
            }
        }
        else
        {
            $referral_info = "";
        }

        return $referral_info;
    }

    public function check_tokens(Request $request)
    {
        $wallet_id = $request->wallet_id;
        $data["bought"]           = Tbl_member_log::where("member_address_id", $wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_status", "!=", "processing")->where("log_mode", "receive")->sum("log_amount");
        $data["purchased"]        = Tbl_member_log::where("member_address_id", $wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_status", "!=", "processing")->where("log_mode", "buy bonus")->sum("log_amount");
        $data["affiliated"]   = Tbl_member_log::where("member_address_id", $wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "floating")->where("log_status", "!=", "canceled")->where("log_status", "!=", "processing")->where("log_mode", "referral bonus")->sum("log_amount");
        $data["manual"]  = Tbl_member_log::where("member_address_id", $wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "floating")->where("log_status", "!=", "canceled")->where("log_status", "!=", "processing")->where("log_mode", "manual")->sum("log_amount");
        return $data;
    }

    public function check_contributions(Request $request)
    {
        $eth_wallet_id = $request->eth_wallet_id;
        $btc_wallet_id = $request->btc_wallet_id;
        $php_wallet_id = $request->php_wallet_id;
        $data["btc"] = Tbl_member_log::where("member_address_id", $btc_wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "processing")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "receive")->where("log_method", "Bitcoin Accepted")->sum("log_amount");
        $data["eth"] = Tbl_member_log::where("member_address_id", $eth_wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "processing")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "receive")->where("log_method", "Ethereum Accepted")->sum("log_amount");
        $data["php"] = Tbl_member_log::where("member_address_id", $php_wallet_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "processing")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "receive")->where("log_method", "Bank Accepted")->sum("log_amount");
        return $data;
    }

    public function pair_google_2fa(Request $request)
    {
        $data = Google::getQRPairingCode($request->user_id);
        return json_encode($data);
    }

    public function change_status_2fa(Request $request)
    {
        $data = Google::changeStatus2FA($request->user_id);
        return json_encode($data);
    }

    public function pair_code_google_2fa(Request $request)
    {
        $data = Google::validateKey($request->user_id, $request->pair_code);
        return json_encode($data);
    }

    public function get_recent_transaction(Request $request)
    {
        $id = $request->id;
        $member_log_details = Tbl_member_log::where("id",$id)->Member();
        if($member_log_details->get()->count() != 0)
        {
           $data = Tbl_member_log::where("id",$id)->where("log_mode","!=","manual");
           $data = $data->where(function($query)
           {
                $query
                ->Where("log_method","Ethereum")
                ->orWhere("log_method","Bitcoin")
                ->orWhere("log_method","Bank")
                ->orWhere("log_method", "Bitcoin Total")
                ->orWhere("log_method", "Bank Total")
                ->orWhere("log_method", "Ethereum Total");
           });
           //dd($data->Member()->get());
           $data = $data->Member()->orderBy("log_time","DESC")->get();
           //$data["list"] = $member_log->where("log_status","accepted")->get();
           foreach ($data as $key => $value) {
                switch($value->log_method)
                {
                case "Bitcoin":
                case "Bitcoin Total":
                    $data[$key]["log_method"] = "Bitcoin";
                break;
                case "Ethereum":
                case "Ethereum Total":
                    $data[$key]["log_method"] = "Ethereum";
                break;
                case "Bank":
                case "Bank Total":
                    $data[$key]["log_method"] = "Bank";
                break;
                }
           }
        }
        else
        {
            $data = "";
        }
        //$member_log = $member_log_details->where("log_method","Bitcoin")->orWhere("log_method","Ethereum");
        //$data["list"] = $member_log->where("log_status","accepted")->get();
        return $data;
    }

    public function get_kyc_level(Request $request)
    {
        $level = 0;
        $level1 = Tbl_User::where("verified_mail",1)->where("id",$request->id)->get();
        if(count($level1) != 0)
        {
            $data = Tbl_knowyourcustomer::where("kyc_member_id",$request->id);
            if($data->count() != 0)
            {
                $level_2 = Tbl_knowyourcustomer::where("kyc_member_id",$request->id)->where("kyc_level",2)->where("kyc_status","completed")->count();
    
                if($level_2 != 0)
                {
                    $level_2_selfie = Tbl_knowyourcustomer::where("kyc_member_id",$request->id)->where("kyc_level",2)->where("kyc_status","completed");
                    $level2_selfie = $level_2_selfie->where("kyc_type","selfie")->get();

                    $level_2_id = Tbl_knowyourcustomer::where("kyc_member_id",$request->id)->where("kyc_level",2)->where("kyc_status","completed");
                    $level2_id = $level_2_id->where("kyc_type","!=","selfie")->distinct("kyc_upload_date")->get();
                    if(count($level2_selfie) != 0 && count($level2_id) != 0)
                    {
                        $level = 2;
                    }
                    else
                    {
                        $level = 1;
                    }
                }
            }
        }

        return json_encode($level);
    }

    public function get_manual_transfer_list(Request $request)
    {
        $data = Member_log::manualTransferList($request->all(),$request->id);
        return $data;
    }
    public function check_notifications(Request $request)
    {
        // $referral_id = Tbl_referral::where("referral_user_id", $request->user_id)->first();
        // $data["new_referrals"] = Tbl_other_info::where("referrer_id", $referral_id->referral_id)->where("is_viewed", 0)->count();

        $btc_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 3)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Bitcoin Accepted")->where("is_viewed", 0)->count();
        $data["new_btc_approve"] = $btc_transactions;

        $eth_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 2)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Ethereum Accepted")->where("is_viewed", 0)->count();
        $data["new_eth_approve"] = $eth_transactions;

        $bank_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 1)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Bank Accepted")->where("is_viewed", 0)->count();
        $data["new_bank_approve"] = $bank_transactions;

        // $referral_bonus = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 4)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_mode", "referral bonus")->where("is_viewed", 0)->count();
        // $data["new_referral_bonus"] = $referral_bonus;

        return json_encode($data);
    }

    public function reset_notifications(Request $request)
    {
        if($request->notif_type == "new_btc_approve")
        {
            $btc_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 3)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Bitcoin Accepted")->where("is_viewed", 0)->get();
            foreach ($btc_transactions as $key => $value) 
            {
                $data = Tbl_member_log::where("member_log_id", $value->member_log_id)->where("is_viewed", 0)->update(["is_viewed" => 1]);
            }
            $return["message"] = "bitcoin notifications reset";
        }
        else if($request->notif_type == "new_eth_approve")
        {
            $eth_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 2)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Ethereum Accepted")->where("is_viewed", 0)->get();
            foreach ($eth_transactions as $key => $value) 
            {
                $data = Tbl_member_log::where("member_log_id", $value->member_log_id)->where("is_viewed", 0)->update(["is_viewed" => 1]);
            }
            $return["message"] = "ethereum notifications reset";
        }
        else if($request->notif_type == "new_bank_approve")
        {
            $bank_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 1)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_method", "Bank Accepted")->where("is_viewed", 0)->get();
            foreach ($bank_transactions as $key => $value) 
            {
                $data = Tbl_member_log::where("member_log_id", $value->member_log_id)->where("is_viewed", 0)->update(["is_viewed" => 1]);
            }
            $return["message"] = "bank notifications reset";
        }

        // if($request->notif_type == "new_referral_bonus")
        // {
        //     $btc_transactions = Tbl_member_address::where("member_id", $request->user_id)->where("coin_id", 4)->join("tbl_member_log", "tbl_member_log.member_address_id", "=", "tbl_member_address.member_address_id")->where("log_mode", "referral bonus")->where("is_viewed", 0)->get();
        //     foreach ($btc_transactions as $key => $value) 
        //     {
        //         $data = Tbl_member_log::where("member_log_id", $value->member_log_id)->where("is_viewed", 0)->update(["is_viewed" => 1]);
        //     }
        //     $return["message"] = "new referral bonus notifications reset";
        // }
        // else
        // {
        //     $referral_id = Tbl_referral::where("referral_user_id", $request->user_id)->first();
        //     $refer = Tbl_other_info::where("referrer_id", $referral_id->referral_id)->where("is_viewed", 0)->update(["is_viewed" => 1]);
        //     $return["message"] = "new referrals notifications reset";
        // }

        return json_encode($return);
    }

    public function first_update_information(Request $request)
    {
        if($request->crypto_purchaser == "none")
        {
            $request->crypto_purchaser = null;
        }
        if($request->platform != "system")
        {
            $rules["email"]                     = array("required", "email", "unique:users");
        }
        $rules["country_code_id"]           = array("required", "alpha_num");
        $rules["phone_number"]              = array("required", "num_spaces", "unique:users");
        $rules["birth_date"]                = array("required", "string");

        $validator                          = Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else
        {
            $update["email"]                = $request->email;
            $update["country_code_id"]      = $request->country_code_id;
            $update["phone_number"]         = $request->phone_number;
            $update["company_name"]         = $request->company_name;
            $update["desired_btc"]          = $request->desired_btc;
            $update["desired_eth"]          = $request->desired_eth;
            $update["birth_date"]           = $request->birth_date;
            $update["entity"]               = $request->entity == 0 ? "Individual" : $request->entity;
            $update["first_time_login"]     = 0;
            $update["crypto_purchaser"]     = $request->crypto_purchaser;

            $data = Tbl_User::where("id", $request->member_id)->update($update);

            if($data)
            {
                $return["message"]  = "Profile Updated! Thank you for your cooperation.";
                $return["status"]   = "success";
            }
            else
            {
                $return["message"]  = "Oops something went wrong. Please check your entries and try again.";
                $return["status"]   = "fail";
            }

        }

        return json_encode($return);
    }

    public function get_verified_mail(Request $request)
    {
        $data = Tbl_User::select("verified_mail")->where("id",$request->id)->first();

        return json_encode($data);
    }
    public function send_verify_email_kyc(Request $request)
    {
         $member_id                                    = $request->id;
         $email_verification["verification_email"]     = $request->email;
         $email_verification["verification_user_id"]   = $member_id;
         $email_verification["expiration_date"]        = Carbon::now('Asia/Manila')->addHours(12);
         $email_verification["date_generated"]         = Carbon::Now('Asia/Manila');
         $email_verification["verification_code"]      = md5(Carbon::now('Asia/Manila')); 
         $id = Tbl_email_verification::insertGetId($email_verification);
         if($id != 0)
         {
            $data["email"] = Tbl_email_verification::where("verification_id",$id)->first();
            $data["member"] = Tbl_User::select("first_name","last_name")->where("id",$request->id)->first();
            Mails::send_email_verification($data);
            $return["status"] = "success";
         }
         else
         {
            $return["status"] = "fails";
            $return["message"] = "Error in submitting verification code";
         }
         return json_encode($return);
    }
    public function verify_email_kyc(Request $request)
    {
        $email_data = Tbl_email_verification::where("verification_code",$request->verification_code)->get()->first();
        $member = Tbl_User::where('email',$request->email)->get()->first();
        
        if($email_data)
        {
            if($member->verified_mail == 0)
            {
                if($email_data->is_used == 1)
                {
                    $return["message"] = "The verification code is used.";
                    $return["status"]  = "fail";
                }
                else if($email_data->expiration_date <= Carbon::now('Asia/Manila'))
                {
                    Tbl_email_verification::where("verification_id", $email_data->verification_id)->update(["is_used" => 1]);
                    $return["message"] = "The verification code is already expired.";
                    $return["status"]  = "fail";
                }
                else
                {
                    $return["message"] = "Your email is successfully verified";
                    $return["status"]  = "success";
                    $update_email_verification["is_used"] = 1;
                    $update_user["verified_mail"]   = 1;
                    Tbl_email_verification::where('verification_code', $email_data->verification_code)->update($update_email_verification);
                    Tbl_User::where('email', $email_data->verification_email)->update($update_user);
                    
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

    public function check_pending_order_method(Request $request)
    {
        $pending_transaction = Tbl_member_log::where("member_address_id", $request->member_address_id)->where("log_method", $request->log_method)->where(function($query)
    {
        $query->where("log_status", "pending")->orWhere("log_status", "processing");
    });

        if($pending_transaction->count() == 1)
        {
            $return = true;
        }
        else
        {
            $return = false;
        }

        return json_encode($return);
    }

    public function get_bank_methods()
    {
        $banks = Tbl_cash_in_method::where("cash_in_method_payment_rule", 0)->get();
        if($banks)
        {
            return json_encode($banks);
        }
    }

    public function upload(Request $request)
    {
        $file = $request->file('upload');

        $path_prefix = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
        $path = "ahm/".$request->folder;
        $storage_path = storage_path();

        if ($file->isValid())
        {
            $full_path = Storage::disk('s3')->putFile($path, $file, "public");
            $url = Storage::disk('s3')->url($full_path);
            return json_encode($url);
        }
    }
}