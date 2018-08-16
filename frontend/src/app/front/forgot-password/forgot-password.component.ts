import { Component, OnInit } from '@angular/core';
import { HttpClient } 		 from '@angular/common/http';
import { MemberInfoService } 	from '../../member/member-info.service';

@Component({
  selector: 'app-forgot-password',
  templateUrl: './forgot-password.component.html',
  styleUrls: ['./forgot-password.component.scss']
})
export class ForgotPasswordComponent implements OnInit {
	email			: any;
	submitted 		: boolean;
	error_message	: string;
	success_message : string;
	config_url		: any;
	_param			: any;

  constructor(private http: HttpClient, private rest: MemberInfoService) { }

  ngOnInit() {
  	this.config_url = this.rest.api_url + "/api/forgot_password";
  	this.submitted = false;
    this.error_message = "no-message";
    this.success_message = "no-message";
  }

  onSubmit():void
  {
    this.error_message = "no-message";
    this.success_message = "no-message";
    this._param = {}
    this._param["login_token"] = this.rest.login_token;
  	this.submitted = true;
  	this._param["email"] = this.email;
  	this.http.post(this.config_url,this._param).subscribe(
  		data=>
  		{
  			if(data["status"] == "success")
  			{
  				this.submitted = false;
  				this.success_message = "Request sent, please check you email.";
  			}
  			else
  			{
  				this.submitted = false;
  				this.error_message = data["message"];
  			}
  		});
  }

}
