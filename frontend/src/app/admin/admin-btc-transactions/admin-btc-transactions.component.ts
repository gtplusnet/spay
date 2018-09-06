import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';


@Component({
	selector: 'app-admin-btc-transactions',
	templateUrl: './admin-btc-transactions.component.html',
	styleUrls: ['./admin-btc-transactions.component.scss']
})
export class AdminBtcTransactionsComponent implements OnInit {

	_param: any = {};
	_table: any;
	page : any;
	pending_transactions : any = 0;
	table_loader: boolean;
	modal_ref : any;
	data_focus : any;

	table_url: string;
	pending_transactions_url: string;

	//parameters
	account_name : string;
	transaction_status : string;
	transaction_date_from : any;
	transaction_date_to : any;

	expected_payment : any = 0;
	
	processing : boolean = false;

	constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

	ngOnInit() 
	{

		/*login token*/
		this._param["login_token"] = this.rest.login_token;

		this.transaction_status = "all";
		this.account_name = null;
		this.transaction_date_from = "";
		this.transaction_date_to = "";

		this.table_url = this.rest.api_url + "/api/admin/btc_transactions";
		this.pending_transactions_url = this.rest.api_url + "/api/admin/btc_pending_transactions";

		this.countPending();
		this.loadTable();
	}

	loadTable()
	{
		this.table_loader = true;
		this._param["account_name"] = this.account_name; 
		this._param["transaction_status"] = this.transaction_status; 
		this._param["transaction_date_from"] = this.transaction_date_from; 
		this._param["transaction_date_to"] = this.transaction_date_to;
		this._param["log_method"] = "Bitcoin";
		this._param["log_method_accepted"] = "Bitcoin Total";

		this.http.post(this.table_url, this._param).subscribe(response =>
		{
			this.countPending();
			this._table = response;
			this.table_loader = false;
			console.log(this._table);
		},
			error=>
			{
				console.log(error);
			});
	}

	countPending()
	{
		this._param["method"] = "Bitcoin";
		this.http.post(this.pending_transactions_url, this._param).subscribe(response=>
		{
			this.pending_transactions = response;
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

	viewTransactionDetails(id, selector )
	{
		this.data_focus = this.rest.findObjectByKey(this._table, 'automatic_cash_in_id', id);
		this.openLg(selector);
	}

	processTransaction(status)
	{
		this.processing = true
		this.http.post(this.rest.api_url + "/api/admin/update_transaction",
		{
			login_token : this.rest.login_token,
			action : status,
			payment : "btc",
			member_id : this.data_focus.id,
			member_log_id : this.data_focus.member_log_id,
			cash_in_date : this.data_focus.log_time
		}).subscribe(response=>
		{
			this.loadTable();
			this.modal_ref.close();
			this.processing = false
		})
	}
}
