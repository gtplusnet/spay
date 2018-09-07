import { Component, OnInit } from '@angular/core';
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { Router }         from "@angular/router";
import * as $ from 'jquery';
import Swiper from 'swiper';
import {Observable} from 'rxjs/Rx';


@Component({
	selector: 'app-home',
	templateUrl: './home.component.html',
	styleUrls: ['./home.component.scss']
})
export class HomeComponent implements OnInit {

	crypto_data : any;

	constructor(public rest: MemberInfoService, private http : HttpClient, private router:Router, public globalConfigService:GlobalConfigService) { }

	ngOnInit() {
		this.icoNewsSwiper();
		this.smooth_scroll();
		this.stopVideo();
		this.cryptoCompare();
		let timer = Observable.timer(10000,10000);
		timer.subscribe(t=> {
		this.cryptoCompare();
		});
	}

	icoNewsSwiper(): void
	{
		var swiper = new Swiper('.swiper-ico-news', {
	    slidesPerView: 3,
	    spaceBetween: 10,
	    loop: true,
	    loopFillGroupWithBlank: true,
	    autoplay:
	    {
	      	delay: 2500,
	      	disableOnInteraction: false
	    },
	    breakpoints: 
	    {
	      	1024: 
	      	{
	      		slidesPerView: 3,
	      		spaceBetween: 40
	      	},
	      	768: 
	      	{
	      		slidesPerView: 2,
	      		spaceBetween: 30
	      	},
	      	640: 
	      	{
	      		slidesPerView: 1,
	      		spaceBetween: 20
	      	},
	      	320: 
	      	{
	      		slidesPerView: 1,
	      		spaceBetween: 10
	      	}
	    },
	    pagination: {
	            el: '.swiper-pagination',
	            clickable: true,
	          },
	    navigation: 
	    {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
	    });
	}
	smooth_scroll(): void
	{
	$(document).on('click', 'a.navigation__link', function(e)
	{
		e.preventDefault();
		var link = $(this).attr('href');

		$('html, body').animate
		({
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

	cryptoCompare()
	{
		var crypto_arr = 
		[
			{'abbr' : 'BTC', 'name' : 'Bitcoin'},
			{'abbr' : 'ETH', 'name' : 'Ethereum'},
			{'abbr' : 'EOS', 'name' : 'EOS'},
			{'abbr' : 'BCH', 'name' : 'Bitcoin Cash'},
			{'abbr' : 'LTC', 'name' : 'Litecoin'},
			{'abbr' : 'ETC', 'name' : 'Ethereum Classic'},
			{'abbr' : 'XRP', 'name' : 'Ripple'},
			{'abbr' : 'ZEC', 'name' : 'ZCash'},
			{'abbr' : 'DASH', 'name' : 'Dash'},
			{'abbr' : 'NEO', 'name' : 'NEO'}
		]
		// console.log(cr)
		this.http.get("https://min-api.cryptocompare.com/data/pricemultifull?fsyms=BTC,ETH,EOS,BCH,LTC,ETC,XRP,ZEC,DASH,NEO&tsyms=USD").subscribe(response=>
		{
			crypto_arr.forEach(function(data, key)
			{
				crypto_arr[key]["r"] 		= response["RAW"][data["abbr"]]
				crypto_arr[key]["d"] 		= response["DISPLAY"][data["abbr"]]
				var c_name = data["name"].toLowerCase().replace(" ", "-")
				crypto_arr[key]["icon"]		= "https://cryptoindex.co/coinlogo/"+c_name+".png"
			});

			this.crypto_data = crypto_arr;
		});
	}

}
