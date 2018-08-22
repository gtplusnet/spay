import { Component, OnInit } from '@angular/core';
import * as $ from 'jquery';
import { Observable } from 'rxjs';

@Component({
  selector: 'app-layout',
  templateUrl: './layout.component.html',
  styleUrls: ['./layout.component.scss']
})
export class LayoutComponent implements OnInit {

  initializing : boolean;
  timer : any;
  subscription : any;

  constructor() { }

  ngOnInit() {
  	this.showScroll();
    this.navExit();
  }
  
  showScroll()
  {
  	$(window).scroll(function() {
  	  if ($(document).scrollTop() > 50) {
  	    $('nav').addClass('show');
  	  } else {
  	    $('nav').removeClass('show');
  	  }
  	});
  }

  navExit()
  {
    if (document.documentElement.clientWidth < 768)
    {
       $('.nav-link').click(function()
       {
         $('.navbar-toggler').click();
       });
    }
  }

}
