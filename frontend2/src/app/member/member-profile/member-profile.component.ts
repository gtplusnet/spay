import { Component, OnInit, Input } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import { ActivatedRoute } from '@angular/router';
import { MemberInfoService }   from '../member-info.service';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-profile',
  templateUrl: './member-profile.component.html',
  styleUrls: ['./member-profile.component.scss']
})
export class MemberProfileComponent implements OnInit {
  profile_page  		: any;
  kyc_page				: any;
  member_id    			: number;
  username				: any;
  first_name			: string;
  last_name				: string;
  email					: string;
  country				: number;
  phone_number	 		: number;
  configUrl				: string;
  is_update_submit		: boolean;
  kyc_status_id			: string;
  kyc_status_selfie		: string;
  submitted				: boolean;

  pairing : boolean = false;

  error_message			= "no-message";
  success_message		= "no-message";
  current_password		: string;
  password				: string;
  password_confirmation : string;

  frontIdImage			: File = null;
  backIdImage			: File = null;
  selfieImage			: File = null;
  image_upload			: any;
  id_type				: string;;
  id_number				: string;
  expiration_date		: string;
  front_link			: any;
  back_link				: any;
  checked				: boolean;
  front_image			: string;
  back_image			: string;
  selfie_image		    : string;
  selfie_link			: any;
  birthday				: string;
  referral_count		: string;
  _table				: any;
  table_loader			: boolean;
  referral_bonus_info   : any;
  google_2fa_qr : any = {};
  pair_code_google_2fa_url : string;
  pair_code : string;
  qr_code_switch : number = 0;
  
  change_status_2fa_url : string;
  pair_google_2fa_url : string;
  view_referral_info_url : string;
  referral_url : string;
  refer_able : any;
  refer_data : any;
  modal_ref : any;
  table_loader_referral : boolean;
  update_contact_number_url : any;
  country_url				: any;
  company_name				: string;
  entity					: string;
  desired_btc				: number;
  desired_eth				: number;
  country_code_id			: number;

  url_kyc_level : string;
  current_kyc_level :any;
  data_focus_transaction				: any;
  data_focus_information				: any;
  data_from								: any = {};
  country_codes							: any;
  update_info							: any;
  verified_mail 						: number;
  verification_code 					: string;
  send_kyc_email						: boolean;
  submitted_send						: boolean;
  submitted_submit						: boolean;
  other_id								: string;
  crypto_purchaser  					: any;
  agreed								: boolean;
  old_selected_crypto_purchaser			: string;
  close_result							: any;
  selected								: string;
  old_google_qr_switch 					: number;
  google_2fa_qr_loader 					: boolean;
  qr_for_enable							: boolean;
  qr_for_disable						: boolean;
  google2fa_enabled						: number;
  referral_list							: any;
  referral_from_member_log				: any;
  referral_to_member_log				: any;
  referral_log_from						: any;
  referral_log_bonus					: any;

  bonus_tx_date_from = null;
  bonus_tx_date_to = null;
  constructor(private http: HttpClient, public rest:MemberInfoService, private globalConfigService:GlobalConfigService, private route: ActivatedRoute, private modalService:NgbModal) { }


  ngOnInit() {
  		this.route.params.subscribe(params=> {
			this.error_message	 = "no-message";
			this.success_message = "no-message";
		    this.profile_page 	 = params['page'];
		    this.kyc_page 	 	 = params['kyc_page'];
		    this.member_id 	  	 = this.rest.member_id;
		    this.username 	  	 = this.rest.username;
		    this.first_name   	 = this.rest.first_name;
		    this.last_name 	  	 = this.rest.last_name;
		    this.email		  	 = this.rest.email;
		    this.phone_number 	 = this.rest.phone_number;
		    this.company_name	 = this.rest.company_name;
		    this.entity			 = this.rest.entity;
		    this.desired_btc	 = this.rest.desired_btc;
		    this.desired_eth	 = this.rest.desired_eth;
		    this.country_code_id = this.rest.country;
		    this.table_loader 	 = false;
		    this._table			 = "";
			if(this.rest.crypto_purchaser !== null)
			{
				this.crypto_purchaser = this.rest.crypto_purchaser;
				this.old_selected_crypto_purchaser = this.crypto_purchaser;
			}
			else
			{
				this.crypto_purchaser = "none";
				this.old_selected_crypto_purchaser = this.crypto_purchaser;
			}
		    this.getReferrals();
		});
		this.rest.serverGetKycStatus().subscribe(response=>
			{
				this.kyc_status_id = response['kyc_status_id'];
				this.kyc_status_selfie = response['kyc_status_selfie'];
				this.getKycLevel(this.rest.member_id);
			},
			error=>
			{
				console.log(error);
			});
		this.is_update_submit = false;
		this.checked = false;
		this.front_image = "";
		this.back_image = "";
		this.selfie_image = "";
		this.referral_url = this.rest.api_url + "/api/member/get_referral_info";
		this.view_referral_info_url = this.rest.api_url + "/api/member/get_view_referral_info";
		this.getReferralInfo();
		this.pairGoogle2FA();
		 this.http.get(this.rest.api_url + "/api/get_country_codes").subscribe(response=>
    		{
    			this.country_codes = response;
    		},
			error=>
			{
				console.log(error);
			});
		this.getIfVerifiedMail();
		if(this.rest.crypto_purchaser !== null)
		{
			this.crypto_purchaser = this.rest.crypto_purchaser;
			this.old_selected_crypto_purchaser = this.crypto_purchaser;
		}
		else
		{
			this.crypto_purchaser = "none";
			this.old_selected_crypto_purchaser = this.crypto_purchaser;
		}
  	}


open(content)
{
	this.modal_ref = this.modalService.open(content);
}

openLg(content)
{
	this.modal_ref = this.modalService.open(content, {'size': 'lg'});
}

updateMemberPassword()
{
	this.error_message	 = "no-message";
	this.success_message = "no-message";
  	this.configUrl = this.rest.api_url + "/api/member/update_password";
  	this.is_update_submit = true;
	var _param = {};
	_param['login_token'] 				= this.rest.login_token;
	_param['id']						= this.rest.member_id;
	_param['current_password'] 			= this.current_password;
	_param['password']		  			= this.password;
	_param['password_confirmation']		= this.password_confirmation;

	this.http.post(this.configUrl,_param).subscribe(
		data=>
		{
			if(data['status']=='success')
			{
				this.error_message 	 		= "no-message";
				this.success_message		= data['message'];
				this.globalConfigService.logout();
				window.location.href 		= "/";
			}
			else
			{
				this.success_message = "no-message";
				this.error_message = data['message'];
			}
			this.is_update_submit = false;
		},
		error =>
      {
        this.error_message = JSON.stringify(error.message);
      })
}
onFileSelectedFront(event)
{
	this.frontIdImage = <File>event.target.files[0];
}
onFileSelectedBack(event)
{
	this.backIdImage = <File>event.target.files[0];
}
onFileSelectedSelfie(event)
{
	this.selfieImage = <File>event.target.files[0];
}

openKycLevel2id(selector)
{
	this.error_message = "no-message";
	this.success_message = "no-message";
	this.openLg(selector);
}
openKycLevel2Selfie(selector)
{
	this.error_message = "no-message";
	this.success_message = "no-message";
	this.openLg(selector);
}
kyclevel2Id()
{
	this.submitted = true;
	this.error_message = "no-message";
	this.success_message = "no-message";
	if(this.checked)
	{
		var _param = {};
		const formData = new FormData();
		if(this.front_image != "" && this.back_image != "")
		{
			formData.append('image', this.frontIdImage);
			this.rest.uploadImageOnServer(formData,"kyc").subscribe(
				response=>{
					if(response['status'] == 'success')
					{
						this.front_link = response['full_path'];

						const formDatab = new FormData();
						formDatab.append('image',this.backIdImage);
						this.rest.uploadImageOnServer(formDatab,"kyc").subscribe(
						response=>{
							if(response['status'] == 'success')
							{
								this.back_link = response['full_path'];
							}
							else
							{
								this.success_message = "no-message";
								this.error_message = response["message"];
								this.submitted = false;
							}
						},
						error =>
				      {
				        this.error_message = JSON.stringify(error.message);
				        this.submitted = false;
				      })
					}
					else
					{
						this.success_message = "no-message";
						this.error_message = response["message"];
						this.submitted = false;
					}
				},
				error =>
		      {
		        this.error_message = JSON.stringify(error.message);
		        this.submitted = false;
		      })
			setTimeout(() => 
			{
			    if(this.front_link !="" || this.back_link !="")
    			{
    				_param["member_id"]			= this.member_id;
    				_param["id_type"]  			= this.id_type;
    				_param["id_number"]			= this.id_number;
    				_param["expiration_date"]	= this.expiration_date;
    				_param["front_id_link"]		= this.front_link;	
    				_param["back_id_link"]		= this.back_link;
    				_param["login_token"]		= this.rest.login_token;
    				_param["level"]				= 2;
    				if(_param["id_type"] == "Other")
    				{
    					_param["id_type"] = this.other_id;
    				}

    				this.configUrl = this.rest.api_url + "/api/member/member_submit_kyc_id";
    				this.http.post(this.configUrl, _param).subscribe(data=>
    				{
    					if(data["status"]=="success")
    					{
    						this.error_message = "no-message";
    						this.success_message = data["message"];
							this.kyc_status_id = "pending";
							this.modal_ref.close();
    						this.submitted = false;
    					}
    					// else
    					// {
    					// 	this.error_message = data["message"];
    					// 	this.success_message = "no-message";
    					// 	this.submitted = false;
    					// }
    				},
    				error =>
				      {
				        this.error_message = JSON.stringify(error.message);
				        this.submitted = false;
				      });
    			}
			},
			5000);
		}
		else
		{
			this.success_message = "no-message";
			this.error_message = "Please select front id picture and back id picture.";
			this.submitted = false;
		}
	}
	else
	{
		this.success_message = "no-message";
		this.error_message = "Please check the agreement to proceed.";
		this.submitted = false;
	}

}
kycLevel2Selfie()
{
	this.submitted = true;
	this.success_message = "no-message";
	this.error_message = "no-message";
	if(this.selfie_image != "")
	{
		var _param = {};
		const formData = new FormData();
		formData.append('image',this.selfieImage);
		this.rest.uploadImageOnServer(formData,"selfie").subscribe(
			response=>{
				if(response['status'] == "success")
				{
					this.selfie_link = response['full_path'];
				}
				else
				{
					this.selfie_link = "";
					this.error_message = response["message"];
    				this.success_message = "no-message";
					this.submitted = false;
				}
		},
		error =>
		    {
		      this.error_message = JSON.stringify(error.message);
		      this.submitted = false;
		    })
		setTimeout(() => 
			{
			    if(this.selfie_link !="")
    			{
    				_param["member_id"]			= this.member_id;
    				_param["selfie_link"]		= this.selfie_link;
    				_param["login_token"]		= this.rest.login_token;
    				_param["level"]				= 2;

    				this.configUrl = this.rest.api_url + "/api/member/member_submit_kyc_selfie";
    				this.http.post(this.configUrl, _param).subscribe(data=>
    				{
    					if(data["status"]=="success")
    					{
    						this.submitted = false;
    						this.error_message = "no-message";
    						this.success_message = data["message"];
							this.kyc_status_selfie = "pending";
							
    						this.modal_ref.close();
    					}
    					// else
    					// {
    					// 	this.submitted = false;
    					// 	this.error_message = data["message"];
    					// 	this.success_message = "no-message";
    					// }
    				});
    			}
			},
			5000);
	}
	else
	{
		this.submitted = false;
		this.success_message = "no-message";
		this.error_message   = "Please select image to upload.";
		setTimeout(()=>
		{
		this.error_message = "no-message";
			},3000);
	}
}

getReferrals()
{
	this.table_loader = true;
	var _params = {}
	this.configUrl = this.rest.api_url + "/api/member/get_referrals";
	_params["login_token"] = this.rest.login_token;
	_params["id"] = this.member_id;

	this.http.post(this.configUrl,_params).subscribe(data=>
	{
		this._table = data["list"];
		this.referral_count = data["count"];
		this.table_loader = false;
	})

}

getReferralInfo()
{
	var _params = {};
	_params["login_token"] = this.rest.login_token;
	_params["id"] = this.rest.member_id;
	_params["auth"] = "member";
	this.http.post(this.referral_url, _params).subscribe(response=>
	{
		this.refer_data = response;
	})
}

viewReferralInfo(id,selector)
{
	this.open(selector);
	this.loadReferralInfo(id);
}

loadReferralInfo(id)
{
	this.table_loader_referral = true;
	var _params = {}
	_params["login_token"] = this.rest.login_token;
	_params["from_id"] = id;
	_params["to_id"] = this.rest.member_id;
	_params["tx_date_from"] = this.bonus_tx_date_from;
	_params["tx_date_to"]   = this.bonus_tx_date_to;
	this.http.post(this.view_referral_info_url,_params).subscribe(data=>
	{
		if(data != null)
		{
			this.referral_bonus_info = data;
			this.data_from = data[0]["from"];
			this.table_loader_referral = false;
			this.data_focus_transaction = this.rest.findObjectByKey(this.data_from,'id',id);
			this.data_focus_information = this.rest.findObjectByKey(this._table,'id',id);
		}
		else
		{	
			this.referral_bonus_info = "";
			this.data_from = "";
			this.data_focus_information = this.rest.findObjectByKey(this._table,'id',id);
			this.table_loader_referral = false;
		}
	});
}

pairGoogle2FA()
{
	this.google_2fa_qr_loader = true;
	this.pair_google_2fa_url = this.rest.api_url + "/api/member/pair_google_2fa";
	var params_2fa = {};
	params_2fa["login_token"] = this.rest.login_token;
	params_2fa["user_id"] = this.rest.member_id; 
	this.http.post(this.pair_google_2fa_url, params_2fa).subscribe(response=>
	{
		this.google_2fa_qr = response;
		this.google2fa_enabled = this.google_2fa_qr.user.google2fa_enabled;
		this.old_google_qr_switch = this.google2fa_enabled;
		//console.log(this.google_2fa_qr);
		this.pairing = false;
		this.google_2fa_qr_loader = false;
	});
}

changeStatus2FA()
{
	this.pairing = true;
	this.change_status_2fa_url = this.rest.api_url + "/api/member/change_status_2fa";
	this.http.post(this.change_status_2fa_url,
	{
		login_token : this.rest.login_token,
		user_id : this.rest.member_id
	}).subscribe(response=>
	{
		this.modal_ref.close();
		this.old_google_qr_switch = this.google_2fa_qr.user.google2fa_enabled;
		this.pairing = false;
		window.location.href = "/member/profile/google-auth";
	})

}

showHideQrPairing()
{
	if(this.qr_code_switch == 0)
	{
		this.qr_code_switch = 1;
	}
	else
	{
		this.qr_code_switch = 0;
	}
}
getKycLevel(id)
{
	this.url_kyc_level = this.rest.api_url + "/api/member/get_kyc_level";
	var param = {}
	param["login_token"] = this.rest.login_token;
	param["id"] = id;

	this.http.post(this.url_kyc_level,param).subscribe(data=>
	{
		this.current_kyc_level = data;
	});
}

updateInformation()
{
	this.is_update_submit = true;
	this.update_contact_number_url = this.rest.api_url + "/api/member/update_contact_number";
	var param = {}
	param["login_token"] 		= this.rest.login_token;
	param["id"]					= this.rest.member_id;
	param["username"]			= this.username;
	param["entity"]				= this.entity;
	param["desired_btc"]		= this.desired_btc;
	param["desired_eth"]		= this.desired_eth;
	param["country_code_id"]	= this.country_code_id;
	param["company_name"]		= this.company_name;
	param["phone_number"]		= this.phone_number;
	param["crypto_purchaser"]	= this.crypto_purchaser;

	this.http.post(this.update_contact_number_url,param).subscribe(response=>
	{
		if(response["status"] == "success")
		{
			this.error_message = "no-message";
			this.success_message = response["message"];
			this.rest.phone_number = response["phone_number"];
			this.rest.entity = this.entity;
			this.rest.desired_btc = this.desired_btc;
			this.rest.desired_eth = this.desired_eth;
			this.rest.company_name = this.company_name;
			this.rest.username = this.username;
			this.rest.country = this.country_code_id;
			this.rest.crypto_purchaser = this.crypto_purchaser;
			this.is_update_submit = false;
		}
		else
		{
			this.success_message = "no-message";
			this.error_message = response["message"];
			this.is_update_submit = false;
		}
	});
}

entityChange()
{
	if(this.entity == "Individual")
	{
		this.desired_btc = null;
		this.desired_eth = null;
		this.company_name = null;
	}
	else
	{
		this.desired_btc = this.rest.desired_btc;
		this.desired_eth = this.rest.desired_eth;
		this.company_name = this.rest.company_name;
	}
}

getIfVerifiedMail()
{
	var param = {};
	var verified_mail_url = this.rest.api_url + "/api/member/get_verified_mail";

	param["login_token"] = this.rest.login_token;
	param["id"] = this.member_id;
	this.http.post(verified_mail_url,param).subscribe(data=>
	{
		this.verified_mail = data["verified_mail"];
		// console.log(this.verified_mail);
	});
}
idVerification(selector)
{
	this.success_message = "no-message";
	this.error_message = "no-message";
	this.open(selector);
}
SendVerification()
{
	this.error_message = "no-message";
	this.success_message = "no-message";
	this.submitted_send = true;
	this.send_kyc_email = true;
	var param = {};
	var send_verification_url = this.rest.api_url + "/api/verify_email_address";
	param["member_id"] = this.rest.member_id;
	param["email_address"] = this.rest.email;
	this.http.post(send_verification_url,param).subscribe(data=>
	{
		this.success_message = "Please check your email for verification link."
		this.submitted_send = false;
	},
	error=>
	{
		this.error_message = error;
		this.submitted_send = false;
	});
}

verifyEmailCode()
{
	this.error_message = "no-message";
	this.success_message = "no-message";
	this.submitted_submit = true;
	var param = {};
	var verify_verification_url = this.rest.api_url + "/api/member/verify_email_kyc";
	param["login_token"] = this.rest.login_token;
	param["email"] = this.rest.email;
	param["verification_code"] = this.verification_code;

	this.http.post(verify_verification_url,param).subscribe(response=>
	{
		if(response["status"] == "success")
		{
			this.error_message = "no-message";
			this.success_message = "Verify Complete."
			this.verified_mail = 1;
			this.getKycLevel(this.rest.member_id);
			this.submitted_submit = false;
			this.modal_ref.close();
		}
		else
		{
			this.success_message = "no-message";
			this.error_message = response["message"];
			this.submitted_submit = false;
		}
	},
	error=>
	{
		this.success_message = "no-message";
		this.error_message = error;
		this.submitted_submit = false;
	});
}

cryptoPurchaser(content)
{
	setTimeout(()=>
	{
		this.selected = this.crypto_purchaser;
		if(this.selected != "none")
		{	
			this.agreed = false;
			this.openLg(content);
    		this.modal_ref.result.then((result) => {
        		this.close_result = result;
    		}, (reason) => {
        	this.close_result = this.getDismissReason(reason);
        		if(this.close_result == "esc")
    			{
					this.crypto_purchaser = this.old_selected_crypto_purchaser;
    			}
    			if(this.close_result == "backdrop")
    			{
					this.crypto_purchaser = this.old_selected_crypto_purchaser;
    			}
    		});
		}
		else
		{	
			this.agreed = false;
			this.selected = "none";
			this.crypto_purchaser = "none";
		}
	},1000)
}
agreedTermAndCondition(crypto_purchaser_type)
{
	this.agreed = true;
	this.old_selected_crypto_purchaser = this.crypto_purchaser;
	this.crypto_purchaser = crypto_purchaser_type;
	this.modal_ref.close();
}

getDismissReason(reason: any): string {
    if (reason === ModalDismissReasons.ESC)
    {
        return 'esc';
    } 
    else if (reason === ModalDismissReasons.BACKDROP_CLICK) 
    {
        return 'backdrop';
    }
    else
    {
        return  reason;
    }
}
switchQrCode(selector)
{
	if(this.google2fa_enabled != 1)
	{
		this.old_google_qr_switch = 0;
		this.google2fa_enabled = 1;
		this.qr_for_disable = false;
		this.qr_for_enable = true;
		this.open(selector);
		this.modal_ref.result.then((result) => {
   		this.close_result = result;
   		}, (reason) => {
    	 	this.close_result = this.getDismissReason(reason);
    	 	if(this.close_result == "Cross click")
    	 	{
    	 		this.google2fa_enabled = this.old_google_qr_switch;
    	 	}
    	 	if(this.close_result == "esc")
   			{
				this.google2fa_enabled = this.old_google_qr_switch;
   			}
   			if(this.close_result == "backdrop")
   			{
				this.google2fa_enabled = this.old_google_qr_switch;
   			}
  		});
  	}
  	else
  	{
  		this.old_google_qr_switch = 1;
  		this.google2fa_enabled = 0;
  		this.qr_for_enable = false
  		this.qr_for_disable = true;
  		this.open(selector);
  		this.modal_ref.result.then((result) => {
   		this.close_result = result;
   		}, (reason) => {
    	 	this.close_result = this.getDismissReason(reason);
    	 	if(this.close_result == "Cross click")
    	 	{
    	 		this.google2fa_enabled = this.old_google_qr_switch;
    	 	}
    	 	if(this.close_result == "esc")
   			{
				this.google2fa_enabled = this.old_google_qr_switch;
   			}
   			if(this.close_result == "backdrop")
   			{
				this.google2fa_enabled = this.old_google_qr_switch;
   			}
  		});
  	}
}
	
}