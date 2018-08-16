import { Injectable } from '@angular/core';
import { Md5 } from 'ts-md5/dist/md5';

@Injectable()
export class GlobalConfigService 
{

  	constructor() { }

  	apiConfig() : object
	{
		var api_key 				= {};
		api_key["salt"] 			= "4114113Y411";
		api_key["login_key"]  		= Md5.hashStr('ALLBYALL2018');
		return api_key;
	}

	login(login_token, login_name) : void
	{
		localStorage.setItem('login_token', login_token);
		localStorage.setItem('login_name', login_name);
	}

	logout() : void
	{
		localStorage.removeItem('login_token');
	}

    isLoggedIn() : boolean
    {
    	if(localStorage.getItem("login_token"))
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }

    isLoggedOut() : boolean
    {
    	if(!localStorage.getItem("login_token"))
    	{
    		return true;
    	}
    	else
    	{
    		return false;
    	}
    }

    login_info() : object
    {
    	var data 		= {};
    	data["token"] 	= localStorage.getItem("login_token");
    	data["name"] 	= localStorage.getItem("login_name");
    	return data;
    }


}
