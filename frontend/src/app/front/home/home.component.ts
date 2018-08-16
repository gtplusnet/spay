import { Component, OnInit } from '@angular/core';
import { MemberInfoService } 	from '../../member/member-info.service';
import { GlobalConfigService }  from '../../global-config.service';
import { HttpClient, HttpHeaders }     from '@angular/common/http';
import { Router }         from "@angular/router";
import {Observable} from 'rxjs/Rx';

import * as $ from "jquery";
import { WOW } from 'wowjs';
import * as Hammer from 'hammerjs';
import { NguCarousel } from '@ngu/carousel';

@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.scss']
})

export class HomeComponent implements OnInit {
    public carouselOne: NguCarousel;
    public pieChartLabels:string[] = ['Pre TGE Stage', 'Public TGE Stage', 'Bounty And Marketing', 'Management', 'Company Reserve'];
    public pieChartData:number[] = [490000000, 210000000, 50000000, 100000000, 150000000];
    public pieChartColors:any[] = [
    {
        backgroundColor: ['#f7af22', '#454445', '#fbe962', '#39b54a', '#2785e3'], 
        borderColor: ['#f7af22', '#454445', '#fbe962', '#39b54a', '#2785e3']
    }
  ];
  public pieChartType:string = 'pie';

  public doughnutChartLabels:string[] = ['Management, Operations, Employees and System Development', 'Company Reserve', 'Legal Advisory', 'Marketing and Expansion', 'Acquisition of Companies'];
  public doughnutChartData:number[] = [45, 10, 10, 15, 20];
  public doughnutChartColors:any[] = [
  {
    backgroundColor: ['#781f96', '#f7af22', '#454445', '#fbe962', '#39b54a'], 
    borderColor: ['#781f96', '#f7af22', '#454445', '#fbe962', '#39b54a']
  }
  ];
  public doughnutChartType:string = 'doughnut';

  // events
  public chartClicked(e:any):void {
    //console.log(e);
  }

  public chartHovered(e:any):void {
    //console.log(e);
  }

  myStyle            : object = {};
  myParams           : object = {};
  width              : number = 100;
  height             : number = 100;

  full_name          : string;
  email_address      : any;
  message            : string;
  error_message      : string;
  success_message    : string;
  config_url         : string;
  sent               : boolean;
  stage_count_down : any;

  initializing : boolean;

  toggle_chat : boolean = false;

  sale_stage_name = null;

  constructor(public rest: MemberInfoService, private http : HttpClient, private router:Router, public globalConfigService:GlobalConfigService) 
  {

  }

  ngOnInit() 
  {
    this.sale_stage_name = this.rest._stages.sale_stage_type ? this.rest._stages.sale_stage_type.replace(new RegExp("_","g"), ' ').toUpperCase() : null;
    this.carouselOne = {
      grid: {xs: 1, sm: 1, md: 5, lg: 5, all: 0},
      slide: 1,
      speed: 400,
      interval: 4000,
      point: {
        visible: true
      },
      load: 2,
      touch: true,
      loop: false,
      custom: 'banner'
    }
        this.error_message = "no-message";
        this.success_message = "no-message";
        this.smooth_scroll();
        this.particles();
        this.parallax();
        this.timeline();
        new WOW().init();
        this.sale_stage_count_down();
        // this.toggleChat();
        this.show_token_allocation();
      }

      public myfunc(event: Event) {
     // carouselLoad will trigger this funnction when your load value reaches
     // it is helps to load the data by parts to increase the performance of the app
     // must use feature to all carousel
   }

    toggleChat()
    {
        // (function(){ var widget_id = 'NPdzpVs6OW';var d=document;var w=window;function l(){
        // var s = document.createElement('script'); s.type = 'text/javascript'; s.async = false; s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if((<any>window).attachEvent){(<any>window).attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        (function () {
            var widget_id = 'UP09zfEF4H'; var d = document; var w = window; function l() {
                var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = '//code.jivosite.com/script/widget/' + widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);
            } if (d.readyState == 'complete') { l(); } else { if ((<any>w).attachEvent) { (<any>w).attachEvent('onload', l); } else { w.addEventListener('load', l, false); } }
        })();
    }
    
    // toggleChat()
    // {
    //     this._ngxZendeskWebwidgetService.activate()
    // }

    show_token_allocation()
    {
      $("#token_allocation").css("display","inline"); 
      $("#proceeds_allocation").css("display","none");
      this.pieChartType = this.pieChartType === 'doughnut' ? 'pie' : 'doughnut'; 
    }  

    show_proceeds_allocation() : void
    {
      $("#token_allocation").css("display","none"); 
      $("#proceeds_allocation").css("display","inline"); 
      this.doughnutChartType = this.doughnutChartType === 'pie' ? 'doughnut' : 'pie';
    }   
    
    smooth_scroll(): void
    {
      $(document).on('click', 'a.navigation__link', function(e){
        e.preventDefault();
        var link = $(this).attr('href');

        $('html, body').animate({
          scrollTop: $(link).offset().top}, 1000);
        return false; 
      });
    }

    actionLogout() : void
    {
      this.globalConfigService.logout();
      this.router.navigate(['/']); 
    }

    sale_stage_count_down() : any
    {
      var countDownDate = new Date(this.rest._stages.sale_stage_end_date).getTime();
      this.stage_count_down = setInterval(function() {

          // Get todays date and time
          var now = new Date().getTime();

          // Find the distance between now an the count down date
          var distance = countDownDate - now;
          var days = Math.floor(distance / (1000 * 60 * 60 * 24));
          var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
          var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
          var seconds = Math.floor((distance % (1000 * 60)) / 1000);

          // Time calculations for days, hours, minutes and seconds      

          $(".days").html(days + "");
          $(".hours").html(hours + "");
          $(".minutes").html(minutes + "");
          
          $(".seconds").html(seconds + "");

          // If the count down is finished, write some text 
          if (distance < 0) 
          {
            clearInterval(this.stage_count_down);

          }
        }, 1000);
    }

    timeline(): void
    {
          // VARIABLES
          const timeline = document.querySelector(".timeline ol"),
          elH = document.querySelectorAll(".timeline li > div"),
          arrows = document.querySelectorAll(".timeline .arrows .arrow"),
          arrowPrev = document.querySelector(".timeline .arrows .arrow__prev"),
          arrowNext = document.querySelector(".timeline .arrows .arrow__next"),
          firstItem = document.querySelector(".timeline li:first-child"),
          lastItem = document.querySelector(".timeline li:last-child"),
          xScrolling = 280,
          disabledClass = "disabled";

          // START
          window.addEventListener("load", init);

          function init() {
            setEqualHeights(elH);
            animateTl(xScrolling, arrows, timeline);
            setSwipeFn(timeline, arrowPrev, arrowNext);
            setKeyboardFn(arrowPrev, arrowNext);
          }

          // SET EQUAL HEIGHTS
          function setEqualHeights(el) {
            let counter = 0;
            for (let i = 0; i < el.length; i++) {
              const singleHeight = el[i].offsetHeight;

              if (counter < singleHeight) {
                counter = singleHeight;
              }
            }

            for (let i = 0; i < el.length; i++) {
              el[i].style.height = `${counter}px`;
            }
          }

          // CHECK IF AN ELEMENT IS IN VIEWPORT
          // http://stackoverflow.com/questions/123999/how-to-tell-if-a-dom-element-is-visible-in-the-current-viewport
          function isElementInViewport(el) {
            const rect = el.getBoundingClientRect();
            return (
              rect.top >= 0 &&
              rect.left >= 0 &&
              rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
              rect.right <= (window.innerWidth || document.documentElement.clientWidth)
              );
          }

          // SET STATE OF PREV/NEXT ARROWS
          function setBtnState(el, flag = true) {
            if (flag) {
              el.classList.add(disabledClass);
            } else {
              if (el.classList.contains(disabledClass)) {
                el.classList.remove(disabledClass);
              }
              el.disabled = false;
            }
          }

          // ANIMATE TIMELINE
          function animateTl(scrolling, el, tl) {
            let counter = 0;
            for (let i = 0; i < el.length; i++) {
              el[i].addEventListener("click", function() {
                if (!(<HTMLButtonElement>arrowPrev).disabled) {
                  (<HTMLButtonElement>arrowPrev).disabled = true;
                }
                if (!(<HTMLButtonElement>arrowNext).disabled) {
                  (<HTMLButtonElement>arrowNext).disabled = true;
                }
                const sign = (this.classList.contains("arrow__prev")) ? "" : "-";
                if (counter === 0) {
                  tl.style.transform = `translateX(-${scrolling}px)`;
                } else {
                  const tlStyle = getComputedStyle(tl);
                  // add more browser prefixes if needed here
                  const tlTransform = tlStyle.getPropertyValue("-webkit-transform") || tlStyle.getPropertyValue("transform");
                  const values = parseInt(tlTransform.split(",")[4]) + parseInt(`${sign}${scrolling}`);
                  tl.style.transform = `translateX(${values}px)`;
                }

                setTimeout(() => {
                  isElementInViewport(firstItem) ? setBtnState(arrowPrev) : setBtnState(arrowPrev, false);
                  isElementInViewport(lastItem) ? setBtnState(arrowNext) : setBtnState(arrowNext, false);
                }, 1100);

                counter++;
              });
            }
          }

          // ADD SWIPE SUPPORT FOR TOUCH DEVICES
          function setSwipeFn(tl, prev, next) {
            const hammer = new Hammer(tl);
            hammer.on("swipeleft", () => next.click());
            hammer.on("swiperight", () => prev.click());
          }

          // ADD BASIC KEYBOARD FUNCTIONALITY
          function setKeyboardFn(prev, next) {
            document.addEventListener("keydown", (e) => {
              if ((e.which === 37) || (e.which === 39)) {
                const timelineOfTop = this.timeline.offsetTop;
                const y = window.pageYOffset;
                if (timelineOfTop !== y) {
                  window.scrollTo(0, timelineOfTop);
                }
                if (e.which === 37) {
                  prev.click();
                } else if (e.which === 39) {
                  next.click();
                }
              }
            });
          }
        }

        particles(): void
        {
          this.myStyle = {
            'position': 'absolute',
            'width': '100%',
            'height': '100%',
            'z-index': 10,
            'top': 0,
            'left': 0,
            'right': 0,
            'bottom': 0,
          };

          this.myParams = {
            "particles": {
              "number": {
                "value": 30,
                "density": {
                  "enable": true,
                  "value_area": 800
                }
              },
              "color": {
                "value": "#31e4ea"
              },
              "shape": {
                "type": "circle",
                "stroke": {
                  "width": 0,
                  "color": "#000000"
                },
                "polygon": {
                  "nb_sides": 5
                },
                "image": {
                  "src": "img/github.svg",
                  "width": 100,
                  "height": 100
                }
              },
              "opacity": {
                "value": 0.49716301422833176,
                "random": false,
                "anim": {
                  "enable": true,
                  "speed": 1,
                  "opacity_min": 0.1,
                  "sync": false
                }
              },
              "size": {
                "value": 3,
                "random": true,
                "anim": {
                  "enable": false,
                  "speed": 40,
                  "size_min": 0.1,
                  "sync": false
                }
              },
              "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.04734885849793636,
                "width": 1
              },
              "move": {
                "enable": true,
                "speed": 6,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                  "enable": false,
                  "rotateX": 600,
                  "rotateY": 1200
                }
              }
            },
            "interactivity": {
              "detect_on": "canvas",
              "events": {
                "onhover": {
                  "enable": true,
                  "mode": "grab"
                },
                "onclick": {
                  "enable": false,
                  "mode": "push"
                },
                "resize": true
              },
              "modes": {
                "grab": {
                  "distance": 400,
                  "line_linked": {
                    "opacity": 0.5
                  }
                },
                "bubble": {
                  "distance": 400,
                  "size": 40,
                  "duration": 2,
                  "opacity": 8,
                  "speed": 3
                },
                "repulse": {
                  "distance": 200,
                  "duration": 0.4
                },
                "push": {
                  "particles_nb": 4
                },
                "remove": {
                  "particles_nb": 2
                }
              }
            },
            "retina_detect": true
          }
        }

        parallax() : void
        {
          if("ontouchstart" in window)
          {
            document.documentElement.className = document.documentElement.className + " touch";
          }
          if(!$("html").hasClass("touch"))
          {
            /* background fix */
            $(".parallax").css("background-attachment", "fixed");
          }

          $(window).resize(this.fullscreenFix);
          this.fullscreenFix();

          $(window).resize(this.backgroundResize);
          $(window).focus(this.backgroundResize);
          this.backgroundResize();

          if(!$("html").hasClass("touch"))
          {
            $(window).resize(this.parallaxPosition);
            //$(window).focus(this.parallaxPosition);
            $(window).scroll(this.parallaxPosition);
            this.parallaxPosition();
          }
        }

        fullscreenFix() : void
        {
          var h = $('body').height();
        // set .fullscreen height
        $(".content-b").each(function(i){
          if($(this).innerHeight() > h){ $(this).closest(".fullscreen").addClass("overflow");
        }
      });
      }

      backgroundResize() : void
      {
        var windowH = $(window).height();
        $(".background").each(function(i){
          var path = $(this);
            // variables
            var contW = path.width();
            var contH = path.height();
            var imgW = parseInt(path.attr("data-img-width"));
            var imgH = parseInt(path.attr("data-img-height"));
            var ratio = imgW / imgH;
            // overflowing difference
            var diff = parseFloat(path.attr("data-diff"));
            diff = diff ? diff : 0;
            // remaining height to have fullscreen image only on parallax
            var remainingH = 0;
            if(path.hasClass("parallax") && !$("html").hasClass("touch")){
              var maxH = contH > windowH ? contH : windowH;
              remainingH = windowH - contH;
            }
            // set img values depending on cont
            imgH = contH + remainingH + diff;
            imgW = imgH * ratio;
            // fix when too large
            if(contW > imgW){
              imgW = contW;
              imgH = imgW / ratio;
            }
            //
            path.data("resized-imgW", imgW);
            path.data("resized-imgH", imgH);
            path.css("background-size", imgW + "px " + imgH + "px");
          });
      }

      parallaxPosition(): void
      {
        var heightWindow = $(window).height();
        var topWindow = $(window).scrollTop();
        var bottomWindow = topWindow + heightWindow;
        var currentWindow = (topWindow + bottomWindow) / 2;
        $(".parallax").each(function(i){
          var path = $(this);
          var height = path.height();
          var top = path.offset().top;
          var bottom = top + height;
            // only when in range
            if(bottomWindow > top && topWindow < bottom){
              var imgW = path.data("resized-imgW");
              var imgH = path.data("resized-imgH");
                // min when image touch top of window
                var min = 0;
                // max when image touch bottom of window
                var max = - imgH + heightWindow;
                // overflow changes parallax
                var overflowH = height < heightWindow ? imgH - height : imgH - heightWindow; // fix height on overflow
                top = top - overflowH;
                bottom = bottom + overflowH;
                // value with linear interpolation
                var value = min + (max - min) * (currentWindow - top) / (bottom - top);
                // set background-position
                var orizontalPosition = path.attr("data-oriz-pos");
                orizontalPosition = orizontalPosition ? orizontalPosition : "50%";
                $(this).css("background-position", orizontalPosition + " " + value + "px");
              }
            });
      }

      onSubmitContactUs()
      {
        this.error_message = "no-message";
        this.success_message = "no-message";
        this.sent            = true;
        var _param = {}
        _param["name"]      = this.full_name;
        _param["email"]     = this.email_address;
        _param["message"]   = this.message;

        this.config_url = this.rest.api_url + "/api/contact_us";
        this.http.post(this.config_url,_param).subscribe(data=>
        {
          if(data["status"] == "success")
          {
            this.error_message   = "no-message";
            this.success_message = data["message"];
            this.full_name       = "";
            this.email_address   = "";
            this.message         = "";
          }
          else
          {
            this.success_message = "no-message";
            this.error_message   = data["message"];
          }
          this.sent = false;
        });

      }
    }

