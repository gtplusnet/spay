import { Component, OnInit } from '@angular/core';
import { GlobalConfigService }  from '../../global-config.service';
import { MemberInfoService }     from '../../member/member-info.service';

@Component({
    selector: 'app-front-layout',
    templateUrl: './front-layout.component.html',
    styleUrls: ['./front-layout.component.scss']
})
export class FrontLayoutComponent implements OnInit {

    logged_in = false;
    constructor(public globalConfigService:GlobalConfigService, public rest: MemberInfoService) { }

    ngOnInit() 
    {
        if(this.globalConfigService.isLoggedIn())
        {
            this.logged_in = true;
        }

        this.toggleChat();
    }

    toggleChat()
    {
        (function(){ var widget_id = 'NPdzpVs6OW';var d=document;var w=window;function l(){
        var s = document.createElement('script'); s.type = 'text/javascript'; s.async = false; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if((<any>window).attachEvent){(<any>window).attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
    }

    actionLogout() : void
    {
        this.globalConfigService.logout();
        window.location.href = "/"; 
    }
    
}
