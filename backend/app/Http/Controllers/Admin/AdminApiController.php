<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Tbl_User;
use App\Tbl_login;
use App\Tbl_coin;
use App\Tbl_coin_conversion;
use Carbon\Carbon;
use App\Globals\Audit;
use App\Globals\Authenticator;
use App\Globals\Wallet;
use App\Globals\User;
use App\Globals\Mails;
use App\Globals\Blockchain;
use App\Globals\Transactions;
use App\Globals\Member_log;
use App\Tbl_cash_in_method;
use App\Tbl_cash_in_proof;
use App\Tbl_member_log;
use App\Tbl_transaction_convert;
use App\Tbl_transaction_transfer;
use App\Tbl_cash_out_requests;
use App\Tbl_automatic_cash_in;
use App\Tbl_btc_transaction;
use App\Tbl_member_address;
use App\Tbl_member_position;
use App\Tbl_kyc_proof_v2;
use App\Tbl_other_info;
use App\Tbl_referral;
use App\Tbl_knowyourcustomer;
use App\Tbl_communication_board;
use App\Tbl_position_requirements;
use App\Tbl_business_application;
use App\Tbl_member_position_log;
use App\Tbl_main_wallet_addresses;
use App\Tbl_referral_bonus_log;
use App\Tbl_central_wallet;
use App\Tbl_faqs;
use App\Tbl_files;
use App\Tbl_release_logs;
use PragmaRX\Google2FA\Google2FA;
use stdClass;
use Crypt;
use Storage;

class AdminApiController extends Controller
{
	public $member;

    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

        $member = Authenticator::checkLogin(request()->login_token);

        if($member)
        {
            $this->member = $member;
        }
        else
        {
            abort(404);
        }
    }

    function get_member_list(Request $request)
    {
        $param["search_name"]   = $request->search_name;
        $param["search_status"] = $request->search_status;
        $param["search_email_status"] = $request->search_email_status;
        $param["search_roles"] = $request->search_roles;
        $param["search_career"] = $request->search_career;
        $param["search_date_from"] = $request->search_date_from;
        $param["search_date_to"] = $request->search_date_to;
        $param["register_platform"] = $request->register_platform;
        $param["career"] = $request->career;

        $data["list"] = User::getList($param);
        return $data["list"];
    }

    function active_deactive_user(Request $request)
    {
        Tbl_User::where("id", $request->id)->update(["status_account" => $request->active]);
        $data['member'] = Tbl_User::where("id",$request->id)->get()->first();
        Mails::send_mail_activate($data);
    }

    function promote_demote_user(Request $request)
    {
        $data = Tbl_User::where("id", $request->id)->update(["is_admin" => $request->active]);
    }

    function change_user_password(Request $request)
    {
        $param["id"] = $request->id;
        $param["new_password"] = $request->c_new_password;
        $param["confirm_new_password"] = $request->c_confirm_new_password;
        $data = User::updateUserPassword($param);
        if($data)
        {
            $datas["member"] = Tbl_User::where("id",$request->id)->get()->first();
            $datas["new_password"] = $request->c_new_password;
            Mails::send_mail_pass($datas);
        }

        return $data;
    }

    function unactivated_members()
    {
        $data = Tbl_User::where("verified_mail",1)->where("status_account",1);

        return $data->count();
    }

    function login_history(Request $request)
    {
        $data["list"] = Tbl_login::where("id", $request->id)->orderBy("login_date", "desc")->get();

        return $data["list"];
    }

    function btc_transaction_list(Request $request)
    {
        $req = request()->all();
        $data["list"] = Transactions::getTransactions($req);
        // dd($data["list"]);
        return $data["list"];
    }

    function btc_pending_transactions()
    {
        $data = Tbl_member_log::where("log_status", "pending")->where("log_method", "Bitcoin");

        return $data->count();
    }

    function get_member_transactions(Request $request)
    {

        $req = request()->all();
        $data["list"] = Transactions::getTransactions($req,null,$request->member_id);
        return $data["list"];
    }

    function get_member_positions(Request $request)
    {
        $data["list"] = Tbl_member_position::where("member_position_id", "!=", 0)->get();

        return $data["list"];
    }

    function get_current_member_positions(Request $request)
    {
        $data["list"] = Tbl_member_position::where("member_position_id", $request->member_position_id)->first();

        return $data["list"];
    }

    function update_current_member_positions(Request $request)
    {

        $update["bonus_method"]                 = $request->bonus_method;
        $update["commission"]                   = $request->commission;
        $update["token_release"]                = $request->token_release;
        $update["initial_release_percentage"]   = $request->initial_release_percentage;
        $update["member_min_purchase"]          = $request->member_min_purchase;
        $update["member_bonus_percentage"]      = $request->member_min_purchase;
        $update["needed_member"]                = $request->needed_member;
        $update["needed_ambassador"]            = $request->needed_ambassador;
        $update["needed_community_manager"]     = $request->needed_community_manager;
        $update["needed_marketing_director"]    = $request->needed_marketing_director;
        $update["needed_advisor"]               = $request->needed_advisor;
        $update["after_purchase_comission"]     = $request->after_purchase_comission;

        $data = Tbl_member_position::where("member_position_id", $request->member_position_id)->update($update);
    }

    function get_salestage_bonus_list(Request $request)
    {
        $name = $request->account_name;
        $from = $request->transaction_date_from;
        $to   = $request->transaction_date_to;
        
        $data["list"] = Member_log::getSaleStageBonusList($name, $from, $to);
        
        return $data["list"];
    }

    function get_referral_bonus_list(Request $request)
    {
        $from = $request->invitee;
        $to   = $request->referrer;
        $date_from = $request->transaction_date_from;
        $date_to   = $request->transaction_date_to;
        
        $data["list"] = Member_log::getReferralBonusList($from, $to, $date_from, $date_to);
        
        return $data["list"];
    }
    function kyc_pending_request()
    {
        $data = Tbl_knowyourcustomer::where("kyc_status","pending")->distinct('kyc_upload_date');

        return $data->count('kyc_id_number');
    }
    function kyc_list(Request $request)
    {
        $level = $request->level;
        $name = $request->search_name;
        $date_from = $request->date_from;
        $date_to = $request->date_to;
        $status = $request->status;

        $data = Tbl_knowyourcustomer::FilterKycDetails($level,$name,$date_from,$date_to,$status);
        foreach($data as $key => $value)
        {
            $id_info = Tbl_knowyourcustomer::where("kyc_upload_date", $value->kyc_upload_date);
            $id_info = $id_info->get();
            $data[$key]["user_info"] = $id_info;

            $id_info = $id_info->first();
            $member_info = Tbl_User::where("id", $id_info->kyc_member_id)->first();
            $data[$key]["member_info"] = $member_info;
        }
        return $data;
    }
    function change_kyc_status(Request $request)
    {
        $kyc_status["kyc_status"]     = $request->status;
        $kyc_upload_date              = $request->kyc_upload_date;
        $change_status                = Tbl_knowyourcustomer::where("kyc_upload_date",$kyc_upload_date)->update($kyc_status);

        if($change_status)
        {
            $return['message'] = "Change successful";
            $return['status']  = "success";
        }
        else
        {
            $return['message'] = "Error in updating";
            $return['status']  = "fail";
        }
        return json_encode($return);
    }

    function communication_board_submit(Request $request)
    {
        $selected               = false;
        $rules["title"]         = array("required");
        $rules["subtitle"]      = array("required");
        $rules["start_date"]    = array("required");
        $rules["end_date"]      = array("required");
        $rules["description"]   = array("required");
        foreach ($request->selected as $key => $value) {
            if($value != false)
            {
                $selected = true;
            }
        }
        $validator = Validator::make($request->all(),$rules);

        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else if($selected)
        {
            if($request->selected["Member"])
            {
                $insert["communication_board_career_member"] = 1;
            }
            if($request->selected["Community Manager"])
            {
                $insert["communication_board_career_community_manager"] = 1;
            }
            if($request->selected["Ambassador"])
            {
                $insert["communication_board_career_ambassador"] = 1;
            }
            if($request->selected["Marketing Director"])
            {
                $insert["communication_board_career_marketing_director"] = 1;
            }
            if($request->selected["Advisor"])
            {
                $insert["communication_board_career_advisor"] = 1;
            }
            if($request->thumbnail != "")
            {
                $insert["communication_board_thumbnail"]  = $request->thumbnail;
            }
            if($request->banner !="")
            {
                $insert["communication_board_banner"] = $request->banner;
            }

            $insert["communication_board_title"]              = $request->title;
            $insert["communication_board_subtitle"]           = $request->subtitle;
            $insert["communication_board_start_date"]         = $request->start_date;
            $insert["communication_board_end_date"]           = $request->end_date;
            $insert["communication_board_description"]        = $request->description;
            $insert["insert_date"]                            = Carbon::now();

            $inserted = Tbl_communication_board::insert($insert);
            if($inserted)
            {
                $return['status'] = "success";
                $return['message'] = "Save successfully";
            }
            else
            {
                $return['status'] = "fail";
                $return['message'] = "Error";
            }
        }
        else
        {
            $return['status'] = "fail";
            $return['message'] = "Please select career type";
        }
        return json_encode($return);
    }

    function communication_board_update(Request $request)
    {
        $rules["title"]         = array("required");
        $rules["subtitle"]      = array("required");
        $rules["start_date"]    = array("required");
        $rules["end_date"]      = array("required");
        $rules["description"]   = array("required");
        $selected               = false;
        foreach ($request->selected as $key => $value) {
            if($value != false)
            {
                $selected = true;
            }
        }
        $validator = Validator::make($request->all(),$rules);

        if($validator->fails())
        {
            $return["message"]  = $validator->errors()->first();
            $return["status"]   = "fail";
        }
        else if($selected)
        {
            if($request->selected["Member"])
            {
                $update["communication_board_career_member"] = 1;
            }
            else
            {
                $update["communication_board_career_member"] = 0;
            }
            if($request->selected["Community Manager"] )
            {
                $update["communication_board_career_community_manager"] = 1;
            }
            else
            {
                $update["communication_board_career_community_manager"] = 0;
            }
            if($request->selected["Ambassador"])
            {
                $update["communication_board_career_ambassador"] = 1;
            }
            else
            {
                $update["communication_board_career_ambassador"] = 0;
            }

            if($request->selected["Marketing Director"])
            {
                $update["communication_board_career_marketing_director"] = 1;
            }
            else
            {
                $update["communication_board_career_marketing_director"] = 0;
            }

            if($request->selected["Advisor"])
            {
                $update["communication_board_career_advisor"] = 1;
            }
            else
            {
                $update["communication_board_career_advisor"] = 0;
            }

            if($request->thumbnail != "")
            {
                $update["communication_board_thumbnail"]  = $request->thumbnail;
            }
            if($request->banner !="")
            {
                $update["communication_board_banner"] = $request->banner;
            }

            $id                                          = $request->id;
            $update["communication_board_title"]         = $request->title;
            $update["communication_board_subtitle"]      = $request->subtitle;
            $update["communication_board_start_date"]    = $request->start_date;
            $update["communication_board_end_date"]      = $request->end_date;
            $update["communication_board_description"]   = $request->description;

            $updated = Tbl_communication_board::where("communication_board_id",$id)->update($update);

            if($updated)
            {
                $return['status'] = "success";
                $return['message'] = "Save successfully";
            }
            else
            {
                $return['status'] = "fail";
                $return['message'] = "Error";
            }
        }
        else
        {
            $return['status'] = "fail";
            $return['message'] = "Please select career type";
        }
        return json_encode($return);
    }

    function get_communication_board_list(Request $request)
    {
        $title = $request->title;
        $career = $request->careers;
        $date_from = $request->date_from;
        $date_to = $request->date_to;

        $data["list"] = Tbl_communication_board::GetFilter($title,$career,$date_from,$date_to);
        
        return $data["list"];
    }

    function get_communication_board_details(Request $request)
    {
        $id = $request->id;

        $data["list"] = Tbl_communication_board::where("communication_board_id",$id)->get()->first();
        return $data["list"];
    }
    function get_career_setting_info(Request $request)
    {
        $id = $request->id;

        $data["user_info"] = Tbl_position_requirements::where("member_id",$id)->joinMember()->get();

        return $data["user_info"];
    }
    function update_career_info(Request $request)
    {
        $id                                       = $request->id;
        $update["commission"]                     = $request->commission;
        $update["after_purchase_commission"]      = $request->after_purchase_commission;
        $update["initial_release_percentage"]     = $request->initial_release_percentage;
        $update["token_release"]                  = $request->token_release;
        $update["needed_advisor"]                 = $request->needed_advisor;
        $update["needed_ambassador"]              = $request->needed_ambassador;
        $update["needed_member"]                  = $request->needed_member;
        $update["needed_marketing_director"]      = $request->needed_marketing_director;
        $update["needed_community_manager"]       = $request->needed_community_manager;

        $updated = Tbl_position_requirements::where("member_id",$id)->update($update);
        if($updated)
        {
            $return["status"]  = "success";
            $return["message"] = "update successful";
        }
        else
        {
            $return["status"]  = "fail";
            $return["message"] = "Error";
        }
        return json_encode($return);
    }

    function get_pending_member()
    {
        $data = Tbl_User::where("verified_mail",0);
        return $data->count();
    }
    function get_total_stored_btc()
    {
        $data = Tbl_member_address::where("coin_id",3);
        return $data->sum("address_actual_balance");
    }
    function get_total_stored_eth()
    {
        $data = Tbl_member_address::where("coin_id",2);
        return $data->sum("address_actual_balance");
    }
    function get_total_token_release()
    {
        $data = Tbl_member_log::where(function($query)
        {
            $query->where("log_status","accepted")->orWhere("log_status","automatic");
        })->where("log_method", "!=", "Bitcoin Accepted")->where("log_method", "!=", "Ethereum Accepted");

        return $data->sum("log_amount");
    }

    function get_business_application_list(Request $request)
    {
        $name = $request->name;
        $date_from = $request->date_from;
        $date_to   = $request->date_to;

        $data["list"] = Tbl_business_application::getList($name,$date_from,$date_to);
        $data["count"] = Tbl_business_application::select('*')->count();

        return $data;
    }
    function get_business_application_details(Request $request)
    {
        $id = $request->id;

        $data["list"] = Tbl_business_application::where("business_application_id",$id)->get()->first();

        return $data["list"];
    }
    function career_change_update(Request $request)
    {
        $update["member_position_id"] = $request->position_id;
        $user_id = $request->id;

        $member_position_logs_insert["member_id"] = $user_id;
        $member_position_logs_insert["member_position_id"] = $request->position_id;
        $member_position_logs_insert["created_at"] = Carbon::now();
        $member_position_logs_update["is_previous"] = 1;

        $default_req = Tbl_member_position::where("member_position_id",$request->position_id)->get()->first();
        $drupdate["initial_release_percentage"]     = $default_req->initial_release_percentage;
        $drupdate["token_release"]                  = $default_req->token_release;
        $drupdate["commission"]                     = $default_req->commission;
        $drupdate["after_purchase_commission"]      = $default_req->commission;
        $drupdate["needed_member"]                  = $default_req->needed_member;
        $drupdate["needed_ambassador"]              = $default_req->needed_ambassador;
        $drupdate["needed_advisor"]                 = $default_req->needed_advisor;
        $drupdate["needed_marketing_director"]      = $default_req->needed_marketing_director;
        $drupdate["needed_community_manager"]       = $default_req->needed_community_manager;

        $other_info = Tbl_other_info::where("user_id",$user_id)->update($update);

        if($other_info)
        {
            $update_req = Tbl_position_requirements::where("member_id",$user_id)->update($drupdate);
            Tbl_member_position_log::where("member_id",$user_id)->update($member_position_logs_update);
            Tbl_member_position_log::insert($member_position_logs_insert);
            $return['status'] = "success";
            
            $data["member"] = Tbl_User::where("id", $user_id)->first();
            $data["position"] = Tbl_member_position::where("member_position_id", $request->position_id)->first();
            Mails::promote_career($data);
        }
        else
        {
            $return['status'] = "fail";
        }


        return json_encode($return);
    }
    function get_recent_details()
    {
        $data["recent_join"] = Tbl_User::select("first_name","last_name","username","email","created_at")->where("verified_mail",1)->where("status_account",1)->select('first_name','last_name','username','email','created_at')->orderBy('created_at','DESC')->get();
        $paramBtc["log_method"] = "Bitcoin";
        $paramBtc["log_method_accepted"] = "Bitcoin Total";
        $paramEth["log_method"] = "Ethereum";
        $paramEth["log_method_accepted"] = "Ethereum Total";
        $data["recent_btc_transaction"] = Transactions::getTransactions($paramBtc,"all");
        $data["recent_eth_transaction"] = Transactions::getTransactions($paramEth,"all");

        // $data["recent_btc_transaction"] = $data["recent_btc_transaction"]->take(5);
        // $data["recent_eth_transaction"] = $data["recent_eth_transaction"]->take(5);

        return $data;
    }

    function check_receiver(Request $request)
    {
        $data = Member_log::checkReceiver($request->credential);

        return $data;
    }

    function transfer_token(Request $request)
    {
        $rules["amount"] = array("required","integer");
        $rules["remarks"] = array("required");

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $data["status"] = "fail";
            $data["message"] =  $validator->errors()->all();
        }
        else
        {
             $data["status"] = Member_log::transferToken(request()->all());
        }   

        return json_encode($data);
    }

    function manual_transfer_list()
    {
        $data = Member_log::manualTransferList(request()->all());
        return $data;
    }

    function total_tokens_transferred()
    {
        $data = Tbl_member_log::where("log_method", "manual transfer")->sum("log_amount");
        return $data;
    }
    function get_view_referral_info(Request $request)
    {
        $member_log_id = Tbl_member_log::where("id",$request->to_id)->where("log_mode","referral bonus")->member()->get();
        if(count($member_log_id) != 0)
        {
            foreach ($member_log_id as $key => $data) {
                $referral[$key] = Tbl_referral_bonus_log::where("member_log_to",$data->member_log_id)->select("member_log_from","member_log_to")->get();
            }
            foreach ($referral as $key => $data) {
                $referral_info[$key]["from"] = Tbl_member_log::where("member_log_id",$referral[$key][0]->member_log_from)->member()->get();

                $referral_info[$key]["to"] = Tbl_member_log::where("id",$request->to_id)->where("member_log_id",$referral[$key][0]->member_log_to)->where("log_mode","referral bonus")->member()->get();
            }
        }
        else
        {
            $referral_info = "";
        }

        return $referral_info;
    }

    function setup_wallet_address(Request $request)
    {
        
        $google2fa = new Google2FA();
        $secret_key = $google2fa->generateSecretKey();

        $insert["mwallet_type"]              = $request->mwallet_type;
        $insert["mwallet_owner"]             = $request->mwallet_owner;
        $insert["mwallet_password"]          = Hash::make($request->mwallet_password);
        $insert["mwallet_address"]           = $request->mwallet_address;
        $insert["mwallet_primary"]           = $request->mwallet_primary;
        $insert["mwallet_email"]             = $request->mwallet_email;
        $insert["g2fa_key"]                  = $secret_key;
        $insert["date_added"]                = Carbon::now('Asia/Manila');

        $data = Tbl_main_wallet_addresses::insert($insert);
        // Mails::setup_wallet_address($insert);
        if($data)
        {
            $return["status"] = "success";
            $return["status_message"] = "Successfully added wallet address.";
        }
        else
        {
            $return["status"] = "fail";
            $return["status_message"] = "Something went wrong. Please try again.";
        }

        return $return;
    }


    function view_all_central_wallet(Request $request)
    {
        $data = Tbl_main_wallet_addresses::where("mwallet_id", "!=", 0)->orderBy("mwallet_default", "descending")->get();
        return json_encode($data);
    }

    function get_admin_notification()
    {
        $return["new_member_request"] = Tbl_user::where("notif_status",1)->count();
        $return["new_business_application"] = Tbl_business_application::where("is_viewed",0)->count();
        $new_kyc = Tbl_knowyourcustomer::select("kyc_upload_date")->where("is_viewed",0)->distinct()->get();
        $return["new_kyc"] = $new_kyc->count();

        return json_encode($return);
    }
    
    function admin_viewed_notif(Request $request)
    {
        if($request->notif_type == "new_member_request")
        {
            $new_member_request = Tbl_user::where("notif_status",1)->get();
            foreach ($new_member_request as $key => $value) {
               $new_member_request_update = Tbl_user::where("notif_status",1)->update(["notif_status" => 0]);
            }
            $return["message"] = "New member request notification reset";
        }
        if($request->notif_type == "new_business_application")
        {
            $new_business_application = Tbl_business_application::where("is_viewed",0)->get();
            foreach ($new_business_application as $key => $value) {
                $new_business_application_update = Tbl_business_application::where("is_viewed",0)->update(["is_viewed" => 1]);
            }
            $return["message"] = "New business application request notification reset";
        }
        if($request->notif_type == "new_kyc")
        {
            $new_kyc = Tbl_knowyourcustomer::where("is_viewed",0)->get();
            foreach ($new_kyc as $key => $value) {
                $new_kyc_update = Tbl_knowyourcustomer::where("is_viewed",0)->update(["is_viewed" => 1]);
            }
            $return["message"] = "New kyc request notification reset";
        }

        return json_encode($return);
    }

    function get_faqs(Request $request)
    {
        $category = $request->category;
        $count = Tbl_faqs::select('*')->count();
        if($count != 0)
        {
            if($category == "all")
            {
                $data["list"] = Tbl_faqs::get();
            }
            else
            {
                $data["list"] = Tbl_faqs::where("faq_category",$category)->get();
            }
        }
        else
        {
            $data["list"] = "";
        }
        return $data["list"];
    }
    function add_faqs(Request $request)
    {
        $rules["add_category"]      = array("required");
        $rules["add_question"]      = array("required");
        $rules["add_answer"]        = array("required");

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $data["status"] = "fail";
            $data["message"] =  $validator->errors()->all();
        }
        else
        {
            $insert["faq_category"] = $request->add_category;
            $insert["faq_question"] = $request->add_question;
            $insert["faq_answer"]   = $request->add_answer;
            $insert["date_added"]   = Carbon::now();
    
            $add_faq = Tbl_faqs::insert($insert);
            if($add_faq)
            {
                $data["status"] = "success";
                $data["message"] = "FAQ successfully added.";
            }
            else
            {
                $data["status"] = "fail";
                $data["message"] = "Error in Adding";
            }
        }
        return $data;
    }
    function edit_faqs(Request $request)
    {
        $rules["edit_category"]      = array("required");
        $rules["edit_question"]      = array("required");
        $rules["edit_answer"]        = array("required");

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            $data["status"] = "fail";
            $data["message"] =  $validator->errors()->all();
        }
        else
        {
            $id                     = $request->edit_id;
            $update["faq_category"] = $request->edit_category;
            $update["faq_question"] = $request->edit_question;
            $update["faq_answer"]   = $request->edit_answer;
            $update["is_active"]    = $request->edit_status;

            $edit_faq = Tbl_faqs::where("faq_id",$id)->update($update);
            if($edit_faq)
            {
                $data["status"] = "success";
                $data["message"] = "FAQ update successfully";
            }
            else
            {
                $data["status"] = "fail";
                $data["message"] = "Error in Adding";
            }
        }
        return $data;
    }

    function get_referral_count_by_career(Request $request)
    {
        $id = $request->id;
        $table = Tbl_other_info::where("referral_user_id",$id);

        $data["member"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name","Member")->joinDetails()->count();
        $data["community_manager"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name","Community Manager")->joinDetails()->count();
        $data["marketing_director"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name","Marketing Director")->joinDetails()->count();
        $data["ambassador"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name","Ambassador")->joinDetails()->count();
        $data["advisor"] = Tbl_other_info::where("referral_user_id",$id)->where("member_position_name","Advisor")->joinDetails()->count();
        return $data;
    }

    function setting_default_wallet_central(Request $request)
    {
        //set wallet defgault
        Tbl_main_wallet_addresses::where("mwallet_id", $request->wallet_id)->where("mwallet_type", $request->wallet_type)->update(["mwallet_default"=>1]);

        //unset other wallets
        Tbl_main_wallet_addresses::where("mwallet_id", "!=", $request->wallet_id)->where("mwallet_type", $request->wallet_type)->update(["mwallet_default"=>0]);

        if(true)
        {
            $return["message"] = "Wallet set to default";
            $return["prompt"] = "success";
        }
        else
        {
            $return["message"] = "Something went wrong. Please try again.";
            $return["prompt"] = "danger";
        }

        return json_encode($return);
    }

    function setting_update_wallet_central(Request $request)
    {
        $update["mwallet_type"] = $request->mwallet_type;
        $update["mwallet_owner"] = $request->mwallet_owner;
        $update["mwallet_email"] = $request->mwallet_email;
        $update["mwallet_address"] = $request->mwallet_address;
        $update["mwallet_primary"] = $request->mwallet_primary;

        //set wallet defgault
        Tbl_main_wallet_addresses::where("mwallet_id", $request->mwallet_id)->update($update);


        if(true)
        {
            $return["message"] = "Wallet updated";
            $return["prompt"] = "success";
        }
        else
        {
            $return["message"] = "Something went wrong. Please try again.";
            $return["prompt"] = "danger";
        }

        return json_encode($return);
    }

    function get_estimated_tx(Request $request)
    {
        $data["to_be_released"] = 0;
        $data["estimated_fee"] = 0;
        if($request->member_address_id)
        {
            $first = Tbl_member_address::where("member_address_id", $request->member_address_id)->where("coin_id", $request->coin_id)->where("address_actual_balance", ">", 0)->first();

            if($first)
            {
                $data["to_be_released"] += $first->address_actual_balance;

                if($request->coin_id == 3)
                {
                    $data["estimated_fee"]  += Blockchain::calculateBTCFee($first->address_actual_balance, $request->usd);
                }
                else
                {
                    $data["estimated_fee"]  += Blockchain::calculateETHFee($first->address_actual_balance, $request->usd);
                }
            }
            
        }
        else
        {
            $list = Tbl_member_address::where("coin_id", $request->coin_id)->where("address_actual_balance", ">", 0)->get();
            if($list)
            {
                foreach ($list as $key => $value) 
                {
                    $data["to_be_released"] += $value->address_actual_balance;
                    if($request->coin_id == 3)
                    {
                        $data["estimated_fee"]  += Blockchain::calculateBTCFee($value->address_actual_balance, $request->usd);
                    }
                    else
                    {
                        $data["estimated_fee"]  += Blockchain::calculateETHFee($value->address_actual_balance, $request->usd);
                    }
                }
            }
            
        }
        
        return json_encode($data);
    }

    function main_wallet_addresses(Request $request)
    {
        $minimum_balance = 0.0001;
        $balance = Tbl_member_address::join('users', 'users.id', '=', 'tbl_member_address.member_id')->where("address_actual_balance", ">=", $minimum_balance);
        if($request->coin_id != 0)
        {
            $balance = $balance->where("coin_id", $request->coin_id);
        }
        else
        {
            $balance = $balance->where(function($query)
            {
                $query->where("coin_id", 2)->orWhere("coin_id", 3);
            });
        }
        $balance = $balance->get();

        foreach ($balance as $key => $value) {
            $balance[$key]["coin"] = Tbl_coin::where("coin_id", $value->coin_id)->first();
        }
        
        return json_encode($balance);
    }

    function release_wallet(Request $request)
    {
        $coin = $request->wallet == "BTC" ? 3 : 2;
        $list = Tbl_member_address::where("member_address_id", $request->member_address_id)->first();

        if($request->wallet == "BTC")
        {
            $release_amt = $list->address_actual_balance * 100000000;
            $data = Blockchain::sendActualBTCWalletToCentralWallet($list->member_address_id, $release_amt, $request->wallet_receiver, $request->usd);
        }
        else
        {
            $release_amt = $list->address_actual_balance * 1000000000000000000;
            $data = Blockchain::sendActualETHWalletToCentralWallet($list->member_address_id, $release_amt, $request->wallet_receiver, $request->usd);
        }

        return $data;
    }

    function batch_release_wallet(Request $request)
    {
        $coin = $request->wallet == "BTC" ? 3 : 2;

        $list = Tbl_member_address::where("coin_id", $coin)->where("address_actual_balance", ">", 0)->get();

        

        if($request->wallet == "BTC")
        {
            foreach ($list as $key => $value) 
            {
                $balance = $value->address_actual_balance * 100000000;
                $data = Blockchain::sendActualBTCWalletToCentralWallet($value->member_address_id, $balance, $request->wallet_receiver, $request->usd);
            }
        }
        else
        {
            foreach ($list as $key => $value) 
            {
                $release_amt = $value->address_actual_balance * 1000000000000000000;
                $balance = Blockchain::get_blockchain_ethereum_balance($value->member_address);
                if($balance->balance > 0)
                {
                    $data = Blockchain::sendActualETHWalletToCentralWallet($value->member_address_id, $release_amt, $request->wallet_receiver, $request->usd);
                }
                else
                {
                    $update["address_actual_balance"] = 0;
                    Tbl_member_address::where("member_address", $value->member_address)->update($update);
                    $data["status_message"] = "No balance to be released";
                    $data["status"] = "success";
                }
            }
            
        }
        return $data;
    }

    function get_total_crypto()
    {
        $data["btc_count"] = Tbl_member_address::where("coin_id", 3)->sum("address_actual_balance");
        $data["eth_count"] = Tbl_member_address::where("coin_id", 2)->sum("address_actual_balance");

        return json_encode($data);
    }

    function get_file_list(Request $request)
    {
        $data = Tbl_files::where("file_id", "!=", 0);

        if($request->category != "all")
        {
            $data = $data->where("file_category", $request->category);
        }

        $data = $data->get();
        return json_encode($data);
    }

    function add_new_file(Request $request)
    {
        $insert["file_name"] = $request->file_name;
        $insert["file_type"] = $request->file_type;
        $insert["file_category"] = $request->file_category;
        $insert["file"] = $request->file;
        $insert["date_added"] = Carbon::now();

        $data = Tbl_files::insert($insert);

        $return = $data ? "success" : "failed";
        
        return json_encode($return);
    }

    function update_user_information(Request $request)
    {
        $update["first_name"] = $request->first_name;
        $update["last_name"] = $request->last_name;
        $update["email"] = $request->email;
        $update["birth_date"] = $request->birth_date;

        $data = Tbl_User::where("id", $request->id)->update($update);
        $_data["status"] = "success";
        $_data["status_message"] = "Successfully updated user information!";

        return json_encode($_data);
    }

    function get_release_logs(Request $request)
    {
        $data = Tbl_release_logs::joinMember();

        if(isset($request->release_type) && $request->release_type != "all")
        {
            $data = $data->where("release_type", $request->release_type);
        }

        if(isset($request->date_from) && $request->date_from)
        {
            $data = $data->whereDate("date_released", ">=", $request->date_from);
        }

        if(isset($request->date_to) && $request->date_to)
        {
            $data = $data->whereDate("date_released", "<=", $request->date_to);
        }

        $data = $data->get();

        return $data;
    }

    function get_kyc_proof(Request $request)
    {
        $data = Tbl_kyc_proof_v2::where("user_id", $request->user_id)->get();
        return $data;
    }

    
}