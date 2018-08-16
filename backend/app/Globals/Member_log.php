<?php
namespace App\Globals;
use stdClass;
use Carbon\Carbon;
use App\Tbl_member_log;
use App\Tbl_referral_bonus_log;
use App\Tbl_member_address;
use App\Tbl_User;
use App\Tbl_member_position;
use App\Globals\Coin;
use App\Globals\CashIn;
use App\Globals\Mails;
class Member_log
{
    public static function insert($request, $member_id, $coin)
    {
        // $coin_id = Coin::getIdByName($request["payment_currency"]);
        $coin_id = Coin::getIdByName($coin);
        $address = Wallet::getAddress2($member_id, $coin_id);
        // $method  = CashIn::get($request["payment_method"]);

        if ($address) 
        {
            $insert["member_address_id"] = $address->member_address_id;
        }
        else
        {
            Wallet::setupWallet($member_id);
            $address = Wallet::getAddress2($member_id, $coin_id);
            $insert["member_address_id"] = $address->member_address_id;
        }

        $insert["log_type"]            = "transfer";
        $insert["log_mode"]            = "receive";
        $insert["log_amount"]          = $request["payment_coin"];
        // $insert["log_transaction_fee"] = $method->cash_in_method_fee;
        $insert["log_transaction_fee"] = 0;
        $insert["log_net_amount"]      = $request["payment_coin"];
        $insert["log_time"]            = Carbon::now();
        if (isset($request["log_status"])) 
        {
            $insert["log_status"]      = $request["log_status"];
        }
        else
        {
            $insert["log_status"]          = "pending";
        }
        $insert["log_message"]         = "Received <b>".$request['received_token']." SPAY Tokens</b> via <b>".ucwords($coin).".</b>";
        $insert["is_viewed"]           = 0;
        $insert["log_method"]          = ucwords($coin). " Accepted";
        $insert["ip_address"]          = $_SERVER['REMOTE_ADDR'];

        

        $id = Tbl_member_log::insertGetId($insert);

        return $id;
    }

    public static function getSaleStageBonusList($name = null, $from = null, $to = null)
    {
        $data = Tbl_member_log::where("log_mode", "buy bonus");

        if($from != null)
        {
            $data = $data->whereDate("log_time", ">=", $from);
        }

        if($to != null)
        {
            $data = $data->whereDate("log_time", "<=", $to);
        }

        $data = $data->leftJoin("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
        $data = $data->leftJoin("users", "users.id", "=", "tbl_member_address.member_id");
        if($name != null)
        {
            $data = $data->where(function ($query) use ($name){
                $query
                ->where("users.first_name", "like", "%".$name."%")
                ->orWhere("users.last_name", "like", "%".$name."%");
            });
        }
        $data = $data->get();
        
        return $data;
    }

    public static function getReferralBonusList($from = null, $to = null, $date_from = null, $date_to = null)
    {
        $data = Tbl_referral_bonus_log::where("referral_bonus_log_id", "!=", 0);
        $data = $data->join("tbl_member_log", "tbl_member_log.member_log_id", "=", "tbl_referral_bonus_log.member_log_to")->where("tbl_member_log.log_mode", "referral bonus");
        if($date_from != null)
        {
            $data = $data->whereDate("referral_bonus_log_date", ">=", $date_from);
        }
        if($date_to != null)
        {
            $data = $data->whereDate("referral_bonus_log_date", "<=", $date_to);
        }
        $data = $data->get();
        foreach($data as $key => $value)
        {
            $bonus_from = Tbl_member_log::where("member_log_id", $value->member_log_from);
            $bonus_from = $bonus_from->leftJoin("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
            $bonus_from = $bonus_from->leftJoin("users", "users.id", "=", "tbl_member_address.member_id");
            if($from != null)
            {
                $bonus_from = $bonus_from->where(function ($query) use ($from){  

                     $query->where("first_name", "like", "%".$from."%")
                     ->orWhere("last_name","like","%".$from."%");

                    if(strpos($from, ' ') !== false )
                    {
                        $surname_from = substr($from, strpos($from, " ") + 1);
                        $query = $query->orWhere("last_name","LIKE","%".$surname_from."%"); 
                    }
                });
            }
            $bonus_from = $bonus_from->first();
            $data[$key]["bonus_from"] = $bonus_from;

            $bonus_to = Tbl_member_log::where("member_log_id", $value->member_log_to);
            $bonus_to = $bonus_to->leftJoin("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
            $bonus_to = $bonus_to->leftJoin("users", "users.id", "=", "tbl_member_address.member_id");
            if($to != null)
            {
                $bonus_to = $bonus_to->where(function ($query) use ($to){
                     $query->where("first_name", "like", "%".$to."%")
                     ->orWhere("last_name","like","%".$to."%");
                    if(strpos($to, ' ') !== false)
                    {
                        $surname_to = substr($to, strpos($to, " ") + 1); 
                        $query = $query->orWhere("last_name", "like", "%".$surname_to."%"); 
                    }
                });
            }
            $bonus_to = $bonus_to->first();
            $data[$key]["bonus_to"] = $bonus_to;

            if(!$data[$key]["bonus_from"])
            {
                unset($data[$key]);
            }
            else if(!$data[$key]["bonus_to"])
            {
                unset($data[$key]);
            }
        }
        if($data->isEmpty())
        {
            $data = "";
        }
        
        return $data;
    }

    public static function checkReceiver($c)
    {
        if($c != null)
        {
            $data["list"] = Tbl_User::where("id", "!=", 0);
            $data["list"] = $data["list"]->join("tbl_other_info", "tbl_other_info.user_id", "=", "users.id");
            $data["list"] = $data["list"]->join("tbl_member_address", "tbl_member_address.member_id", "=", "users.id")->where("coin_id", 4);
            if($c != null || $c != "")
            {
                $data["list"] = $data["list"]->where( function($query) use ($c)
                {
                    $query
                    ->where('users.email',$c)
                    ->orWhere('users.phone_number',$c)
                    ->orWhere('tbl_member_address.member_address',$c);
                });
            }
            
            $data["list"] = $data["list"]->get();
            foreach ($data["list"] as $key => $value) 
            {
                $data["list"][$key]["member_position"] = Tbl_member_position::where("member_position_id", $value->member_position_id)->first();
            }

            if(count($data["list"]) != 0)
            {
                $data["message"] = "success";
            }
            else
            {
                $data["message"] = "error";
            }
        }
        else
        {
            $data["message"] = "null result";
        }

        return $data;
    }

    public static function memberInfo($member_address)
    {
        $data["member"] = Tbl_User::where("id", "!=", 0);
        $data["member"] = $data["member"]->join("tbl_other_info", "tbl_other_info.user_id", "=", "users.id");
        $data["member"] = $data["member"]->join("tbl_member_address", "tbl_member_address.member_id", "=", "users.id")->where("coin_id", 4);
        $member["list"] = $data["member"]->where('tbl_member_address.member_address','like','%'.$member_address.'%')->first();
        
        return $member["list"];
    }

    public static function transferTokenInfo($member_address)
    {
        $data["member"] = Tbl_User::select("first_name","last_name","email")->where("id", "!=", 0);
        $data["member"] = $data["member"]->join("tbl_other_info", "tbl_other_info.user_id", "=", "users.id");
        $data["member"] = $data["member"]->join("tbl_member_address", "tbl_member_address.member_id", "=", "users.id")->where("coin_id", 4);
        $member["list"] = $data["member"]->where('tbl_member_address.member_address_id',$member_address)->first();
        
        return $member["list"];
    }

    public static function transferToken($params = null)
    {
        if(isset($params))
        {
            $insert["member_address_id"]   = $params["address_id"];
            $insert["log_type"]            = "transfer";
            $insert["log_mode"]            = "manual";
            $insert["log_amount"]          = $params["amount"];
            $insert["log_transaction_fee"] = 0;
            $insert["log_net_amount"]      = $params["amount"];
            $insert["log_time"]            = Carbon::now();
            $insert["log_status"]          = "transferred";
            $insert["log_message"]         = $params["remarks"];
            $insert["is_viewed"]           = 0;
            $insert["log_method"]          = "manual transfer";
            $insert["ip_address"]          = $_SERVER['REMOTE_ADDR'];

            $data                          = Tbl_member_log::insert($insert);
            $member["info"]                = Self::transferTokenInfo($insert["member_address_id"]);
            $member["log_amount"]          = $insert["log_amount"];
            $member["remarks"]             = $insert["log_message"];

            Mails::send_transfer_token($member);
            if($data)
            {
                $return = "success";
            }
            else
            {
                $return = "error";
            }
        }
        

        return $return;
    }

    public static function manualTransferList($filters = null, $id = null)
    {
        $data = Tbl_member_log::where("log_method", "manual transfer");
        $data = $data->join("tbl_member_address", "tbl_member_address.member_address_id", "=", "tbl_member_log.member_address_id");
        $data = $data->join("users", "users.id", "=", "tbl_member_address.member_id");
        $data = $data->join("tbl_other_info", "tbl_other_info.user_id", "=", "users.id");
        $data = $data->join("tbl_member_position", "tbl_other_info.member_position_id", "=", "tbl_member_position.member_position_id");

        if($id != null)
        {
            $data = $data->where("id", $id);
        }
        if(isset($filters["member_type"]) && $filters["member_type"] != "all")
        {
            $data = $data->where("tbl_other_info.member_position_id", $filters["member_type"]);
        }

        if(isset($filters["date_from"]))
        {
            $data = $data->whereDate("tbl_member_log.log_time", ">=", $filters["date_from"]);
        }

        if(isset($filters["date_to"]))
        {
            $data = $data->whereDate("tbl_member_log.log_time", "<=", $filters["date_to"]);
        }

        $data = $data->select("tbl_member_log.*", "users.first_name", "users.last_name", "tbl_member_address.member_address", "tbl_other_info.member_position_id", "tbl_member_position.member_position_name");
        $data = $data->orderBy('log_time','ACS')->get();
        
        return $data;
    }
    
}