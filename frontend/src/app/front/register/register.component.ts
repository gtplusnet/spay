import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import {Observable} from 'rxjs/Rx';
import { MemberInfoService } from '../../member/member-info.service';
import { ActivatedRoute, Params } from '@angular/router';
import { GlobalConfigService }  from '../../global-config.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';


import {
    AuthService,
    FacebookLoginProvider,
    GoogleLoginProvider
} from 'angular5-social-login';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrls: ['./register.component.scss']
})
export class RegisterComponent implements OnInit {
  
  error_message = "no-message";
  submitted = false;
  country_codes : any;
  _params : any;
  get_datas : any = {};
  system_register : any = {};
  recaptcha_v2_token : any = "test";

  modal_ref : any;
  pair_code : string;
  validating : boolean = false;
  validate_key_url : string;
  member_id : any;

  //registration form
  full_name : string;
  first_name : string;
  last_name : string;
  email : string;
  user_name : string;
  country_code : any;
  phone_number : string;
  password : string;
  password_confirmation : string;
  entity: any;
  birth_date: any;
  company_name: string;
  desired_btc: number;
  desired_eth: number;

  login_url: string;
  register_url: string;
  login_data: any = {};
  registering: boolean = false;

  referral_link : any;
  career : any;

  user_data : any;
  social_data : any;
  //validation
  email_approve : boolean = false;
  fname_approve : boolean = false;
  lname_approve : boolean = false;
  password_approve : boolean = false;
  confirm_password_approve : boolean = false;
  register_approve : boolean = false;

  form_data = null;
  uploading : boolean = false;
  primary_1 = null;
  secondary_1 = null;
  secondary_2 = null;

  primary_id1 = null;
  secondary_id1 = null;
  secondary_id2 = null;
  selfie_id = null;

  secondary_ids = [
  "NBI Clearance",
  "Police Clearance",
  "Barangay Clearance",
  "Cedula or Community Tax Certificate",
  "Voter’s Certification",
  "Government Service Record",
  "School ID (For Recent students)",
  "Seaman’s Book",
  "Philhealth Card",
  "PWD ID",
  "TIN Card",
  "Firearm’s",
  "PLRA ID",
  "Company ID",
  "Alumni ID"]

  secondary_ids2 = [
  "NBI Clearance",
  "Police Clearance",
  "Barangay Clearance",
  "Cedula or Community Tax Certificate",
  "Voter’s Certification",
  "Government Service Record",
  "School ID (For Recent students)",
  "Seaman’s Book",
  "Philhealth Card",
  "PWD ID",
  "TIN Card",
  "Firearm’s",
  "PLRA ID",
  "Company ID",
  "Alumni ID"]


  public captchaResp = null;

  country_loading : boolean;

  constructor(private http : HttpClient, private rest : MemberInfoService, private route: ActivatedRoute, private socialAuthService: AuthService, private globalConfigService:GlobalConfigService, private modalService: NgbModal) {  
    
    this.referral_link = this.route.snapshot.queryParams["referral"] ? this.route.snapshot.queryParams["referral"] : null;

    switch(this.route.snapshot.queryParams["career"])
    {
      case "community_manager":
        this.career = 2;
        break;
      case "marketing_director":
        this.career = 3;
        break;
      case "ambassador":
        this.career = 4;
        break;
      case "advisor":
        this.career = 5;
        break;
      default:
        this.career = 1;
        break;
    }
  }

  public captchaResponse: string = '';
  public submitCaptcha(captchaResponse: string) {
    const newResponse = captchaResponse
      ? `${captchaResponse.substr(0, 7)}...${captchaResponse.substr(-7)}`
      : captchaResponse;
    this.captchaResponse += `${JSON.stringify(newResponse)}\n`;
    if(this.captchaResponse)
    {
      this.newRegister();
    }
    else
    {
    }
  }

  ngOnInit() {
    this.login_url = this.rest.api_url + "/api/new_login";
    this.register_url = this.rest.api_url + "/api/new_register";
    this.country_loading = true;
    this.country_code = 0;
    this.entity = 0;
    $("#reset_captcha").trigger('click');
  	this.http.get(this.rest.api_url + "/api/get_country_codes").subscribe(response=>
  	{
      this.country_codes = response;
      this.system_register.country_code_id = this.country_codes[0].country_code_id;
      this.system_register.gender          = "Male";
      this.country_loading = false;
  	});
    this.primary_id1 = "Passport";
    this.secondary_id1 = this.secondary_ids[0];
    this.secondary_id2 = this.secondary_ids2[0];
    this.changeSecondary();
  }

  changeSecondary()
  {
    this.secondary_ids2 = [
    "NBI Clearance",
    "Police Clearance",
    "Barangay Clearance",
    "Cedula or Community Tax Certificate",
    "Voter’s Certification",
    "Government Service Record",
    "School ID (For Recent students)",
    "Seaman’s Book",
    "Philhealth Card",
    "PWD ID",
    "TIN Card",
    "Firearm’s",
    "PLRA ID",
    "Company ID",
    "Alumni ID"]

    for(var i = 0; i <= this.secondary_ids2.length; i++)
    {
      if(this.secondary_id1 == this.secondary_ids2[i])
      {
        this.secondary_ids2.splice(i, 1);
      }
    }
  this.secondary_id2 = this.secondary_ids2[0];
  }

  resolved(captchaResponse: string) 
  {
      this.http.post(this.rest.api_url + "/api/verify_captcha", 
      {
        response : captchaResponse
      });

      this.captchaResp = captchaResponse;
  }

  socialSignIn(socialPlatform : string) {
    this.error_message = "no-message";
    
    this.registering = true;
    let socialPlatformProvider;

    if(socialPlatform == "facebook")
    {
      socialPlatformProvider = FacebookLoginProvider.PROVIDER_ID;
    }else if(socialPlatform == "google")
    {
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
            this.submitted = true;
            this.onRegister(this.social_data, socialPlatform);
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

  socialLogIn()
  {
    this.http.post(this.login_url, this.social_data).subscribe(data=>
    {
      if(data['status'] == 'success')
          {
            this.globalConfigService.login(data["message"], data["name"]);
            this.submitted     = false;
            window.location.href = "/member";
          }
          else
          {
            this.error_message   = data['message'];
            this.submitted     = false;
          }
    })
  }

  onRegister(params, platform) : void
  {
    if(platform == "google")
    {
      params.first_name = params.given_name;
      params.last_name = params.family_name;
    }

    params.login_key        = this.globalConfigService.apiConfig()["login_key"];
    params.platform         = platform;
    params.referral_link    = this.referral_link;
    params.career_id        = this.career;
    if(this.rest._stages)
    {
      params.sale_stage_id    = this.rest._stages.sale_stage_id;
    }
    else
    {
      params.sale_stage_id    = null;
    }
    

    this.http.post(this.register_url, params).subscribe(
      data =>
      {

        if(!this.captchaResp && platform == 'system')
        {
          this.error_message = "Captcha is required. If you do not see any captcha please reload the page.";
          this.submitted = false;
        }
        else if(data['status'] == 'success')
        {
          if(platform != "system")
          {
            this.socialLogIn();
          }
          else
          {
            window.location.href = "/login";
          }
        }
        else
        {
          this.error_message   = data['message'];
          this.submitted     = false;
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

  newRegister() 
  {
    this.error_message = "no-message";
    this.submitted = true;
    
    this.system_register.primary_id1 = this.primary_id1
    this.system_register.secondary_id1 = this.secondary_id1
    this.system_register.secondary_id2 = this.secondary_id2

    this.system_register.primary_id = this.primary_1
    this.system_register.secondary_id_1 = this.secondary_1
    this.system_register.secondary_id_2 = this.secondary_2

    this.system_register.selfie_verification = this.selfie_id


    this.onRegister(this.system_register, 'system');  
  }


  register() 
  {
    this._params = {};
    this._params["first_name"]                       = this.first_name;
    this._params["last_name"]                        = this.last_name;
    this._params["email"]                            = this.email;
    this._params["username"]                         = this.user_name;
    this._params["country_code"]                     = this.country_code;
    this._params["phone_number"]                     = this.phone_number;
    this._params["password"]                         = this.password;
    this._params["password_confirmation"]            = this.password_confirmation;
    this._params["entity"]                           = this.entity;
    this._params["birth_date"]                       = this.birth_date;
    this._params["company_name"]                     = this.company_name;
    this._params["desired_btc"]                      = this.desired_btc;
    this._params["desired_eth"]                      = this.desired_eth;
    this._params["referral_link"]                    = this.referral_link;
    this._params["career_id"]                        = this.career;
    this._params["sale_stage_id"]                    = this.rest._stages.sale_stage_id;
    // this._params["career"] = this.has_career ? this.career : 1;

    // if(this.has_referral_link)
    // {
    //   this._params["referral_link"] = this.referral_link;
    // }
    // else
    // {
    //   this._params["referral_link"] = null;
    // }
    
    this.submitted = true;
    this.error_message = "no-message";
    
    this.http.post(this.rest.api_url + "/api/register", this._params).subscribe(response=>
    {

      if(response['status'] == 'success')
        {
          window.location.href='/login';
          this.submitted = false;
         this.registering = false;

        }
        else
        {
          this.error_message = response['message'];
          this.submitted = false;
          this.registering = false;
        }
      },
      error =>
      {
        this.error_message = JSON.stringify(error.message);
        this.submitted = false;
      });
  }

  validate()
  {
   
    var e_val = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    this.email_approve = e_val.test(String(this.system_register.email).toLowerCase()) ? true : false;
    
    var name_val = /^[a-zA-Z-,]+(\s{0,1}[a-zA-Z-, ])*$/;
    this.fname_approve = name_val.test(String(this.system_register.first_name).toLowerCase()) ? true : false; 
    this.lname_approve = name_val.test(String(this.system_register.last_name).toLowerCase()) ? true : false;
    // console.log(this.email_approve, this.fname_approve, this.lname_approve);
    if(this.email_approve && this.fname_approve && this.lname_approve)
    {
      this.register_approve = true;
    }
  }

  openSm(content)
  {
    //console.log(content);
    this.modal_ref = this.modalService.open(content, {'backdrop': 'static', 'keyboard':false, 'centered' : true});
  }



  onFileChange(event, type)
  {
    if(type == 'secondary_2')
    {
      this.primary_1 = null;
    }
    else if(type == 'primary_1')
    {
      this.primary_1 = null;
      this.secondary_1 = null;
      this.secondary_2 = null;
    }

    this.form_data = new FormData();

    if(event.target.files.length > 0)
    {
      this.form_data.append('upload', event.target.files[0]);
      this.form_data.append('folder', 'kyc_proof');

      this.uploading         = true;

      this.rest.uploadProof(this.form_data).subscribe(response =>
      {
        if(response)
        {
          if(type == 'primary_1')
          {
            this.primary_1 = response;
          }
          else if(type == 'secondary_1')
          {
            this.secondary_1 = response;
          }
          else if(type == 'secondary_2')
          {
            this.secondary_2 = response;
          }
          else if(type == 'selfie_id')
          {
            this.selfie_id = response;
          }
          console.log(this.primary_1, this.secondary_1, this.secondary_2)
          this.uploading       = false;
        }
      });
    }
  }

  uploadProof(id)
  {
    if(!this.primary_1 && !this.secondary_1)
    {
      $("#"+id).trigger('click');
    }
    else if(this.secondary_1 && !this.primary_1 && !this.secondary_2)
    {
      $("#"+id).trigger('click');
    }

    if(id == "selfie_id" && !this.selfie_id)
    {
      $("#"+id).trigger('click');
    }
  }
}
