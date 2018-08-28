import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { catchError, map, tap } from 'rxjs/operators';
import { GlobalConfigService }  from '../../global-config.service';
import { Router } from "@angular/router";
import { MemberInfoService } 	from '../../member/member-info.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { FormsModule }   from '@angular/forms';

import {
  AuthService,
  FacebookLoginProvider,
  GoogleLoginProvider
} from 'angular5-social-login';


@Component({
	selector: 'app-login',
	templateUrl: './login.component.html',
	styleUrls: ['./login.component.scss']
})
export class LoginComponent implements OnInit {

	error_message = "no-message";
	submitted = false;
	username : any;
	password : any;
	configUrl : string;
	modal_ref : any;
	pair_code : string;
	validating : boolean = false;
	validate_key_url : string;
	member_id : any;
  user_data : any = {};
  social_data : any = {};
  login_url : string;
  get_datas : any = {};
  captcha : any;
  captchaRef : any;


  constructor(private http: HttpClient, private globalConfigService:GlobalConfigService, public memberInfoService: MemberInfoService, private router:Router, private modalService: NgbModal, private socialAuthService: AuthService ) // 
  { 

  }

  ngOnInit() 
  {
    this.login_url = this.memberInfoService.api_url + "/api/new_login";
    this.configUrl = this.memberInfoService.api_url + "/api/login";
    this.submitted = false;
    $("#reset_captcha").trigger('click');
  }

  // submitCaptcha(captchaResponse: string): void {
  //   this.http.post(this.captcha, {captcha: captchaResponse,});
  // }

  public captchaResponse: string = '';
  public submitCaptcha(captchaResponse: string, selector = null) {
    const newResponse = captchaResponse
      ? `${captchaResponse.substr(0, 7)}...${captchaResponse.substr(-7)}`
      : captchaResponse;
    this.captchaResponse += `${JSON.stringify(newResponse)}\n`;
    if(this.captchaResponse)
    {
      this.onSubmit(selector);
    }
  }

  onSubmit(selector) : void
  {
  	var _param = {};

  	_param["login_key"] 	= this.globalConfigService.apiConfig()["login_key"];
  	_param["username"]		= this.username;
    _param["password"]    = this.password;
  	_param["captcha"]		  = this.captcha;

  	this.submitted 				= true;
  	this.error_message = "no-message";

  	this.http.post(this.configUrl, _param).subscribe(
  		data =>
  		{

  			if(data['status'] == 'success')
  			{
  				this.globalConfigService.login(data["message"], data["name"]);
  				this.submitted 		= false;
  				window.location.href = "/member";
  			}
  			else if(data['status'] == 'google2fa_enabled')
  			{
  				this.openGoogle2FA(1, selector);
  				this.member_id = data['member_id'];
  				//console.log(this.member_id);
  			}
  			else
  			{
  				this.error_message 	= data['message'];
          this.submitted 		= false;
          $("#reset_captcha").trigger('click');
  			}
  		},
  		error =>
  		{
  			this.error_message = JSON.stringify(error.message);
  			this.submitted = false;
  		}

  		);
  }

  submit(captchaResponse: string): void 
  {
    console.log(captchaResponse);
  }

  resolved(captchaResponse: string) 
  {
      if(captchaResponse)
      {
        this.captcha = captchaResponse;
      }
  }

  openSm(content)
  {
  	//console.log(content);
  	this.modal_ref = this.modalService.open(content, {'backdrop': 'static', 'keyboard':false, 'centered' : true});
  }

  openGoogle2FA(id, selector)
  {
    this.openSm(selector);
  }

  validateCode()
  {
  	this.validating = true;
  	this.error_message = "no-message";
  	this.validate_key_url = this.memberInfoService.api_url + "/api/validate_key";
  	this.http.post(this.validate_key_url, 
  	{
  		code: this.pair_code,
  		member_id: this.member_id
  	}).subscribe(data=>
  	{
      if(data['status'] == 'success')
      {
        this.validating = false;
        this.globalConfigService.login(data["message"], data["name"]);
        this.submitted 		= false;
        window.location.href = "/member";
      }
      else
      {
        this.validating = false;
        this.error_message 	= data['message'];
        this.submitted 		= false;
      }
    })
  }

  socialSignIn(socialPlatform : string) {
    this.submitted     = true;
    let socialPlatformProvider;
    if(socialPlatform == "facebook"){
      socialPlatformProvider = FacebookLoginProvider.PROVIDER_ID;
    }else if(socialPlatform == "google"){
      socialPlatformProvider = GoogleLoginProvider.PROVIDER_ID;
    }
    
    this.socialAuthService.signIn(socialPlatformProvider).then(
      (userData) => 
      {
        this.user_data = userData;

        if(socialPlatform == "facebook")
        {
          this.get_datas = "https://graph.facebook.com/"+this.user_data.id+"?access_token="+this.user_data.token+"&fields=first_name,last_name,email";
        }
        else if(socialPlatform == "google")
        {
          this.get_datas = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token="+this.user_data.token;
        }

        this.http.get(this.get_datas).subscribe(response=>
        {
          this.social_data = response;
          this.systemLogIn();
        });
      }
      );
  }

  systemLogIn()
  {
    this.http.post(this.login_url, this.social_data).subscribe(data=>
    {
      if(data['status'] == 'success')
      {
        this.globalConfigService.login(data["message"], data["name"]);
        window.location.href = "/member";
        this.submitted     = false;
      }
      else
      {
        this.error_message   = data['message'];
        this.submitted     = false;
      }
    })
  }

}
