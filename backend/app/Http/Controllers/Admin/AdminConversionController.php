<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Request;
use App\Globals\Authenticator;
use Redirect;
use App\Globals\Seed;
use App\Globals\Coin;

class AdminConversionController extends Controller
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

    public function index()
    {
    	/* Seed Coin */
    	Seed::coin();

    	/* Get Coin List */
        $data["_coin"] = Coin::getListWithLOKConversion();
       
        /* Get ABA Coin ID */
        $data["aba_id"] = Coin::getLOKId();

        /* Get Sale Stage List */
        $data["_sale_stage"] = Coin::getSaleStageList();
       
        /* Get Bonus Coin per Sale Stage List */
        $data["_sale_stage_bonus"] = Coin::getBonusSaleStageList();

        /* Return View */
        return json_encode($data);
    }

    public function sale_stage_update()
    {
        $sale_stage = Request::input('sale_stage');
        $_sale_stage_bonus = Request::input('sale_stage_bonus');
        $_coin_conversion  = Request::input('_coin');

        Coin::updateCoinConversion($_coin_conversion);
        $return = Coin::updateSaleStage($sale_stage, $_sale_stage_bonus);
        
        return json_encode($return);
    }

    public function submit()
    {
        /* Insert / Update Convert */
		$convert = Request::input("convert");
		Coin::putConvert($convert);

        /* Insert / Update Sale Stage */
        $sale_stage = Request::input("sale_stage");
        Coin::insertSaleStage($sale_stage);
    	
    	return Redirect::to("/admin/conversion");
    }
}
