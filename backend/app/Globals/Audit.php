<?php
namespace App\Globals;
use App\Tbl_audit;
use Carbon\Carbon;
class Audit
{
	public static function log($member_id, $log)
	{
		$insert["audit_log"]	= $log;
		$insert["member_id"] 	= $member_id;
		$insert["audit_date"] 	= Carbon::now();

		return Tbl_audit::insertGetId($insert);
	}
}