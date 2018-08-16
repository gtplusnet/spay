import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-referral-settings',
  templateUrl: './admin-referral-settings.component.html',
  styleUrls: ['./admin-referral-settings.component.scss']
})
export class AdminReferralSettingsComponent implements OnInit {
  
member_position_url : any;
member_current_position_url : any;
update_member_current_position_url : any;
_params : any;
_list : any;
_current : any;
current_loading: any;

member_position_id : any;
bonus_method : string;
commission : number;
member_min_purchase : number;
token_release : number;   
member_bonus_percentage : number;
initial_release_percentage: number;

needed_member                : number;
needed_community_manager     : number;
needed_ambassador            : number;
needed_marketing_director    : number;
needed_advisor               : number;
after_purchase_comission     : number;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit() 
  {
  	this.member_position_url = this.rest.api_url + "/api/admin/get_member_positions";
  	this.member_current_position_url = this.rest.api_url + "/api/admin/get_current_member_positions";
  	this.update_member_current_position_url = this.rest.api_url + "/api/admin/update_current_member_positions";
  	this.member_position_id = 1;
  	this._params = {};
  	this._params["login_token"] = this.rest.login_token;
  	this.loadMemberPositions();
  	this.loadCurrentMemberPositions();
  }

  loadMemberPositions()
  {
  	this.http.post(this.member_position_url, this._params).subscribe(response=>
  	{
  		this._list = response;
  	},
      error=>
      {
        console.log(error);
      });
  }

  loadCurrentMemberPositions()
  {
  	this.current_loading = true;
  	this._params["member_position_id"] = this.member_position_id;
  	this.http.post(this.member_current_position_url, this._params).subscribe(response=>
  	{
  		this.current_loading 		          = false;
  		this._current 				            = response;
  		this.bonus_method 			          = this._current.bonus_method;
      this.commission                   = this._current.commission;
      this.token_release                = this._current.token_release;
      this.initial_release_percentage   = this._current.initial_release_percentage;
		  this.member_min_purchase 	        = this._current.member_min_purchase;
		  this.member_bonus_percentage      = this._current.member_bonus_percentage;
      this.needed_member                = this._current.needed_member;
      this.needed_ambassador            = this._current.needed_ambassador;
      this.needed_community_manager     = this._current.needed_community_manager;
      this.needed_marketing_director    = this._current.needed_marketing_director;
      this.needed_advisor               = this._current.needed_advisor;
      this.after_purchase_comission     = this._current.after_purchase_comission;
  	},
      error=>
      {
        console.log(error);
      });
  }

  updateCurrentMemberPosition()
  {
  	this.current_loading = true;
  	this._params["bonus_method"]                 = this.bonus_method
    this._params["commission"]                   = this.commission
    this._params["token_release"]                = this.token_release
    this._params["initial_release_percentage"]   = this.initial_release_percentage
    this._params["member_min_purchase"]          = this.member_min_purchase
    this._params["member_bonus_percentage"]      = this.member_bonus_percentage
    this._params["needed_member"]                = this.needed_member
    this._params["needed_ambassador"]            = this.needed_ambassador
    this._params["needed_community_manager"]     = this.needed_community_manager
    this._params["needed_marketing_director"]    = this.needed_marketing_director
    this._params["needed_advisor"]               = this.needed_advisor
    this._params["after_purchase_comission"]     = this.after_purchase_comission;

  	this.http.post(this.update_member_current_position_url, this._params).subscribe(response=>
  	{
  		this.loadCurrentMemberPositions();
  	},
      error=>
      {
        console.log(error);
      });
  }

}
