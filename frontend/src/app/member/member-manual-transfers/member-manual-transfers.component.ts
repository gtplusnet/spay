import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-manual-transfers',
  templateUrl: './member-manual-transfers.component.html',
  styleUrls: ['./member-manual-transfers.component.scss']
})
export class MemberManualTransfersComponent implements OnInit {

  constructor(private rest : MemberInfoService, private http: HttpClient, private modalService: NgbModal) { }

  	table_loader 				: boolean;
  	manual_transfer_list 		: any;
  	manual_transfer_params  	: any = {};
  	manual_transfer_list_url	: any;
  	modal_ref					: any;
  	data_focus					: any;
  ngOnInit() {
  	this.manual_transfer_list_url = this.rest.api_url + "/api/member/get_manual_transfer_list";
  	this.getManualTransferList();
  }

   openLg(content)
  {
    this.modal_ref = this.modalService.open(content, {'size': 'lg'});
  }

   open(content)
   {
   	 this.modal_ref = this.modalService.open(content);
   }

  getManualTransferList()
  {
    this.table_loader = true;
  	this.manual_transfer_params.login_token = this.rest.login_token;
  	this.manual_transfer_params.id = this.rest.member_id;

  	this.http.post(this.manual_transfer_list_url,this.manual_transfer_params).subscribe(data=>
  	{
  		this.manual_transfer_list = data;
  		this.table_loader = false;
  	},
      error=>
      {
        console.log(error);
      });
  }

  viewTransferListDetails(member_log_id,selector)
  {
  	this.data_focus = this.rest.findObjectByKey(this.manual_transfer_list, 'member_log_id', member_log_id);
  	this.open(selector);
  	//console.log(this.data_focus);
  }
}
