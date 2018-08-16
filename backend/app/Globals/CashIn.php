<?php
namespace App\Globals;
use Carbon\Carbon;
use App\Tbl_cash_in_method;
use App\Tbl_cash_in_proof;
use App\Tbl_cash_in_eth;
use App\Tbl_bitcoin_cash_in;
use Validator;

class CashIn
{
	public static function getList($limit = null, $search = null)
	{
		$query = Tbl_cash_in_method::select("*");

		if ($search) 
		{
			$query = $query->where('cash_in_method_name','LIKE',"%{$search}%");
		}

		if ($limit) 
		{
			$query = $query->paginate($limit);
		}
		else
		{
			$query = $query->get();
		}

		return $query;
	}

	public static function get($id)
	{
		$query = Tbl_cash_in_method::where("cash_in_method_id", $id);
		$query = $query->first();

		return $query;
	}

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

	public static function add($insert)
	{
		$rules     = CashIn::rules();
		$validator = Validator::make($insert, $rules);

        if ($validator->fails()) 
        {
        	$result["message"] = $validator->errors()->all();
        	$result["status"] = "error";
        }
        else
        {
        	Tbl_cash_in_method::insert($insert);

        	$result["message"] = "Successfully Added";
        	$result["status"] = "success";
        }

        return $result;
	}

	public static function edit($edit)
	{
		$rules     = CashIn::rules();
		$validator = Validator::make($edit, $rules);

        if ($validator->fails()) 
        {
        	$result["message"] = $validator->errors()->all();
        	$result["status"] = "error";
        }
        else
        {
        	Tbl_cash_in_method::where("cash_in_method_id", $edit["cash_in_method_id"])->update($edit);

        	$result["message"] = "Successfully Updated";
        	$result["status"] = "success";
        }

        return $result;
	}

	public static function insertCashInProof($request, $member_log_id, $proof_image, $member_id)
	{
		$method = CashIn::get($request["payment_method"]);
		$rate   = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName($request["payment_currency"]));
		
		$insert["cash_in_proof_date"]       = Carbon::now();
		$insert["cash_in_method_id"]        = $request["payment_method"];
		$insert["cash_in_proof_image"]      = $proof_image;
		$insert["cash_in_reference_number"] = $request["cash_in_reference_number"];
		$insert["cash_in_amount"]           = $request["payment_coin"] * $rate;
		$insert["cash_in_fee"]              = $method->cash_in_method_fee;
		$insert["cash_in_by"]               = $member_id;
		$insert["member_log_id"]            = $member_log_id;

		Tbl_cash_in_proof::insert($insert);

		return true;
	}

	public static function insertCashInEth($request, $member_log_id, $member_id)
	{
		$rate = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName($request["payment_currency"]));
		
		$insert["cash_in_eth_date"]          = Carbon::now();
		$insert["cash_in_reference_address"] = $request["cash_in_reference_address"];
		$insert["cash_in_amount"]            = $request["payment_coin"] * $rate;
		$insert["cash_in_fee"]               = 0;
		$insert["cash_in_by"]                = $member_id;
		$insert["member_log_id"]             = $member_log_id;

		Tbl_cash_in_eth::insert($insert);

		return true;
	}

	public static function getRequestsViaBTC($id = null, $transaction_id = null, $status = null, $date_from = null, $date_to = null, $name = null)
	{
		 $data = Tbl_bitcoin_cash_in::memberlog()->memberaddress()->member();

		 if($id != null)
		 {
		 	$data = $data->where("users.id", $id);
		 }

		 if($transaction_id != null)
		 {
		 	$data = $data->where("tbl_bitcoin_cash_in.bitcoin_cash_in_id", $transaction_id);
		 }

		 if($status != null)
		 {
		 	$data = $data->where("tbl_member_log.log_status", $status);
		 }

		 if($date_from != null || $date_from != "")
		 {
		 	$data = $data->whereDate("tbl_member_log.log_time", ">=", $date_from);
		 }

		 if($date_to != null || $date_to != "")
		 {
		 	$data = $data->whereDate("tbl_member_log.log_time", "<=", $date_to);
		 }

		 if($name != null || $name != "")
		 {
		 	$data = $data
		 	->where( function($query) use ($name){
                $query
                ->where('users.first_name', 'like', '%' . $name . '%')
                ->orWhere('users.last_name', 'like', '%'. $name . '%');
            });
		 }

		 return $data;
	}

}