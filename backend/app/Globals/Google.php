<?php
namespace App\Globals;

use App\Tbl_country;
use App\Tbl_User;
use App\Tbl_other_info;


use stdClass;
use Carbon\Carbon;
use PragmaRX\Google2FA\Google2FA;


class Google
{
    public static function getQRPairingCode($user_id)
    {
        $google2fa = new Google2FA();
        $data["user"] = Tbl_other_info::where("user_id", $user_id)->join("users", "users.id", "=", "tbl_other_info.user_id")->first();

        $google2fa->setAllowInsecureCallToGoogleApis(true);

        $data["google2fa_url"] = $google2fa->getQRCodeGoogleUrl(
            'AHM',
            $data["user"]->email,
            $data["user"]->google2fa_secret_key
        );

        return $data;
    }

    public static function changeStatus2FA($user_id)
    {
        $user = Tbl_other_info::where("user_id", $user_id);
        $first = $user->first();
        if($first->google2fa_enabled == 0)
        {
            $user = $user->update(["google2fa_enabled" => 1]);
            $return["message"] = "Successfully Enabled Google 2FA";
            $return["type"] = "enable";
        }
        else
        {
            $user = $user->update(["google2fa_enabled" => 0]);
            $return["message"] = "Successfully Disabled Google 2FA";
            $return["type"] = "disable";
        }

        return $return;
    }

    public static function validateKey($user_id, $user_code)
    {
        $google2fa = new Google2FA();
        $user = Tbl_other_info::where("user_id", $user_id)->join("users", "users.id", "=", "tbl_other_info.user_id")->first();
        $timestamp = $google2fa->verifyKeyNewer($user->google2fa_secret_key, $user_code, $user->google2fa_ts);

        if ($timestamp !== false) {
            // $user->update(['google2fa_ts' => $timestamp]);
            $google2fa_ts = Tbl_other_info::where("user_id", $user->user_id)->update(["google2fa_ts" => $timestamp]);
            $return["message"] = "valid";
        } else {
            $return["message"] = "invalid";
        }

        return $return;
    }
}