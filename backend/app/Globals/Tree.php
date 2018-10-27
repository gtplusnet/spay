<?php
namespace App\Globals;
use App\Tbl_tree_sponsor;
use App\Tbl_User;
use DB;

class Tree
{	
    public static function insert_tree_sponsor($user_info, $new_slot, $level)
    {
        if($user_info != null)
        {
            $upline_info = Tbl_User::where("id",$user_info->user_sponsor_id)->first();
            /*CHECK IF TREE IS ALREADY EXIST*/
            $check_if_exist = null;
            if($upline_info)
            {
                $check_if_exist = Tbl_tree_sponsor::where("sponsor_child_id",$new_slot->id)
                ->where('sponsor_parent_id', '=', $upline_info->id )
                ->first();
            }
            else
            {
                $check_if_exist = Tbl_tree_sponsor::where("sponsor_child_id",$new_slot->id)->first();
            }

            if($upline_info)
            {
                    if($upline_info)
                    {
                        if($upline_info->id != $new_slot->id)
                        {
                            if(!$check_if_exist)
                            {                            
                            	$insert["sponsor_parent_id"] = $upline_info->id;
                                $insert["sponsor_child_id"] = $new_slot->id;
                                $insert["sponsor_level"] = $level;
                                Tbl_tree_sponsor::insert($insert);
                            }
                            $level++;
                            Tree::insert_tree_sponsor($upline_info, $new_slot, $level);  
                        }
                    }
            }
        }
    }
}