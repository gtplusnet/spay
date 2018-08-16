import { Component, OnInit } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import {ActivatedRoute} from '@angular/router';
import { MemberInfoService }     from '../../member/member-info.service';

@Component({
  selector: 'app-change-password-request',
  templateUrl: './change-password-request.component.html',
  styleUrls: ['./change-password-request.component.scss']
})
export class ChangePasswordRequestComponent implements OnInit {
	request_id 		 	    : number;
	new_password	 	    : string;
	confirm_password 	  : string;
	verification_code	  : string;
	_param				      : any;
	config_url 			    : any;
  
	error_message		    : string;
  error_message_link  : string;
	success_message 	  : string;
	request_data		    : any;
	valid				        : boolean;
  submitted           : boolean;
	  
  constructor(private http: HttpClient, private route: ActivatedRoute, private rest: MemberInfoService) { }

  ngOnInit() {
  	this.route.params.subscribe(params=>
  	{
  		this._param={}
  		this.request_id = params["request_id"];
  		this._param["login_token"] = this.rest.login_token;
  		this.error_message = "no-message";
  		this.success_message = "no-message";
      this.error_message_link = "no-message";
  		this.checkIfUsed();
      this.submitted = false;
  	});

  }

  checkIfUsed()
  {
    this.submitted = true;
    this.config_url = this.rest.api_url + "/api/change_password_request";
  	this._param["request_id"] = this.request_id;
  	this.http.post(this.config_url,this._param).subscribe(
  		data=>
  	{
  		if(data["status"] == "success")
  		{
  			this.request_data = data["data"];
  			this.valid = true;
        this.submitted = false;
  			if(this.request_data["used"] == 1)
  			{
          this.valid = false;
          this.submitted = false;
  				this.error_message_link = "Link is used.";
  			}
  		}
      else
      {
        this.submitted = false;
        this.valid = false;
        this.error_message_link = data["message"];
      }
  	},error =>
        {
        this.error_message = JSON.stringify(error.message);
    });
  }

  onSubmit()
  {
    this.valid = true;
    this.submitted = true;
    this.success_message = "no-message";
    this.error_message = "no-message";
    this.error_message_link = "no-message";

    this.config_url = this.rest.api_url + "/api/change_password_submit";

    if(this.new_password != this.confirm_password)
    {
      this.error_message = "The new password and confirm password must match.";
    }
    else
    {
      this._param["request_id"]             = this.request_id;
      this._param["new_password"]           = this.new_password;
      this._param["verification_code"]      = this.verification_code;
      this.http.post(this.config_url,this._param).subscribe(
        data=>
      {
        if(data["status"] == "success")
        {
          this.success_message = "Password successfully change.";
          this.new_password = "";
          this.confirm_password = "";
          this.verification_code = "";
        }
        else
        { 
          this.success_message = "no-message";
          this.error_message = data["message"];
        }
      },error =>
        {
        this.error_message = JSON.stringify(error.message);
      });
    }
    this.submitted = false;
  }
}
