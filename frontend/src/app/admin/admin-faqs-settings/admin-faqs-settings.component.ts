import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-faqs-settings',
  templateUrl: './admin-faqs-settings.component.html',
  styleUrls: ['./admin-faqs-settings.component.scss']
})
export class AdminFaqsSettingsComponent implements OnInit {

  constructor(public rest : MemberInfoService, private http: HttpClient, private modalService : NgbModal) { }

  modal_ref 			: any;
  faqs_table 			: any;
  faqs_table_loader 	: boolean
  faqs_table_url		: any;
  faqs_param			: any = {};
  faqs_add				: any;
  faqs_edit				: any;
  data_faq				: any;
  faq_id				: any;
  submitted				: boolean;

  success_message 		: string;
  error_message			: any;

  ngOnInit() {
  	this.success_message = "no-message";
  	this.error_message = "no-message";
  	this.faqs_param.login_token = this.rest.login_token;
  	this.faqs_param.category = "all";
  	this.faqs_table_url = this.rest.api_url + "/api/admin/get_faqs";
  	this.faqs_add = this.rest.api_url + "/api/admin/add_faqs";
  	this.faqs_edit = this.rest.api_url + "/api/admin/edit_faqs";
  	this.getFaqs();
  }

open(content)
{
	this.modal_ref = this.modalService.open(content);
}
openLg(content)
{
	this.modal_ref = this.modalService.open(content, {'size': 'lg'});
}

  getFaqs()
  {
  	this.faqs_table_loader = true;
  	this.http.post(this.faqs_table_url,this.faqs_param).subscribe(data=>
  	{
  		this.faqs_table = data;
  		this.faqs_table_loader = false;
  	},
      error=>
      {
        console.log(error);
      });
  }

  viewDetailsFaq(id,selector)
  {
  	this.success_message = "no-message";
  	this.error_message = "no-message";
  	if(id != 0)
  	{
  		this.faqs_param.edit_id = id;
  		this.data_faq = this.rest.findObjectByKey(this.faqs_table,'faq_id',id);
  		this.faqs_param.edit_category = this.data_faq.faq_category;
  		this.faqs_param.edit_answer = this.data_faq.faq_answer;
  		this.faqs_param.edit_question = this.data_faq.faq_question;
  		this.faqs_param.edit_status = this.data_faq.is_active;
  		this.openLg(selector);
  	}
  	else
  	{
      this.faqs_param.add_category = "";
      this.faqs_param.add_answer = "";
      this.faqs_param.add_question = "";
      this.faqs_param.add_status = "";
  		this.openLg(selector);
  	}
  }

  onSubmitAdd()
  {
  	this.success_message = "no-message";
  	this.error_message = "no-message";
  	this.submitted = true;
  	this.http.post(this.faqs_add,this.faqs_param).subscribe(data=>
  	{
  		if(data["status"] == "success")
  		{
  			this.submitted = false;
        this.success_message = data["message"];
        setTimeout(()=>
        {
          this.modal_ref.close();
          this.getFaqs();
        },2000);
  		}
  		else
  		{
  			this.success_message = "no-message";
  			this.error_message = data["message"];
  			this.submitted = false;
  		}
  	},
      error=>
      {
        console.log(error);
      });
  }

  onSubmitEdit()
  {
  	this.success_message = "no-message";
  	this.error_message = "no-message";
  	this.submitted = true;
  	this.http.post(this.faqs_edit,this.faqs_param).subscribe(data=>
  	{
  		if(data["status"] == "success")
  		{
  			this.submitted = false;
        this.success_message = data["message"];
        setTimeout(()=>
        {
          this.modal_ref.close();
          this.getFaqs()
        },2000);
  		}
  		else
  		{
  			this.success_message = "no-message";
  			this.error_message = data["message"];
  			this.submitted = false;
  		}
  	},
      error=>
      {
        console.log(error);
      });
  }
}
