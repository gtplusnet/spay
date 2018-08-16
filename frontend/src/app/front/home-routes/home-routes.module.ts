import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from '../home/home.component';
import { LoginComponent } from '../login/login.component';
import { RegisterComponent } from '../register/register.component';
import { ForgotPasswordComponent } from '../forgot-password/forgot-password.component';
import { ChangePasswordRequestComponent } from '../change-password-request/change-password-request.component';
import { VerifyEmailComponent } from '../verify-email/verify-email.component';
import { ResendEmailComponent } from '../resend-email/resend-email.component';
import { Become01PurchaserComponent } from '../become01-purchaser/become01-purchaser.component';
import { Become02CommunityManagerComponent } from '../become02-community-manager/become02-community-manager.component';
import { Become03AmbassadorComponent } from '../become03-ambassador/become03-ambassador.component';
import { Become04MarketingDirectorComponent } from '../become04-marketing-director/become04-marketing-director.component';
import { Become05AdvisorComponent } from '../become05-advisor/become05-advisor.component';
import { PrivatePreSaleComponent } from '../private-pre-sale/private-pre-sale.component';
import { Feat01UtilityTokenComponent } from '../feat01-utility-token/feat01-utility-token.component';
import { Feat02LaunchPackageComponent } from '../feat02-launch-package/feat02-launch-package.component';
import { Feat03MultiCryptoComponent } from '../feat03-multi-crypto/feat03-multi-crypto.component';
import { Feat04TokenExchangeComponent } from '../feat04-token-exchange/feat04-token-exchange.component';
import { BusinessRegistrationComponent } from '../business-registration/business-registration.component';
import { MainFuncComponent } from '../main-func/main-func.component';
import { DemoFrontComponent } from '../demo-front/demo-front.component';
import { FaqComponent } from '../faq/faq.component';
import { LayoutComponent } from '../layout/layout.component';

const routes: Routes = [
  { path: '', component: MainFuncComponent, children: 
    [
      { path: '', component:LayoutComponent, children: 
        [
          { path: '', redirectTo: 'home', pathMatch: 'full'},
          { path: 'home', component: HomeComponent},
          { path: 'login', component: LoginComponent},
          { path: 'register', component: RegisterComponent},
          { path: 'forgot-password', component: ForgotPasswordComponent},
          { path: 'reset_password/:request_id', component: ChangePasswordRequestComponent},
          { path: 'verify_email/:verification_code', component: VerifyEmailComponent},
          { path: 'resend_email', component: ResendEmailComponent},
          { path: 'purchaser', component: Become01PurchaserComponent},
          { path: 'community-manager', component: Become02CommunityManagerComponent},
          { path: 'ambassador', component: Become03AmbassadorComponent},
          { path: 'marketing-director', component: Become04MarketingDirectorComponent},
          { path: 'advisor', component: Become05AdvisorComponent},
          { path: 'private_pre-sale', component: PrivatePreSaleComponent},
          { path: 'core-features/creation-of-a-utility-token', component: Feat01UtilityTokenComponent},
          { path: 'core-features/ico-launch-package', component: Feat02LaunchPackageComponent},
          { path: 'core-features/multi-crypto-payment', component: Feat03MultiCryptoComponent},
          { path: 'core-features/token-exchange', component: Feat04TokenExchangeComponent},
          { path: 'business-registration', component: BusinessRegistrationComponent},
          { path: 'demo-front', component: DemoFrontComponent},
          { path: 'faqs', component: FaqComponent}
        ]
      }
    ]
  }
];

// const routes: Routes = [
//   { path: '', component: HomeLayoutComponent,
//   	children : 	[
//   		{ path: 'home', component: HomeComponent },
//   	]}
// ];

@NgModule({
  imports: [ RouterModule.forRoot(routes) ],
  exports: [ RouterModule ],
  declarations: []
})
export class HomeRoutesModule { }
