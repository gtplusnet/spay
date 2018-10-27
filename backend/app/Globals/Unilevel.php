<?php
namespace App\Globals;
use App\Tbl_tree_sponsor;
use App\Tbl_User;
use App\Tbl_unilevel_settings;
use DB;

class Unilevel
{	
    public static function distribute($id,$amount)
    {
        $child_tree = Tbl_tree_sponsor::where("sponsor_child_id",$id)->get();

        foreach($child_tree as $child)
        {
            $settings = Tbl_unilevel_settings::where("unilevel_settings_level",$child->sponsor_level)->first();
            if($settings)
            {
                $computed_amount = $amount * ($settings->unilevel_settings_amount/100);

                if($computed_amount != 0)
                {
                    /* INSERT WALLET HERE */
                    dd($computed_amount);
                }
            }
        }
    }
}