import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { MemberInfoService }   from '../../member/member-info.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-kyc-verification',
  templateUrl: './admin-kyc-verification.component.html',
  styleUrls: ['./admin-kyc-verification.component.scss']
})
export class AdminKycVerificationComponent implements OnInit {
	config_url 		      : any;
	page 			          : any;
	data_focus		      : any;
	modal_ref		        : any;
  success_message     : string;
  error_message       : string;
  submitted_accept    : boolean;
  submitted_reject    : boolean;
  submitted           : boolean;

	number_of_pending   : any;
	kyc_list		        : any;
	count 			        : number;
	search_name		      : string;
	level			          : number;
	date_from		        : any;
	date_to			        : any;
	table_loader	      : boolean;
  status              : string;
  constructor(private http:HttpClient, private rest : MemberInfoService, private modalService: NgbModal) { }

  ngOnInit() {
    this.success_message              = "no-message";
    this.error_message                = "no-message";
  	this.number_of_pending 		        = 0;
  	this.count 							          = 0;
    this.level                        = 0;
  	this.table_loader                 = true;
    this.submitted_accept             = false;
    this.status                       = "all";
  	this.get_kyc_pending_request();
  	this.get_kyc_list();

  }

  get_kyc_pending_request(): void
  {
  	var _param = {};
  	_param["login_token"] = this.rest.login_token;
  	this.config_url  	= this.rest.api_url + "/api/admin/get_kyc_pending";
  	this.http.post(this.config_url,_param).subscribe(response=>
  	{
  		this.number_of_pending = response;
  	},
      error=>
      {
        console.log(error);
      });
  }
  get_kyc_list() : void
  {  	
    var _param = {};
    this.table_loader           = true;
  	_param["level"] 		        = this.level;
  	_param["date_from"] 	      = this.date_from;
  	_param["date_to"]		        = this.date_to;
  	_param["search_name"]	      = this.search_name;
  	_param["login_token"] 	    = this.rest.login_token;
  	_param["status"]            = this.status;
    
  	this.config_url  	= this.rest.api_url + "/api/admin/get_kyc_list";
  	this.http.post(this.config_url,_param).subscribe(response=>
  	{
  		this.kyc_list = response;
  		this.table_loader = false;
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

	viewIdentificationDetails(kyc_upload_date, selector)
	{
    this.success_message        = "no-message";
    this.error_message          = "no-message";
		this.data_focus = this.rest.findObjectByKey(this.kyc_list, 'kyc_upload_date', kyc_upload_date);
		this.openLg(selector);
	}

  changeStatus(status,kyc_upload_date)
  {
    var _param = {}
    this.submitted           = true;
    this.config_url = this.rest.api_url + "/api/admin/change_status";
    _param["kyc_upload_date"] = kyc_upload_date;
    _param["login_token"]   = this.rest.login_token;
    if(status == "completed")
    {
      _param["status"] = status;
      this.submitted_accept = true;
    }
    else if(status == "rejected")
    {
      _param["status"] = status; 
      this.submitted_reject = true;
    }
    this.http.post(this.config_url,_param).subscribe(data=>
    {
      if(data['status'] == "success")
      {
         this.error_message = "no-message";
         this.success_message = data["message"];
         this.get_kyc_list();
         this.get_kyc_pending_request();
         this.submitted = false;
         this.submitted_reject = false;
         this.submitted_accept = false;
         this.modal_ref.close();
      }
      else
      {
         this.success_message = "no-message";
         this.error_message = data["message"];
         this.submitted = false;
         this.submitted_reject = false;
         this.submitted_accept = false;
         this.modal_ref.close();
      }
    },
      error=>
      {
        console.log(error);
      });
  }
}
