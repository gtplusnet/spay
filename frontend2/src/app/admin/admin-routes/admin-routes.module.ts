import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { Routes, RouterModule } from '@angular/router';
import { AdminLayoutComponent } from '../admin-layout/admin-layout.component';
import { AdminDashboardComponent } from '../admin-dashboard/admin-dashboard.component';
import { AdminBtcTransactionsComponent } from '../admin-btc-transactions/admin-btc-transactions.component';
import { AdminEthTransactionsComponent } from '../admin-eth-transactions/admin-eth-transactions.component';
import { AdminSettingsComponent } from '../admin-settings/admin-settings.component';
import { AdminMemberListComponent } from '../admin-member-list/admin-member-list.component';
import { AdminConversionSettingsComponent } from '../admin-conversion-settings/admin-conversion-settings.component';
import { AdminReferralSettingsComponent } from '../admin-referral-settings/admin-referral-settings.component';
import { AdminKycVerificationComponent } from '../admin-kyc-verification/admin-kyc-verification.component';
import { AdminBonusSalestageComponent } from '../admin-bonus-salestage/admin-bonus-salestage.component';
import { AdminBonusReferralComponent } from '../admin-bonus-referral/admin-bonus-referral.component';
import { AdminCommunicationBoardComponent } from '../admin-communication-board/admin-communication-board.component';
import { AdminMainWalletSettingsComponent } from '../admin-main-wallet-settings/admin-main-wallet-settings.component';
import { AdminBusinessApplicationComponent } from '../admin-business-application/admin-business-application.component';
import { AdminTransferTokenComponent } from '../admin-transfer-token/admin-transfer-token.component';
import { AdminFaqsSettingsComponent } from '../admin-faqs-settings/admin-faqs-settings.component';
import { AdminFilesComponent } from '../admin-files/admin-files.component';
const routes: Routes = 
[
	{path: '', component: AdminLayoutComponent,
		children:
		[
			{ path: 'admin', redirectTo: 'admin/dashboard', pathMatch: 'full' },
			{ path: 'admin/dashboard', component: AdminDashboardComponent },
			{ path: 'admin/member-list', component: AdminMemberListComponent },
			{ path: 'admin/btc-transactions', component: AdminBtcTransactionsComponent },
			{ path: 'admin/eth-transactions', component: AdminEthTransactionsComponent },
			{ path: 'admin/settings', redirectTo: 'admin/settings/conversion-settings' },
			{ path: 'admin/settings/conversion-settings', component: AdminConversionSettingsComponent },
			{ path: 'admin/settings/referral-settings', component: AdminReferralSettingsComponent },
			{ path: 'admin/settings/main-wallet-settings', component: AdminMainWalletSettingsComponent },
			{ path: 'admin/settings/faqs-settings', component: AdminFaqsSettingsComponent },
			{ path: 'admin/kyc-verification', component: AdminKycVerificationComponent },
			{ path: 'admin/communication-board', component: AdminCommunicationBoardComponent },
			{ path: 'admin/bonus', redirectTo: 'admin/bonus/sale-stage' },
			{ path: 'admin/bonus/sale-stage', component: AdminBonusSalestageComponent },
			{ path: 'admin/bonus/referral', component: AdminBonusReferralComponent },
			{ path: 'admin/business-application', component: AdminBusinessApplicationComponent },
			{ path: 'admin/transfer-token', component: AdminTransferTokenComponent },
			{ path: 'admin/settings/system-files', component: AdminFilesComponent },
		]
	}
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AdminRoutesModule { }
