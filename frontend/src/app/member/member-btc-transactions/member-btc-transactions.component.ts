import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-btc-transactions',
  templateUrl: './member-btc-transactions.component.html',
  styleUrls: ['./member-btc-transactions.component.scss']
})
export class MemberBtcTransactionsComponent implements OnInit {
	_param 			    	           : any;
  get_btc_transaction_url      : any;
  cancel_transaction_url       : any;
  error_message			           : string;
  table_loader					       : boolean;
  _table					             : any;
  log_status                   : number;
  page                         : any;
  modal_ref                    : any;
  data_focus                   : any;
  wallet_address               : any;
  deposited_wallet             : string;
  //params
  transaction_status      : string;
  account_name            : string;
  transaction_date_to     : any;
  transaction_date_from   : any;
  data_table : any;
  text_to_copy            : string;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit(){
    this._param = {};
    this._param["login_token"] = this.rest.login_token;
    this.get_btc_transaction_url   = this.rest.api_url + "/api/member/get_btc_transaction";
    this.cancel_transaction_url   = this.rest.api_url + "/api/member/cancel_transaction";
    this.error_message = "no-message";

    this.transaction_status = "all";
    this.account_name       = "";
    this.transaction_date_from = "";
    this.transaction_date_to = "";
    this.loadTable();
  }

  

  loadTable()
  {
    this.table_loader = true;
    this._param["member_id"]   = this.rest.member_id;
    this._param["transaction_status"]      = this.transaction_status;
    this._param["account_name"]   = this.account_name;
    this._param["transaction_date_to"]     = this.transaction_date_to;
    this._param["transaction_date_from"]     = this.transaction_date_from;
    this._param["log_method"] = "Bitcoin";
    this._param["log_method_accepted"] = "Bitcoin Total";

    this.http.post(this.get_btc_transaction_url,this._param).subscribe(
      response=>
      {
        this._table = response["list"];
        this.wallet_address = response["address"];
        this.table_loader = false;
      });
  }

  cancelTransaction(t_id)
  {
    this.table_loader = true;
    this._param["member_log_id"] = t_id;
    this._param["log_method"] = "Bitcoin";
    this.http.post(this.cancel_transaction_url, this._param).subscribe(
      response=>
      {
        this.loadTable();
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

  viewTransactionDetails(id, selectorDetails,wallet,selectorWallet)
  {
    this.data_focus = this.rest.findObjectByKey(this._table, 'automatic_cash_in_id', id);
    this.text_to_copy = this.wallet_address.member_address;
    if(wallet == 1)
    {
      this.openLg(selectorWallet);
    }
    else
    {
      this.openLg(selectorDetails);
    }
  }

  copyText(text:string) {
        const event = (t : ClipboardEvent) => {
            t.clipboardData.setData('text/plain', text);
            t.preventDefault();
            // ...('copy', e), as event is outside scope
            //document.removeEventListener('copy',t);
        }
        document.addEventListener('copy', event);
        document.execCommand('copy');
    }

}
