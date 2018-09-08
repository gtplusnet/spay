<?php
namespace App\Globals;
use App\Tbl_login;
use App\User;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Crypt;

class Authenticator
{
	public static function login($member_id, $password = null)
	{
		
		$agent = new Agent();
		$device = $agent->device();
		$platform = $agent->platform();
		$languages = $agent->languages();

		$insert["id"]				= $member_id;
		$insert["login_key"]		= Self::incrementalHash();
		$insert["login_regex"]		= serialize($languages);
		$insert["login_parent"]		= $device;
		$insert["login_platform"]	= $platform;
		$insert["login_ip"]			= Self::get_ip_address();
		$insert["login_date"]		= Carbon::now('Asia/Manila');
		
		Tbl_login::insert($insert);
		$passkey = Self::randomPassword();
		$store_session = Crypt::encryptString(serialize($insert["login_key"] . "-" . $password));
		return $store_session;
	}

	public static function randomPassword()
	{
	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array(); 
	    $alphaLength = strlen($alphabet) - 1;
	    
	    for ($i = 0; $i < 8; $i++)
	    {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }

	    return implode($pass);
	}


	public static function checkLogin($session_login)
	{

		if($session_login)
		{
			$login_session 	= explode("-", unserialize(Crypt::decryptString($session_login)));
			$login_key 		= $login_session[0];
			$hashed_pw 		= $login_session[1];
			$login 			= Tbl_login::where("login_key", $login_key)->joinMember()->first();
			
			if($hashed_pw == $login->password)
			{
				unset($login->password);
				return $login;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

	}
	public static function adminOnly($member_id)
	{
		if(Tbl_member::where("member_id", $member_id)->value("is_admin") == 0)
		{
			abort(404);
		}
	}
	public static function incrementalHash()
	{
		$charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
		$base = strlen($charset);
		$result = '';

		$now = explode(' ', microtime())[1];

		while ($now >= $base)
		{
			$i = $now % $base;
			$result = $charset[$i] . $result;
			$now /= $base;
		}

		return $result;
	}
	public static function get_ip_address()
	{
	    $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
	    
	    foreach ($ip_keys as $key)
	    {
	        if (array_key_exists($key, $_SERVER) === true)
	        {
	            foreach (explode(',', $_SERVER[$key]) as $ip)
	            {
	                // trim for safety measures
	                $ip = trim($ip);
	                // attempt to validate IP
	                if (Self::validate_ip($ip))
	                {
	                    return $ip;
	                }
	            }
	        }
	    }

	    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
	}
	/**
	 * Ensures an ip address is both a valid IP and does not fall within
	 * a private network range.
	 */
	public static function validate_ip($ip)
	{
	    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false)
	    {
	        return false;
	    }

	    return true;
	}
}