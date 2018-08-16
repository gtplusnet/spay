<?php
namespace App\Globals;
use App\Globals\Wallet;
use App\Globals\Reward;
use App\Tbl_lend;
use App\Tbl_member;
use App\Tbl_member_tree;
use App\Tbl_lend_config;
use App\Tbl_lend_reward;
use App\Tbl_interest_rate;
use stdClass;
use Carbon\Carbon;

class Lend
{
	public static function setupLend()
	{
		if(!Tbl_lend_config::first())
		{
			Self::setupLendConfig();
		}
	}

    public static function getConfig()
    {
        return Tbl_lend_config::get();
    }
	public static function lendWallet($member_id, $coin_id, $amount)
	{
		$error = false;

        /* VALIDATION FOR ENOUGH WALLET */
        if(!Wallet::validateWallet($member_id, $coin_id, $amount))
        {
            $error = "You don't have enough ATK to proceed with this transaction.";
        }

        if($amount < 0)
        {
            $error = "Invalid Value for Zero or Less";
        }

		if($error)
		{
			return $error;
		}
		else
		{
            /* DEDUCT MEMBER WALLET */
            Wallet::transferWallet($member_id, 1, $coin_id, $amount);

            /* DETERMINE LEND TIER */
            $lend_config                = Tbl_lend_config::where("price_range", ">", $amount)->orderBy("price_range", "asc")->first();

            if(!$lend_config)
            {
                return "Amount not included in the package";
            }
            else
            {
                /* INSERT LEND AMOUNT */
                $insert["lend_amount"]      = $amount * 100000000;
                $insert["lend_date"]        = Carbon::now();
                $insert["lend_last_compute"]= Carbon::now();
                $insert["locked_until"]     = Carbon::now()->addDays($lend_config->lock_in_days);
                $insert["lend_by"]          = $member_id;
                $insert["lend_interest"]    = $lend_config->monthly_interest;
                $insert["lend_config"]      = $lend_config->lend_config_id;

                $lend_id = Tbl_lend::insertGetId($insert);

                Reward::unilevelLendReward($lend_id);
            }

            Self::recomputeMemberLend($member_id);

			return true;
		}

		return "an error occurred";
	}

    public static function recomputeMemberLend($member_id)
    {
        $update["total_lend"]       = Tbl_lend::where("lend_by", $member_id)->sum("lend_amount");
        $update["current_lend"]     = Tbl_lend::where("lend_by", $member_id)->where("lend_active", 1)->sum("lend_amount");

        Tbl_member::where("member_id", $member_id)->update($update);
    }

    public static function getTotalLendPerLevel($member_id, $level)
    {
        return Tbl_member_tree::where("parent_id", $member_id)->where("level", $level)->child()->sum("total_lend");
    }

    public static function getLogsPerLevel($member_id, $level)
    {
        return Tbl_member_tree::where("parent_id", $member_id)->where("level", $level)->childLend()->get();
    }

    public static function getLendReward($lend_id)
    {
        return Tbl_lend_reward::where("lend_id", $lend_id)->sum("lend_reward_amount");
    }

    public static function computeLendInterest($lend_id)
    {
        $lend = Tbl_lend::where("lend_id", $lend_id)->first();

        $last_compute_date  = Carbon::parse($lend->lend_last_compute);
        $today_date         = Carbon::parse(date("Y-m-d", time()));

        while($last_compute_date != $today_date)
        {
            $last_compute_date->addDay();
            $day_lend_rate  = Tbl_interest_rate::where("day_number", $last_compute_date->format("d"))->value("day_rate");
      
            $lend_amount    = $lend->lend_amount;
            $lend_interest  = $lend->lend_interest;
            $reward_rate    = $day_lend_rate * ($lend_interest / 100) / 31;
            $reward_amount  = $lend_amount * $reward_rate / 100;

            $insert_lend_reward["lend_reward_rate"]         = $reward_rate;
            $insert_lend_reward["lend_reward_amount"]       = $reward_amount;
            $insert_lend_reward["lend_reward_date"]         = $last_compute_date->format("Y-m-d");
            $insert_lend_reward["lend_reward_timestamp"]    = Carbon::now();
            $insert_lend_reward["lend_id"]                  = $lend->lend_id;

            Tbl_lend_reward::insert($insert_lend_reward);

            $update["lend_last_compute"] = $last_compute_date->format("Y-m-d");
            Tbl_lend::where("lend_id", $lend->lend_id)->update($update);

            Wallet::transferWallet(1, $lend->lend_by, 2, $reward_amount / 100000000, "Lend Reward");
            Self::recomputeMemberLendReward($lend->lend_by);
        }
    }

    public static function recomputeMemberLendReward($member_id)
    {
        $update["lending_reward"] = Tbl_lend_reward::where("lend_by", $member_id)->lend()->sum("lend_reward_amount");
        Tbl_member::where("member_id", $member_id)->update($update);
    }

	public static function setupLendConfig()
	{
    	$insert["price_range"] 		= 100;
    	$insert["monthly_interest"] = 10;
    	$insert["lock_in_days"] 	= 210;
    	$insert["level_1"] 			= 0;
    	$insert["level_2"] 			= 0;
    	$insert["level_3"] 			= 0;
    	$insert["level_4"] 			= 0;
    	$insert["level_5"] 			= 0;
    	$insert["level_6"] 			= 0;
    	$insert["level_7"] 			= 0;
    	$insert["level_8"] 			= 0;
    	$insert["level_9"] 			= 0;
    	$insert["level_10"] 		= 0;
    	$inserts[] 					= $insert;

    	$insert["price_range"] 		= 1000;
    	$insert["monthly_interest"] = 25;
    	$insert["lock_in_days"] 	= 180;
    	$insert["level_1"] 			= 2.5;
    	$insert["level_2"] 			= 1;
    	$insert["level_3"] 			= 0;
    	$insert["level_4"] 			= 0;
    	$insert["level_5"] 			= 0;
    	$insert["level_6"] 			= 0;
    	$insert["level_7"] 			= 0;
    	$insert["level_8"] 			= 0;
    	$insert["level_9"] 			= 0;
    	$insert["level_10"] 			= 0;
    	$inserts[] 					= $insert;

    	$insert["price_range"] 		= 5000;
    	$insert["monthly_interest"] = 30;
    	$insert["lock_in_days"] 	= 150;
    	$insert["level_1"] 			= 2;
    	$insert["level_2"] 			= 1;
    	$insert["level_3"] 			= 1;
    	$insert["level_4"] 			= 1;
    	$insert["level_5"] 			= 1;
    	$insert["level_6"] 			= 1;
    	$insert["level_7"] 			= 1;
    	$insert["level_8"] 			= 1;
    	$insert["level_9"] 			= 1;
    	$insert["level_10"] 		= 1;
    	$inserts[] 					= $insert;

    	$insert["price_range"] 		= 10000;
    	$insert["monthly_interest"] = 35;
    	$insert["lock_in_days"] 	= 120;
    	$insert["level_1"] 			= 5;
    	$insert["level_2"] 			= 2;
    	$insert["level_3"] 			= 1;
    	$insert["level_4"] 			= 1;
    	$insert["level_5"] 			= 1;
    	$insert["level_6"] 			= 1;
    	$insert["level_7"] 			= 1;
    	$insert["level_8"] 			= 1;
    	$insert["level_9"] 			= 1;
    	$insert["level_10"] 		= 1;
    	$inserts[] 					= $insert;


    	$insert["price_range"] 		= 100000;
    	$insert["monthly_interest"] = 40;
    	$insert["lock_in_days"] 	= 90;
    	$insert["level_1"] 			= 5;
    	$insert["level_2"] 			= 2;
    	$insert["level_3"] 			= 1;
    	$insert["level_4"] 			= 1;
    	$insert["level_5"] 			= 1;
    	$insert["level_6"] 			= 1;
    	$insert["level_7"] 			= 1;
    	$insert["level_8"] 			= 1;
    	$insert["level_9"] 			= 1;
    	$insert["level_10"] 		= 1;
    	$inserts[] 					= $insert;

        Tbl_lend_config::insert($inserts);
	}
}