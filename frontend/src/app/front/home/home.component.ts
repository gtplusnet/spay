import { Component, OnInit } from '@angular/core';
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { Router }         from "@angular/router";
import * as $ from 'jquery';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {

  constructor(public rest: MemberInfoService, private http : HttpClient, private router:Router, public globalConfigService:GlobalConfigService) { }

  ngOnInit() {
  	this.smooth_scroll();
    this.stopVideo();
  }

  smooth_scroll(): void
  {
    $(document).on('click', 'a.navigation__link', function(e){
      e.preventDefault();
      var link = $(this).attr('href');

      $('html, body').animate({
        scrollTop: $(link).offset().top - 65}, 800 );
      return false; 
    });
  }

  stopVideo(): void
    {
      $(document).ready(function () {
        $('.modal').each(function () {
          var src = $(this).find('iframe').attr('src');

          $(this).on('click', function () {

            $(this).find('iframe').attr('src', '');
            $(this).find('iframe').attr('src', src);

          });
        });
      });
    }

}
