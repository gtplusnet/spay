import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import {HttpClientModule, HttpClient} from '@angular/common/http';
import { FormsModule }   from '@angular/forms';
import { isDevMode } from '@angular/core';

//Outside modules
import { ParticlesModule } from 'angular-particle';
import {NgbModule} from '@ng-bootstrap/ng-bootstrap';
import {NgxPaginationModule}            from 'ngx-pagination';
import { ChartsModule } from 'ng2-charts';
import { NguCarouselModule } from '@ngu/carousel';
import { ColorPickerModule } from 'ngx-color-picker';
import { QRCodeModule } from 'angularx-qrcode';
import { CKEditorModule } from 'ngx-ckeditor';
import {
    SocialLoginModule,
    AuthServiceConfig,
    GoogleLoginProvider,
    FacebookLoginProvider,
} from "angular5-social-login";
import { RecaptchaModule } from 'ng-recaptcha'; //siteKey = 6LfK_2MUAAAAAMYO92Tz7uhXFy_AQBzBtphdiR6R / secretKey = 6LfK_2MUAAAAALxg0xtnJx96Q83rtggs7i7oy1U8
import { RecaptchaFormsModule } from 'ng-recaptcha/forms';
import { BsDropdownModule } from 'ngx-bootstrap/dropdown';
import { ClipboardModule } from 'ngx-clipboard';

//Services
import { GlobalConfigService }          from './global-config.service';
import { MemberInfoService }            from './member/member-info.service';
import { CookieService } from 'ngx-cookie-service';
import { GoogleAnalyticsService } from './google-analytics.service';
import { PushNotificationsService } from './push-notification.service';


//Routes
import { AppRoutingModule } from './/app-routing.module';
import { MemberRoutesModule } from './member/member-routes/member-routes.module';
import { AdminRoutesModule } from './admin/admin-routes/admin-routes.module';

import { AppComponent } from './app.component';
import { HomeComponent } from './front/home/home.component';
import { HomeRoutesModule } from './front/home-routes/home-routes.module';
import { LoginComponent } from './front/login/login.component';
import { RegisterComponent } from './front/register/register.component';
import { MemberLayoutComponent } from './member/member-layout/member-layout.component';
import { MemberDashboardComponent } from './member/member-dashboard/member-dashboard.component';
import { MemberProfileComponent } from './member/member-profile/member-profile.component';
import { MemberBtcTransactionsComponent } from './member/member-btc-transactions/member-btc-transactions.component';
import { MemberEthTransactionsComponent } from './member/member-eth-transactions/member-eth-transactions.component';
import { MemberAccountSettingsComponent } from './member/member-account-settings/member-account-settings.component';
import { AdminLayoutComponent } from './admin/admin-layout/admin-layout.component';
import { AdminDashboardComponent } from './admin/admin-dashboard/admin-dashboard.component';
import { AdminMemberListComponent } from './admin/admin-member-list/admin-member-list.component';
import { AdminBtcTransactionsComponent } from './admin/admin-btc-transactions/admin-btc-transactions.component';
import { AdminEthTransactionsComponent } from './admin/admin-eth-transactions/admin-eth-transactions.component';
import { AdminSettingsComponent } from './admin/admin-settings/admin-settings.component';
import { AdminConversionSettingsComponent } from './admin/admin-conversion-settings/admin-conversion-settings.component';
import { ForgotPasswordComponent } from './front/forgot-password/forgot-password.component';
import { ChangePasswordRequestComponent } from './front/change-password-request/change-password-request.component';
import { VerifyEmailComponent } from './front/verify-email/verify-email.component';
import { ResendEmailComponent } from './front/resend-email/resend-email.component';
import { AdminReferralSettingsComponent } from './admin/admin-referral-settings/admin-referral-settings.component';
import { Become01PurchaserComponent } from './front/become01-purchaser/become01-purchaser.component';
import { Become02CommunityManagerComponent } from './front/become02-community-manager/become02-community-manager.component';
import { Become03AmbassadorComponent } from './front/become03-ambassador/become03-ambassador.component';
import { Become04MarketingDirectorComponent } from './front/become04-marketing-director/become04-marketing-director.component';
import { Become05AdvisorComponent } from './front/become05-advisor/become05-advisor.component';
import { PrivatePreSaleComponent } from './front/private-pre-sale/private-pre-sale.component';
import { Feat01UtilityTokenComponent } from './front/feat01-utility-token/feat01-utility-token.component';
import { Feat02LaunchPackageComponent } from './front/feat02-launch-package/feat02-launch-package.component';
import { Feat03MultiCryptoComponent } from './front/feat03-multi-crypto/feat03-multi-crypto.component';
import { Feat04TokenExchangeComponent } from './front/feat04-token-exchange/feat04-token-exchange.component';
import { BusinessRegistrationComponent } from './front/business-registration/business-registration.component';
import { AdminKycVerificationComponent } from './admin/admin-kyc-verification/admin-kyc-verification.component';
import { AdminBonusReferralComponent } from './admin/admin-bonus-referral/admin-bonus-referral.component';
import { AdminBonusSalestageComponent } from './admin/admin-bonus-salestage/admin-bonus-salestage.component';
import { AdminCommunicationBoardComponent } from './admin/admin-communication-board/admin-communication-board.component';
import { MainFuncComponent } from './front/main-func/main-func.component';
import { AdminMainWalletSettingsComponent } from './admin/admin-main-wallet-settings/admin-main-wallet-settings.component';
import { DemoFrontComponent } from './front/demo-front/demo-front.component';
import { AdminBusinessApplicationComponent } from './admin/admin-business-application/admin-business-application.component';
import { AdminTransferTokenComponent } from './admin/admin-transfer-token/admin-transfer-token.component';
import { MemberManualTransfersComponent } from './member/member-manual-transfers/member-manual-transfers.component';
import { AdminFaqsSettingsComponent } from './admin/admin-faqs-settings/admin-faqs-settings.component';
import { FaqComponent } from './front/faq/faq.component';
import { LayoutComponent } from './front/layout/layout.component';
import { AdminFilesComponent } from './admin/admin-files/admin-files.component';
import { AdminBankTransactionsComponent } from './admin/admin-bank-transactions/admin-bank-transactions.component';
import { MemberBankTransactionsComponent } from './member/member-bank-transactions/member-bank-transactions.component';

export function getAuthServiceConfigs() {

  let config = new AuthServiceConfig(
      [
        {
          id: FacebookLoginProvider.PROVIDER_ID,
          provider: new FacebookLoginProvider("289277415200105")
        },
        {
          id: GoogleLoginProvider.PROVIDER_ID,
          provider: new GoogleLoginProvider("129406182986-ies58m8jelei4ljmqbta1t0n5gb36dpc.apps.googleusercontent.com")
        },
      ]
  );
  return config;
}

@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    LoginComponent,
    RegisterComponent,
    MemberLayoutComponent,
    MemberDashboardComponent,
    MemberProfileComponent,
    MemberBtcTransactionsComponent,
    MemberEthTransactionsComponent,
    MemberAccountSettingsComponent,
    AdminLayoutComponent,
    AdminDashboardComponent,
    AdminMemberListComponent,
    AdminBtcTransactionsComponent,
    AdminEthTransactionsComponent,
    AdminSettingsComponent,
    AdminConversionSettingsComponent,
    ForgotPasswordComponent,
    ChangePasswordRequestComponent,
    VerifyEmailComponent,
    ResendEmailComponent,
    AdminReferralSettingsComponent,
    Become01PurchaserComponent,
    Become02CommunityManagerComponent,
    Become03AmbassadorComponent,
    Become04MarketingDirectorComponent,
    Become05AdvisorComponent,
    PrivatePreSaleComponent,
    Feat01UtilityTokenComponent,
    Feat02LaunchPackageComponent,
    Feat03MultiCryptoComponent,
    Feat04TokenExchangeComponent,
    BusinessRegistrationComponent,
    AdminKycVerificationComponent,
    AdminBonusReferralComponent,
    AdminBonusSalestageComponent,
    MainFuncComponent,
    AdminCommunicationBoardComponent,
    AdminMainWalletSettingsComponent,
    DemoFrontComponent,
    AdminBusinessApplicationComponent,
    AdminTransferTokenComponent,
    MemberManualTransfersComponent,
    AdminFaqsSettingsComponent,
    FaqComponent,
    LayoutComponent,
    AdminFilesComponent,
    AdminBankTransactionsComponent,
    MemberBankTransactionsComponent
  ],
  exports: [  ],
  imports: [
    NgbModule.forRoot(),
    RecaptchaModule.forRoot(),
    BsDropdownModule.forRoot(),

    BrowserModule,
    AppRoutingModule,
    HomeRoutesModule,
    HttpClientModule,
    ParticlesModule,
    FormsModule,
    MemberRoutesModule,
    AdminRoutesModule,
    NgxPaginationModule,
    ChartsModule,
    CKEditorModule,
    NguCarouselModule,
    ColorPickerModule,
    QRCodeModule,
    SocialLoginModule,
    ClipboardModule
  ],
  providers: [
      GlobalConfigService, 
      MemberInfoService,
      GoogleAnalyticsService, 
      PushNotificationsService,
      { 
      provide: AuthServiceConfig,
      useFactory: getAuthServiceConfigs
      },
      CookieService],
  bootstrap: [AppComponent]
})
export class AppModule { }
