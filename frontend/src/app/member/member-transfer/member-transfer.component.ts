import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-transfer',
  templateUrl: './member-transfer.component.html',
  styleUrls: ['./member-transfer.component.scss']
})
export class MemberTransferComponent implements OnInit {

  modal_ref : any;
  alert_message = "no-message";
  alert_status = "error";
  //recipient check
  loading = false;
  tbl_loading = false;
  loading_msg : string;
  recipient_address : string;
  receiver_data : any;
  token_amount : number;
  request : any = {};
  table : any;

  page : any;

  constructor(private rest : MemberInfoService, private http: HttpClient, private modalService: NgbModal) { }

  ngOnInit() 
  {
    this.getTransferLogs();
  }

  open(content)
  {
    this.receiver_data = null;
    this.alert_message = "no-message";
    this.modal_ref = this.modalService.open(content);
  }

  getTransferLogs()
  {
    this.tbl_loading = true;
    this.request.login_token  = this.rest.login_token
    this.request.member_id    = this.rest.member_id
    this.http.post(this.rest.api_url + "/api/member/get_transfer_logs", this.request).subscribe(response=>
    {
      this.table = response;
      this.tbl_loading = false;
    })
  }

  validateCheckRequest()
  {
    this.receiver_data = null
    this.alert_message = "no-message"
    this.loading_msg = "Checking Recipient"
    this.loading = true
    if(this.recipient_address)
    {
      this.checkRecipientAddress()
    }
    else
    {
      this.alert_message = "Recipient's wallet address cannot be blank"
      this.loading = false
    }
  }

  checkRecipientAddress()
  {
    this.http.post(this.rest.api_url + "/api/member/check_member_transfer",
    {
      login_token : this.rest.login_token,
      receiver : this.recipient_address,
      member_id : this.rest.member_id
    }).subscribe(response=>
    {
      if(response["status"] == "success")
      {
        this.receiver_data = response["data"];
      }
      else
      {
        this.alert_message = response["status_message"];
      }
      this.loading = false
    })
  }

  sendTokens()
  {
    this.alert_message = "no-message"
    this.loading_msg = "Sending Tokens"
    this.loading = true
    if(this.receiver_data)
    {
      this.http.post(this.rest.api_url + "/api/member/record_member_transfer",
      {
        login_token : this.rest.login_token,
        receiver : this.recipient_address,
        sender : this.rest.member_id,
        amount : this.token_amount
      }).subscribe(response=>
      {
        if(response["status"] == "success")
        {
          this.receiver_data = response["data"];
          this.getTransferLogs();
        }
        this.alert_status  = response["status"];
        this.alert_message = response["status_message"];
        this.loading = false
      })
    }
    else
    {
      this.alert_message = "Something went wrong. Please re-check and try again."
    }
  }

  copyText(text:string) 
  {
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
