import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-bank-transactions',
  templateUrl: './member-bank-transactions.component.html',
  styleUrls: ['./member-bank-transactions.component.scss']
})
export class MemberBankTransactionsComponent implements OnInit {

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

  //update payment
  bank_methods : any = null;
  bank_info : any = {};
  cash_in_method : any;
  form_data         = null;
  cash_in_proof : any;
  tx_number : string;
  image_uploading : any;
  updating : boolean = false;
  update_focus : any;

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }

  ngOnInit(){
    this._param = {};
    this._param["login_token"] = this.rest.login_token;
    this.get_btc_transaction_url   = this.rest.api_url + "/api/member/get_php_transaction";
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
    this._param["log_method"] = "Bank";
    this._param["log_method_accepted"] = "Bank Total";

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
    this._param["log_method"] = "Bank";
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

  openUpdatePayment(selector, t_id)
  {
    this.error_message = "no-message";
    this.update_focus = this.rest.findObjectByKey(this._table, 'member_log_id', t_id);
    this.cash_in_proof = null;
    this.tx_number = null;
    this.open(selector);
  }

  updatePayment()
  {
    this.error_message = "no-message";
    this.updating = true;
    if(this.cash_in_proof && this.tx_number)
    {
      this.http.post(this.rest.api_url + "/api/member/update_payment_proof",
      {
        login_token : this.rest.login_token,
        id : this.update_focus.member_log_id,
        img_proof : this.cash_in_proof,
        tx_proof : this.tx_number
      }).subscribe(response=>
      {
        if(response["status"] == 'success')
        {
          this.modal_ref.close();
          this.loadTable();
        }
        else
        {
          this.error_message = response["status_message"];
        }
      })
    }
    else
    {
      this.error_message = "Proof Image/Transaction Number cannot be blank."
      this.updating = false;
    }
    
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
    this.data_focus["wallet_address"] = this.wallet_address.member_address;
    if(wallet == 1)
    {
      this.openLg(selectorWallet);
    }
    else
    {
      this.openLg(selectorDetails);
    }
  }

  onFileChange(event)
  {
    this.image_uploading = true;
    this.form_data = new FormData();

    if(event.target.files.length > 0)
    {
      this.form_data.append('upload', event.target.files[0]);
      this.form_data.append('folder', "cash_in_proof");
      this.form_data.append('login_token', this.rest.login_token);

      this.rest.uploadProofOnServer(this.form_data).subscribe(
      response =>
      {
          this.cash_in_proof = response;
          this.image_uploading = false;
      },
      error =>
      {
          this.image_uploading = false;
      });
    }
    else
    {
      this.image_uploading = false;
    }

  }

  openUploadProof()
  {
    if(!this.cash_in_proof)
    {
      $('#payment_proof').trigger('click')
    }
  }
}
