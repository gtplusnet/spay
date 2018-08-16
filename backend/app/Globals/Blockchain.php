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
use App\Tbl_other_info;
use App\Tbl_referral;
use App\Tbl_referral_bonus_log;
use App\Tbl_User;
use App\Tbl_position_requirements;
use App\Globals\Wallet;
use App\Globals\Member_log;
use Illuminate\Support\Facades\Crypt;

class Blockchain
{
    public static function generate_blockchain_bitcoin_address($passkey)
    {
        try {
            $ch = curl_init();
            $api_code = "1da456d5-f176-%E2%80%8E4997-8105-2f95a4f95cfd";
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "http://206.189.155.9:3000/api/v2/create?password=".$passkey."&api_code=".$api_code);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            if (FALSE === $content)
                throw new \Exception(curl_error($ch), curl_errno($ch));

            // ...process $content now
        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
            E_USER_ERROR);

        }

        return json_decode($content);
    }

    public static function generate_blockchain_ethereum_address()
    {
        try {
            $ch = curl_init();
            $token = "7e7ea4a09e96460b8b20c915a48bcfb6";
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "https://api.blockcypher.com/v1/eth/main/addrs");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "token=".$token);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            if (FALSE === $content)
                throw new \Exception(curl_error($ch), curl_errno($ch));

            // ...process $content now
        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
            E_USER_ERROR);

        }

        return json_decode($content);
    }

    public static function get_blockchain_bitcoin_balance($guid, $address_api_password)
    {
        $address_api_password = Crypt::decryptString($address_api_password);
        try {
            $ch = curl_init();
            $api_code = "1da456d5-f176-%E2%80%8E4997-8105-2f95a4f95cfd";
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "http://206.189.155.9:3000/merchant/". $guid ."/balance?password=" . $address_api_password);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            if (FALSE === $content)
                throw new \Exception(curl_error($ch), curl_errno($ch));

            // ...process $content now
        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
            E_USER_ERROR);

        }

        $_return = json_decode($content);

        if (isset($_return->balance) && $_return->balance != null) 
        {
            return $_return->balance;
        }
        else
        {
            return 0;
        }
    }

    public static function get_blockchain_ethereum_balance($address)
    {
        try {
            $ch = curl_init();
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "https://api.blockcypher.com/v1/eth/main/addrs/" . $address);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);


            if (FALSE === $content)
                throw new \Exception(curl_error($ch), curl_errno($ch));

            // ...process $content now
        } catch(Exception $e) {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
            E_USER_ERROR);

        }

        $_return = json_decode($content);

        return $_return;
    }

    public static function send_bitcoin_outside_system($passkey)
    {
        try 
        {
            $ch = curl_init();
            $api_code = "1da456d5-f176-%E2%80%8E4997-8105-2f95a4f95cfd";
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "http://206.189.155.9:3000/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&fee=$fee");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $content = curl_exec($ch);

            if (FALSE === $content)
                throw new \Exception(curl_error($ch), curl_errno($ch));

            // ...process $content now
        } 
        catch(Exception $e) 
        {

            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
            E_USER_ERROR);
        }

        return json_decode($content);
    }

    public static function sendBTC($member_address_id, $member_address_to, $amount)
    {
        $address_info           = Tbl_member_address::where("member_address_id", $member_address_id)->first();
        $member_id              = $address_info->member_id;
        $insert["send_from"]    = $address_info->member_address_id;
        $insert["send_to"]      = $member_address_to;
        $insert["amount"]       = $amount;

        $passkey = $address_info->address_api_password;
        $password = hash('sha256', $member_id . "-" . $passkey);
        $guid = $address_info->address_api_reference;
        //http://206.189.155.9:3000/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&fee=$fee
        $url = Self::$blockchain_url . '/merchant/' . $guid . "/payment";

        $post["password"]   = hash('sha256', $member_id . "-" . $passkey);
        $post["to"]         = $member_address_to;
        $post["amount"]     = $amount - 10000;
        $post["from"]       = $address_info->member_address;
        $post["fee"]        = 10000;

        $myvars = http_build_query($post);
        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        
        $json_feed = json_decode($response);
        //dd($address_info);
        dd($json_feed);

        //$json_data = file_get_contents($url);
        //$json_feed = json_decode($json_data);

        /* STORE BTC VALUE */
        $return = new stdClass();
        $balance = @($json_feed->balance);

        return $balance;
    }

    public static function checkBalanceBTC($member_id, $sale_stage_id)
    {

        $btc_wallet_info = Tbl_member_address::JoinCoin($member_id, 'bitcoin')->first();
        $lok_wallet_info = Tbl_member_address::JoinCoin($member_id, 'lokalize')->first();
        // Self::recordReferralBonus($lok_wallet_info['member_id'], 123);
        // dd(Crypt::encryptString($btc_wallet_info['address_api_password']));
        $btc_wallet_balance = Self::get_blockchain_bitcoin_balance($btc_wallet_info['guid'], $btc_wallet_info['address_api_password']);
        // $btc_wallet_balance = $btc_wallet_balance/100000000;
        // dd($btc_wallet_balance);


        /*if blockchain btc_wallet change update system btc_wallet*/
        if (($btc_wallet_balance/100000000) != $btc_wallet_info['address_actual_balance']) 
        {

            $member_log = Tbl_member_log::JoinAutomaticCashIn($lok_wallet_info['member_address_id'], 'pending', 'Bitcoin')->first();
            
            /*if btc actual balance decrease for some reason*/
            if($btc_wallet_balance/100000000 < $btc_wallet_info['address_actual_balance'])
            {
                $update_btc_wallet_address['address_actual_balance'] = $btc_wallet_balance;
                Tbl_member_address::where('member_address_id',$btc_wallet_info['member_address_id'])->update($update_btc_wallet_address);
            }
            /*if has aba request using bitcoin cash in*/
            else if ($member_log) 
            {

                // dd($lok_wallet_info, $btc_wallet_info, $member_log);
                $added_btc_amount_bitcoin_format = @($btc_wallet_balance/100000000) - $btc_wallet_info['address_actual_balance'];
                // $added_btc_amount_bitcoin_format =  Self::convertSatoshiToBitcoin($added_btc_amount);
                
                $update_btc_wallet_address['address_actual_balance'] = Self::convertSatoshiToBitcoin($btc_wallet_balance);
                Tbl_member_address::where('member_address_id',$btc_wallet_info['member_address_id'])->update($update_btc_wallet_address);
                
                $payment_discount            = @($member_log['sale_stage_discount']/100);
                
                // dd($added_btc_amount_bitcoin_format, $payment_discount, $member_log["exchange_rate"], $member_log, ($member_log['exchange_rate']*$payment_discount));
                $added_lok_wallet_address = ($added_btc_amount_bitcoin_format ) / ($member_log['exchange_rate']*$payment_discount);

                /*update log*/
                $update_member_log['log_amount']        = $added_lok_wallet_address;
                $update_member_log['log_net_amount']    = $added_lok_wallet_address;
                $update_member_log['log_method']        = "Bitcoin Total";
                $update_member_log['log_time']          = Carbon::now('Asia/Manila');
                $update_member_log['log_status']        = 'accepted';

                Tbl_member_log::where('member_log_id', $member_log['member_log_id'])->update($update_member_log);

                /*buy bonus token*/
                if($sale_stage_id)
                {
                    $get_bonus_percentage = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where(function($query) use ($added_lok_wallet_address){
                        $query
                        ->where("buy_coin_bonus_from", "<=", $added_lok_wallet_address)
                        ->where("buy_coin_bonus_to", ">=", $added_lok_wallet_address);
                    })->first();

                    if(!$get_bonus_percentage)
                    {
                        $check_if_higher = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_to", "<", $added_lok_wallet_address)->orderBy("buy_coin_bonus_to", "desc")->first();
                        if($check_if_higher)
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = $check_if_higher->buy_coin_bonus_percentage;
                        }
                        else
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = 0;
                        }
                    }
                    else
                    {
                        $update_bonus_percentage['sale_stage_bonus'] = $get_bonus_percentage->buy_coin_bonus_percentage;
                    }

                    $final_bonus = $update_bonus_percentage['sale_stage_bonus'];

                    Tbl_automatic_cash_in::where("member_log_id", $member_log['member_log_id'])->update($update_bonus_percentage);

                    if($final_bonus != 0)
                    {
                        $token_bonus_percentage  = @($final_bonus/100); 

                        Self::recordBuyBonus($added_lok_wallet_address, $token_bonus_percentage, $member_log['member_log_id'], $lok_wallet_info['member_address_id'], "Bitcoin");
                    }
                }

                /*referral_bonus_token*/
                Self::recordReferralBonus($lok_wallet_info['member_id'], $added_lok_wallet_address, $member_log['member_log_id'], "Bitcoin");

                /*role bonus token*/
                // $member_role = Tbl_other_info::joinDetails()->where("user_id", $lok_wallet_info['member_id'])->first();

                // if($member_role->member_buy_bonus_percentage != 0)
                // {
                //     $role_bonus = @($member_role->member_buy_bonus_percentage/100);
                //     Self::recordRoleBonus($added_lok_wallet_address, $role_bonus, $lok_wallet_info['member_address_id']);
                // }

                /*insert log for btc wallet*/
                $data["payment_coin"]   = $added_btc_amount_bitcoin_format;
                $data["received_token"] = $added_lok_wallet_address;
                $data["log_status"]     = "accepted";
                Member_log::insert($data, $lok_wallet_info['member_id'], 'bitcoin');

                $accepted["member"] = Tbl_User::where("id", $member_id)->first();
                $accepted["amount"] = $added_lok_wallet_address;
                $accepted["record"] = Tbl_automatic_cash_in::where("member_log_id", $member_log['member_log_id'])->first();

                $member_info = Tbl_other_info::where("user_id", $member_id)->first();
                if($member_info->member_position_id != 1)
                {
                    $member_info = Tbl_other_info::where("user_id", $member_id)->update(["first_buy"=>1]);
                }
                Mails::order_accepted($accepted);
            }
        }
        Wallet::recomputeWallet($btc_wallet_info['member_address_id']);
        Wallet::recomputeWallet($lok_wallet_info['member_address_id']);
    }

    public static function checkBalanceETH($member_id, $sale_stage_id)
    {

        $eth_wallet_info = Tbl_member_address::JoinCoin($member_id, 'ethereum')->first();
        $lok_wallet_info = Tbl_member_address::JoinCoin($member_id, 'lokalize')->first();

        $eth_wallet = Self::get_blockchain_ethereum_balance($eth_wallet_info['member_address']);

        $eth_wei = 1000000000000000000;
        $eth_balance = @($eth_wallet->balance/$eth_wei);

        if($eth_balance != $eth_wallet_info['address_actual_balance'])
        {
            $member_log = Tbl_member_log::JoinAutomaticCashIn($lok_wallet_info['member_address_id'], 'pending', 'Ethereum')->first();

            /*if eth actual balance decrease for some reason*/
            if($eth_balance < $eth_wallet_info['address_actual_balance'])
            {
                $update_eth_wallet_address['address_actual_balance'] = $eth_balance;
                Tbl_member_address::where('member_address_id',$eth_wallet_info['member_address_id'])->update($update_eth_wallet_address);
            }
            else if ($member_log)
            {
                $added_eth_amount = $eth_balance - $eth_wallet_info['address_actual_balance'];
                // dd($added_eth_amount, $eth_balance, $eth_wallet_info['address_actual_balance']);
                $update_eth_actual_balance['address_actual_balance'] = $eth_balance;
                Tbl_member_address::where('member_address_id',$eth_wallet_info['member_address_id'])->update($update_eth_actual_balance);

                $payment_discount            = @($member_log['sale_stage_discount']/100);

                // dd($added_btc_amount_bitcoin_format, $payment_discount, $member_log["exchange_rate"], $member_log, ($member_log['exchange_rate']*$payment_discount));
                $added_lok_wallet_address = ($added_eth_amount) / ($member_log['exchange_rate']*$payment_discount);

                /*update log*/
                $update_member_log['log_amount']        = $added_lok_wallet_address;
                $update_member_log['log_net_amount']    = $added_lok_wallet_address;
                $update_member_log['log_method']        = "Ethereum Total";
                $update_member_log['log_time']          = Carbon::now('Asia/Manila');
                $update_member_log['log_status']        = 'accepted';

                Tbl_member_log::where('member_log_id', $member_log['member_log_id'])->update($update_member_log);

                if($sale_stage_id)
                {
                    /*buy bonus token*/
                    $get_bonus_percentage = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where(function($query) use ($added_lok_wallet_address){
                        $query
                        ->where("buy_coin_bonus_from", "<=", $added_lok_wallet_address)
                        ->where("buy_coin_bonus_to", ">=", $added_lok_wallet_address);
                    })->first();

                // dd(count($get_bonus_percentage), $get_bonus_percentage, $added_lok_wallet_address, $member_log);


                    if(!$get_bonus_percentage)
                    {
                        $check_if_higher = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_to", "<", $added_lok_wallet_address)->orderBy("buy_coin_bonus_to", "desc")->first();
                        if($check_if_higher)
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = $check_if_higher->buy_coin_bonus_percentage;
                        }
                        else
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = 0;
                        }
                    }
                    else
                    {
                        $update_bonus_percentage['sale_stage_bonus'] = $get_bonus_percentage->buy_coin_bonus_percentage;
                    }

                    $final_bonus = $update_bonus_percentage['sale_stage_bonus'];

                    Tbl_automatic_cash_in::where("member_log_id", $member_log['member_log_id'])->update($update_bonus_percentage);

                    if($final_bonus != 0)
                    {
                        $token_bonus_percentage  = @($final_bonus/100); 

                        Self::recordBuyBonus($added_lok_wallet_address, $token_bonus_percentage, $member_log['member_log_id'], $lok_wallet_info['member_address_id'], "Ethereum");
                    }

                }

                /*referral_bonus_token*/
                Self::recordReferralBonus($lok_wallet_info['member_id'], $added_lok_wallet_address, $member_log['member_log_id'], "Ethereum");

                /*role bonus token*/
                // $member_role = Tbl_other_info::joinDetails()->where("user_id", $lok_wallet_info['member_id'])->first();

                // if($member_role->member_buy_bonus_percentage != 0)
                // {
                //     $role_bonus = @($member_role->member_buy_bonus_percentage/100);
                //     Self::recordRoleBonus($added_lok_wallet_address, $role_bonus, $lok_wallet_info['member_address_id']);
                // }

                /*insert log for btc wallet*/
                $data["payment_coin"]   = $added_eth_amount;
                $data["received_token"] = $added_lok_wallet_address;
                $data["log_status"]     = "accepted";
                Member_log::insert($data, $lok_wallet_info['member_id'], 'ethereum');

                $accepted["member"] = Tbl_User::where("id", $member_id)->first();
                $accepted["amount"] = $added_lok_wallet_address;
                $accepted["record"] = Tbl_automatic_cash_in::where("member_log_id", $member_log['member_log_id'])->first();
                Mails::order_accepted($accepted);
            }
        }

        Wallet::recomputeWallet($eth_wallet_info['member_address_id']);
        Wallet::recomputeWallet($lok_wallet_info['member_address_id']);
    }

    public static function recordBuyBonus($lok_amount, $bonus_percentage, $member_log_id, $lok_address_id, $payment_type)
    {
        $buy_bonus_token = $lok_amount * $bonus_percentage;

        $insert_buy_bonus["log_amount"]         = $buy_bonus_token;
        $insert_buy_bonus["log_net_amount"]     = $buy_bonus_token;
        $insert_buy_bonus["log_status"]         = "accepted";
        $insert_buy_bonus["member_address_id"]  = $lok_address_id;
        $insert_buy_bonus["log_type"]           = "transfer";
        $insert_buy_bonus["log_mode"]           = "buy bonus";
        $insert_buy_bonus["log_method"]         = $payment_type . " - Buy Bonus";
        $insert_buy_bonus["log_message"]        = "Buy Bonus Token from ".$payment_type." Transaction #".$member_log_id;
        $insert_buy_bonus["log_time"]           = Carbon::now('Asia/Manila');
        $insert_buy_bonus["log_transaction_fee"] = 0;

        $buy_bonus = Tbl_member_log::insert($insert_buy_bonus);  
    }

    public static function recordRoleBonus($lok_amount, $bonus_percentage, $lok_address_id, $payment_type)
    {
        $role_bonus_token = $lok_amount * $bonus_percentage;

        $insert_role_bonus["log_amount"]         = $role_bonus_token;
        $insert_role_bonus["log_net_amount"]     = $role_bonus_token;
        $insert_role_bonus["log_status"]         = "accepted";
        $insert_role_bonus["member_address_id"]  = $lok_address_id;
        $insert_role_bonus["log_type"]           = "transfer";
        $insert_role_bonus["log_mode"]           = "role bonus";
        $insert_role_bonus["log_method"]         = $payment_type . " - Role Bonus";
        $insert_role_bonus["log_message"]        = "Role Bonus Token from ".$payment_type." Transaction #".$member_log_id;
        $insert_role_bonus["log_time"]           = Carbon::now('Asia/Manila');
        $insert_role_bonus["log_transaction_fee"] = 0;

        $buy_bonus = Tbl_member_log::insert($insert_role_bonus);  
    }

    public static function recordReferralBonus($user_id, $lok_amount, $member_log_id, $payment_type)
    {
        $invitee = Tbl_other_info::joinDetails()->where("user_id", $user_id)->first();

        $_referral = Tbl_referral::where("referral_id", $invitee->referrer_id)->value('referral_user_id');

        // $referrer = Tbl_other_info::joinDetails()->where("user_id", $_referral)->first();
        $referrer = Tbl_position_requirements::joinMember()->where("member_id", $_referral)->first();

        $referrer_info = Tbl_other_info::where("user_id", $_referral)->first();

        $lok_address_id = Tbl_member_address::where("member_id", $_referral)->where("coin_id", 4)->value("member_address_id");

        // dd($invitee, $_referral, $referrer);
        // $_referrer = Tbl_User::where("id", $_referral)->first();
        if(count($referrer) != 0)
        {
            $referrer_bonus_percentage = $referrer_info->first_buy ? @($referrer->after_purchase_commission/100) : @($referrer->commission/100);
            $referral_bonus_token = $lok_amount * $referrer_bonus_percentage;

            $insert_referral_bonus["log_amount"]         = $referral_bonus_token;
            $insert_referral_bonus["log_net_amount"]     = $referral_bonus_token;
            $insert_referral_bonus["log_status"]         = "automatic";
            $insert_referral_bonus["member_address_id"]  = $lok_address_id;
            $insert_referral_bonus["log_type"]           = "transfer";
            $insert_referral_bonus["log_mode"]           = "referral bonus";
            $insert_referral_bonus["log_method"]         = $payment_type. " - Referral Bonus";
            $insert_referral_bonus["log_message"]        = "Referral Bonus Token from ".$payment_type." Transaction #".$member_log_id;
            $insert_referral_bonus["log_time"]           = Carbon::now('Asia/Manila');
            $insert_referral_bonus["log_transaction_fee"] = 0;

            $referral_bonus = Tbl_member_log::insertGetId($insert_referral_bonus); 

            $insert_bonus_log["member_log_from"]         = $member_log_id;
            $insert_bonus_log["member_log_to"]           = $referral_bonus;
            $insert_bonus_log["referral_bonus_log_date"] = Carbon::now('Asia/Manila');

            $referral_bonus_log = Tbl_referral_bonus_log::insert($insert_bonus_log);
        }

    }

    public static function updateExpiredRequestBitcoinCashIn()
    {
        $member_logs = Tbl_member_log::JoinBitcoinCashIn(0, 'pending', 'automatic_cash_in')->get();

        foreach ($member_logs as $key => $member_log) 
        {
            $expiration_date = date_format(new DateTime($member_log['expiration_date']), "Y-m-d");
            $date_now = date_format(new DateTime(), "Y-m-d");

            if($expiration_date < $date_now)
            {
                $update['log_status'] = "rejected";
                Tbl_member_log::where('member_log_id', $member_log['member_log_id'])->update($update);
            }
        }
    }

    public static function scheduleCheckSystemMemberBitcoin()
    {
        $members = Tbl_member_address::JoinCoin(0,'bitcoin')->get();
        foreach ($members as $key => $member) 
        {
            Self::checkBalanceBTC($member['member_id']);
        }
    }

    public static function convertSatoshiToBitcoin($satoshi_amount)
    {
        $_return = $satoshi_amount / 100000000;

        return $_return;
    }

    public static function sendBTCCentralWallet($member_address_id, $member_address_to, $amount, $fee = 10000)
    {
        if ($member_address_id == 0) // send from central wallet
        {
            $address_info = Tbl_central_wallet::where('coin_name', 'bitcoin')->first();
            $passkey = Crypt::decryptString($address_info->address_api_password);
            $guid = $address_info->address_api_reference;
            $address_from = $address_info->address_wallet;
        }
        else // send from member wallet
        {
            $address_info = Tbl_member_address::where("member_address_id", $member_address_id)->first();
            $passkey = Crypt::decryptString($address_info->address_api_password);
            $guid = $address_info->guid;
            $address_from = $address_info->member_address;
        }

        $url = 'http://206.189.155.9:3000/merchant/' . $guid . "/payment";

        $post["password"]   = $passkey;
        $post["to"]         = $member_address_to;
        $post["amount"]     = $amount;
        $post["from"]       = $address_from;
        $post["fee"]        = $fee;

        $myvars = http_build_query($post);
        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        
        $json_feed = json_decode($response);
       
        /* STORE BTC VALUE */
        $return = new stdClass();
        $balance = @($json_feed->balance);
        
        return $json_feed;
    }

    /*Send all member wallet btc to cental wallet*/
    public static function sendActualBTCWalletToCentralWallet($member_address_id, $amount)
    {
        $btc_wallet = Tbl_member_address::where("member_address_id", $member_address_id)->first();
        // $btc_central_wallet = Tbl_central_wallet::where('coin_name', 'bitcoin')->first();
        
        $update                         = null;
        $update['address_balance']      = @(Self::get_blockchain_bitcoin_balance($btc_wallet->guid, $btc_wallet->address_api_password) / 100000000);
        
        if ($update['address_balance'] != 0 && $update['address_balance'] > 10000) 
        {  
            Self::sendBTCCentralWallet($btc_wallet->member_address_id, '1EP1meuhnErMF11SywRWV6LduSJ94b7XHq', $amount, 10000);
            Tbl_member_address::where('member_address_id', $btc_wallet->member_address_id)->update($update);
        }
    }

}