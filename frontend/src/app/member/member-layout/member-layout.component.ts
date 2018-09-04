import { Component, OnInit, EventEmitter, Output} from '@angular/core';
import { PushNotificationsService } from '../../push-notification.service';
import { HttpClient, HttpHeaders }   from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import { Router }         from "@angular/router";
import { MemberInfoService }   from '../member-info.service';
import {Observable} from 'rxjs/Rx';
import * as $ from 'jquery';
import { NgbModal, ModalDismissReasons } from '@ng-bootstrap/ng-bootstrap';


@Component({
  selector: 'app-member-layout',
  templateUrl: './member-layout.component.html',
  styleUrls: ['./member-layout.component.scss']
})

export class MemberLayoutComponent implements OnInit 
{

  initializing  : boolean;
  load_message  : string;
  buying_btc    : number;
  selling_btc   : number;
  notif : any = {};
  country_codes : any;
  update_info : any = {};
  updating : boolean;
  check_mail : boolean;
  post_output : any;
  modal_ref : any;
  old_selected_crypto_purchaser : any;
  selected : any;
  close_result : any;
  agreed : boolean;
  push_notif : any = {};

  constructor(private _notificationService: PushNotificationsService, public rest: MemberInfoService, public globalConfigService:GlobalConfigService, private router:Router, private http : HttpClient, private modalService:NgbModal) 
  { 
  }

  ngOnInit() 
  {
    

  	this.carbon_js();
    var app = this;
    app.initializing = true;
    app.buying_btc = 0;
    app.selling_btc = 0;
    app.update_info.country_code_id = 1;
    app.update_info.entity = "Individual";
    app.update_info.crypto_purchaser = "none";
    app.old_selected_crypto_purchaser = app.update_info.crypto_purchaser;

    if(app.globalConfigService.isLoggedIn())
    {
      app.load_message = "Syncing Member Data";

      app.rest.serverGetBasicInfo().subscribe(response =>
      {
        app.rest.syncFromServerResponseMember(response);
        app.rest.serverGetOtherInfo().subscribe(response =>
        {
          if(app.rest.status_account == 1)
          {
            app.rest.syncFromServerResponseOther(response);
            app.rest.serverGetSaleStage().subscribe(response =>
            {
              app.rest.syncFromServerGetSaleStage(response);

              let timer = Observable.timer(3000,10000);
              timer.subscribe(t=> {
                this.timer_ticks();
                this.checkNotifications();
              });

              app.rest.serverGetLiveExchangeRate().subscribe(response=>
              {
                app.rest.syncFromServerGetLiveExchangeRate(response);
                this._notificationService.requestPermission();
                app.initializing = false;
                this.rest.loading = true;
                app.update_info.email = app.rest.email == null ? '' : app.rest.email;
              });
            });
          }
          else
          {
            this.actionLogout();
          }
          
          
        });
      },
      error =>
      {
        // this.actionLogout();
      });
    }
    else
    {
      app.globalConfigService.logout();
      app.router.navigate(['/']); 
    }

    this.http.get(this.rest.api_url + "/api/get_country_codes").subscribe(response=>
    {
      this.country_codes = response;
    });
  }
  openLg(content)
  {
    this.modal_ref = this.modalService.open(content, {'size': 'lg'});
  }

  timer_ticks() : void
  {
    this.rest.serverGetOtherInfo().subscribe(response =>
    {
      this.rest.syncFromServerResponseOther(response);
    },
    error=>
    {
      console.log(error);
    });
  }

  checkNotifications() : void
  {
    localStorage.getItem("new_referrals") ?  localStorage.getItem("new_referrals") : localStorage.setItem("new_referrals", "0");
    localStorage.getItem("new_btc_approve") ?  localStorage.getItem("new_btc_approve") : localStorage.setItem("new_btc_approve", "0");
    localStorage.getItem("new_eth_approve") ?  localStorage.getItem("new_eth_approve") : localStorage.setItem("new_eth_approve", "0");
    localStorage.getItem("new_referral_bonus") ?  localStorage.getItem("new_referral_bonus") : localStorage.setItem("new_referral_bonus", "0");
    var check_notif_url = this.rest.api_url + "/api/member/check_notifications";
    this.http.post(check_notif_url, 
    {
      login_token : this.rest.login_token,
      user_id : this.rest.member_id,
    }).subscribe(response=>
    {
      this.notif = response;
      console.log(localStorage.getItem("new_referral"));
      if(parseInt(localStorage.getItem("new_referrals")) != this.notif.new_referrals && this.notif.new_referrals != 0)
      {
        localStorage.setItem("new_referrals", this.notif.new_referrals);
        console.log(localStorage.getItem("new_referral"));
        this.notify("New Referral", "You have received a new referral!");
      }

      if(parseInt(localStorage.getItem("new_btc_approve")) != this.notif.new_btc_approve && this.notif.new_btc_approve != 0)
      {
        localStorage.setItem("new_btc_approve", this.notif.new_btc_approve);
        this.notify("New BTC Transaction", "Your BTC Transaction has been approved!");
      }

      if(parseInt(localStorage.getItem("new_eth_approve")) != this.notif.new_eth_approve && this.notif.new_eth_approve != 0)
      {
        localStorage.setItem("new_eth_approve", this.notif.new_eth_approve);
        this.notify("New ETH Transaction", "Your ETH Transaction has been approved!");
      }

      if(parseInt(localStorage.getItem("new_referral_bonus")) != this.notif.new_referral_bonus && this.notif.new_referral_bonus != 0)
      {
        localStorage.setItem("new_referral_bonus", this.notif.new_referral_bonus);
        this.notify("New Referral Bonus", "You have received a new referral bonus!");
      }
    },
    error=>
    {
      console.log(error);
    });


  }

  resetNotifications(type) : void
  {
    var reset_notif_url = this.rest.api_url + "/api/member/reset_notifications";
    this.http.post(reset_notif_url, 
    {
      login_token : this.rest.login_token,
      user_id : this.rest.member_id,
      notif_type : type
    }).subscribe(response=>
    {
      this.checkNotifications();
    },
    error=>
    {
      console.log(error);
    });
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

   update_information()
   {
     this.updating = true;
     this.update_info.login_token = this.rest.login_token;
     this.update_info.member_id = this.rest.member_id;
     this.update_info.platform = this.rest.platform;
     this.http.post(this.rest.api_url + "/api/member/first_update_information", this.update_info).subscribe(response=>
     {
       this.post_output = response;
       if(response["status"] == "success")
       {
         setTimeout(function(){ window.location.href = "/member" }, 1500);
       }
       else
       {

       }
       this.updating = false;
     },
     error=>
     {
       console.log(error);
     });
   }

   cryptoPurchaser(content)
   {
     setTimeout(()=>
     {
       this.selected = this.update_info.crypto_purchaser;
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
             this.update_info.crypto_purchaser = this.old_selected_crypto_purchaser;
           }
           if(this.close_result == "backdrop")
           {
             this.update_info.crypto_purchaser = this.old_selected_crypto_purchaser;
           }
         });
       }
       else
       {  
         this.agreed = false;
         this.selected = "none";
         this.update_info.crypto_purchaser = "none";
       }
     },1000)
   }

   agreedTermAndCondition(crypto_purchaser_type)
   {
     this.agreed = true;
     this.old_selected_crypto_purchaser = this.update_info.crypto_purchaser;
     this.update_info.crypto_purchaser = crypto_purchaser_type;
     this.modal_ref.close();
   }

   getDismissReason(reason: any): string 
   {
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
       return  `with: ${reason}`;
     }
   }

   notify(title, description) 
   {
     let data: Array < any >= [];
     data.push(
     {
       'title': title,
       'alertContent': description
     });
     this._notificationService.generateNotification(data);
   }


 }
