import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { Observable } 				from "rxjs/Observable";
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';

@Component({
  selector: 'app-admin-conversion-settings',
  templateUrl: './admin-conversion-settings.component.html',
  styleUrls: ['./admin-conversion-settings.component.scss']
})
export class AdminConversionSettingsComponent implements OnInit 
{
  
  _sale_stage                  : any;
  _sale_stage_bonus            : any;
  _coin                        : any;
  sale_stage_id                : any;
  sale_stage_index             : any;
  sale_stage_selected          : any;
  sale_stage_bonus_selected    : any;
  sale_stage_start_date        : any;
  sale_stage_end_date          : any;
  sale_stage_discount          : any;
  sale_stage                   : any;
  eth_convertion               : any;
  btc_convertion               : any;
  sale_stage_days_remain       : any;
  eth_discount_equivalent      : any;
  btc_discount_equivalent      : any;
  array_experiment             : any;
  is_loaded                       : boolean;
  min_purchase                 : number;
  max_purchase                 : number;

  success_message : any = "no-message";
  button_loader : boolean;
  error_message : any = "no-message";

  constructor(private rest : MemberInfoService, private globalConfigService:GlobalConfigService, private http : HttpClient ) 
  { 

  }

  ngOnInit() 
  {
  	this.getSaleStageList();
    this.sale_stage_index = 0;
    this.is_loaded = false;
  }


  getSaleStageList()
  {
  	var _param = {};
  	_param["login_token"] 	= this.rest.login_token;

  	var _url = this.rest.api_url + "/api/admin/conversion";
  	
  	this.http.post(_url, _param).subscribe(
  	data =>
  	{
      this._sale_stage = data["_sale_stage"];
      this._coin = data["_coin"];
      this._sale_stage_bonus = data["_sale_stage_bonus"];
      this.changeSaleStage();
      this.is_loaded = true;
      // console.log(data);
    }
    ,
      error=>
      {
        console.log(error);
      });
  }

  changeSaleStage()
  {

    this.sale_stage_bonus_selected = this._sale_stage_bonus[this.sale_stage_index];
    this.sale_stage_selected   = this._sale_stage[this.sale_stage_index];
    this.eth_convertion        = this._coin[1].conversion_multiplier;
    this.btc_convertion        = this._coin[2].conversion_multiplier;
    
    this.refresh();
  
    // console.log(this.sale_stage_selected);
  }


  refresh()
  {
    this.sale_stage_days_remain = Math.ceil(Math.abs((new Date(this.sale_stage_selected.sale_stage_start_date).valueOf()) - (new Date(this.sale_stage_selected.sale_stage_end_date).valueOf())) / (1000 * 3600 * 24)) + " days";
    this.eth_discount_equivalent = this._coin[1].conversion_multiplier - (this._coin[1].conversion_multiplier * ( this.sale_stage_selected.sale_stage_discount / 100 ));
    this.btc_discount_equivalent = this._coin[2].conversion_multiplier - (this._coin[2].conversion_multiplier * ( this.sale_stage_selected.sale_stage_discount / 100 ));
  }

  update()
  {
      // console.log(this.sale_stage_bonus_selected, this.sale_stage_selected);
        this.button_loader = true;
        this.success_message = "no-message";
        this.error_message = "no-message";
        var _param = {};
        _param["login_token"]       = this.rest.login_token;
        _param["sale_stage"]        = this.sale_stage_selected;
        _param["sale_stage_bonus"]  = this.sale_stage_bonus_selected;
        _param["_coin"]             = this._coin;
        var _url = this.rest.api_url + "/api/admin/sale_stage_update";
        
        this.http.post(_url, _param).subscribe(
        data =>
        {
             if (data["status"] == "success") 
             {
               this.success_message = "no-message";
               this.success_message = "Settings Saved.";
               this.button_loader = false;
             }
             else
             { 
               this.button_loader = true;
               this.success_message = "no-message";
               this.error_message = data['message'];
             }
        },
      error=>
      {
        console.log(error);
      });
  }

}
 