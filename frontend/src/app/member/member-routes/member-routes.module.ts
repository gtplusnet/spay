import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { MemberRoutesRoutingModule } from './member-routes-routing.module';
import { MemberLayoutComponent } from '../member-layout/member-layout.component';
import { MemberDashboardComponent } from '../member-dashboard/member-dashboard.component';
import { MemberProfileComponent } from '../member-profile/member-profile.component';
import { MemberBtcTransactionsComponent } from '../member-btc-transactions/member-btc-transactions.component';
import { MemberEthTransactionsComponent } from '../member-eth-transactions/member-eth-transactions.component';
import { MemberAccountSettingsComponent } from '../member-account-settings/member-account-settings.component';
import { MemberManualTransfersComponent } from '../member-manual-transfers/member-manual-transfers.component';
const routes: Routes = 
[
  { path: '', component: MemberLayoutComponent, 
	  children: [
	  { path: 'member', redirectTo: 'member/dashboard', pathMatch: 'full' },
	  { path: 'member/dashboard', component: MemberDashboardComponent },
	  { path: 'member/profile', redirectTo: 'member/profile/basic-information' },
	  { path: 'member/profile/:page', component: MemberProfileComponent },
	  { path: 'member/profile/kyc/:kyc_page', component: MemberProfileComponent },
	  { path: 'member/btc-transactions', component: MemberBtcTransactionsComponent },
	  { path: 'member/eth-transactions', component: MemberEthTransactionsComponent },
	  { path: 'member/account-settings', component: MemberAccountSettingsComponent },
	  { path: 'member/manual-transfer', component: MemberManualTransfersComponent }
  ]},
];

@NgModule({
  imports: [ RouterModule.forRoot(routes) ],
  exports: [ RouterModule ]
})
export class MemberRoutesModule { }
