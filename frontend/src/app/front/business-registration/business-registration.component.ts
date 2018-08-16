import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import { MemberInfoService }   from '../../member/member-info.service';


@Component({
  selector: 'app-business-registration',
  templateUrl: './business-registration.component.html',
  styleUrls: ['./business-registration.component.scss']
})
export class BusinessRegistrationComponent implements OnInit {
  	company_name 				: string;
	industry	 				: string;
	director_name				: string;
	country						: string;
	number_of_employees			: number;
	annual_revenue				: number;
	prefered_token_name			: string;
	contact_number				: any;
	contact_email				: any;
	remarks						: string;
	supporting_document			: File = null;
	supporting_document_name	: string;
				
	submitted					: boolean;
	error_message				: any;
	success_message				: string;
	supporting_document_link	: any;
	uploaded					: boolean;
	uploading					: boolean;
	config_url					: any;
	checked						: boolean;
	country_codes				: any;
	error_document_message 		: string;
	other_industry				: string;
	preferred_ico_name			: string;
	position					: string;

  constructor(private http : HttpClient, private rest:MemberInfoService, private globalConfigService:GlobalConfigService) { }

  ngOnInit() {
  	this.submitted = false;
  	this.error_message 	 			= "no-message";
  	this.success_message 			= "no-message";
  	this.remarks		 			= "business_registration";
  	this.error_document_message     = "no-message";
  	this.supporting_document_name 	= "";
  	this.supporting_document_link   = "";
  	this.checked					= false;
  	this.http.get(this.rest.api_url + "/api/get_country_codes").subscribe(response=>
    {
      this.country_codes = response;
    });
  }
onFileSelectedDocument(event)
{
	this.supporting_document = <File>event.target.files[0];
	this.supporting_document_link = "";
}
onSubmit()
{
	if(this.checked)
	{
		this.error_message = "no-message";
		this.success_message = "no-message";
		this.error_document_message = "no-message";
		var _param = {};
		const formData = new FormData();
		if(this.director_name != null  && this.country != null 
			&& this.contact_number != null && this.contact_email != null 
			&& this.prefered_token_name != null && this.preferred_ico_name != null)
		{
			if(this.supporting_document_name != "")
			{
				formData.append('document', this.supporting_document);
				this.uploading = true;
				this.rest.uploadDocumentOnServerForBusiness(formData).subscribe(response=>
				{
					if(response['status'] == 'success')
					{
						this.uploaded = true;
						this.uploading = false;
						this.submitted = true;
						this.supporting_document_link = response['full_path'];
					}
					else
					{
						this.uploading = false;
						this.uploaded = false;
						this.error_message = response['message'];
					}
				},
				error =>
				{
					this.error_document_message = JSON.stringify(error.message);
				});
			}
			else
			{
				_param["company_name"]  					= this.company_name;
    			_param["name"]       						= this.director_name;
    			_param["position"]							= this.position;
    			_param["country"]             				= this.country;
    			_param["number_of_employee"] 				= this.number_of_employees;	
    			_param["pref_token"]     					= this.prefered_token_name;
    			_param["pref_ico_name"]     				= this.preferred_ico_name;
    			_param["contact_number"]      				= this.contact_number;
    			_param["contact_email"]       				= this.contact_email;
    			_param["remarks"]             				= this.remarks;
	
    			this.config_url = this.rest.api_url + "/api/submit_business_registration";
    			this.http.post(this.config_url, _param).subscribe(data=>
    			{
    				if(data['status']=="success")
    				{
						this.submitted = false;
    					this.error_message = "no-message";
    					this.success_message = data["message"];
    					setTimeout(() => 
     				 	{
     				     window.location.href='/';
     				 	},
     				 	3000);
    				}
    				else
    				{
						this.submitted = false;
    					this.success_message = "no-message";
    					this.error_message = data["message"];
    				}
    			});
			}
			setTimeout(() => 
			{
				if(this.uploaded)
    			{
    				_param["company_name"]  					= this.company_name;
    				_param["name"]       						= this.director_name;
    				_param["position"]							= this.position;
    				_param["country"]             				= this.country;
    				_param["number_of_employee"] 				= this.number_of_employees;	
    				_param["supporting_document"]				= this.supporting_document_link;
    				_param["pref_token"]     					= this.prefered_token_name;
    				_param["pref_ico_name"]     				= this.preferred_ico_name;
    				_param["contact_number"]      				= this.contact_number;
    				_param["contact_email"]       				= this.contact_email;
    				_param["remarks"]             				= this.remarks;
    				this.config_url = this.rest.api_url + "/api/submit_business_registration";
    				this.http.post(this.config_url, _param).subscribe(data=>
    				{
    					if(data['status']=="success")
    					{
							this.submitted = false;
    						this.error_message = "no-message";
    						this.success_message = data["message"];
    						setTimeout(() => 
     					 	{
     					     window.location.href='/';
     					 	},
     					 	3000);
    					}
    					else
    					{
							this.submitted = false;
    						this.success_message = "no-message";
    						this.error_message = data["message"];
    					}
    				});
    			}
			},5000);
		}
	}
	else
	{
		this.success_message = "no-message";
		this.error_document_message = "Please check the box to proceed.";
	}

}

}
