import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-business-application',
  templateUrl: './admin-business-application.component.html',
  styleUrls: ['./admin-business-application.component.scss']
})
export class AdminBusinessApplicationComponent implements OnInit {
 table_loader 		: boolean;
 search_name				    : string;
 date_from							: string;
 date_to							  : string;
	
 company_name						  : string;
 company_line						  : string;
 director_name						: string;
 business_country					: string;
 employee_number					: number;
 annual_revenue						: number;
 document_link						: string;
 prefer_token_name					: string;
 contact_number						: number;
 contact_email						: string;
 date_submitted						: string;
 position                  : string;
 pref_ico_name              : string;
	
 business_application_list  		: any;
 business_application_details		: any;
 business_application_count			: any;
 get_business_application_list  	: any;
 get_business_application_details	: any;
 modal_ref							: any;
 data_focus             : any;

  constructor(private rest:MemberInfoService, private http:HttpClient, private modalService:NgbModal) { }

  ngOnInit() {
  	this.table_loader = false;
  	this.business_application_count = 0;
  	this.get_business_application_list = this.rest.api_url + "/api/admin/get_business_application_list";
  	this.get_business_application_details = this.rest.api_url + "/api/admin/get_business_application_details";
  	this.getBusinessApplicationList();
  }

  open(content)
   {
   	this.modal_ref = this.modalService.open(content);
   }
   openLg(content)
   {
   	this.modal_ref = this.modalService.open(content, {'size': 'lg'});
   }

  getBusinessApplicationList()
  {
  	this.table_loader = true;
  	var _param = {}
  	_param["login_token"]  = this.rest.login_token;
  	_param["name"]         = this.search_name;
  	_param["date_from"]	   = this.date_from;
  	_param["date_to"]	     = this.date_to;

  	this.http.post(this.get_business_application_list,_param).subscribe(response=>
  	{
  		this.business_application_list = response["list"];
  		this.business_application_count = response["count"];
  		this.table_loader = false;
  	},
      error=>
      {
        console.log(error);
      });
  }

  viewBusinessApplicationDetails(id,selector)
  {
    this.data_focus = this.rest.findObjectByKey(this.business_application_list,'business_application_id',id);
    console.log(this.data_focus);
  	this.openLg(selector);
  }

}
