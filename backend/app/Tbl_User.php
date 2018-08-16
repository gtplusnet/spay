<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_User extends Model
{
    protected $table = 'users';
	protected $primaryKey = "id";

	public function scopeJoinMemberAddress($query)
    {
        $query->leftJoin("tbl_member_address", "users.id", "=", "tbl_member_address.member_id")->select("users.*", "tbl_member_address.address_balance");        
    }

    public function scopeJoinCountryCode($query)
    {
    	$query->join("tbl_country_codes", "users.country_code_id", "=", "tbl_country_codes.country_code_id")->select("users.*", "tbl_country_codes.country_code_abbr", "tbl_country_codes.country_code", "tbl_country_codes.country");
        
    }
}
