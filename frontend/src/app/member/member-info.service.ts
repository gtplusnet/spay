import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { Observable } 				from "rxjs/Observable";
import { CurrencyPipe } 			from '@angular/common';
import { isDevMode } from '@angular/core';


@Injectable()
export class MemberInfoService 
{
	config 						: object;
	api_url 					: string;
	login_token 				: string;
	member_id 					: number;
	first_name 					: string;
	last_name 					: string;
	email 						: string;
	birthday					: string;
	phone_number 				: number;
	username	 				: string;
	is_admin 					: boolean;
	created_at					: string;
	_wallet 					: any;
	_transaction 				: any;
	_transaction_confirmed 		: any;
	_transaction_pending 		: any;
	_transaction_processing		: any;
	_cash_in_method 			: any;
	_cash_out_method 			: any;
	_exchange_rate				: any;
	_rates 						: any;
	_stages						: any;
	_bonus						: any;
	stage_count_down			: any;
	loading 					: boolean;
	_conversion 				: any;
	devmode 					: boolean;
	btc_transaction_fee			: number;
	kyc_id_status				: string;
	first_time_login			: boolean;
	platform					: string;
	country						: number;
	entity						: string;
	company_name				: string;
	desired_btc					: number;
	desired_eth					: number;
	verified_mail				: number;
	status_account				: number;
	crypto_purchaser			: string;
	_files : any = {};

  	constructor(private http : HttpClient)
	{
		this.login_token 				= localStorage.getItem('login_token');

		if(isDevMode())
		{
			this.api_url 				= "http://ico.test";
		}
		else
		{
			this.api_url 				= "http://api-ahm.digimahouse.com";
		}
		
		this.first_name 				= "";
		this.last_name 					= "";
		this.is_admin 					= false;
		this._wallet 					= null;
		this._cash_in_method 			= null;
		this._cash_out_method 			= null;
		this._transaction 				= null;
		this._transaction_pending 		= null;
		this._transaction_confirmed 	= null;
		this._transaction_processing	= null;
		this._rates 					= null;
		this._stages					= null;
		this.loading 					= false;
	}

	serverGetBasicInfo() : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/member/member_info";
		var param 				= {};
		param['login_token'] 	= app.login_token;
		return app.http.post(sync_url, param);
	}
	
	syncFromServerResponseMember(response) : void
	{
		var app 						= this;
		app.member_id 					= response['member_id'];
		app.first_name 					= response['first_name'];
		app.last_name 					= response['last_name'];
		app.email 						= response['email'];
		app.birthday					= response['birth_date'];
		app.phone_number 				= response['phone_number'];
		app.entity						= response['entity'];
		app.company_name				= response['company_name'];
		app.country						= response['country_code_id'];
		app.desired_btc					= response['desired_btc'];
		app.desired_eth					= response['desired_eth'];
		app.first_time_login 			= response['first_time_login'];
		app.verified_mail 				= response['verified_mail'];
		app.status_account 				= response['status_account'];
		app.platform 					= response['platform'];
		app.username 					= response['username'];
		app.is_admin 					= (response['is_admin'] == 0 ? false : true);
		app.crypto_purchaser			= response['crypto_purchaser'];
		app.created_at					= response['created_at'].date;
		app.btc_transaction_fee         = response['btc_transaction_fee'];
		app._wallet 					= {};
		app._transaction 				= response['_transaction'];
		app._transaction_confirmed 		= response['_transaction_confirmed'];
		app._transaction_pending 		= response['_transaction_pending'];
		app._transaction_processing		= response['_transaction_processing'];
		
		response['_wallet'].forEach(wallet =>
		{
			app._wallet[wallet['coin_id']] 						= wallet;
			app._wallet[wallet['coin_id']]['display_wallet'] 	= this.formatDisplayWallet(wallet['address_balance'], wallet['coin_abb'], wallet['coin_decimal']);
		});
	}

	reloadMemberInfo()
	{
		this.serverGetBasicInfo().subscribe(response =>
		{
			this.syncFromServerResponseMember(response);
		});
	}

	serverGetOtherInfo() : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/member/other_info";
		var param 				= {};

		app._conversion 		= {};

		param['login_token'] 	= app.login_token;

		return app.http.post(sync_url, param);
	}

	syncFromServerResponseOther(response) : void
	{
		var app = this;
		this._rates 					= response;
	}

	serverGetSaleStage() : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/member/sale_stages";
		var param 				= {};

		param['login_token'] 	= app.login_token;
		return app.http.post(sync_url, param);
	}

	syncFromServerGetSaleStage(response) : void
	{
		var app = this;
		this._stages = response;
	}

	serverGetLiveExchangeRate() : Observable<object>
	{
		var app = this;
		var sync_url = "https://min-api.cryptocompare.com/data/pricemulti?fsyms=BTC,ETH&tsyms=USD";

		return app.http.get(sync_url);
	}

	syncFromServerGetLiveExchangeRate(response) : void
	{
		var app = this;
		this._exchange_rate = response;
		app.http.get("https://api.exchangeratesapi.io/latest?base=PHP").subscribe(response=>
		{
			this._exchange_rate.PHP = response["rates"].USD;
		})
	}

	uploadImageOnServer(form_data,uploadfor) : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/member/upload_file";

		form_data.append('login_token', app.login_token);
		form_data.append('for',uploadfor);
		return app.http.post(sync_url, form_data);
	}

	uploadProofOnServer(form_data) : Observable<object>
    {
        var app                 = this;
		var sync_url            = app.api_url + "/api/member/upload";
		
		// form_data.login_token = app.login_token
        return app.http.post(sync_url, form_data);
	}
	
	uploadDocumentOnServerForBusiness(form_data) : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/upload_file_business";

		form_data.append('login_token', app.login_token);
		return app.http.post(sync_url, form_data);
	}

	uploadSystemFiles(form_data) : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/upload_system_files_documents";

		form_data.append('login_token', app.login_token);
		return app.http.post(sync_url, form_data);
	}

	submitPaymentProof(param) : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/submit_proof";
		param['login_token'] 	= app.login_token;

		return app.http.post(sync_url, param);
	}

	formatDisplayWallet(balance, currency, decimal) : string
	{
		var mult 	= Math.pow(10, decimal);
		var truncated = Math.floor(balance * mult) / mult;
		return currency + " " + this.addCommas(truncated);	
		//return currency + " " + this.addCommas(balance.toFixed(decimal));
	}

	addCommas(nStr)
	{
		nStr += '';
		var x = nStr.split('.');
		var x1 = x[0];
		var x2 = x.length > 1 ? '.' + x[1] : '';
		var rgx = /(\d+)(\d{3})/;
		while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
		}
		return x1 + x2;
	}

	findObjectByKey(array, key, value) : any
	{
		var ret = null;

		array.forEach(data =>
		{
			if(data[key] == value)
			{
				ret = data;
			}
		});

		return ret;
	}

	serverGetKycStatus() : Observable<object>
	{
		var app 		= this;
		var sync_url	= app.api_url + "/api/member/get_kyc_status";
		var param = {};
		var res = null;

		param['login_token']	= app.login_token;
		param['member_id']		= app.member_id;
		return app.http.post(sync_url,param);
	}

	serverGetFiles() : Observable<object>
	{
		var app 				= this;
		var sync_url			= app.api_url + "/api/get_system_files";

		return app.http.get(sync_url);
	}

	syncFromServerGetFiles(response) : void
	{
		var app = this;
		app._files 					= response;
	}

	formatDate(date)
	{
		var t = date.split(/[- :]/);
		var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
		var new_date = new Date(d);
		var month = new_date.getMonth() + 1;
		var year = new_date.getFullYear();
		var day = new_date.getDate();
		return month + "/" + day + "/" + year;
	}

	formatTime(time)
	{
		var t = time.split(/[- :]/);
		var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
		var new_date = new Date(d);
		var hour = new_date.getHours();
		var minute = new_date.getMinutes();
		var seconds = new_date.getSeconds();
		var ampm = hour >= 12 ? 'PM' : 'AM';
		hour = hour % 12;
		hour = hour ? hour : 12; // the hour '0' should be '12'
		var minutes = minute < 10 ? '0'+minute : minute;

		return hour + ":" + minutes + " " + ampm;
	}

	sumArrayByKey(array, column, key = null , status = null) : any
    {
        var sum = 0;
        array.forEach(data=>
        {
            if(key)
            {
               if(data[key] == status)
               {
                   sum += data[column];
               }
            }
            else
            {  
                sum += data[column];
            }
        })
        return sum;
    }
}
