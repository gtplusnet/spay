<?php
namespace App\Globals;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use App\Tbl_member_address;
use App\Tbl_member;
use App\Tbl_member_log;
use App\Tbl_transaction;
use App\Tbl_trading;
use App\Tbl_coin;
use App\Tbl_coin_conversion;
use App\Tbl_country;
use App\Tbl_lend;
use App\Tbl_lend_config;
use App\Tbl_bitcoin_cash_in;
use App\Tbl_cash_in_proof;
use App\Tbl_cash_out_requests;
use App\Tbl_transaction_convert;
use App\Tbl_transaction_transfer;
use App\Tbl_login;
use App\Tbl_User;
use App\Tbl_sale_stage;
use App\Tbl_sale_stage_bonus;
use App\Tbl_automatic_cash_in;
use App\Globals\Helper;
use stdClass;
use DateTime;


class Wallet
{
	public static $blockchain_api_key = "6a77230c-82d8-403e-9b7c-b2d3b78cefe1";
	public static $blockchain_url = "127.0.0.1:3000";

	public static function conversionSync()
	{
		$_coin = Tbl_coin::get();	

		foreach($_coin as $coin_from)
		{
			foreach($_coin as $coin_to)
			{
                $multiplier = Self::conversion_compute($coin_from, $coin_to);

                if($multiplier)
                {
					Self::conversion_data($coin_from->coin_id, $coin_to->coin_id, $multiplier);
				}
			}
		}
	}

    public static function conversion_data($from, $to, $multiplier)
    {
        if(Tbl_coin_conversion::where("coin_from", $from)->where("coin_to", $to)->first()) //update if exist
        {
            $update["conversion_multiplier"]    = $multiplier;
            Tbl_coin_conversion::where("coin_from", $from)->where("coin_to", $to)->update($update);
        }
        else
        {
            $insert["coin_from"]                = $from;
            $insert["coin_to"]                  = $to;
            $insert["conversion_multiplier"]    = $multiplier;

            Tbl_coin_conversion::insert($insert);
        }
    }
    public static function conversion_compute($from, $to)
    {
    	$conversion 	= null;

    	if($from->coin_id == $to->coin_id) // same coin
    	{
    		$conversion = 0;
    	}
    	elseif($from->coin_id == 1) //if convert from PHP
    	{
    		if($to->coin_id == 2) //php to bitcoin
    		{
		        $json 			= file_get_contents('https://api.coindesk.com/v1/bpi/currentprice/PHP.json');
		        $conversion     = 1 / json_decode($json)->bpi->PHP->rate_float;
		        $conversion  	= $conversion - ($conversion * (0.015));
    		}
    	}
    	else
    	{
    		$reverse_method 	= true;

    		if($to->coin_id == 1 || $from->coin_id == 1)
    		{
    			$reverse_table 		= Tbl_coin_conversion::where("coin_from", $to->coin_id)->where("coin_to", $from->coin_id)->first();
    			$conversion 		= 1 / $reverse_table->conversion_multiplier;
    		}
    		else
    		{
    			$php_conversion 	= Tbl_coin_conversion::where("coin_from", $from->coin_id)->where("coin_to", 1)->value("conversion_multiplier");
    			$target_conversion 	= Tbl_coin_conversion::where("coin_from", 1)->where("coin_to", $to->coin_id)->value("conversion_multiplier");
    			$conversion 		= $php_conversion * $target_conversion;
    		}
    	}

   		$sell_margin = Tbl_coin::where("coin_id", $from->coin_id)->value("sell_margin");

   		if($sell_margin != 0)
   		{
   			$conversion = $conversion - ($conversion * ($sell_margin / 100));
   		}

    	return $conversion;

    }

	public static function formatWallet($amount, $suffix, $decimal = 2)
	{
		return $suffix . " " . number_format($amount, $decimal);
	}

	public static function recordTransaction($member_id, $coin_id, $sale_stage_id, $conversion_rate, $amount, $token_amount, $log_method = null, $log, $status = "pending")
	{
		$member_address = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		$insert["member_address_id"] 		= $member_address->member_address_id;
		$insert["log_type"] 				= "transfer";
		$insert["log_mode"] 				= "receive";
		$insert["log_amount"] 				= $token_amount;
		$insert["log_transaction_fee"] 		= 0;
		$insert["log_net_amount"] 			= $amount;
		$insert["log_time"] 				= Carbon::now();
		$insert["log_message"] 				= $log;
		$insert["log_status"] 				= $status;
		$insert["log_method"] 				= $log_method;

		$pending_transaction = Tbl_member_log::where("member_address_id", $member_address->member_address_id)->where("log_status", "pending")->where("log_method", $log_method);

		if($pending_transaction->count() == 1)
		{
			$pending_transaction = $pending_transaction->first();

			Tbl_member_log::where("member_log_id", $pending_transaction->member_log_id)->update(["log_status" => "canceled"]);
		}


		$member_log_id 						= Tbl_member_log::insertGetId($insert);

		$_sale_stage_id = Tbl_sale_stage::where("sale_stage_id", $sale_stage_id)->first();
		$_sale_stage_bonus_id = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)
		->where(function($query) use ($token_amount){
				$query
				->where("buy_coin_bonus_from", "<=", $token_amount)
				->where("buy_coin_bonus_to", ">=", $token_amount);
		})->first();

		// dd($_sale_stage_id, $_sale_stage_bonus_id, $token_amount+$token_amount);
		
		
		$insert = null;
        $insert['member_log_id']        = $member_log_id;
        $insert['exchange_rate']  		= $conversion_rate;
        $insert['amount_requested']     = $token_amount;
        $insert['sale_stage_discount']  = $_sale_stage_id->sale_stage_discount;
        $insert['sale_stage_bonus']     = $_sale_stage_bonus_id == null ? 0 : $_sale_stage_bonus_id->buy_coin_bonus_percentage;
        $insert['expiration_date']      = Carbon::now()->addDays(2);
        $insert['date_requested']      	= Carbon::now();
        
        // dd($_sale_stage_id, $_sale_stage_bonus_id, $insert);

        $automatic_cashin = Tbl_automatic_cash_in::insertGetId($insert);

        $data["member"] = Tbl_User::where("id", $member_id)->first();
        $data["amount"] = $token_amount;
        $data["method"] = $log_method;
        $data["record"] = Tbl_automatic_cash_in::where("automatic_cash_in_id", $automatic_cashin)->first();

        if($log_method == 'Bitcoin' || $log_method == 'Ethereum')
        {
        	Mails::order_placed($data);
        }

		Self::recomputeWallet($member_address->member_address_id);

		
		return $member_log_id;
	}

	public static function recomputeWallet($member_address_id)
	{
		$total_receive 			= Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "receive")->sum("log_amount");
		$total_send 			= Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "send")->sum("log_amount");
		$total_buy_bonus 		= Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_status", "!=", "canceled")->where("log_mode", "buy bonus")->sum("log_amount");
		$total_referral_bonus 	= Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "floating")->where("log_status", "!=", "canceled")->where("log_mode", "referral bonus")->sum("log_amount");
		$total_manual_transfer 	= Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "floating")->where("log_status", "!=", "canceled")->where("log_mode", "manual")->sum("log_amount");
		$total_role_bonus 		= 0; //Tbl_member_log::where("member_address_id", $member_address_id)->where("log_status", "!=", "pending")->where("log_status", "!=", "rejected")->where("log_mode", "role bonus")->sum("log_amount");
		
		$wallet 			= ($total_receive - $total_send) + ($total_buy_bonus + $total_referral_bonus + $total_role_bonus) + ($total_manual_transfer);

		$update["address_balance"] = $wallet;

		Tbl_member_address::where("member_address_id", $member_address_id)->update($update);
	}	

	public static function getTransaction($member_id, $status = null)
	{
		$_transaction 	= Tbl_member_log::where("users.id", $member_id)->orderBy("member_log_id", "desc")->member();
		
		if($status)
		{
			$_transaction->where("log_status", $status);
		}

		return $_transaction->get();
	}
	public static function getBtcTransaction($member_id,$status = null, $date_from = null, $date_to = null)
	{
		$_transaction = Tbl_member_log::where("log_method","Bitcoin")->where("users.id",$member_id)->member();
		
		if($status)
		{
			if($date_from)
			{
				$_transaction->where("log_status", $status)->where("log_time", $date_from);
			}
			else if($date_from && $date_to)
			{
				$_transaction->where("log_status",$status)->whereBetween("log_time", array($date_from, $date_to));
			}
		}
		else if($date_from)
		{
			$_transaction->where("log_status", $status)->where("log_time", $date_from);
			
			if($date_from && $date_to)
			{
				$_transaction->where("log_status",$status)->whereBetween("log_time", array($date_from, $date_to));
			}
		}

		return $_transaction->get();
	}
	public static function getWalletList($member_id)
	{
		$__wallet 	= null;
		$_wallet 	= Tbl_member_address::where("member_id", $member_id)->joinCoin();
		
		$_wallet = $_wallet->get();

		foreach($_wallet as $key => $wallet)
		{
			$__wallet[$key]	 					= new stdClass();
			$__wallet[$key]->coin_name 			= $wallet->coin_name;
			$__wallet[$key]->coin_abb 			= $wallet->coin_abb;
			$__wallet[$key]->coin_id 			= $wallet->coin_id;
			$__wallet[$key]->member_address 	= $wallet->member_address;
			$__wallet[$key]->member_address_id 	= $wallet->member_address_id;
			$__wallet[$key]->coin_decimal 		= $wallet->coin_decimal;
			$__wallet[$key]->address_balance 	= $wallet->address_balance;
		}

		return $__wallet;
	}

	public static function setupWallet($member_id)
	{
		$_coin = Tbl_coin::get();
		
		foreach($_coin as $coin)
		{
			$check_address = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin->coin_id)->first();

			if(!$check_address)
			{
				$methodGenereateAddress = "generateWallet" . ucfirst($coin->coin_name);
				Self::$methodGenereateAddress($member_id, $coin);
			}
		}
	}
	public static function generateWalletSuccessmall($member_id, $coin)
	{
		$passkey 									= Self::randomPassword();
		$insert_address["member_id"] 				= $member_id;
		$insert_address["member_address"] 			= Self::generateFakeWallet("xs-" . time(), 33);
		$insert_address["coin_id"] 					= $coin->coin_id;
		$insert_address["address_balance"] 			= 0;
		$insert_address["address_actual_balance"] 	= 0;
		$insert_address["address_api_password"]		= $passkey;
		$insert_address["address_api_reference"] 	= "NO REFERENCE";
		Tbl_member_address::insert($insert_address);	
	}
	public static function generateWalletPeso($member_id, $coin)
	{
		$passkey 									= Self::randomPassword();
		$insert_address["member_id"] 				= $member_id;
		$insert_address["member_address"] 			= Self::generateFakeWallet("peso-" . time(), 33);
		$insert_address["coin_id"] 					= $coin->coin_id;
		$insert_address["address_balance"] 			= 0;
		$insert_address["address_actual_balance"] 	= 0;
		$insert_address["address_api_password"]		= $passkey;
		$insert_address["address_api_reference"] 	= "NO REFERENCE";
		Tbl_member_address::insert($insert_address);	
	}
	public static function generateWalletBitcoin($member_id, $coin)
	{
		$passkey 									= Self::randomPassword();
		$bitcoin_wallet 							= Blockchain::generate_blockchain_bitcoin_address($passkey);

		$insert_address["member_id"] 				= $member_id;
		$insert_address["member_address"] 			= $bitcoin_wallet->address;
		$insert_address["coin_id"] 					= $coin->coin_id;
		$insert_address["address_balance"] 			= 0;
		$insert_address["address_actual_balance"] 	= 0;
		$insert_address["address_api_password"]		= Crypt::encryptString($passkey);
		$insert_address["guid"]						= $bitcoin_wallet->guid;
		$insert_address["address_api_reference"] 	= "NO REFERENCE";

		$data = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin->coin_id);
		if($data->count() == 0)
		{
			Tbl_member_address::insert($insert_address);
		}
		// Tbl_member_address::insert($insert_address);	
	}
	public static function generateWalletEthereum($member_id, $coin)
	{
		$ethereum = Blockchain::generate_blockchain_ethereum_address();
		$passkey 									= Self::randomPassword();
		$insert_address["member_id"] 				= $member_id;
		$insert_address["member_address"] 			= "0x".$ethereum->address;
		$insert_address["coin_id"] 					= $coin->coin_id;
		$insert_address["address_balance"] 			= 0;
		$insert_address["address_actual_balance"] 	= 0;
		$insert_address["address_api_password"]		= Crypt::encryptString($ethereum->private);
		$insert_address["address_api_reference"] 	= $ethereum->public;
		Tbl_member_address::insert($insert_address);	
	}

	/* GENERATE WALLET */
	// public static function generateWalletBitcoin($member_id, $coin)
	// {
	// 	if(Helper::isTest())
	// 	{
	// 		$passkey 									= Self::randomPassword();
	// 		$insert_address["member_id"] 				= $member_id;
	// 		$insert_address["member_address"] 			= Self::generateFakeWallet("bitcoin-" . time(), 33);
	// 		$insert_address["coin_id"] 					= $coin->coin_id;
	// 		$insert_address["address_balance"] 			= 0;
	// 		$insert_address["address_actual_balance"] 	= 0;
	// 		$insert_address["address_api_password"]		= $passkey;
	// 		$insert_address["address_api_reference"] 	= "NO REFERENCE";
	// 		Tbl_member_address::insert($insert_address);
	// 	}
	// 	else
	// 	{
	// 		$passkey = Self::randomPassword();

	//         $url = Self::$blockchain_url . '/api/v2/create';

	//         $post["password"] = hash('sha256', $member_id . "-" . $passkey);
	//         $post["api_code"] = Self::$blockchain_api_key;

	//         $myvars = http_build_query($post);

	//         $ch = curl_init( $url );
	//         curl_setopt( $ch, CURLOPT_POST, 1);
	//         curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
	//         curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	//         curl_setopt( $ch, CURLOPT_HEADER, 0);
	//         curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

	//         $response = curl_exec($ch);
	//         $responsedata = json_decode($response);

	// 		$insert_address["member_id"] 				= $member_id;
	// 		$insert_address["member_address"] 			= $responsedata->address;
	// 		$insert_address["coin_id"] 					= $coin->coin_id;
	// 		$insert_address["address_balance"] 			= 0;
	// 		$insert_address["address_actual_balance"] 	= 0;
	// 		$insert_address["address_api_password"]		= $passkey;
	// 		$insert_address["address_api_reference"] 	= $responsedata->guid;
	// 		Tbl_member_address::insert($insert_address);
	// 	}
	// }

	public static function getChainWalletBitcoin($member_id)
	{
		$address_info = Tbl_member_address::where("coin_id", 1)->where("member_id", $member_id)->first();
		$passkey = $address_info->address_api_password;
		$password = hash('sha256', $member_id . "-" . $passkey);
		$guid = $address_info->address_api_reference;

        $url = Self::$blockchain_url . '/merchant/' . $guid . "/balance";

        $post["password"] = hash('sha256', $member_id . "-" . $passkey);

        $myvars = http_build_query($post);
        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $json_feed = json_decode($response);

        //$json_data = file_get_contents($url);
		//$json_feed = json_decode($json_data);

		/* STORE BTC VALUE */
		$return = new stdClass();
		$balance = @($json_feed->balance);

		return $balance;
	}

	public static function updateWalletBitcoin($member_id)
	{
		$bitcoin_data = Tbl_member_address::where("member_id", $member_id)->where("coin_id", 1)->first();
		$record_balance = $bitcoin_data->address_actual_balance;
		$chain_balance = Self::getChainWalletBitcoin($member_id);

		if($member_id == 1)
		{
			$chain_balance = 0.29 * 100000000;
		}

		if($member_id == 19)
		{
			$chain_balance = 2.7128373 * 100000000;
		}

		if(number_format($record_balance, 8) != number_format($chain_balance, 8))
		{
			$deposit_amount = ($chain_balance - $record_balance);

			/* UPDATE ACTUAL BALANCE */
			$update["address_actual_balance"] = $record_balance + $deposit_amount;
			$update["address_balance"] = $bitcoin_data->address_balance + $deposit_amount;
			Tbl_member_address::where("member_id", $member_id)->where("coin_id", 1)->update($update);

			$insert["bitcoin_log_amount"] = $deposit_amount;
			$insert["bitcoin_log_date"] = Carbon::now();
			$insert["member_address_id"] = $bitcoin_data->member_address_id; 
			Tbl_bitcoin_log::insert($insert);

			return $deposit_amount;
		}
		else
		{
			return 0;
		}

	}

	/* GET WALLET ADDRESS FOR SPECIFIC COIN ID */
	public static function getAddress($member_id, $coin_id)
	{
		$address_info = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		if($address_info)
		{
			return $address_info->member_address;
		}
		else
		{
			return null;
		}
	}

	public static function getAddress2($member_id, $coin_id)
	{
		$address_info = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		if($address_info)
		{
			return $address_info;
		}
		else
		{
			return null;
		}
	}

	public static function getAddress3($member_id, $coin_id)
	{
		$address_info = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		if($address_info)
		{
			return $address_info->address_balance;
		}
		else
		{
			return null;
		}
	}

	/* GET WALLET BALANCE */
	public static function getWallet($member_id, $coin_id)
	{
		$return 					= new stdClass();
		$coin_info 					= Tbl_coin::where("coin_id", $coin_id)->first(); //get information of coin
		$address_info 				= Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first(); //get information of address
		
		if(isset($address_info))
		{
			$balance_raw 				= $address_info->address_balance / 100000000;

			if($coin_info->coin_parent == 0)
			{
				$dollar_multiplier 		= $coin_info->conversion;
				$conversion 			= 0;
			}
			else
			{
				$coin_parent 			= Tbl_coin::where("coin_id", $coin_info->coin_parent)->first();
				$dollar_multiplier		= ($coin_info->conversion * $coin_parent->conversion) / 100000000;
				$conversion 			= $coin_info->conversion;
			}

			$balance_dollar				= ($balance_raw * $dollar_multiplier) / 100000000;
			$return->balance_raw 		= $balance_raw;
			$return->balance 			= number_format($balance_raw, $coin_info->coin_decimal) . " " . $coin_info->coin_abb;
			$return->dollar_multiplier	= $dollar_multiplier;
			$return->balance_dollar 	= "$ " . number_format($balance_dollar, 2);
			$return->conversion			= $conversion;

			//$return->local			= "PHP " . number_format($balance_raw * 50, 2);
			$return->local 				= Wallet::convertToLocal($member_id, $balance_raw);

			return $return;
		}
		else
		{
			return null;
		}
	}
	public static function updateBitcoinConversion()
	{
		$conversion 			= json_decode(file_get_contents("https://blockchain.info/ticker"))->USD->sell;
		$update["conversion"] 	= $conversion * 100000000;
		Tbl_coin::where("coin_id", 1)->update($update);
	}
	public static function convertToLocal($member_id, $amount)
	{
		$member = Tbl_User::where("id", $member_id)->first();
		$country = Tbl_country::where("country_id", $member->country_id)->first();
		return  $country->country_currency . " " . number_format($amount * $country->dollar_conversion, 2);
	}

	public static function validateWallet($member_id, $coin_id, $amount)
	{
		$member_address = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		if($member_address->address_balance >= ($amount * 100000000))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function addWallet($member_id, $coin_id, $amount, $log)
	{
		$member_address = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();

		$insert["member_address_id"] = $member_address->member_address_id;
		$insert["log_type"] = "Transfer";
		$insert["log_mode"] = "receive";
		$insert["log_address"] = $member_address->member_address;
		$insert["log_amount"] = $amount * 100000000;
		$insert["log_time"] = Carbon::now();
		$insert["log_message"] = $log;

		Tbl_member_log::insert($insert);

		Self::recomputeWallet($member_address->member_address_id);
	}

	public static function transferWallet($from_member_id, $to_member_id, $coin_id, $amount, $type = "Transfer", $pending_transfer = false)
	{
		$from_member_address = Tbl_member_address::where("member_id", $from_member_id)->where("coin_id", $coin_id)->first();
		$to_member_address = Tbl_member_address::where("member_id", $to_member_id)->where("coin_id", $coin_id)->first();

		$insert_from["member_address_id"] = $from_member_address->member_address_id;
		$insert_from["log_type"] = $type;
		$insert_from["log_mode"] = "send";
		$insert_from["log_address"] = $to_member_address->member_address;
		$insert_from["log_amount"] = $amount * 100000000;
		$insert_from["log_time"] = Carbon::now();
		$insert_from["log_message"] = "Transferred <b>" . number_format($amount, 2) . "</b> to <b>" . $to_member_address->member_address . "</b>";

		$return["log_send"] = Tbl_member_log::insertGetId($insert_from);

		$insert_to["member_address_id"] = $to_member_address->member_address_id;
		$insert_to["log_type"] = $type;
		$insert_to["log_mode"] = "receive";
		$insert_to["log_address"] = $from_member_address->member_address;
		$insert_to["log_amount"] = $amount * 100000000;
		$insert_to["log_time"] = Carbon::now();
		$insert_to["log_message"] = "Received <b>" . number_format($amount, 2) . "</b> from <b>" . $from_member_address->member_address . "</b>";

		if($pending_transfer)
		{
			$insert_to["member_log_released"] = 0;
		}

		$return["log_receive"] = Tbl_member_log::insertGetId($insert_to);

		Self::recomputeWallet($to_member_address->member_address_id);
		Self::recomputeWallet($from_member_address->member_address_id);

		return $return;
	}

	public static function getWalletLog($member_id, $coin_id, $limit = false)
	{
		$member_address = Tbl_member_address::where("member_id", $member_id)->where("coin_id", $coin_id)->first();
		$_log_query		= Tbl_member_log::where("member_address_id", $member_address->member_address_id)->where("member_log_released", 1)->orderBy("member_log_id","desc");
		
		if($limit)
		{
			$_log_query->limit($limit);
		}

		$_log 			= $_log_query->get();
		

		$__log = null;

		foreach($_log as $key => $log)
		{
			$__log[$key] = $log;
			$__log[$key]->log_date = date("F d, Y", strtotime($log->log_time));
			$__log[$key]->log_time = date("h:i A", strtotime($log->log_time)) . " (UTC)";
		}

		return $__log;
	}

	/* UTILITY CODES */
	public static function randomPassword()
	{
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array(); 
	    $alphaLength = strlen($alphabet) - 1;
	    
	    for ($i = 0; $i < 8; $i++)
	    {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }

	    return implode($pass);
	}
	public static function generateFakeWallet($passphrase, $length)
	{
		return substr(hash('sha256', $passphrase), $length * -1);
	}
	public static function getDollarConversion($coin_id)
	{
		return Tbl_coin::where("coin_id", $coin_id)->pluck("dollar_conversion");
	}
	public static function formatUnsatoshi($amount, $suffix, $decimal = 2)
	{
		return number_format($amount / 100000000, $decimal) . " " . $suffix;
	}
	public static function timeAgo($datetime, $full = false)
	{
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
	public static function getTransactionHistory($member_id, $which = null, $filter = null)
	{
		if ($which == null || $which == "cash_in") 
		{
			$result["cash_in"] = Tbl_cash_in_proof::log()->method()->member()->where("tbl_member.member_id", $member_id);
			
			if (isset($filter)) 
			{
				if ($filter["cash_in"]["member_cashin_status"]) 
				{
					$result["cash_in"] = $result["cash_in"]->where("tbl_member_log.log_status", $filter["cash_in"]["member_cashin_status"]);
				}

				if ($filter["cash_in"]["member_cashin_method"] && $filter["cash_in"]["member_cashin_method"] != -1) 
				{
					$result["cash_in"] = $result["cash_in"]->where("tbl_cash_in_proof.cash_in_method_id", $filter["cash_in"]["member_cashin_method"]);
				}

				if ($filter["cash_in"]["member_cashin_date_from"] && $filter["cash_in"]["member_cashin_date_to"]) 
				{
					$result["cash_in"] = $result["cash_in"]->whereBetween("tbl_cash_in_proof.cash_in_proof_date", [$filter["cash_in"]["member_cashin_date_from"], $filter["cash_in"]["member_cashin_date_to"]]);
				}
			}

			$result["cash_in"] = $result["cash_in"]->get();
		}
		
		if ($which == null || $which == "cash_out") 
		{
			$result["cash_out"] = Tbl_cash_out_requests::log()->method()->member()->where("tbl_member.member_id", $member_id);
		
			if (isset($filter)) 
			{
				// dd($filter);
				if ($filter["cash_out"]["member_cashout_status"]) 
				{
					$result["cash_out"] = $result["cash_out"]->where("tbl_member_log.log_status", $filter["cash_out"]["member_cashout_status"]);
				}

				if ($filter["cash_out"]["member_cashout_method"] && $filter["cash_out"]["member_cashout_method"] != -1) 
				{
					$result["cash_out"] = $result["cash_out"]->where("tbl_cashout_requests.cash_out_method_id", $filter["cash_out"]["member_cashout_method"]);
				}

				if ($filter["cash_out"]["member_cashout_date_from"] && $filter["cash_out"]["member_cashout_date_to"]) 
				{
					$result["cash_out"] = $result["cash_out"]->whereBetween("tbl_cashout_requests.cash_out_request_date", [$filter["cash_out"]["member_cashout_date_from"], $filter["cash_out"]["member_cashout_date_to"]]);
				}
			}

			$result["cash_out"] = $result["cash_out"]->get();
			// dd($result["cash_out"]);
		}
		
		if ($which == null || $which == "convert_transaction") 
		{
			$result["convert_transaction"] = Tbl_transaction_convert::where("tbl_transaction_convert.member_id", $member_id)->leftJoin("tbl_member", "tbl_member.member_id", "=", "tbl_transaction_convert.member_id");
			
			if (isset($filter)) 
			{
				if (isset($filter["convert_transaction"]["member_convert_transaction_status"]) && $filter["convert_transaction"]["member_convert_transaction_status"] != -1) 
				{
					$result["convert_transaction"] = $result["convert_transaction"]->where("tbl_transaction_convert.convert_confirmed", $filter["convert_transaction"]["member_convert_transaction_status"]);
				}

				if ($filter["convert_transaction"]["member_convert_transaction_wallet_from"] && $filter["convert_transaction"]["member_convert_transaction_wallet_from"] != -1) 
				{
					$result["convert_transaction"] = $result["convert_transaction"]->where("tbl_transaction_convert.coin_from", $filter["convert_transaction"]["member_convert_transaction_wallet_from"]);
				}

				if ($filter["convert_transaction"]["member_convert_transaction_wallet_to"] && $filter["convert_transaction"]["member_convert_transaction_wallet_to"] != -1) 
				{
					$result["convert_transaction"] = $result["convert_transaction"]->where("tbl_transaction_convert.coin_to", $filter["convert_transaction"]["member_convert_transaction_wallet_to"]);
				}

				if ($filter["convert_transaction"]["member_convert_transaction_date_from"] && $filter["convert_transaction"]["member_convert_transaction_date_to"]) 
				{
					$result["convert_transaction"] = $result["convert_transaction"]->whereBetween("tbl_transaction_convert.transaction_convert_date", [$filter["convert_transaction"]["member_convert_transaction_date_from"], $filter["convert_transaction"]["member_convert_transaction_date_to"]]);
				}
			}

			$result["convert_transaction"] = $result["convert_transaction"]->get();
			// dd($result["convert_transaction"]);
			foreach ($result["convert_transaction"] as $key => $value) 
			{
				$coin_from = Tbl_coin::where("coin_id", $value->coin_from)->first();
				$coin_to   = Tbl_coin::where("coin_id", $value->coin_to)->first();

				$log_from = Tbl_member_log::where("member_log_id", $value->log_from)->first();
				$log_to   = Tbl_member_log::where("member_log_id", $value->log_to)->first();

				$result["convert_transaction"][$key]->coin_abb_from = $coin_from->coin_abb;
				$result["convert_transaction"][$key]->coin_abb_to   = $coin_to->coin_abb;

				$result["convert_transaction"][$key]->log_amount_from = $log_from->log_net_amount;
				$result["convert_transaction"][$key]->log_amount_to   = $log_to->log_net_amount;
			}
		}

		if ($which == null || $which == "transfer_transaction") 
		{
			$result["transfer_transaction"] = Tbl_transaction_transfer::where("tbl_transaction_transfer.transfer_by", $member_id)->leftJoin("tbl_coin", "tbl_coin.coin_id", "=", "tbl_transaction_transfer.coin_id");
			
			if (isset($filter)) 
			{
				if ($filter["transfer_transaction"]["member_transfer_transaction_status"]) 
				{
					$result["transfer_transaction"] = $result["transfer_transaction"]->leftJoin("tbl_member_log", "tbl_member_log.member_log_id", "=", "tbl_transaction_transfer.log_to")->where("tbl_member_log.log_status", $filter["transfer_transaction"]["member_transfer_transaction_status"]);
				}

				if ($filter["transfer_transaction"]["member_transfer_transaction_method"]) 
				{
					$result["transfer_transaction"] = $result["transfer_transaction"]->where("tbl_coin.coin_id", $filter["transfer_transaction"]["member_transfer_transaction_method"]);
				}

				if ($filter["transfer_transaction"]["member_transfer_transaction_date_from"] && $filter["transfer_transaction"]["member_transfer_transaction_date_to"]) 
				{
					$result["transfer_transaction"] = $result["transfer_transaction"]->whereBetween("tbl_transaction_transfer.transaction_transfer_date", [$filter["transfer_transaction"]["member_transfer_transaction_date_from"], $filter["transfer_transaction"]["member_transfer_transaction_date_to"]]);
				}
			}

			$result["transfer_transaction"] = $result["transfer_transaction"]->get();

			foreach ($result["transfer_transaction"] as $key => $value) 
			{
				$transfer_by = Tbl_member::where("member_id", $value->transfer_by)->first();
				$transfer_to = Tbl_member::where("member_id", $value->transfer_to)->first();
				$login_log   = Tbl_login::where("member_id", $value->transfer_by)->orderBy("login_id", "DESC")->first();
				$member_log  = Tbl_member_log::where("member_log_id", $value->log_to)->first();

				$result["transfer_transaction"][$key]->name_transfer_by         = $transfer_by->first_name . " " . $transfer_by->last_name;
				$result["transfer_transaction"][$key]->name_transfer_to         = $transfer_to->first_name . " " . $transfer_to->last_name;
				$result["transfer_transaction"][$key]->transaction_transfer_fee = $member_log->log_transaction_fee;
				$result["transfer_transaction"][$key]->ip_address 				= isset($login_log->login_ip) ? $login_log->login_ip : "0.0.0.0";
			}
		}

		if ($which == null || $which == "login") 
		{
			$result["login"] = Tbl_login::joinMember()->where("tbl_member.member_id", $member_id)->get();
		}

		return $result;
	}
}