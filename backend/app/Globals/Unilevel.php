<?php
namespace App\Globals;
use App\Tbl_tree_sponsor;
use App\Tbl_User;
use App\Tbl_unilevel_settings;
use App\Globals\Blockchain;
use DB;

class Unilevel
{	
    public static function distribute($id,$amount,$log_id, $type)
    {
        $child_tree       = Tbl_tree_sponsor::where("sponsor_child_id",$id)->get();
        $child_tree_count = Tbl_tree_sponsor::where("sponsor_child_id",$id)->count();

        if($child_tree_count != 0)
        {
            foreach($child_tree as $child)
            {
                $settings = Tbl_unilevel_settings::where("unilevel_settings_level",$child->sponsor_level)->first();
                if($settings)
                {
                    $computed_amount = $amount * ($settings->unilevel_settings_amount/100);

                    if($computed_amount != 0)
                    {
                        $receiver_id = $child->sponsor_parent_id;

                        /* INSERT WALLET HERE */
                        Blockchain::recordReferralBonus($receiver_id, $computed_amount, $log_id, $type);
                    }
                }
            }
        }

    }
}