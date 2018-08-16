import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { MemberInfoService } from '../../member/member-info.service';

@Component({
  selector: 'app-resend-email',
  templateUrl: './resend-email.component.html',
  styleUrls: ['./resend-email.component.scss']
})
export class ResendEmailComponent implements OnInit {
	_param 				    : any;
	config_url			  : any;
	email				      : string;
	success_message 	: string;
	error_message		  : string;
  submitted         : boolean;

  constructor(private http:HttpClient, private rest:MemberInfoService) { }

  ngOnInit() 
  {
   this._param={};
   this.config_url = this.rest.api_url + "/api/resend_email";
   this.success_message = "no-message";
   this.error_message = "no-message";
   this.submitted = false;
  }
  resendEmailVerification()
{
   this.success_message = "no-message";
   this.error_message = "no-message";
   this.submitted = true;
   this._param["email"] = this.email;
   this.http.post(this.config_url,this._param).subscribe(data=>
   {
     if(data["status"] == "success")
    {
      this.submitted = false;
      this.success_message = data["message"];
      this.error_message = "no-message";
      setTimeout(() => 
      {
          window.location.href='/';
      },
      2000);
    }
    else
    {
      this.submitted = false;
      this.error_message = data["message"];
      this.success_message = "no-message";
    }
  },error=>
  {
    this.submitted = false;
    this.error_message = JSON.stringify(error.message);
  });
  }

}
