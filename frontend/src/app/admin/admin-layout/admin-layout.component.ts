import { Component, OnInit } from '@angular/core';
import * as $ from "jquery";
import { Router }         from "@angular/router";
import { MemberInfoService }   from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs/Rx';
import { HttpClient, HttpHeaders }     from '@angular/common/http';

@Component({
  selector: 'app-admin-layout',
  templateUrl: './admin-layout.component.html',
  styleUrls: ['./admin-layout.component.scss']
})

export class AdminLayoutComponent implements OnInit {

  constructor(private route: ActivatedRoute, public rest: MemberInfoService, private http : HttpClient, public globalConfigService:GlobalConfigService, private router:Router) 
  { 
  }

  load_message           : string;
  initializing           : boolean;
  new_cash_in_notif      = 0;
  new_cash_out_notif     = 0;
  new_member_notif_count = 0;
  new_internal_external = 0;
  new_external_internal = 0;
  total_notif_count      = 0;
  transaction_type : any;
  admin_notif : any = {};

  ngOnInit() 
  {

    let timer = Observable.timer(3000,10000);
    timer.subscribe(t=> {
       this.checkAdminNotification();
      });
  	this.carbon_js();
    var app = this;
        app.initializing = true;
        
    if(app.globalConfigService.isLoggedIn())
    {
      app.rest.serverGetBasicInfo().subscribe(response =>
      {
          app.rest.syncFromServerResponseMember(response);
          app.rest.serverGetOtherInfo().subscribe(response =>
          {
            if(app.rest.is_admin)
            {
              app.rest.syncFromServerResponseOther(response);
              app.rest.serverGetSaleStage().subscribe(response =>
              {
                app.rest.syncFromServerGetSaleStage(response);
                app.rest.serverGetLiveExchangeRate().subscribe(response=>
                {
                  app.rest.syncFromServerGetLiveExchangeRate(response);
                  app.initializing = false;
                  app.rest.loading = true;
                });
              });
            }
            else
            {
              app.router.navigate(['/member']);
            }
          });
        
      },
      error =>
      {
        this.actionLogout();
      });
    }
    else
    {
       app.rest.serverGetOtherInfo().subscribe(response =>
        {
          app.rest.syncFromServerResponseOther(response);
          app.rest.serverGetSaleStage().subscribe(response =>
          {
            app.rest.syncFromServerGetSaleStage(response);
            app.rest.serverGetLiveExchangeRate().subscribe(response=>
            {
              app.rest.syncFromServerGetLiveExchangeRate(response);
              
              this.rest.loading = true;
              app.initializing = false;
            });
          });
      });
    }
    //console.log(app.rest._stages);
  }

  actionLogout() : void
  {
    this.globalConfigService.logout();
    this.router.navigate(['/']); 
  }

  carbon_js()
  {
  	 /**
     * Sidebar Dropdown
     */
    $('.nav-dropdown-toggle').on('click', function (e) {
        e.preventDefault();
        $(this).parent().toggleClass('open');
    });

    // open sub-menu when an item is active.
    $('ul.nav').find('a.active').parent().parent().parent().addClass('open');

    /**
     * Sidebar Toggle
     */
    $('.sidebar-toggle').on('click', function (e) {
        e.preventDefault();
        $('.page-wrapper').toggleClass('sidebar-hidden');
    });

    /**
     * Mobile Sidebar Toggle
     */
    $('.sidebar-mobile-toggle').on('click', function () {
        $('.page-wrapper').toggleClass('sidebar-mobile-show');
    });
  }

  checkAdminNotification()
  {
    var check_admin_notif = this.rest.api_url + "/api/admin/get_admin_notification";

    this.http.post(check_admin_notif,{
      login_token : this.rest.login_token
    }).subscribe(response=>
    {
      this.admin_notif = response;
    },
      error=>
      {
        console.log(error);
      });
  }

  adminViewedNotif(type)
  {
    var admin_viewed_notif = this.rest.api_url + "/api/admin/admin_viewed_notif";

    this.hideNavBar();
    this.http.post(admin_viewed_notif,
    {
      login_token : this.rest.login_token,
      notif_type  : type
    }).subscribe(response=>
    {
       this.checkAdminNotification();
    },
      error=>
      {
        console.log(error);
      });
  }

hideNavBar()
{
  $('.page-wrapper').toggleClass('sidebar-hidden');
  $('.page-wrapper').toggleClass('sidebar-mobile-show');
}
}
