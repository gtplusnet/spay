import { Component, OnInit } from '@angular/core';
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { Router }         from "@angular/router";
import * as $ from 'jquery';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-layout',
  templateUrl: './layout.component.html',
  styleUrls: ['./layout.component.scss']
})
export class LayoutComponent implements OnInit {

  initializing : boolean;
  timer : any;
  subscription : any;

  constructor(public rest: MemberInfoService, private http : HttpClient, private router:Router, public globalConfigService:GlobalConfigService) { }

  ngOnInit() {
  	this.showScroll();
    this.navExit();
  }
  
  showScroll()
  {
  	$(window).scroll(function() {
  	  if ($(document).scrollTop() > 50) {
  	    $('nav').addClass('show');
  	  } else {
  	    $('nav').removeClass('show');
  	  }
  	});
  }

  navExit()
  {
    if (document.documentElement.clientWidth < 768)
    {
       $('.nav-link').click(function()
       {
         $('.navbar-toggler').click();
       });
    }
  }

  actionLogout() : void
	{
	    this.globalConfigService.logout();
	    this.router.navigate(['/']); 
	}

}
