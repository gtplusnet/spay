import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-bonus-referral',
  templateUrl: './admin-bonus-referral.component.html',
  styleUrls: ['./admin-bonus-referral.component.scss']
})
export class AdminBonusReferralComponent implements OnInit {

	salestage_bonus_url: string;
	_params: any;
	_table: any;
	table_loader: any;

	referrer : any;
	invitee : any;
	transaction_date_from : any;
	transaction_date_to : any;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit() {
  	this.salestage_bonus_url = this.rest.api_url + "/api/admin/get_referral_bonus_list";
  	this._params = {};
  	this._params["login_token"] = this.rest.login_token;
  	this.loadTable();
  }

  loadTable()
  {
  	this.table_loader = true;
  	this._params["referrer"] = this.referrer;
  	this._params["invitee"] = this.invitee;
  	this._params["transaction_date_from"] = this.transaction_date_from;
  	this._params["transaction_date_to"] = this.transaction_date_to;
  	this.http.post(this.salestage_bonus_url, this._params).subscribe(response=>
  	{
        if(response != null)
        {
          this._table = response;
          this.table_loader = false;
        }
        else
        {
          this._table = "";
          this.table_loader = false;
        }
  		  // this._table = response;
  		  // for (var i = 0; i < this._table.length; i++) {
      //   if(this._table[i]["bonus_from"].length == 0)
      //   {
      //     this._table.splice(i);
      //   }
      // }
  		  // this.table_loader = false;
    },
      error=>
      {
        console.log(error);
      });
  }

}
