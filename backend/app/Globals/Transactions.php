<?php
namespace App\Globals;
use Carbon\Carbon;
use App\Globals\Helper;
use stdClass;
use DateTime;
use App\Tbl_member_address;
use App\Tbl_member_log;
use App\Tbl_sale_stage_bonus;
use App\Tbl_automatic_cash_in;
use Illuminate\Support\Facades\Crypt;

class Transactions
{
    public static function getTransactions($param = null, $filter = null, $id= null)
    {
        
        $methods["log_method"]          = ucfirst($param['log_method']);
        if(isset($param['log_method_accepted']))
        {
            $methods["log_method_accepted"] = ucwords($param['log_method_accepted']);
        }
        $data = Tbl_automatic_cash_in::joinTransactions($methods);

        if($id != null)
        {
            $data = $data->where("member_id", $id);
        }

        if($filter == null)
        {
            if($param["account_name"] != "" || $param["account_name"] != null)
            {
                $name = $param["account_name"];
                $data = $data->where(function($query) use ($name){
                    $query
                    ->where("first_name", "like", "%".$name."%")
                    ->orWhere("last_name", "like", "%".$name."%");
                    if(strpos($name, ' ') !== false )
                    {
                        $surname_name = substr($name, strpos($name, " ") + 1);
                        $query = $query->orWhere("last_name","LIKE","%".$surname_name."%"); 
                    }
                });
            }

            if($param["transaction_status"] != "all")
            {
                $transaction_status = $param["transaction_status"];
                $data = $data->where("log_status", $transaction_status);
            }

            if($param["transaction_date_from"] != false)
            {
                $transaction_date_from = $param["transaction_date_from"];
                $data = $data->whereDate("log_time", ">=", $transaction_date_from);
            }

            if($param["transaction_date_to"] != false)
            {
                $transaction_date_to = $param["transaction_date_to"];
                $data = $data->whereDate("log_time", "<=", $transaction_date_to);
            }

        }

        $data = $data->orderBy("log_time", "desc");
        $data = $data->get();
        foreach ($data as $key => $value) {
            $bonus = @(($value->sale_stage_bonus/100)+1);
            $paid_amount = (($value->sale_stage_discount/100)*$value->exchange_rate);
            $exchange_rate = (($value->sale_stage_discount/100)*$value->exchange_rate)*$value->amount_requested;
            
            // $data[$key]["amount_paid"] = $paid_amount*$value->log_amount;
            // $data[$key]["expected_payment"] = $value->amount_requested-$exchange_rate;

            $data[$key]["amount_paid"] = $paid_amount*$value->log_amount;
            $data[$key]["expected_payment"] = $value->amount_requested-$exchange_rate;
        }

       
        return $data;
    }
}