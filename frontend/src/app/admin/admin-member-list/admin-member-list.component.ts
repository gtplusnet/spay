import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-member-list',
  templateUrl: './admin-member-list.component.html',
  styleUrls: ['./admin-member-list.component.scss']
})
export class AdminMemberListComponent implements OnInit {

  _param : any;
  _table : any;
  submitted : boolean;
  total_members : any;
  table_url : any;
  login_history;
  total_members_url : any;
  active_deactive_user_url: any;
  promote_demote_user_url: any;
  change_user_password_url: any;
  login_history_url : any;
  modal_ref : any;
  data_focus : any;

  modal_table_loader : boolean;
  modal_table_loader_eth : boolean;
  modal_btc_table_url : string;
  modal_eth_table_url : string;
  _param_m : any;
  _table_m : any;
  _table_eth : any;

  table_loader : boolean
  //ngx-pagination;

  page : any;
  page_modal : any;

  //search filters
  search_name : string;
  search_career : any;
  search_status : string;
  search_email_status : string;
  search_roles : string;
  search_date_from : any;
  search_date_to : any;
  register_platform : any;
  //change password
  c_new_password : any;
  c_confirm_new_password: any;
  error_message : any;

  //modal
  mem_transaction_status : string;
  mem_transaction_date_from : any;
  mem_transaction_date_to : any;
  mem_id                  : any;

  member_id                        : number;
  member_position_id               : any;
  commission                       : number;
  member_min_purchase              : number;
  token_release                    : number;   
  after_commission_purchase        : number;
  initial_release_percentage       : number;
  minimum_purchase                 : number;
      
  needed_member                    : number;
  needed_community_manager         : number;
  needed_ambassador                : number;
  needed_marketing_director        : number;
  needed_advisor                   : number;
    
  career_settings_url              : any;
  career_setting_update_url        : any;
  career_change_update_url         : any;
  career_setting_info              : any;
  first_name                       : string;
  last_name                        : string;
  member_position                  : string;
  career_member_id                 : any;
  career_type                      : any;
  update_loading                   : boolean;
  referral_bonus_info              : any;
  table_loader_referral            : boolean;
  view_referral_info_url           : string;
  referral_url                     : string;
  _table_refer                     : any;
  affliate_table_loader            : boolean;
  check_tokens_url                 : any;
  w_balance                        : any;
  refer_data                       : any;
  loading_token_wallet             : boolean;
  referral_count_by_career         : any = {};
  referral_count_by_career_url     : any;
  search_by_career                 : string;
  id_referral_table                : number;
  referral_table_loader            : boolean;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit() {
    this._param = {};
    this._param["login_token"] = this.rest.login_token;

    //view details urls
    this.login_history_url              = this.rest.api_url + "/api/admin/login_history";
    this.total_members_url              = this.rest.api_url + "/api/admin/unactivated_members";
  	this.table_url                      = this.rest.api_url + "/api/admin/get_member_list";
    this.active_deactive_user_url       = this.rest.api_url + "/api/admin/active_deactive_user";
    this.promote_demote_user_url        = this.rest.api_url + "/api/admin/promote_demote_user";
    this.change_user_password_url       = this.rest.api_url + "/api/admin/change_user_password";
    this.career_settings_url            = this.rest.api_url + "/api/admin/career_setting";
    this.career_setting_update_url      = this.rest.api_url + "/api/admin/career_setting_update";
    this.career_change_update_url       = this.rest.api_url + "/api/admin/career_change_update";
    this.referral_url                   = this.rest.api_url + "/api/member/get_referrals";
    this.view_referral_info_url         = this.rest.api_url + "/api/admin/view_referral_info";
    this.check_tokens_url               = this.rest.api_url + "/api/member/check_tokens";
    this.referral_count_by_career_url   = this.rest.api_url + "/api/admin/get_referral_count_by_career";

    this.search_status = "all";
  	this.search_career = "all";
	  this.search_email_status = "all";
	  this.search_roles = "all";
    this.register_platform = "all"
    this.error_message = "no-message";
    this.total_members = 0;
    this.countUnactivated();
	  this.loadTable();
    this.mem_transaction_status = "all";
    this.mem_transaction_date_from = "";
    this.mem_transaction_date_to = "";
    this.loading_token_wallet = false;
    this.search_by_career = "all";
  }

  loadTable()
	{
		this.table_loader = true;
		this._param["search_name"] = this.search_name; 
		this._param["search_status"] = this.search_status; 
    this._param["search_roles"] = this.search_roles; 
		this._param["search_career"] = this.search_career; 
		this._param["search_email_status"] = this.search_email_status;
    this._param["register_platform"] = this.register_platform;
		this._param["search_date_from"] = this.search_date_from;
		this._param["search_date_to"] = this.search_date_to; 

		this.http.post(this.table_url, this._param).subscribe(response =>
		{
			this._table = response;
			this.table_loader = false;
		},
      error=>
      {
        console.log(error);
      });
	}

  loadMemberTableBtc(id)
  {
    this.modal_table_loader = true;
    this._param_m = {}
    this._param_m["login_token"] = this.rest.login_token;
    this._param_m["member_id"] = id;
    this._param_m["log_method"] = "Bitcoin";
    this._param_m["log_method_accepted"] = "Bitcoin Total";
    this._param_m["account_name"] = "";
    this._param_m["transaction_status"] = this.mem_transaction_status;
    this._param_m["transaction_date_from"] = this.mem_transaction_date_from;
    this._param_m["transaction_date_to"] = this.mem_transaction_date_to;
    
    this.http.post(this.modal_btc_table_url, this._param_m).subscribe(
      response=>
      {
        this._table_m = response;
        this.modal_table_loader = false;
      },
      error=>
      {
        console.log(error);
      });
  }

  loadMemberTableEth(id) : void
  {
    this.modal_table_loader_eth = true;
    this._param_m = {}
    this._param_m["login_token"] = this.rest.login_token;
    this._param_m["member_id"] = id;
    this._param_m["log_method"] = "Ethereum";
    this._param_m["log_method_accepted"] = "Ethereum Total";
    this._param_m["account_name"] = "";
    this._param_m["transaction_status"] = this.mem_transaction_status;
    this._param_m["transaction_date_from"] = this.mem_transaction_date_from;
    this._param_m["transaction_date_to"] = this.mem_transaction_date_to;
    
    this.http.post(this.modal_btc_table_url, this._param_m).subscribe(
      response=>
      {
        this._table_eth = response;
        this.modal_table_loader_eth = false;
      },
      error=>
      {
        console.log(error);
      });
  }

  open(content)
  {
    this.modal_ref = this.modalService.open(content);
  }

  openLg(content)
  {
    this.modal_ref = this.modalService.open(content, {'size': 'lg'});
  }


  countUnactivated()
  {
    this.http.post(this.total_members_url, this._param).subscribe(response=>
    {
      this.total_members = response;
    },
      error=>
      {
        console.log(error);
      });
  }

  viewAccountDetails(id, selector)
  {
    this.modal_btc_table_url = this.rest.api_url + "/api/admin/get_member_transactions";
    this.modal_eth_table_url = this.rest.api_url + "/api/admin/get_member_transactions";
    this.loadMemberTableBtc(id);
    this.loadMemberTableEth(id);
    this.viewReferralInfo(id);
    this.data_focus = this.rest.findObjectByKey(this._table, 'id', id);
    this.getReferralInfo(id);
    this.checkTokens(this.data_focus["lokalize"].member_address_id);
    this.openLg(selector);

    this._param["id"] = id;
    this.mem_id = id;
    this.http.post(this.login_history_url, this._param).subscribe(response=>
    {
      this.login_history = response;
    },
      error=>
      {
        console.log(error);
      });
  }

  changeUserPassword(id, selector )
  {
    this.error_message = "no-message";
    this.c_new_password = "";
    this.c_confirm_new_password = "";
    this.data_focus = this.rest.findObjectByKey(this._table, 'id', id);
    this.open(selector);
  }

  updatePassword(id)
  {
    this.error_message = "no-message";
    this.submitted = true;
    this._param["id"] = id;
    this._param["c_new_password"] = this.c_new_password;
    this._param["c_confirm_new_password"] = this.c_confirm_new_password;
    
    this.http.post(this.change_user_password_url, this._param).subscribe(response=>
      {
        this.error_message = response;
        this.submitted = false;
      });
  }

  activeDeactiveUser(id, param)
  {

    this.table_loader = true;
    this._param["active"] = param;
    this._param["id"] = id;
    this.http.post(this.active_deactive_user_url, this._param).subscribe(response=>
        {
          this.countUnactivated();
          this.loadTable();
        },
      error=>
      {
        console.log(error);
      });
  }

  promoteDemoteUser(id, param)
  {
    this.table_loader = true;
    this._param["active"] = param;
    this._param["id"] = id;
    this.http.post(this.promote_demote_user_url, this._param).subscribe( response=>
        {
          this.loadTable();
        },
      error=>
      {
        console.log(error);
      });
  }

  careerSettings(id,selector)
  {
    this.first_name                    = "";
    this.last_name                     = "";
    this.member_position               = "";
    this.needed_ambassador             = 0;
    this.needed_advisor                = 0;
    this.needed_member                 = 0;
    this.needed_community_manager      = 0;
    this.needed_marketing_director     = 0;
    this.token_release                 = 0;
    this.commission                    = 0;
    this.initial_release_percentage    = 0;
    this.minimum_purchase              = 0;
    this.after_commission_purchase     = 0;

    this._param["login_token"] = this.rest.login_token;
    this._param["id"] = id;
    this.member_id = id;
    this.http.post(this.career_settings_url,this._param).subscribe(response=>
    { 
      this.career_setting_info           = response;
      this.first_name                    = this.career_setting_info[0].first_name;
      this.last_name                     = this.career_setting_info[0].last_name;
      this.member_position               = this.career_setting_info[0].member_position_name;
      this.needed_ambassador             = this.career_setting_info[0].needed_ambassador;
      this.needed_advisor                = this.career_setting_info[0].needed_advisor;
      this.needed_member                 = this.career_setting_info[0].needed_member;
      this.needed_community_manager      = this.career_setting_info[0].needed_community_manager;
      this.needed_marketing_director     = this.career_setting_info[0].needed_marketing_director;
  
      this.token_release                 = this.career_setting_info[0].token_release;
      this.commission                    = this.career_setting_info[0].commission;
      this.initial_release_percentage    = this.career_setting_info[0].initial_release_percentage;
      this.minimum_purchase              = this.career_setting_info[0].member_min_purchase;
      this.after_commission_purchase     = this.career_setting_info[0].after_purchase_commission;
      
    },
      error=>
      {
        console.log(error);
      });
    this.openLg(selector);
  }

  onUpdateCareerSetting()
  {
    this._param ={}
    this._param["login_token"]                  = this.rest.login_token;
    this._param["id"]                           = this.member_id;
    this._param["token_release"]                = this.token_release;
    this._param["commission"]                   = this.commission;
    this._param["initial_release_percentage"]   = this.initial_release_percentage;
    this._param["minimum_purchase"]             = this.minimum_purchase;
    this._param["after_purchase_commission"]    = this.after_commission_purchase;
    this._param["needed_ambassador"]            = this.needed_ambassador;
    this._param["needed_advisor"]               = this.needed_advisor;
    this._param["needed_member"]                = this.needed_member;
    this._param["needed_community_manager"]     = this.needed_community_manager
    this._param["needed_marketing_director"]    = this.needed_marketing_director;

    this.http.post(this.career_setting_update_url,this._param).subscribe(data=>
    {
      if(data["status"] == "success")
      {
        this.modal_ref.close();
      }
      else
      {
         this.error_message = data["message"];
      }
    },
      error=>
      {
        console.log(error);
      })

  }

  ViewCurrentCareerType(id,selector)
  {
    this.update_loading = false;
    this.first_name    = "";
    this.last_name     = "";
    this.career_type   = "";

    this._param["login_token"] = this.rest.login_token;
    this._param["id"] = id;
    this.career_member_id = id;
    this.http.post(this.career_settings_url,this._param).subscribe(response=>
    { 
      this.career_setting_info           = response;
      this.first_name                    = this.career_setting_info[0].first_name;
      this.last_name                     = this.career_setting_info[0].last_name;
      this.career_type                   = this.career_setting_info[0].member_position_id;
      
    },
      error=>
      {
        console.log(error);
      });
    this.open(selector);
  }

  onUpdateChangeCareerType()
  {
    this.update_loading = true;
    this._param = {}
    this._param["login_token"] = this.rest.login_token;
    this._param["id"] = this.career_member_id;
    this._param["position_id"] = this.career_type;

    this.http.post(this.career_change_update_url,this._param).subscribe(data=>
    {
      if(data['status'] == "success")
      {
        this.update_loading = false;
        this.modal_ref.close();
      }
    },
      error=>
      {
        console.log(error);
      })
  }

viewReferralInfo(id)
{
  this.referral_bonus_info = "";
  this.table_loader_referral = true;
  var _params = {}
  _params["login_token"] = this.rest.login_token;
  _params["to_id"] = id
  this.http.post(this.view_referral_info_url,_params).subscribe(response=>
  {
    this.referral_bonus_info = response;
    if(response != null)
    {
      this.referral_bonus_info = response;
      this.table_loader_referral = false;
    }
    else
    {
      this.referral_bonus_info = "";
      this.table_loader_referral = false;
    }
  },
      error=>
      {
        console.log(error);
      })
}

checkTokens(wallet_id)
  {
      this.loading_token_wallet = true;
      var param = {};
      param["login_token"] = this.rest.login_token;
      param["wallet_id"] = wallet_id;
      this.http.post(this.check_tokens_url, param).subscribe(response=>
      {
        this.w_balance = response;
        this.loading_token_wallet = false;
      },
      error=>
      {
        console.log(error);
      });
  }

 getReferralInfo(id)
{
  var _params = {};
  var referral_url =  this.rest.api_url + "/api/member/get_referral_info";
  _params["login_token"] = this.rest.login_token;
  _params["id"] = id;
  _params["auth"] = 'admin';
  this.http.post(referral_url, _params).subscribe(response=>
  {
    this.refer_data = response;
  },
      error=>
      {
        console.log(error);
      })
}
getReferralCountByCareer(id)
{
  this.referral_count_by_career.member = 0;
  this.referral_count_by_career.community_manager = 0;
  this.referral_count_by_career.marketing_director = 0;
  this.referral_count_by_career.ambassador = 0;
  this.referral_count_by_career.advisor = 0;
  var param = {}
  param["login_token"] = this.rest.login_token;
  param["id"] = id;

  this.http.post(this.referral_count_by_career_url,param).subscribe(data=>
  {
    this.referral_count_by_career = data;
  },
      error=>
      {
        console.log(error);
      })
}

getReferrals(id,selector)
{
  this.search_by_career = "all";
  this.id_referral_table = id;
  this.openLg(selector);
  this.referralLoadTable();
  this.getReferralCountByCareer(id);
}

referralLoadTable() : void
{
  this.referral_table_loader = true;
  var _params = {};
  this._table_refer = {};
  _params["login_token"] = this.rest.login_token;
  _params["id"] = this.id_referral_table;
  _params["career"] = this.search_by_career;
  this.http.post(this.referral_url,_params).subscribe(data=>
  {
    this._table_refer = data["list"];
    this.referral_table_loader = false;
  },
      error=>
      {
        console.log(error);
      });
}
}
