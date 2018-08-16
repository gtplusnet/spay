<?php
namespace App\Globals;
use stdClass;
use Carbon\Carbon;
use App\Tbl_member_address;
use App\Globals\Wallet;

class Member_address
{
    public static function insert($request)
    {
        if ($request["member_address_id"]) 
        {
            $insert["member_address_id"] = $request["member_address_id"];
        }
        else
        {
            $insert["member_address_id"] = "";
        }

        $insert["log_type"] = "transfer";
        $insert["log_mode"] = "receive";
        $insert["log_amount"] = $request["payment_coin"];
        dd($insert);
        Tbl_member_log::insert();
    }
}