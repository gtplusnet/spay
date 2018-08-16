import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-dashboard',
  templateUrl: './admin-dashboard.component.html',
  styleUrls: ['./admin-dashboard.component.scss']
})
export class AdminDashboardComponent implements OnInit {
  pending_member		 		: any;
  total_stored_btc		 		: any;
  total_stored_eth		 		: any;
  total_token_release	 		: any;
  get_total_token_release_url   : any;

  get_pending_member_url 		: any;
  get_total_stored_btc_url   	: any;
  get_total_stored_eth_url		: any;
  get_recent_details_url        : any;

  recent_join       : any;
  recent_eth_transaction  : any;
  recent_btc_transaction  : any;
  _param				 		: any;

  recent_btc: any;
  recent_eth: any;

  constructor(private rest:MemberInfoService, private http:HttpClient,private modalService: NgbModal) { }

  ngOnInit() {
  	this.get_pending_member_url 		= this.rest.api_url + "/api/admin/get_pending_member";
  	this.get_total_stored_btc_url		= this.rest.api_url + "/api/admin/get_total_stored_btc";
  	this.get_total_stored_eth_url		= this.rest.api_url + "/api/admin/get_total_stored_eth";
  	this.get_total_token_release_url	= this.rest.api_url + "/api/admin/get_total_token_release";
    this.get_recent_details_url     = this.rest.api_url + "/api/admin/get_recent_details";
    this._param={}
    this._param["login_token"] = this.rest.login_token;
  	this.pending_member = 0;
  	this.total_stored_btc = 0.00;
  	this.total_stored_eth = 0.00;
  	this.total_token_release = 0.00;
    this.getPendingMember();
    this.getTotalStoredBTC();
    this.getTotalStoredETH();
    this.getTotalTokenRelease();
    this.getRecentDetails();
  }

  getPendingMember()
  {
  	
  	this.http.post(this.get_pending_member_url,this._param).subscribe(response=>
  	{
  		this.pending_member = response;
  	},
      error=>
      {
        console.log(error);
      })
  }

  getTotalStoredBTC()
  {
  	this.http.post(this.get_total_stored_btc_url,this._param).subscribe(response=>
  	{
  		this.total_stored_btc = response;
  	})
  }

  getTotalStoredETH()
  {
  	this.http.post(this.get_total_stored_eth_url,this._param).subscribe(response=>
  	{
  		this.total_stored_eth = response;
  	},
      error=>
      {
        console.log(error);
      })
  }

  getTotalTokenRelease()
  {
  	this.http.post(this.get_total_token_release_url,this._param).subscribe(response=>
  	{
  		this.total_token_release = response;
  	},
      error=>
      {
        console.log(error);
      })
  }
  getRecentDetails()
  {
    this._param = {}
    this._param["login_token"] = this.rest.login_token;
    this.http.post(this.get_recent_details_url,this._param).subscribe(response=>
      {
        this.recent_join = response["recent_join"];
        this.recent_btc_transaction = response["recent_btc_transaction"];
        this.recent_eth_transaction = response["recent_eth_transaction"];
      },
      error=>
      {
        console.log(error);
      });
  }
}
