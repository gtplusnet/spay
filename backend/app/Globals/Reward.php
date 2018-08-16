<?php
namespace App\Globals;
use App\Tbl_member_tree;
use App\Tbl_member;
use App\Tbl_lend;
use App\Tbl_lend_config;
use App\Tbl_unilevel_reward;
use Carbon\Carbon;


class Reward
{
	public static function createTree($member_id, $registree_id, $level)
	{
		$member_info 	= Tbl_member::where("member_id", $member_id)->first();
		
		if($member_info->sponsor != 0)
		{
			$insert["parent_id"] 	= $member_info->sponsor;
			$insert["child_id"] 	= $registree_id;
			$insert["level"]		= $level;
			Tbl_member_tree::insert($insert);
			Self::createTree($member_info->sponsor, $registree_id, $level+1);
		}
	}
	public static function unilevelLendReward($lend_id)
	{
		$lend 			= Tbl_lend::where("lend_id", $lend_id)->first();
		$lend_by 		= Tbl_member::where("member_id", $lend->lend_by)->first();

		/* TODO: INSERT CODE TO DETERMINE REWARD SETTING BASED ON CURRENT LEND */
		$_tree 			= Tbl_member_tree::where("child_id", $lend_by->member_id)->get();

		foreach($_tree as $tree)
		{
			$parent 		= Tbl_member::where("member_id", $tree->parent_id)->first();
			$lend_config    = Tbl_lend_config::where("price_range", ">", ($parent->current_lend / 100000000))->orderBy("price_range", "asc")->first();
			$level			= "level_" . $tree->level;

			if(isset($lend_config->$level))
			{
				$unilevel_income					= $lend->lend_amount * ($lend_config->$level / 100);
				$insert["unilevel_reward_amount"] 	= $unilevel_income;
				$insert["unilevel_current_lend"] 	= $parent->current_lend;
				$insert["unilevel_reward_date"] 	= Carbon::now();
				$insert["unilevel_reward_level"] 	= $tree->level;
				$insert["unilevel_reward_message"] 	= "Congratulations! Since you have a current investment of " . number_format($parent->current_lend, 2) . ", you've earned " . number_format($unilevel_income/100000000) . " ATK which is " . number_format($lend_config->$level) . "% of " . number_format($lend->lend_amount/100000000, 2) . "  because someone invested on your " . Self::addOrdinalNumberSuffix($tree->level) . " level.";
				$insert["unilevel_reward_to"] 		= $parent->member_id;
				$insert["unilevel_lend_id"] 		= $lend->lend_id;
				$insert["unilevel_lended_amount"] 	= $lend->lend_amount;
				$insert["unilevel_percentage"] 		= $lend_config->$level;
				$insert["unilevel_reward_tier"] 	= $lend_config->lend_config_id;
				Tbl_unilevel_reward::insert($insert);

				/* TRANSFER REWARD WALLET */
				Wallet::transferWallet(1, $parent->member_id, 2, $unilevel_income/100000000, "Unilevel Reward");

				/* RECOMPUTE REWARD */
				Reward::recomputeUnilevelLendReward($parent->member_id);
			}
			else
			{
				abort(403, "Unilevel Exceeds Parameters (" . $level . ")");
			}
		}
	}
	public static function recomputeUnilevelLendReward($member_id)
	{
        $update["unilevel_reward"] = Tbl_unilevel_reward::where("unilevel_reward_to", $member_id)->sum("unilevel_reward_amount");
        Tbl_member::where("member_id", $member_id)->update($update);
	}
	public static function addOrdinalNumberSuffix($num)
	{
		if (!in_array(($num % 100),array(11,12,13)))
		{
			switch ($num % 10)
			{
				// Handle 1st, 2nd, 3rd
				case 1:  return $num.'st';
				case 2:  return $num.'nd';
				case 3:  return $num.'rd';
			}
		}

		return $num.'th';
	}

	public static function countMemberLevel($member_id, $level)
	{
		return Tbl_member_tree::where("parent_id", $member_id)->where("level", $level)->count();
	}
	public static function getUnilevelLogsPerLevel($member_id, $level)
	{
		return Tbl_unilevel_reward::where("unilevel_reward_level", $level)->where("unilevel_reward_to", $member_id)->lendMember()->orderBy("unilevel_reward_id", "desc")->get();
	}
	public static function getMemberOnLevel($member_id, $level)
	{
		$_member = Tbl_member_tree::where("parent_id", $member_id)->where("level", $level)->child()->get();
		$__member = null;

		foreach($_member as $key => $member)
		{
			$__member[$key] = $member;
			$__member[$key]->current_lend_format = Wallet::formatUnsatoshi($member->current_lend, "ATK");
		}

		return $__member;
	}
	public static function getTotalUnilevelRewardLevel($member_id, $level)
	{
		return Tbl_unilevel_reward::where("unilevel_reward_level", $level)->where("unilevel_reward_to", $member_id)->sum("unilevel_reward_amount");
	}
}