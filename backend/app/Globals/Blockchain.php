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
use App\Tbl_main_wallet_addresses;
use App\Tbl_User;
use App\Tbl_position_requirements;
use App\Tbl_release_logs;
use App\Globals\Wallet;
use App\Globals\Member_log;
use Illuminate\Support\Facades\Crypt;
use SSH;
class Blockchain
{
    public static function generate_blockchain_bitcoin_address($passkey)
    {
        try {
            $ch = curl_init();
            $api_code = "1da456d5-f176-%E2%80%8E4997-8105-2f95a4f95cfd";
            if (FALSE === $ch)
                throw new \Exception('failed to initialize');

            curl_setopt($ch, CURLOPT_URL, "http://128.199.209.141:3000/api/v2/create?password=".$passkey."&api_code=".$api_code);
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

            curl_setopt($ch, CURLOPT_URL, "http://128.199.209.141:3000/merchant/". $guid ."/balance?password=" . $address_api_password);
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

            curl_setopt($ch, CURLOPT_URL, "http://128.199.209.141:3000/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&fee=$fee");
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
        //http://128.199.209.141:3000/merchant/$guid/payment?password=$main_password&second_password=$second_password&to=$address&amount=$amount&from=$from&fee=$fee
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

    public static function checkBalanceBTC($member_id, $sale_stage_id, $token_name = 'Successmall')
    {
        // wallet information
        $btc_wallet_info        = Tbl_member_address::JoinCoin($member_id, 'bitcoin')->first();
        $token_wallet_info      = Tbl_member_address::JoinOther()->JoinCoin($member_id, $token_name)->first();

        //set member id variable
        $token_ma_id = $token_wallet_info['member_address_id'];
        $btc_ma_id = $btc_wallet_info['member_address_id'];

        // check btc actual wallet balance
        $btc_actual_balance = Self::get_blockchain_bitcoin_balance($btc_wallet_info['guid'], $btc_wallet_info['address_api_password']);
        
        // crypto conversion
        $cc = 100000000;
        $btc_value = $btc_actual_balance > 0 ? $btc_actual_balance/$cc : 0;

        if ( $btc_value != $btc_wallet_info['address_actual_balance'] )
        {
            // check if there is pending transaction
            $transaction = Tbl_member_log::JoinAutomaticCashIn($token_ma_id, 'pending', 'Bitcoin')->first();

            // check address actual balance deductions
            if($btc_value < $btc_wallet_info['address_actual_balance'])
            {
                $update_member_btc_wallet['address_actual_balance'] = $btc_value;
                Tbl_member_address::where('member_address_id', $btc_ma_id)->update($update_member_btc_wallet);
            }
            else if($transaction)
            {
                // to be added btc to wallet
                $btc_addition = $btc_value - $btc_wallet_info['address_actual_balance'];
                // update to latest btc actual balance
                Tbl_member_address::where('member_address_id', $btc_ma_id)->update(["address_actual_balance" => $btc_value]);

                // check discount then compute token addition
                $payment_discount = $transaction['sale_stage_discount'] > 0 ? $transaction['sale_stage_discount']/100 : 0;

                if($payment_discount > 0)
                {
                    $token_addition = $btc_addition / ($transaction['exchange_rate'] - ($transaction['exchange_rate'] * $payment_discount));
                }
                else
                {
                    $token_addition = $btc_addition / $transaction['exchange_rate'];
                }

                // update transaction
                $update_transaction['log_amount']           = $token_addition;
                $update_transaction['log_net_amount']       = $token_addition;
                $update_transaction['log_method']           = "Bitcoin Total";
                $update_transaction['log_time']             = Carbon::now('Asia/Manila');
                $update_transaction['log_status']           = 'accepted';
                Tbl_member_log::where('member_log_id', $transaction['member_log_id'])->update($update_transaction);

                // compute sale stage bonus
                if($sale_stage_id)
                {
                    $round = round($token_addition);
                    $ss_bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_from", "<=", $round)->where("buy_coin_bonus_to", ">=", $round)->first();
                    
                    if(!$ss_bonus)
                    {
                        $higher_amount = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_to", "<", $round)->orderBy("buy_coin_bonus_to", "desc")->first();

                        if($higher_amount)
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = $higher_amount->buy_coin_bonus_percentage;
                        }
                        else
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = 0;
                        }
                    }
                    else
                    {
                        $update_bonus_percentage['sale_stage_bonus'] = $ss_bonus->buy_coin_bonus_percentage;
                    }

                    Tbl_automatic_cash_in::where("member_log_id", $transaction['member_log_id'])->update($update_bonus_percentage);

                    $final_bonus = $update_bonus_percentage['sale_stage_bonus'];

                    if($final_bonus > 0)
                    {
                        $token_bonus_percentage  = $final_bonus/100; 

                        Self::recordBuyBonus($token_addition, $token_bonus_percentage, $transaction['member_log_id'], $token_ma_id, "Bitcoin");
                    }
                }
                
                // dd($token_wallet_info);
                if($token_wallet_info['referrer_id'])
                {
                    // dd($token_wallet_info);
                    Self::recordReferralBonus($token_wallet_info['member_id'], $token_addition, $transaction['member_log_id'], "Bitcoin");
                }

                /*insert log for btc wallet*/
                $data["payment_coin"]   = $btc_addition;
                $data["received_token"] = $token_addition;
                $data["log_status"]     = "accepted";
                Member_log::insert($data, $token_wallet_info['member_id'], 'bitcoin');

                $accepted["member"] = Tbl_User::where("id", $member_id)->first();
                $accepted["amount"] = $token_addition;
                $accepted["record"] = Tbl_automatic_cash_in::where("member_log_id", $transaction['member_log_id'])->first();

                $member_info = Tbl_other_info::where("user_id", $member_id)->first();
                if($member_info->member_position_id != 1)
                {
                    $member_info = Tbl_other_info::where("user_id", $member_id)->update(["first_buy"=>1]);
                }
                // Mails::order_accepted($accepted);
            }
        }
        Wallet::recomputeWallet($btc_ma_id);
        Wallet::recomputeWallet($token_ma_id);
    }

    public static function checkBalanceETH($member_id, $sale_stage_id, $token_name = 'Successmall')
    {
        // wallet information
        $eth_wallet_info        = Tbl_member_address::JoinCoin($member_id, 'ethereum')->first();
        $token_wallet_info      = Tbl_member_address::JoinOther()->JoinCoin($member_id, $token_name)->first();

        //set member id variable
        $token_ma_id = $token_wallet_info['member_address_id'];
        $eth_ma_id = $eth_wallet_info['member_address_id'];

        // check eth actual wallet balance
        $eth_actual_balance = Self::get_blockchain_ethereum_balance($eth_wallet_info['member_address']);
        
        // crypto conversion
        $cc = 1000000000000000000;
        $eth_value = $eth_actual_balance->balance > 0 ? $eth_actual_balance->balance/$cc : 0;

        if ( $eth_value != $eth_wallet_info['address_actual_balance'] )
        {
            // check if there is pending transaction
            $transaction = Tbl_member_log::JoinAutomaticCashIn($token_ma_id, 'pending', 'Ethereum')->first();

            // check address actual balance deductions
            if($eth_value < $eth_wallet_info['address_actual_balance'])
            {
                $update_member_eth_wallet['address_actual_balance'] = $eth_value;
                Tbl_member_address::where('member_address_id', $eth_ma_id)->update($update_member_eth_wallet);
            }
            else if($transaction)
            {
                // to be added eth to wallet
                $eth_addition = $eth_value - $eth_wallet_info['address_actual_balance'];

                // update to latest eth actual balance
                Tbl_member_address::where('member_address_id', $eth_ma_id)->update(["address_actual_balance" => $eth_value]);

                // check discount then compute token addition
                $payment_discount = $transaction['sale_stage_discount'] > 0 ? $transaction['sale_stage_discount']/100 : 0;
                if($payment_discount > 0)
                {
                    $token_addition = $eth_addition / ($transaction['exchange_rate'] - ($transaction['exchange_rate'] * $payment_discount));
                }
                else
                {
                    $token_addition = $eth_addition / $transaction['exchange_rate'];
                }

                // update transaction
                $update_transaction['log_amount']           = $token_addition;
                $update_transaction['log_net_amount']       = $token_addition;
                $update_transaction['log_method']           = "Ethereum Total";
                $update_transaction['log_time']             = Carbon::now('Asia/Manila');
                $update_transaction['log_status']           = 'accepted';
                Tbl_member_log::where('member_log_id', $transaction['member_log_id'])->update($update_transaction);

                // compute sale stage bonus
                if($sale_stage_id)
                {
                    $round = round($token_addition);
                    $ss_bonus = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_from", "<=", $round)->where("buy_coin_bonus_to", ">=", $round)->first();

                    if(!$ss_bonus)
                    {
                        $higher_amount = Tbl_sale_stage_bonus::where("sale_stage_id", $sale_stage_id)->where("buy_coin_bonus_to", "<", $round)->orderBy("buy_coin_bonus_to", "desc")->first();

                        if($higher_amount)
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = $higher_amount->buy_coin_bonus_percentage;
                        }
                        else
                        {
                            $update_bonus_percentage['sale_stage_bonus'] = 0;
                        }
                    }
                    else
                    {
                        $update_bonus_percentage['sale_stage_bonus'] = $ss_bonus->buy_coin_bonus_percentage;
                    }

                    Tbl_automatic_cash_in::where("member_log_id", $transaction['member_log_id'])->update($update_bonus_percentage);

                    $final_bonus = $update_bonus_percentage['sale_stage_bonus'];

                    if($final_bonus > 0)
                    {
                        $token_bonus_percentage  = $final_bonus/100; 

                        Self::recordBuyBonus($token_addition, $token_bonus_percentage, $transaction['member_log_id'], $token_ma_id, "Ethereum");
                    }
                }

                if($token_wallet_info['referrer_id'])
                {
                    Self::recordReferralBonus($token_wallet_info['member_id'], $token_addition, $transaction['member_log_id'], "Ethereum");
                }

                /*insert log for eth wallet*/
                $data["payment_coin"]   = $eth_addition;
                $data["received_token"] = $token_addition;
                $data["log_status"]     = "accepted";
                Member_log::insert($data, $token_wallet_info['member_id'], 'ethereum');

                $accepted["member"] = Tbl_User::where("id", $member_id)->first();
                $accepted["amount"] = $token_addition;
                $accepted["record"] = Tbl_automatic_cash_in::where("member_log_id", $transaction['member_log_id'])->first();

                $member_info = Tbl_other_info::where("user_id", $member_id)->first();
                if($member_info->member_position_id != 1)
                {
                    $member_info = Tbl_other_info::where("user_id", $member_id)->update(["first_buy"=>1]);
                }
                Mails::order_accepted($accepted);
            }
        }
        Wallet::recomputeWallet($eth_ma_id);
        Wallet::recomputeWallet($token_ma_id);
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
        if($referrer)
        {
            // dd($user_id, $lok_amount, $member_log_id, $payment_type, $invitee, $_referral, $referrer, $referrer_info, $lok_address_id);
            $after_purchase = $referrer->after_purchase_commission <= 0 ? 0 : $referrer->after_purchase_commission/100;
            $before_purchase = $referrer->commission <= 0 ? 0 : $referrer->commission/100;
            $referrer_bonus_percentage = $referrer_info->first_buy ? $after_purchase : $before_purchase;
            $referral_bonus_token = $lok_amount * $referrer_bonus_percentage;
            // dd($referrer, $after_purchase, $before_purchase, $referrer_bonus_percentage, $referral_bonus_token);
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

    public static function sendBTCCentralWallet($member_address_id, $member_address_to, $amount, $fee = 0)
    {
        
        // $fee = ;
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

        // dd($member_address_id, $member_address_to, $amount, $fee, $address_info, $passkey, $guid, $address_from);
        $url = 'http://128.199.209.141:3000/merchant/' . $guid . "/payment";

        $post["password"]   = $passkey;
        $post["to"]         = $member_address_to;
        $post["amount"]     = $amount - floor($fee);
        $post["from"]       = $address_from;
        $post["fee"]        = floor($fee);
        $myvars = http_build_query($post);
        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        
        $json_feed = json_decode($response);
        // dd($post, $url, $myvars, $ch, $response, $json_feed);
        // dd($json_feed, $response, $ch, $myvars, $post, $url, $address_from, $guid, $passkey, $address_info);
        /* STORE BTC VALUE */
        $balance = @($json_feed->balance);
        // dd($post, $response, $json_feed, $return, $balance, $amount);
        if($json_feed)
        {
            $return["data"] = $json_feed;
            $return["status"]    = "success";
            $return["status_message"]    = "Bitcoin has been sent";

            $insert["release_type"] = "BTC";
            $insert["release_amount"] = $json_feed->amounts[0] != 0 ? $json_feed->amounts[0]/100000000 : 0;
            $insert["release_fee"] =  $json_feed->fee != 0 ? $json_feed->fee/100000000 : 0;
            $insert["released_tx_hash"] = $json_feed->tx_hash;
            $insert["released_from"] = $json_feed->from[0];
            $insert["released_to"] = $json_feed->to[0];
            $insert["date_released"] = Carbon::now();

            Tbl_member_address::where("member_address_id", $member_address_id)->update(["address_actual_balance" => 0]);
            Tbl_release_logs::insert($insert);
        }
        else
        {
            $return["status"]   = "error";
            $return["status_message"]   = "Unexpected error, Please try again.";
        }

        return $return;
    }

    /*Send all member wallet btc to cental wallet*/
    // public static function sendActualBTCWalletToCentralWallet($member_address_id, $amount)
    // {
    //     $btc_wallet = Tbl_member_address::where("member_address_id", $member_address_id)->first();
    //     // $btc_central_wallet = Tbl_central_wallet::where('coin_name', 'bitcoin')->first();
        
    //     $update                         = null;
    //     $update['address_balance']      = @(Self::get_blockchain_bitcoin_balance($btc_wallet->guid, $btc_wallet->address_api_password) / 100000000);
        
    //     if ($update['address_balance'] != 0 && $update['address_balance'] > 10000) 
    //     {  
    //         $central_wallet = Tbl_central_wallet::first();
    //         if($central_wallet)
    //         {
    //             Self::sendBTCCentralWallet($btc_wallet->member_address_id, $central_wallet->central_wallet_address, $amount);
    //             Tbl_member_address::where('member_address_id', $btc_wallet->member_address_id)->update($update);
    //         }
            
    //     }
    // }

    public static function sendActualBTCWalletToCentralWallet($member_address_id, $amount, $receiver = null, $usd = null)
    {
        $btc_wallet = Tbl_member_address::where("member_address_id", $member_address_id)->first();
        // $btc_central_wallet = Tbl_central_wallet::where('coin_name', 'bitcoin')->first();
        // dd($btc_wallet, $amount, $member_address_id, $receiver, $usd);
        $update                         = null;
        $update['address_actual_balance']      = @(Self::get_blockchain_bitcoin_balance($btc_wallet->guid, $btc_wallet->address_api_password) / 100000000);
        if ($update['address_actual_balance'] > 0) 
        {  
            $mwallet = Tbl_main_wallet_addresses::where("mwallet_id", $receiver)->first();
            if($mwallet)
            {
                $fee = Self::calculateBTCFee($amount, $usd);
                $response = Self::sendBTCCentralWallet($btc_wallet->member_address_id, $mwallet->mwallet_address, $amount, $fee);
                // Tbl_member_address::where('member_address_id', $btc_wallet->member_address_id)->update($update);
                return $response;
            }
        }
    }

    public static function calculateBTCFee($amount = 0, $usd)
    {
        $churl = curl_init("https://bitcoinfees.earn.com/api/v1/fees/recommended");

        // Get cURL resource
        $curl = curl_init();
        // Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://bitcoinfees.earn.com/api/v1/fees/recommended',
        ));
        // Send the request & save response to $resp
        $resp = curl_exec($curl);
        // Close request to clear up some resources
        $resp = json_decode($resp);
        
        // $amount = $amount * 100000000;
        $amt = $amount * $usd;

        $median = 225 * $resp->halfHourFee;

        $rate = $amt*$median;
        // $original = $amount * 100000000;
        
        // $total = $original - $rate;
        curl_close($curl);
        // dd($amt, $rate, $original, $total);
        return $rate/100000000;

    }

    public static function sendActualETHWalletToCentralWallet($member_address_id, $amount, $receiver = null, $usd = null)
    {
        $eth_wallet = Tbl_member_address::where("member_address_id", $member_address_id)->first();
        $actual_balance       = @(Self::get_blockchain_ethereum_balance($eth_wallet->member_address)/1000000000000000000);
        if ($actual_balance >= 0) 
        {  
            $mwallet = Tbl_main_wallet_addresses::where("mwallet_id", $receiver)->first();
            if($mwallet)
            {
                $response = Self::eth_create_transaction($eth_wallet->member_address, $mwallet->mwallet_address, $amount);
                return $response;
            }
        }
    }

    public static function eth_create_transaction($sender, $receiver, $amt)
    {
        $api_code = "7e7ea4a09e96460b8b20c915a48bcfb6";

        $url = "https://api.blockcypher.com/v1/eth/main/txs/new?token=".$api_code;

        //fee calculation
        $gaslimit = 21000;
        $tx_fee = ($gaslimit * (5/$gaslimit))*1000000000000000000;

        $amt = $amt-$tx_fee;
        $amt = (int)$amt;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\n\t\"inputs\":\n\t\t[{\n\t\t\t\"addresses\": [\"".$sender."\"]\n\t\t}],\n\t\"outputs\":\n\t\t[{\n\t\t\t\"addresses\": [\"".$receiver."\"], \n\t\t\t\"value\": ".$amt."\n\t\t}]\n\t\n}",
          CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
            "Postman-Token: 12ec0fb8-a763-4fe6-be80-461db63636cf"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
           $return["message"] = "cURL Error #:" . $err;
        } else {

            $json_feed = json_decode($response);
            $funds = Tbl_member_address::where("member_address", $sender)->first();
            $pvkey = Crypt::decryptString($funds->address_api_password);
            // dd($json_feed, $pvkey, $response, $curl, $amt, $url, $api_code, $sender, $receiver, $amt);
            $sign_transaction = Self::eth_sign_transaction($json_feed, $pvkey);
        }

        return $sign_transaction;

        // $post["inputs"]["addresses"]             = [substr($sender, 2)];
        // $post["outputs"]["addresses"]            = [substr($receiver, 2)];
        // $post["value"]                           = ;

        // // $post["inputs"]             = 
        // // {
        // //     "addresses" : [substr($sender, 2)]
        // // };
        // // $post["outputs"]            = 
        // // {
        // //     "addresses" : [substr($receiver, 2)]
        // // };
        // // $post["value"]                           = $amt;
        // $myvars = http_build_query($post);
        // // $myvars = json_encode($myvars);

        // $ch = curl_init( $url );

        // curl_setopt( $ch, CURLOPT_POST, 1);
        // curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        // curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        // curl_setopt( $ch, CURLOPT_HEADER, 0);
        // curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        // $response = curl_exec($ch);
        
        // $json_feed = json_decode($response);

        // dd($post, $ch, $myvars, $response, $json_feed,$sender, $receiver, $amt);
    }

    public static function eth_sign_transaction($data, $pk)
    {
        $signer = "./signer ".$data->tosign[0]." ".$pk;
        $commands = ["cd btcutils/signer", $signer];
        
        $_data["tx"]         = $data->tx;
        $_data["tosign"]     = [$data->tosign[0]];

        SSH::into('production')->run($commands, function($line) use ($_data)
        {
            $line = str_replace("\n", "", $line);
            $_data["signatures"] = [$line];
            if($line)
            {
                $send_transaction = Self::eth_send_transaction($_data);
            }
        });
    }

    public static function eth_send_transaction($params)
    {
        $api_code = "7e7ea4a09e96460b8b20c915a48bcfb6";

        $url = "https://api.blockcypher.com/v1/eth/main/txs/send?token=".$api_code;

        $post["tx"]             = $params["tx"];
        $post["tosign"]         = $params["tosign"];
        $post["signatures"]     = $params["signatures"];

        $myvars = json_encode($post);
        $ch = curl_init( $url );

        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $json_feed = json_decode($response);
        $return["data"] = $json_feed;
        $return["status"] = "success";
        $return["status_message"] = "successfully released eth";

        $insert["release_type"] = "ETH";
        $insert["release_amount"] = $json_feed->tx->total != 0 ? $json_feed->tx->total/1000000000000000000 : 0;
        $insert["release_fee"] =  $json_feed->tx->fees != 0 ? $json_feed->tx->fees/1000000000000000000 : 0;
        $insert["released_tx_hash"] = $json_feed->tx->hash;
        $insert["released_from"] = "0x".$json_feed->tx->inputs[0]->addresses[0];
        $insert["released_to"] = "0x".$json_feed->tx->outputs[0]->addresses[0];
        $insert["date_released"] = Carbon::now();

        Tbl_member_address::where("member_address", $insert["released_from"])->update(["address_actual_balance" => 0]);
        Tbl_release_logs::insert($insert);
        return $return;

    }

    public static function calculateETHFee($amount = 0, $usd)
    {
        $gaslimit = 21000;
        $tx_fee = ($gaslimit * (5/$gaslimit));

        return $tx_fee;
    }

}