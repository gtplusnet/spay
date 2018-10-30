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


    public static function place_sponsor($member, $req)
    {
        $member         = json_decode($member);

        $owner_info = Tbl_User::where("id",$member->id)->first();
        $sponsor    = $req;
        $user_info  = Tbl_User::where("email",$sponsor->email)->first();

        if($user_info)
        {
            if($user_info->id == $member->id)
            {
                $response["status"]  = "fail";
                $response["message"] = "You cannot sponsor your own user..."; 
            }
            else
            {
                if($user_info->user_sponsor_id == 0  && $user_info->top_slot == 0)
                {
                    $response["status"]  = "fail";
                    $response["message"] = "Your target sponsor should pick a sponsor first..."; 
                }
                else
                {
                    $update["user_sponsor_id"] = $user_info->id;
                    if($owner_info->top_slot == 0 &&  $owner_info->user_sponsor_id == 0)
                    {
                        Tbl_User::where("id",$owner_info->id)->update($update);
                        
                        $owner_info          = Tbl_User::where("id",$member->id)->first();
                        Self::insert_tree_sponsor($owner_info, $owner_info, 1);
                        $response["status"]  = "success";
                        $response["message"] = "Success";
                    }
                    else
                    {
                        $response["status"]  = "fail";
                        $response["message"] = "You already have a sponsor...";
                    }
                }
            }
        }
        else
        {
            $response["status"]  = "fail";
            $response["message"] = "Sponsor not found...";
        }

        // dd($req,$member);
        return json_encode($response);
    }
}