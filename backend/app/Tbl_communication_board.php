<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tbl_communication_board extends Model
{
    protected $table = 'tbl_communication_board';
	protected $primaryKey = "communication_board_id";
	public $timestamps = false;

	public function scopeGetFilter($query, $title = null, $career = null, $date_from = null, $date_to = null)
	{
		$careerMember = 0;
		$careerAdvisor = 0;
		$careerMd = 0;
		$careerAmbassador = 0;
		$careerCm = 0;
		$data = Tbl_communication_board::select('*');
		if($title != null)
		{
			$data = $data->where('communication_board_title',"like","%".$title."%");
		}
		if($career != null)
		{
			if($career == "Member")
			{
				$careerMember = 1;
				$data = $data->where('communication_board_career_member',$careerMember);
			}
			if($career == "Advisor")
			{
				$careerAdvisor = 1;
				$data = $data->where('communication_board_career_advisor',$careerAdvisor);
			}
			if($career == "Marketing Director")
			{
				$careerMd = 1;
				$data = $data->where('communication_board_career_marketing_director',$careerMd);
			}
			if($career == "Ambassador")
			{
				$careerAmbassador = 1;
				$data = $data->where('communication_board_career_ambassador',$careerAmbassador);
			}
			if($career == "Community Manager")
			{
				$careerCm = 1;
				$data = $data->where('communication_board_career_community_manager',$careerCm);
			}
		}
		if($date_from != null)
		{
			$data = $data->whereDate("insert_date", ">=" ,$date_from);
		}
		if($date_to != null)
		{
			$data = $data->whereDate("insert_date", "<=" ,$date_to);
		}
		return $data->get();
	}
}
