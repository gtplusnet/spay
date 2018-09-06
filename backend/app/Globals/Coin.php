<?php
namespace App\Globals;
use Carbon\Carbon;
use DB;

use App\Tbl_coin;
use App\Tbl_coin_conversion;
use App\Tbl_sale_stage;
use App\Tbl_sale_stage_bonus;

class Coin
{
	public static function getList()
	{
		$coin = Tbl_coin::get();

		return $coin;
	}

	public static function getListWithLOKConversion()
	{
		$coin = Coin::getList();
		
		$lok_id = Coin::getLOKId();
		
		foreach ($coin as $key => $value) 
		{
			$conversion = Tbl_coin_conversion::where("coin_from", $lok_id)->where("coin_to", $value->coin_id)->first();
			
			if ($conversion) 
			{
				$coin[$key]->conversion_multiplier = $conversion->conversion_multiplier;
			}
			else
			{
				//generate convertion
				$insert = null;
				$insert["coin_from"] = 	 $lok_id;
				$insert["coin_to"] =  $value->coin_id;
				$insert["conversion_multiplier"] = 	0;
				Tbl_coin_conversion::insert($insert);

				$coin[$key]->conversion_multiplier = 0;
			}
		}

		return $coin;
	}

	public static function getABAId()
	{
		$coin = Tbl_coin::where("coin_name", "allbyall")->where("coin_abb", "ABA")->first();

		if ($coin) 
		{
			return $coin->coin_id;
		}
		else
		{
			return null;
		}
	}

	public static function getLOKId()
	{
		$coin = Tbl_coin::where("coin_name", "ahm")->where("coin_abb", "AHM")->first();

		if ($coin) 
		{
			return $coin->coin_id;
		}
		else
		{
			return null;
		}
	}

	public static function putConvert($convert)
	{
		foreach ($convert as $key => $value) 
		{
			if (!$value["conversion_multiplier"]) 
			{
				$value["conversion_multiplier"] = 0;
			}

			$query = Tbl_coin_conversion::where("coin_from", $value["coin_from"])->where("coin_to", $value["coin_to"]);
			$exist = $query->first();

			if ($exist) 
			{
				$update["conversion_multiplier"] = $value["conversion_multiplier"];
				$query->update($update);
			}
			else
			{
				Tbl_coin_conversion::insert($value);
			}
		}

		return true;
	}

	public static function getCurrentSaleStage()
	{
		$sale_stage = Tbl_sale_stage::where("sale_stage_start_date", "<=", date('Y-m-d'))->where("sale_stage_end_date", ">=", date('Y-m-d'))->first();
		
		return $sale_stage;
	}

	public static function getConvertRate($coin_from, $coin_to, $ignore = 0)
	{
		$convert = Tbl_coin_conversion::where("coin_from", $coin_from)->where("coin_to", $coin_to)->first();

		if ($convert) 
		{
			$sale_stage = Coin::getCurrentSaleStage();

			if ($sale_stage && $ignore == 0) 
			{
				$result = $convert->conversion_multiplier - $convert->conversion_multiplier * ($sale_stage->sale_stage_discount / 100);

				return $result;
			}
			else
			{
				return $convert->conversion_multiplier;
			}
		}
		else
		{
			return 0;
		}
	}

	public static function getIdByName($name)
	{
		$coin = Tbl_coin::where("coin_name", $name)->orWhere("coin_abb", $name)->first();

		if ($coin) 
		{
			return $coin->coin_id;
		}
		else
		{
			return null;
		}
	}

	public static function setSaleStageDefault()
	{
		$exist = Tbl_sale_stage::first();

		if (!$exist) 
		{
			$sale_stage[0]["sale_stage_type"] 			= "pre_sales";
			$sale_stage[0]["sale_stage_start_date"] 	= Carbon::now();
			$sale_stage[0]["sale_stage_end_date"] 		= Carbon::now()->addDay();
			$sale_stage[0]["sale_stage_discount"] 		= 20;
			$sale_stage[0]["sale_stage_min_purchase"]   = 1;
			$sale_stage[0]["sale_stage_max_purchase"]   = 10000;

			$sale_stage[1]["sale_stage_type"]       	= "private_pre_sales";
			$sale_stage[1]["sale_stage_start_date"] 	= Carbon::now()->addDays(2);
			$sale_stage[1]["sale_stage_end_date"]   	= Carbon::now()->addDays(3);
			$sale_stage[1]["sale_stage_discount"]   	= 15;
			$sale_stage[1]["sale_stage_min_purchase"]   = 1;
			$sale_stage[1]["sale_stage_max_purchase"]   = 10000;
			
			$sale_stage[2]["sale_stage_type"]       	= "ico_sales";
			$sale_stage[2]["sale_stage_start_date"] 	= Carbon::now()->addDays(4);
			$sale_stage[2]["sale_stage_end_date"]   	= Carbon::now()->addDays(5);
			$sale_stage[2]["sale_stage_discount"]   	= 10;
			$sale_stage[2]["sale_stage_min_purchase"]   = 1;
			$sale_stage[2]["sale_stage_max_purchase"]   = 10000;
			
			$sale_stage[3]["sale_stage_type"]       	= "post_sales";
			$sale_stage[3]["sale_stage_start_date"] 	= Carbon::now()->addDays(6);
			$sale_stage[3]["sale_stage_end_date"]   	= Carbon::now()->addDays(7);
			$sale_stage[3]["sale_stage_discount"]   	= 5;
			$sale_stage[3]["sale_stage_min_purchase"]   = 1;
			$sale_stage[3]["sale_stage_max_purchase"]   = 10000;

			$sale_stage[4]["sale_stage_type"]       	= "no_sale_stage";
			$sale_stage[4]["sale_stage_start_date"] 	= Carbon::now()->addDays(8);
			$sale_stage[4]["sale_stage_end_date"]   	= Carbon::now()->addDays(9);
			$sale_stage[4]["sale_stage_discount"]   	= 0;
			$sale_stage[4]["sale_stage_min_purchase"]   = 1;
			$sale_stage[4]["sale_stage_max_purchase"]   = 10000;

			foreach ($sale_stage as $key => $value) 
			{
				Tbl_sale_stage::insert($value);
			}
		}

		return true;
	}

	public static function setBonusSaleStageDefault()
	{
		$_sale_stage = Coin::getSaleStageList();

		if (!Tbl_sale_stage_bonus::first()) 
		{
			foreach ($_sale_stage as $key => $sale_stage) 
			{
				$insert[0]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[0]['buy_coin_bonus_from']          = 1;
				$insert[0]['buy_coin_bonus_to']            = 2;
				$insert[0]['buy_coin_bonus_percentage']    = 10;

				$insert[1]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[1]['buy_coin_bonus_from']          = 3;
				$insert[1]['buy_coin_bonus_to']            = 4;
				$insert[1]['buy_coin_bonus_percentage']    = 20;

				$insert[2]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[2]['buy_coin_bonus_from']          = 5;
				$insert[2]['buy_coin_bonus_to']            = 6;
				$insert[2]['buy_coin_bonus_percentage']    = 30;

				$insert[3]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[3]['buy_coin_bonus_from']          = 7;
				$insert[3]['buy_coin_bonus_to']            = 8;
				$insert[3]['buy_coin_bonus_percentage']    = 40;

				$insert[4]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[4]['buy_coin_bonus_from']          = 9;
				$insert[4]['buy_coin_bonus_to']            = 10;
				$insert[4]['buy_coin_bonus_percentage']    = 50;

				$insert[5]['sale_stage_id']            	   = $sale_stage["sale_stage_id"];
				$insert[5]['buy_coin_bonus_from']          = 11;
				$insert[5]['buy_coin_bonus_to']            = 12;
				$insert[5]['buy_coin_bonus_percentage']    = 60;

				Tbl_sale_stage_bonus::insert($insert);
			}
		}

		return true;
	}

	public static function getSaleStageList()
	{
		Coin::setSaleStageDefault();

		return Tbl_sale_stage::get();
	}

	public static function getBonusSaleStageList()
	{
		Coin::setBonusSaleStageDefault();
		
		$_sale_stage_bonus = null;
		$_sale_stage = Coin::getSaleStageList();

		foreach ($_sale_stage as $key => $sale_stage) 
		{
			$_sale_stage_bonus[$key] = Tbl_sale_stage_bonus::where('sale_stage_id', $sale_stage["sale_stage_id"])->get();
		}

		return $_sale_stage_bonus;
	}

	public static function getSaleStageListWithBonusCoin()
	{
		Coin::setBonusSaleStageDefault();

		$_sale_stage_bonus = null;
		$_sale_stage = Coin::getSaleStageList();

		foreach ($_sale_stage as $key => $sale_stage) 
		{
			$_sale_stage[$key] = Tbl_sale_stage_bonus::where('sale_stage_id', $sale_stage["sale_stage_id"])->get();
		}

		return $_sale_stage;
	}

	public static function getSaleStage($type)
	{
		Coin::setSaleStageDefault();
		$sale_stage = Tbl_sale_stage::where("sale_stage_type", $type)->first();

		if ($sale_stage) 
		{
			$sale_stage->btc_value = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("bitcoin"), 1) - Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("bitcoin"), 1) * ($sale_stage->sale_stage_discount / 100);
			$sale_stage->eth_value = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("ethereum"), 1) - Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("ethereum"), 1) * ($sale_stage->sale_stage_discount / 100);
			$sale_stage->php_value = Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("peso"), 1) - Coin::getConvertRate(Coin::getIdByName("allbyall"), Coin::getIdByName("peso"), 1) * ($sale_stage->sale_stage_discount / 100);
		}

		return $sale_stage;
	}

	public static function insertSaleStage($sale_stage)
	{
		foreach ($sale_stage as $key => $value) 
		{
			$query = Tbl_sale_stage::where("sale_stage_type", $value["sale_stage_type"]);
			$exist = $query->first();

			if ($exist) 
			{
				$update["sale_stage_start_date"] = $value["sale_stage_end_date"] ? date("Y-m-d",strtotime($value["sale_stage_start_date"])) : null;
				$update["sale_stage_end_date"]   = $value["sale_stage_end_date"] ? date("Y-m-d",strtotime($value["sale_stage_end_date"])) : null;
				$update["sale_stage_discount"]   = $value["sale_stage_discount"];

				$query->update($update);
			}
			else
			{
				Tbl_sale_stage::insert($value);
			}
		}
	}

	public static function updateSaleStage($sale_stage, $_sale_stage_bonus = "")
	{
		$sale_stage_id 		= $sale_stage["sale_stage_id"];
		$update_sale_stage  = null;
		$start_date 		= $sale_stage["sale_stage_start_date"];
		$end_date 			= $sale_stage["sale_stage_end_date"];
		$overlap			= 0;

		$update_sale_stage['sale_stage_start_date']  	= $sale_stage["sale_stage_start_date"];
		$update_sale_stage['sale_stage_end_date']		= $sale_stage["sale_stage_end_date"];
		$update_sale_stage['sale_stage_min_purchase']	= $sale_stage["sale_stage_min_purchase"];
		$update_sale_stage['sale_stage_max_purchase']	= $sale_stage["sale_stage_max_purchase"];
		$update_sale_stage['sale_stage_discount']		= $sale_stage["sale_stage_discount"];
		if($start_date > $end_date)
		{
			$return['status'] = "fail";
			$return['message'] = "Invalid Date Range";
		}
		else
		{
			$date = Tbl_sale_stage::select("sale_stage_start_date","sale_stage_end_date")->where("sale_stage_id","!=",$sale_stage_id)->get();
			foreach ($date as $key => $value) {
				if($date[$key]->sale_stage_start_date >= $start_date || $date[$key]->sale_stage_end_date >= $start_date)
				{
					if($date[$key]->sale_stage_start_date <= $end_date || $date[$key]->sale_stage_end_date <= $end_date)
					{
						$overlap = 1;
					}
				}
			}
			if($overlap != 1)
			{
				Tbl_sale_stage::where('sale_stage_id', $sale_stage_id)->update($update_sale_stage);
		
				if ($_sale_stage_bonus != "") 
				{
					foreach ($_sale_stage_bonus as $key => $sale_stage_bonus) 
					{
						$sale_stage_bonus_id = $sale_stage_bonus["sale_stage_bonus_id"];
						
						$update_sale_stage_bonus = null;
						$update_sale_stage_bonus["buy_coin_bonus_from"] 		= $sale_stage_bonus["buy_coin_bonus_from"];
						$update_sale_stage_bonus["buy_coin_bonus_to"] 			= $sale_stage_bonus["buy_coin_bonus_to"];
						$update_sale_stage_bonus["buy_coin_bonus_percentage"] 	= $sale_stage_bonus["buy_coin_bonus_percentage"];
		
						Tbl_sale_stage_bonus::where('sale_stage_bonus_id', $sale_stage_bonus_id)->update($update_sale_stage_bonus);
					}
		
				}
		
				$return['status']  = "success";
				$return['message'] = "sale stage updated";
			}
			else
			{
				$return['status'] = "fail";
				$return['message'] = "Overlap Date or Same Date in other Sale Stage is Invalid";
			}
		}
		return $return;
	}

	public static function updateCoinConversion($_coin_conversion)
	{
		foreach ($_coin_conversion as $key => $coin_conversion) 
		{
			$coin_to = $coin_conversion["coin_id"];
			$update = null;
			$update["conversion_multiplier"] = $coin_conversion["conversion_multiplier"];
			Tbl_coin_conversion::where('coin_to', $coin_to)->update($update);
		}

		return true;
	}
}