import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { MemberInfoService }   from '../../member/member-info.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-transfer-token',
  templateUrl: './admin-transfer-token.component.html',
  styleUrls: ['./admin-transfer-token.component.scss']
})
export class AdminTransferTokenComponent implements OnInit {
  table_loader : any;
  page : any;
  modal_ref: any;
  receiver_credential: any;
  check_receiver_url : string;
  transfer_token_url : string;
  total_tokens_transferred_url : string;
  manual_transfer_url : string;
  manual_transfer_params : any = {};
  manual_transfer_list : any;
  _receiver: any;
  receiver: boolean = false;
  sending: boolean;
  amount_to_send : number;
  send_message : boolean = false;
  remarks : string;
  count_total_tokens_transferred : any;
  details                      : any;
  error_message   : any;
  alert_msg : boolean;
  enabled_commission : boolean = true;
  constructor(public rest: MemberInfoService, private http: HttpClient, private modalService: NgbModal) {  }

  ngOnInit() 
  {
    this.check_receiver_url = this.rest.api_url + "/api/admin/check_receiver";
    this.loadManualTransfers();
    this.manual_transfer_params.member_type = "all";
    this.error_message = "no-message";
    this.alert_msg = false;
  }

  open(content)
  {
    this.amount_to_send = null;
    this.remarks = null;
    this.sending = false;
    this.send_message = false;
    this.receiver_credential = null;
    this.checkReceiver();
  	this.modal_ref = this.modalService.open(content);
  }

  openLg(content)
  {
    this.modal_ref = this.modalService.open(content, {'size': 'lg'});
  }

  checkReceiver()
  {
    this.receiver = false;
    var _params = {};
    _params["login_token"] = this.rest.login_token;
    _params["credential"] = this.receiver_credential;
    this.http.post(this.check_receiver_url, _params).subscribe(response=>
    {
      this._receiver = response;
      if(this._receiver.message = "success")
      {
        this.receiver = true;
      }
    },
      error=>
      {
        console.log(error);
      });
  }

  transferToken(address_id)
  {
    this.alert_msg = confirm("Are you sure to transfer token to " + this.receiver_credential + "?");
    if(this.alert_msg)
    {
      this.error_message = "no-message";
      this.transfer_token_url = this.rest.api_url + "/api/admin/transfer_token";
      this.sending = true;
  	  var _param = {};
      _param["login_token"] = this.rest.login_token;
      _param["amount"] = this.amount_to_send;
      _param["remarks"] = this.remarks;
      _param["address_id"] = address_id;
      _param["enabled_commission"] = this.enabled_commission;
  
      this.http.post(this.transfer_token_url, _param).subscribe(response=>
      {
          var message;
          message = response["status"];
          if(message == "success")
          {
            this.alert_msg = false;
            this.sending = false;
            this.emptyTransfer();
            this.loadManualTransfers();
            setTimeout(()=>
            {
                this.modal_ref.close();
            },3000);
          }
          else
          {
            this.alert_msg = false;
            this.error_message = response["message"];
            this.sending = false;
          }
      },
      error=>{error});
    }
    else
    {
      this.alert_msg = false;
      this.sending = false;

    }
  }

  emptyTransfer()
  {
    this.send_message = true;
    this.amount_to_send = null;
    this.remarks = null;
  }

  loadManualTransfers()
  {
    this.table_loader = true;
    this.manual_transfer_url = this.rest.api_url + "/api/admin/manual_transfer_list";
    this.manual_transfer_params.login_token = this.rest.login_token;
    this.http.post(this.manual_transfer_url, this.manual_transfer_params).subscribe(response=>
    {
      this.manual_transfer_list = response;
      this.table_loader = false;
      this.loadTotalTokensTransferred();
    },
      error=>
      {
        console.log(error);
      });
  }

  loadTotalTokensTransferred()
  {
    this.total_tokens_transferred_url = this.rest.api_url + "/api/admin/total_tokens_transferred";
    var __params = {};
    __params["login_token"] = this.rest.login_token;
    this.http.post(this.total_tokens_transferred_url, __params).subscribe(response=>
    {
      this.count_total_tokens_transferred = response;
    },
      error=>
      {
        console.log(error);
      });
  }

  viewDetailsTransferToken(id,selector)
  {
    this.details = this.rest.findObjectByKey(this.manual_transfer_list,'member_log_id',id);
    this.open(selector);
  }
}
