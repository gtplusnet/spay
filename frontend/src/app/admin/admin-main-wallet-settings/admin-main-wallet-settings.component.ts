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
  page : any;
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

  constructor(public rest:MemberInfoService, private http:HttpClient, private modalService:NgbModal) { }
  ngOnInit() {
    this.viewAll();
    this.table_url = this.rest.api_url + "/api/admin/main_wallet_addresses";
    this.release_url = this.rest.api_url + "/api/admin/release_wallet";
    this.loadTable();
    this.coin_id = 0;
  }

  open(content)
   {
   	this.modal_ref = this.modalService.open(content);

   }

   openLg(content)
   {
    this.set_default.message = "no-message";
    this.saving_output.message = "no-message";

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
     })
   }

   openRelease(id, selector)
   {
     this.release_info = this.rest.findObjectByKey(this.table, 'member_address_id', id);
     this.open(selector);
   }

   releaseWallet(address_id, balance)
   {
     this.released = false;
     this.http.post(this.release_url,
     {
       login_token: this.rest.login_token,
       member_address_id: address_id,
       address_actual_balance: balance
     }).subscribe(
     response=>
     {
       console.log(response);
       this.released = true;
     })
   }

   viewReleaseWalletDetails(selector)
   {
   	this.openLg(selector);
   }

   showAddNew()
   {
     this.portion = "add new";
     this.new_central_wallet = {};
   }

   showViewAll()
   {
     this.portion = "view all";
     this.viewAll();
   }

   addNew()
   {
     this.adding = true;
     var add_new_central_url = this.rest.api_url + "/api/admin/add_new_central_wallet";
     this.new_central_wallet.login_token = this.rest.login_token;
     this.http.post(add_new_central_url, this.new_central_wallet).subscribe(response=>
     {
       this.adding = false;
       this.showViewAll();
     },
      error=>
      {
        console.log(error);
      });
   }

   viewAll()
   {
     this.loading = true;
     this.editable = false;
     $("[name^='central_wallet_']").attr("disabled");
     $("[name^='central_wallet_']").prop("disabled");
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

   showViewDetails(id)
   {
     this.viewAll();
     this.portion = "view details";
     this.data_focus = this.rest.findObjectByKey(this.view_central_wallet, 'central_wallet_id', id);
   }

   setAsDefault()
   {
     this.setting_default = true;
     this.set_default.message = "no-message";
     var setting_default_url = this.rest.api_url + "/api/admin/setting_default_wallet_central";
     this.http.post(setting_default_url, 
     {
       login_token: this.rest.login_token,
       wallet_id : this.data_focus.central_wallet_id
     }).subscribe(response=>
     {
       this.set_default = response;
       this.setting_default = false;
     },
      error=>
      {
        console.log(error);
      });
   }

   enableEditing()
   {
     this.editable = true;
     this.set_default.message = "no-message";
     this.saving_output.message = "no-message";
   }

   cancelEditing(id)
   {
     this.showViewDetails(id);
     this.editable = false;
     this.set_default.message = "no-message";
     this.saving_output.message = "no-message";
   }

   saveEditing()
   {
     this.saving_details = true;
     this.saving_output.message = "no-message";
     var save_editing_url = this.rest.api_url + "/api/admin/edit_central_wallet";
     this.data_focus.login_token = this.rest.login_token;
     this.http.post(save_editing_url, this.data_focus).subscribe(response=>
     {
       this.saving_output = response;
       this.viewAll();
       this.saving_details = false;
     },
      error=>
      {
        console.log(error);
      });
   }


}
