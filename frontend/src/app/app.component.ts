import { Component, OnInit } from '@angular/core';
import fontawesome from '@fortawesome/fontawesome';
import faUser from '@fortawesome/fontawesome-free-solid/';
import faBitcoin from '@fortawesome/fontawesome-free-brands/';
import faAddressBook from '@fortawesome/fontawesome-free-regular/';
import { isDevMode } from '@angular/core';
import { GoogleAnalyticsService } from './google-analytics.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { MemberInfoService } from './member/member-info.service';



@Component({
	selector: 'app-root',
	templateUrl: './app.component.html',
	styleUrls: ['./app.component.scss']
})

export class AppComponent {
	analytics_data : any;
	constructor(private googleAnalyticsService: GoogleAnalyticsService, private http : HttpClient, private rest : MemberInfoService){
		fontawesome.library.add(faUser, faBitcoin, faAddressBook);
		if(isDevMode())
		{
			
		}
		else
		{
			if (window.location.hostname == "lokalize.io") 
			{
				var loc = window.location.href+'';
				if (loc.indexOf('http://')==0){
					window.location.href = loc.replace('http://','https://');
				}
			}
		}
	}
	title = 'app';

	ngOnInit() 
	{
		console.log(window.name);
		this.appendGaTrackingCode();
		var get_analytics = this.rest.api_url + "/api/google_analytics_data";
		this.http.get(get_analytics).subscribe(response=>
		{
			this.analytics_data = response;
  	    	this.googleAnalyticsService.emitEvent("ipAddress", this.analytics_data.ip_address);
  	    	this.googleAnalyticsService.emitEvent("operatingSystem", this.analytics_data.operating_system);
		})
	}

	appendGaTrackingCode() 
	{
	    try {
	      const script = document.createElement('script');
	      script.innerHTML = `
	        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	        })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

	        ga('create', '` + this.googleAnalyticsService.googleAnalyticsKey + `', 'auto');
	      `;
	      document.head.appendChild(script);
	    } catch (ex) {
	     console.error('Error appending google analytics');
	     console.error(ex);
	    }
  	}

  	acceptCookie() : void
    {
        localStorage.setItem('cookie_policy', this.analytics_data.ip_address);
    }

    isCookieAccepted() : boolean
    {
        return localStorage.getItem('cookie_policy') ? true : false;
    }


}
