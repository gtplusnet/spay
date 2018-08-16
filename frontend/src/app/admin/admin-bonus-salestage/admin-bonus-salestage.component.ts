import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-bonus-salestage',
  templateUrl: './admin-bonus-salestage.component.html',
  styleUrls: ['./admin-bonus-salestage.component.scss']
})
export class AdminBonusSalestageComponent implements OnInit {
  
	salestage_bonus_url: string;
	_params: any;
	_table: any;
	table_loader: any;

	account_name : any;
	transaction_date_from : any;
	transaction_date_to : any;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit() 
  {
  	this.salestage_bonus_url = this.rest.api_url + "/api/admin/get_salestage_bonus_list";
  	this._params = {};
  	this._params["login_token"] = this.rest.login_token;
  	this.loadTable();
  }

  loadTable() : void
  {
  	this.table_loader = true;
  	this._params["account_name"] = this.account_name;
  	this._params["transaction_date_from"] = this.transaction_date_from;
  	this._params["transaction_date_to"] = this.transaction_date_to;
  	this.http.post(this.salestage_bonus_url, this._params).subscribe(response=>
  	{
  		this._table = response;
  		//console.log(this._table);
  		this.table_loader = false;
  	},
      error=>
      {
        console.log(error);
      });
  }

}
