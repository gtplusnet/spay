<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_tree_sponsor extends Model
{
    protected $table = 'tbl_tree_sponsor';
	protected $primaryKey = "sponsor_id";

	public function scopeParent($query)
    {
        $query
        ->join("users", "users.id", "=", "tbl_tree_sponsor.sponsor_parent_id");
    }

    public function scopeChild($query)
    {
        $query
        ->join("users", "users.id", "=", "tbl_tree_sponsor.sponsor_child_id");
    }
}
