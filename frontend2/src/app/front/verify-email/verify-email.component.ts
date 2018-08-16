import { Component, OnInit } from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import { MemberInfoService } from '../../member/member-info.service';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';

@Component({
  selector: 'app-verify-email',
  templateUrl: './verify-email.component.html',
  styleUrls: ['./verify-email.component.scss']
})
export class VerifyEmailComponent implements OnInit {

  constructor(private http: HttpClient,private globalConfigService:GlobalConfigService, private route: ActivatedRoute, private rest: MemberInfoService) { }
  	verification_code	  : any;
  	error_message		    : string;
	  success_message 	  : string;
    email               : any;
  	config_url 				  : any;
  	_param					    : any;
  	valid					      : boolean;
    submitted           : boolean;

  ngOnInit() {
  	this.route.params.subscribe(params=>
  	{
  		this._param = {};
  		this.verification_code = params["verification_code"];
  		this.error_message = "no-message";
  		this.success_message = "no-message";
  		this.config_url = this.rest.api_url + "/api/verify_code";
  		this.checkVerificationCode();
  	});
  }

checkVerificationCode()
{
	this._param["verification_code"] = this.verification_code;
	this.http.post(this.config_url,this._param).subscribe(data=>
	{
		if(data["status"] == "success")
		{
			this.valid = true;
			this.success_message = data["message"];
			this.error_message = "no-message";
		}
		else
		{
			this.valid = false;
			this.error_message = data["message"];
      this.success_message = "no-message";
      setTimeout(() => 
      {
          window.location.href='/';
      },
      2000);
		}
	},error=>
	{
		this.error_message = JSON.stringify(error.message);
	});
}

}

