import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-main-wallet-settings',
  templateUrl: './admin-main-wallet-settings.component.html',
  styleUrls: ['./admin-main-wallet-settings.component.scss']
})
export class AdminMainWalletSettingsComponent implements OnInit {

  //main table
  table : any;
  table_url : string;
  coin_id : any;
  table_loader : boolean = true;

  //release wallet
  release_url : string;
  released : boolean = true;
  release_info : any;

  modal_ref 		: any;
  portion : string = "view all";
  new_central_wallet : any;
  view_central_wallet : any;
  adding : boolean = false;
  loading : boolean = false;
  data_focus : any;
  editable : boolean = false;

  saving_details : boolean = false;
  saving_output : any = {};

  setting_default : boolean = false;
  set_default : any = {};

  setup_wallet : any = {};
  step : any;
  edit_wallet : any;
  wallet : any;
  release_data : any;
  wallet_receiver : any;
  totals  : any;

  constructor(public rest:MemberInfoService, private http:HttpClient, private modalService:NgbModal) { }

  ngOnInit() {
    this.viewAll();
    this.table_url = this.rest.api_url + "/api/admin/main_wallet_addresses";
    this.release_url = this.rest.api_url + "/api/admin/release_wallet";
    this.loadTable();
    this.coin_id = 0;
    this.step = 1;
  }

  getTotals()
  {
    this.http.post(this.rest.api_url + "/api/admin/get_total_crypto", 
    {
      login_token : this.rest.login_token
    }).subscribe(response=>
    {
      this.totals = response;
    })
  }

  open(content)
   {
   	this.modal_ref = this.modalService.open(content);
    this.step = 1;
    this.wallet = null;
   }

   openLg(content)
   {
    this.set_default.message = "no-message";
    this.saving_output.message = "no-message";
    this.step = 1;
    this.wallet = null;
    this.getEstimation();
   	this.modal_ref = this.modalService.open(content, {'size': 'lg'});
   }

   loadTable()
   {
     this.table_loader = true;
     this.http.post(this.table_url, 
     {
       login_token : this.rest.login_token,
       coin_id : this.coin_id
     }).subscribe(
     response=>
     {
       this.table = response;
       this.table_loader = false;
       this.getTotals();
       
     })
   }

   openRelease(id, selector)
   {
     this.release_info = this.rest.findObjectByKey(this.table, 'member_address_id', id);
     this.open(selector);
     this.getEstimation(id);
   }

   releaseWallet(address_id)
   {
     this.released = false;
     this.http.post(this.release_url,
     {
       login_token: this.rest.login_token,
       member_address_id: address_id,
       usd : this.rest._exchange_rate.BTC.USD,
       wallet : this.wallet,
       wallet_receiver : this.wallet_receiver
     }).subscribe(
     response=>
     {
       this.released = true;
       this.modal_ref.close();
       this.loadTable();
     })
   }

   batchReleaseWallet()
   {
     this.released = false;
     this.http.post(this.rest.api_url + "/api/admin/batch_release_wallet",
     {
       login_token: this.rest.login_token,
       wallet_receiver : this.wallet_receiver,
       usd : this.rest._exchange_rate.BTC.USD,
       wallet : this.wallet
     }).subscribe(
     response=>
     {
       this.released = true;
       this.modal_ref.close();
       this.loadTable();
     })
   }

   viewReleaseWalletDetails(selector)
   {
   	this.openLg(selector);
   }

   viewAll()
   {
     var view_all_central_url = this.rest.api_url + "/api/admin/view_all_central_wallet";
     this.http.post(view_all_central_url, {login_token: this.rest.login_token}).subscribe(response=>
     {
       this.view_central_wallet = response;
       this.loading = false;
     },
      error=>
      {
        console.log(error);
      });
   }

   setUpWalletAddress()
   {
     this.saving_details = true;
     this.setup_wallet.login_token = this.rest.login_token;
     this.http.post(this.rest.api_url + "/api/admin/setup_wallet_address", this.setup_wallet).subscribe(response=>
    {
      this.saving_details = false;
      this.viewAll();
      this.step = 1;
       this.modal_ref.close();

    })
   }

   goToStep(param = null, step)
   {
    this.step = step; 
    this.setup_wallet.mwallet_type = param;
    this.wallet = param;
   }

   editWallet(id)
   {
     this.saving_details = false;
     this.edit_wallet = this.rest.findObjectByKey(this.view_central_wallet, 'mwallet_id', id);
     this.viewAll();
   }

   setAsDefault(id, type)
   {
     this.saving_details = true;
     this.http.post(this.rest.api_url + "/api/admin/setting_default_wallet_central", 
       {
         login_token : this.rest.login_token,
         wallet_id : id,
         wallet_type : type
       }).subscribe(response=>
     {
       this.saving_details = false;
       this.edit_wallet = null;
       this.viewAll();
     })
   }

   updateWallet()
   {
     this.saving_details = true;
     this.edit_wallet.login_token = this.rest.login_token;
     this.http.post(this.rest.api_url + "/api/admin/setting_update_wallet_central", this.edit_wallet).subscribe(response=>
     {
       this.saving_details = false;
       this.edit_wallet = null;
       this.viewAll();
     })
   }

   getEstimation(id = null)
   {
     this.http.post(this.rest.api_url + "/api/admin/get_estimated_tx",
     {
       coin_id : 3,
       usd : this.rest._exchange_rate.BTC.USD,
       login_token : this.rest.login_token,
       member_address_id : id
     }).subscribe(response=>
     {
       this.release_data = response;
     })
   }

}
