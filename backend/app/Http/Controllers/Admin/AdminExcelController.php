<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Tbl_User;
use App\Tbl_coin;
use App\Tbl_coin_conversion;
use Carbon\Carbon;
use App\Globals\Audit;
use App\Globals\Authenticator;
use App\Globals\Wallet;
use App\Globals\User;
use App\Globals\Transactions;
use App\Tbl_cash_in_method;
use App\Tbl_cash_in_proof;
use App\Tbl_member_address;
use App\Tbl_transaction_convert;
use App\Tbl_transaction_transfer;
use App\Tbl_cash_out_requests;
use App\Tbl_btc_transaction;
use App\Globals\Member_log;
use App\Tbl_other_info;
use stdClass;
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;

use Excel;


class AdminExcelController extends Controller
{
    public function member_wallet(Request $request)
    {
        $data["list"] = User::getWalletList($request->member_id);
        $data["member"] = Tbl_member::where("member_id", $request->member_id)->first();

        Excel::create("Member Wallet", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1", function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.member_wallet', $data);
            });
        })->export('xlsx');
    }

    public function export_member_list(Request $request)
    {
        $param["search_name"]   = $request->search_name;
        $param["search_status"] = $request->search_status;
        $param["search_email_status"] = $request->search_email_status;
        $param["search_roles"] = $request->search_roles;
        $param["search_career"] = $request->search_career;
        $param["search_date_from"] = $request->search_date_from;
        $param["search_date_to"] = $request->search_date_to;
        $param["register_platform"] = $request->register_platform;
        //dd($param);
        if($request->data == "filtered")
        {
            $data["list"] = User::getList($param);
        }
        else
        {
            $data["list"] = User::getList();
        }

        Excel::create("Member List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1", function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_member_list', $data);
            });
        })->export('xlsx');
    }

    public function export_transaction_list(Request $request)
    {        
        if($request->data == "filtered")
        {
            $param["account_name"]          = $request->account_name;
            $param["transaction_status"]    = $request->transaction_status;
            $param["log_method_accepted"]   = $request->log_method_accepted;
            $param["transaction_date_from"] = $request->transaction_date_from;
            $param["transaction_date_to"]   = $request->transaction_date_to;
            $param["log_method"]            = $request->log_method;
            //dd($param);
            $data["list"] = Transactions::getTransactions($param, null);
        }
        else
        {
            $param["log_method"]            = $request->log_method;
            $param["log_method_accepted"]   = $request->log_method_accepted;
            $data["list"] = Transactions::getTransactions($param, "all");
        }

        Excel::create(ucfirst($request->log_method)." Transaction List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1", function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_transaction_list', $data);
            });
        })->export('xlsx');
    }

    public function export_member_transaction_list(Request $request)
    {        
        if($request->data == "filtered")
        {
            $param["account_name"]          = $request->account_name;
            $param["log_method_accepted"]   = $request->log_method_accepted;
            $param["transaction_status"]    = $request->transaction_status;
            $param["transaction_date_from"] = $request->transaction_date_from;
            $param["transaction_date_to"]   = $request->transaction_date_to;
            $param["log_method"]            = $request->log_method;
            $data["list"] = Transactions::getTransactions($param, null, $request->member_id);
        }
        else
        {
            $param["log_method"]            = $request->log_method;
            $param["log_method_accepted"]   = $request->log_method_accepted;
            $data["list"] = Transactions::getTransactions($param, "all", $request->member_id);
        }

        Excel::create(ucwords($request->first_name." ".$request->last_name." - ".$request->log_method)." Transaction List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1", function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_transaction_list', $data);
            });
        })->export('xlsx');
    }

     public function export_transfer_token(Request $request)
    {        
        if($request->data == "filtered")
        {
            $data["list"] = Member_log::manualTransferList($request->all());
        }
        else
        {
            $data["list"] = Member_log::manualTransferList();
        }

        Excel::create("Transfer Token Transaction List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1", function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_transfer_token_list', $data);
            });
        })->export('xlsx');
    }

    public function export_referral_bonus_list(Request $request)
    {
        if($request->data == "filtered")
        {
             $from = $request->invitee;
             $to   = $request->referrer;
             $date_from = $request->transaction_date_from;
             $date_to   = $request->transaction_date_to;
             $data["list"] = Member_log::getReferralBonusList($from, $to, $date_from, $date_to);
        }
        else
        {
            $data["list"] = Member_log::getReferralBonusList();
        }
        Excel::create("Referral Bonus List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1",function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_referral_bonus_log',$data);
            });
        })->export('xlsx');
    }
    public function export_sale_stage_bonus_list(Request $request)
    {
        if($request->data == "filtered")
        {
             $name = $request->account_name;
             $date_from = $request->transaction_date_from;
             $date_to   = $request->transaction_date_to;
             $data["list"] = Member_log::getSaleStageBonusList($name, $date_from, $date_to);
        }
        else
        {
            $data["list"] = Member_log::getSaleStageBonusList();
        }
        Excel::create("Sale Stage Bonus List", function($excel) use ($data)
        {
            $excel->sheet("Sheet 1",function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_sale_stage_list',$data);
            });
        })->export('xlsx');
    }
    public function export_affiliate_history_list(Request $request)
    {
        $member = Tbl_User::select("first_name","last_name")->where("id",$request->id)->first();
        if($request->data == "filtered")
        {
            if($request->career != "all")
            {
                $data["list"] = Tbl_other_info::where("referral_user_id",$request->id)->where("member_position_name",$request->career)->joinDetails()->get();
            }
            else
            {
                $data["list"] = Tbl_other_info::where("referral_user_id",$request->id)->joinDetails()->get();
            }
        }
        else
        {
            $data["list"] = Tbl_other_info::where("referral_user_id",$request->id)->joinDetails()->get();
        }
        Excel::create("Affiliate History - ". $member->first_name . " " . $member->last_name, function($excel) use ($data)
        {
            $excel->sheet("Sheet 1",function($sheet) use ($data)
            {
                $sheet->setOrientation('landscape');
                $sheet->loadView('excel.admin_affiliate_history_list',$data);
            });
        })->export('xlsx');
    }
}
