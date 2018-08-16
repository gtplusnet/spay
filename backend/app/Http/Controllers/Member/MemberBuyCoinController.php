<?php
namespace App\Http\Controllers\Member;
use App\Http\Controllers\Controller;

use Request;
use Redirect;
use Input;

use App\Globals\Coin;
use App\Globals\CashIn;
use App\Globals\Member_log;
use App\Tbl_member_address;
use App\Tbl_member_log;
use App\Tbl_bitcoin_cash_in;
use Carbon\Carbon;
use QrCode;

use Illuminate\Http\Request as Request2;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class MemberBuyCoinController extends MemberController
{
    public function index()
    {
        $data["Page"]     = "Buy Coin";
        $data["btc_rate"] = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("bitcoin"));
        $data["eth_rate"] = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("ethereum"));
        $data["php_rate"] = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("peso"));
        $data["_coin"]    = Coin::getList();
        $data["aba_id"]   = Coin::getABAId();

        return view ("member.buy_coin", $data);
    }

    public function submit(Request2 $request)
    {
        if (Request::input("from") == "bank_deposit") 
        {
            /* Transaction required */
            if (!Request::input("cash_in_reference_number")) 
            {
                return Redirect::to("/member/buy_coin")->with('error', 'Reference number is required.');
            }

            /* Upload Image */
            $file = $request->file('cash_in_proof_image');

            if ($file) 
            {
                $path_prefix  = 'https://aeolus-storage.sgp1.digitaloceanspaces.com/';
                $path         = "allbyall/proof";
                $storage_path = storage_path();

                if ($request->file('cash_in_proof_image')->isValid())
                {
                    $image_path = $path_prefix . Storage::putFile($path, $request->file('cash_in_proof_image'), "public");
                }
                else
                {
                    return Redirect::to("/member/buy_coin")->with('error', 'Proof of image is not valid.');
                }
            }
            else
            {
                return Redirect::to("/member/buy_coin")->with('error', 'Proof of image is required.');
            }
            
            /* Insert Member Log */
            $member_log    = Member_log::insert(Request::input(), $this->member->id, "allbyall");
            $cash_in_proof = CashIn::insertCashInProof(Request::input(), $member_log, $image_path, $this->member->id);
        }
        elseif (Request::input("from") == "eth")
        {
            /* Insert Member Log */
            $member_log  = Member_log::insert(Request::input(), $this->member->id, "allbyall");
            $cash_in_eth = CashIn::insertCashInEth(Request::input(), $member_log, $this->member->id);
        }

        return Redirect::to('/member/dashboard');
    }

    // POPUPS

    /* PHP */
    public function buy_via_bankdeposit()
    {
        $data["Page"]     = "Buy Coin via Bank Deposit";
        $data["currency"] = strtoupper(Request::input("payment_currency"));
        $data["rate"]     = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName(Request::input("payment_currency")));
        $data["_method"]  = CashIn::getList();
        
        return view ("popups.buy_via_bankdeposit", $data);
    }

    public function confirm_buy_via_bankdeposit()
    {
        $data["Page"]   = "Confirm Buy Coin via Bank Deposit";
        $data["method"] = CashIn::get(Request::input("payment_method"));
        $data["rate"]     = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName(Request::input("payment_currency")));
        
        return view ("popups.confirm_buy_via_bankdeposit", $data);
    }

    public function complete_buy_via_bankdeposit()
    {
        $data["Page"] = "Complete Buy Coin via Bank Deposit";
        return view ("popups.complete_buy_via_bankdeposit", $data);
    }

    /* Bitcoin */
    public function buy_via_btc_send()
    {
        $member_id = $this->member->id;
        $data['btc_wallet_info'] = Tbl_member_address::JoinUserCoin($member_id, 'bitcoin')->first();

        $data["Page"] = "Buy Coin via BTC";

        // dd($data);

        return view ("popups.buy_via_btc_send", $data);
    }

    public function buy_via_btc_transaction()
    {
        $data["Page"] = "Confirm Buy Coin via BTC";
        return view ("popups.buy_via_btc_transaction", $data);
    }

    public function buy_via_btc_request()
    {
        $aba_wallet_info = Tbl_member_address::JoinUserCoin($this->member->id, 'allbyall')->first();
       
       /*Reject pending automatic payment via Bitcoin*/
       $update['log_status']           = 'rejected';
       Tbl_member_log::where('member_address_id', $aba_wallet_info['member_address_id'])->where('log_method','automatic_cash_in')->where('log_status','pending')->update($update);

        /*Insert new record in tbl_member_log*/
        $insert['member_address_id']    = $aba_wallet_info['member_address_id'];
        $insert['log_type']             = "transfer";
        $insert['log_mode']             = "receive";
        $insert['log_amount']           = Request::input("aba_coin_value");
        $insert['log_transaction_fee']  = 0;
        $insert['log_net_amount']       = Request::input("aba_coin_value");
        $insert['log_time']             = Carbon::now('Asia/Manila');
        $insert['log_message']          = "bitcoin";
        $insert['log_method']           = "automatic_cash_in";

        $member_log_id = Tbl_member_log::insertGetId($insert);

        $insert = null;

        /*Insert bitcoin cash in*/
        $insert['member_log_id']        = $member_log_id;
        $insert['bitcoin_to_aba_rate']  = Request::input('btc_rate');
        $insert['amount_requested']     = Request::input('aba_coin_value');
        $insert['expiration_date']      = Carbon::now('Asia/Manila')->addDays(2);
        
        Tbl_bitcoin_cash_in::insert($insert);

        $_return['status'] = "success";

        return json_encode($_return);
    }

    /* Ethereum */
    public function buy_via_eth_send()
    {
        $data["Page"]     = "Send ETH";
        $payment_currency = Request::input("payment_currency");
        $payment_coin     = Request::input("payment_coin");
        $data["rate"]     = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName($payment_currency));

        return view ("popups.buy_via_eth_send", $data);
    }

    public function buy_via_eth_transaction()
    {
        $data["Page"]     = "Transaction Number";
        $payment_currency = Request::input("payment_currency");
        $payment_coin     = Request::input("payment_coin");
        $data["rate"]     = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName($payment_currency));

        return view ("popups.buy_via_eth_transaction", $data);
    }
}
