import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { Observable } 				from "rxjs/Observable";
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { NgbModal }  from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-member-dashboard',
  templateUrl: './member-dashboard.component.html',
  styleUrls: ['./member-dashboard.component.scss']
})
export class MemberDashboardComponent implements OnInit {
  modal_ref : any;

  buy_step : any;
  payment_type : number;
  token_amount : any;
  to_be_paid : any = 0;
  discount: number;
  bonus: any;
  bonus_token : number;
  total_token : number;
  bonus_percentage : any;
  lok_exchange_rate : number;
  exchange_rate : any = 0.00;
  buy_token_url : any;
  error_message : any;
  buy_loading : boolean;
  upcoming_event : any;
  upcoming_event_list : any;
  upcoming_event_details : any;
  banner_link : string;
  thumbnail_link : string;
  description : string;
  event_title  : string;
  event_sub_title: string;
  start_date    :string;
  end_date    : string;
  events : boolean;
  getting_bonus : boolean = false;

  check_pending_transacts : any;

  sale_stage_name : any;

  check_toa_1 : boolean;
  check_toa_2 : boolean;
  check_toa_3 : boolean;

  tos_toggle : any = {};

  stage_count_down                : any;


  check_tokens_url : string;
  w_balance : any;

  check_contributions_url : string;
  w_contributions : any;

  _table_recent_transaction : any;
  _table_recent_transaction_loader : boolean;
  constructor(private modalService: NgbModal, public rest : MemberInfoService, private globalConfigService:GlobalConfigService, private http : HttpClient ) 
  { 
  }

  ngOnInit() 
  {
    // console.log(this.rest._stages);
    this.sale_stage_name = this.rest._stages.sale_stage_type.replace("_"," ");
  	this.dashboard_data();
    this.error_message = "no-message"; 
    this.check_toa_1 = false;
    this.check_toa_2 = false;
    this.check_toa_3 = false;
    this.payment_type = 2;
    this.sale_stage_count_down();
    this.getUpcomingEvent(this.rest.member_id);
    this.getRecentTransaction();
    console.log(this._table_recent_transaction);
    this.check_tokens_url = this.rest.api_url + "/api/member/check_tokens";
    this.check_contributions_url = this.rest.api_url + "/api/member/check_contributions";
    this.checkTokens();
    this.checkContributions();
    localStorage.removeItem('c1');
    localStorage.removeItem('c2');
    localStorage.removeItem('c3');
    this.tos_toggle.c1 = null;
    this.tos_toggle.c2 = null;
    this.tos_toggle.c3 = null;

  }

  dashboard_data()
  {
  	var _param = {};

  	_param["login_token"] 	= this.rest.login_token;
  	// _param["username"]		= this.username;
  	// _param["password"]		= this.password;

  	var _url = this.rest.api_url + "/api/member/dashboard";
  	
  	this.http.post(_url, _param).subscribe(
  		data =>
  		{

  		},
      error=>
      {
        console.log(error);
      });
  }

  buy_step_1(id, selector, buy_step = null)
  {
    
    if(this.token_amount == 0 || this.token_amount == "" || this.token_amount == null)
    {
      this.to_be_paid = 0;
    }

    if(this.payment_type == null)
    {
      this.payment_type = 0;
    }

    this.total_token = this.token_amount;

    this.buy_step = "buy_step_1";
    if(buy_step == null)
    {
      this.openLg(selector);

    } 
  }

  buy_step_2()
  {
    this.total_token = this.token_amount;
    this.buy_loading = true;
    if(this.token_amount == "" || this.token_amount == null || this.token_amount < this.rest._stages.sale_stage_min_purchase || this.token_amount > this.rest._stages.sale_stage_max_purchase)
    {
      this.error_message = "Amount of Tokens cannot be less than " + this.rest._stages.sale_stage_min_purchase + " tokens or more than " + this.rest._stages.sale_stage_max_purchase + " tokens.";
      this.buy_loading = false;
    }
    else
    {
      if(this.check_toa_1 && this.check_toa_2 && this.check_toa_3)
      {
        this.error_message = "no-message";
        var log_method = this.payment_type == 2 ? 'Ethereum' : 'Bitcoin';
        var log_method_accepted = this.payment_type == 2 ? 'Ethereum Total' : 'Bitcoin Total';
        var check_message = "no-message";
        this.http.post(this.rest.api_url + "/api/member/check_pending_order_method", 
        {
          login_token : this.rest.login_token,
          member_address_id : this.rest._wallet[4].member_address_id,
          log_method : log_method,
          log_method_accepted : log_method_accepted
        }).subscribe(response=>
        {
          this.check_pending_transacts = response;
          this.buy_step = "buy_step_2";
          this.buy_loading = false;
        });
        
        
      }
      else
      {
        this.error_message = "You need to agree to all terms and services.";
        this.buy_loading = false;
      }
    }
  }

  checkTokens()
  {
      var param = {};
      param["login_token"] = this.rest.login_token;
      param["wallet_id"] = this.rest._wallet[4].member_address_id;
      this.http.post(this.check_tokens_url, param).subscribe(response=>
      {
        this.w_balance = response;
      });
  }

  checkContributions()
  {
      var param = {};
      param["login_token"] = this.rest.login_token;
      param["btc_wallet_id"] = this.rest._wallet[3].member_address_id;
      param["eth_wallet_id"] = this.rest._wallet[2].member_address_id;
      this.http.post(this.check_contributions_url, param).subscribe(response=>
      {
        this.w_contributions = response;
      });
  }

  closeModal()
  {
    this.error_message = "no-message";
    this.token_amount = 0;
    this.check_toa_1 = false;
    this.check_toa_2 = false;
    this.check_toa_3 = false;
    this.modal_ref.close();
  }

  buy_step_3()
  {
    this.buy_loading = true;
    this.buy_token_url = this.rest.api_url + "/api/member/record_transaction";

    var param = {};
    param["login_token"] = this.rest.login_token;
    param["member_id"] = this.rest.member_id;
    param["amount_to_pay"] = this.to_be_paid;
    param["token_amount"] = this.token_amount;
    param["payment_method"] = this.payment_type == 3 ? 'Bitcoin' : 'Ethereum';
    param["lok_exchange_rate"] = this.lok_exchange_rate;
    param["sale_stage_id"] = this.rest._stages.sale_stage_id;
    // param["sale_stage_id"] = this.rest._rates[0].sale_stage_id;    

    this.http.post(this.buy_token_url, param).subscribe(response=>
    {
      this.error_message = response;
      if(this.error_message.type == "success")
      {
        this.buy_loading = false;
        this.buy_step = "buy_step_3";
      }
      else
      {
        this.buy_loading = false;
        
      }
      
    });
  }

  check_tos(i)
  {
    // console.log(i);
    if(i == 'check_toa_1')
    {
      if(this.check_toa_1)
      {
        this.check_toa_1 = false;
      }
      else
      {
        this.check_toa_1 = true;
      }
    }
    else if (i == 'check_toa_2')
    {
      if(this.check_toa_2)
      {
        this.check_toa_2 = false;
      }
      else
      {
        this.check_toa_2 = true;
      }
    }
    else
    {
      if(this.check_toa_3)
      {
        this.check_toa_3 = false;
      }
      else
      {
        this.check_toa_3 = true;
      }
    }
  }
  
  compute_to_pay()
  {
    this.getting_bonus = true;
    if(this.payment_type == 2)
    {
      this.to_be_paid = parseInt(this.token_amount) * this.rest._rates[0].conversion_multiplier;
      this.lok_exchange_rate = this.rest._rates[0].conversion_multiplier;
      this.exchange_rate = this.rest._exchange_rate.ETH.USD;
    }
    else
    {
      this.to_be_paid = parseInt(this.token_amount) * this.rest._rates[1].conversion_multiplier;
      this.lok_exchange_rate = this.rest._rates[1].conversion_multiplier;
      this.exchange_rate = this.rest._exchange_rate.BTC.USD;
    }

    this.discount = (this.rest._stages.sale_stage_discount/100)*this.to_be_paid;
    this.to_be_paid = this.to_be_paid - this.discount;
    

    if(this.token_amount == 0 || this.token_amount == "" || this.token_amount == null)
    {
      this.to_be_paid = 0;
    }
    else
    {
      this.to_be_paid = this.to_be_paid.toFixed(8);
    }
    this.exchange_rate = this.exchange_rate * this.to_be_paid;
    this.exchange_rate = this.exchange_rate.toFixed(2);


    this.getBuyBonus(this.rest._stages.sale_stage_id, this.token_amount);
    // this.bonus = this.rest._stages.bonus;
    // this.bonus.forEach(response=>{
    //   if(this.token_amount >= response.buy_coin_bonus_from && this.token_amount <= response.buy_coin_bonus_to)
    //   {
    //     this.bonus_percentage = parseInt(response.buy_coin_bonus_percentage);
    //   }
    // });    
  }

  setPaymentType(type)
  {
    this.payment_type = type;
    this.compute_to_pay();
  }

  getBuyBonus(salestage_id, token_amount)
  {
    if(this.getting_bonus)
    {
      this.bonus_percentage = "computing...";
    }
    var get_buy_bonus_url = this.rest.api_url + "/api/member/get_buy_bonus";
    this.http.post(get_buy_bonus_url, 
      {
        login_token : this.rest.login_token,
        sale_stage_id : salestage_id,
        token_amount : token_amount

      }).subscribe(response=>
    {
      this.bonus = response;
      this.bonus_percentage = this.bonus.percentage + "%";
      this.getting_bonus = false;
    });
  }

  open(content)
  {
    this.modal_ref = this.modalService.open(content);
  }

  openLg(content)
  {
    this.modal_ref = this.modalService.open(content, {'size': 'lg', 'backdrop': 'static', 'keyboard':false});
  }

  sale_stage_count_down() : any
  {
    var countDownDate = new Date(this.rest._stages.sale_stage_end_date).getTime();
    this.stage_count_down = setInterval(function() {

      // Get todays date and time
      var now = new Date().getTime();

      // Find the distance between now an the count down date
      var distance = countDownDate - now;
      var days = Math.floor(distance / (1000 * 60 * 60 * 24));
      var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
      // Time calculations for days, hours, minutes and seconds      

      $("#days").html(days + "");
      $("#hours").html(hours + "");
      $("#minutes").html(minutes + "");
      
      $("#seconds").html(seconds + "");
     
      // If the count down is finished, write some text 
      if (distance < 0) 
      {
        clearInterval(this.stage_count_down);
       
      }
    }, 1000);
  }
  

  change_count_down(distance) : any
  {
     
  }

  getUpcomingEvent(member_id)
  {
    var _url = this.rest.api_url + "/api/member/get_upcoming_event";
    var _param={}
    _param["login_token"] = this.rest.login_token;
    _param["member_id"] = member_id;

    this.http.post(_url,_param).subscribe(response=>
    {
      this.upcoming_event = response;
      this.upcoming_event_list = this.upcoming_event.list;
      this.events = this.upcoming_event.count != 0 ? true : false;

      this.lokalizeSlider();

    });
  }

  viewEventDetails(id,selector)
  {
    this.thumbnail_link = "";
    this.banner_link = "";
    this.description = "";
    this.event_title = "";
    this.event_sub_title = "";
    this.start_date = "";
    this.end_date = "";
    
    var _url = this.rest.api_url + "/api/member/get_upcoming_event_details";
    var _param={}
    _param["login_token"] = this.rest.login_token;
    _param["id"] = id;

    this.http.post(_url,_param).subscribe(response=>
    {
      this.upcoming_event_details = response;
      //console.log(response);
      this.banner_link = this.upcoming_event_details.communication_board_banner;
      this.thumbnail_link = this.upcoming_event_details.communication_board_thumbnail;
      this.description = this.upcoming_event_details.communication_board_description;
      this.event_title = this.upcoming_event_details.communication_board_title;
      this.event_sub_title = this.upcoming_event_details.communication_board_subtitle;
      this.start_date = this.upcoming_event_details.communication_board_start_date;
      this.end_date = this.upcoming_event_details.communication_board_end_date;
    })
    this.openLg(selector);
  }

  getRecentTransaction()
  {
    this._table_recent_transaction_loader = true;
    var _param = {}
    var config_url = this.rest.api_url + "/api/member/get_recent_transaction";
    _param["login_token"] = this.rest.login_token;
    _param["id"] = this.rest.member_id;

    this.http.post(config_url,_param).subscribe(data=>
    {
      this._table_recent_transaction = data;
      this._table_recent_transaction_loader = false;
    });
  }

  lokalizeSlider()
  {
    $(document).ready(function()
    {
      var imageholder = $(".slider-image-holder");
      $(".slider-main").css("grid-template-columns","repeat("+imageholder.length+", 1fr)");

      var left = $(".slider-main").css("left");
          left = left.replace("px", "");
      var move = parseInt(left);

      $(".next").on('click', function()
      {
        move = move - 336;
        if(Math.abs(move) <= (imageholder.width() * imageholder.length)-336)
        {
          $(".slider-main").css({"left":move+"px","transition":"400ms"});
        }
        else
        {
          $(".slider-main").css({"left":"0px","transition":"400ms"});
          move = 0;
        }
      })
      $(".prev").on('click', function()
      {
        if(move < 0)
        {
          move = move + 336;
          $(".slider-main").css({"left":move+"px","transition":"400ms"});
        }
      })
    })
    
  }

  toggleTos(param)
  {
    this.tos_toggle.c1 = localStorage.getItem('c1')
    this.tos_toggle.c2 = localStorage.getItem('c2')
    this.tos_toggle.c3 = localStorage.getItem('c3')

    if(param == 'c1')
    {
      this.tos_toggle.c1 = this.tos_toggle.c1 ? localStorage.removeItem('c1') : localStorage.setItem('c1', 'checked')
    }

    else if(param == 'c2')
    {
      this.tos_toggle.c2 = this.tos_toggle.c2 ? localStorage.removeItem('c2') : localStorage.setItem('c2', 'checked')
    }

    else if(param == 'c3')
    {
      this.tos_toggle.c3 = this.tos_toggle.c3 ? localStorage.removeItem('c3') : localStorage.setItem('c3', 'checked')
    }

    this.tos_toggle.c1 = localStorage.getItem('c1')
    this.tos_toggle.c2 = localStorage.getItem('c2')
    this.tos_toggle.c3 = localStorage.getItem('c3')

    console.log(this.tos_toggle.c1, this.tos_toggle.c2, this.tos_toggle.c3);
  }
}

