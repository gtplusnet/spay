import { Component, OnInit } from '@angular/core';
import * as $ from 'jquery';

@Component({
  selector: 'app-layout',
  templateUrl: './layout.component.html',
  styleUrls: ['./layout.component.scss']
})
export class LayoutComponent implements OnInit {

  constructor() { }

  ngOnInit() {
  	this.showScroll();
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

}