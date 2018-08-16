<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_login extends Model
{
    protected $table = 'tbl_login';
	protected $primaryKey = "login_id";

    public function scopeJoinMember($query)
    {
        $query->join("users", "users.id", "=", "tbl_login.id");
    }
}
