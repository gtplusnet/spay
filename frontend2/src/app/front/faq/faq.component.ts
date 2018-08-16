import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { MemberInfoService } 	from '../../member/member-info.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { FormsModule }   from '@angular/forms';


@Component({
  selector: 'app-faq',
  templateUrl: './faq.component.html',
  styleUrls: ['./faq.component.scss']
})
export class FaqComponent implements OnInit {

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }
  faq_loading : boolean;
  faq_content : any;
  faq_question_data : any;
  list : any = {};
  modal_ref : any;
  data_focus : any;
  search_box : string;
  faq_counts : any = {};
  ngOnInit() {
  	this.getFaqs();
  }

  getFaqs()
  {
  	this.faq_loading = true;
  	var faq_url = this.rest.api_url + "/api/get_faqs";
  	this.http.post(faq_url,
      {
        search : this.search_box ? this.search_box : null
      }).subscribe(response=>
  	{
  		this.list = response;
      this.faq_counts.all = this.list.all.length;
      this.faq_counts.withdraw = this.list.withdraw.length;
      this.faq_counts.buy_coin = this.list.buy_coin.length;
      this.faq_counts.promotion = this.list.promotion.length;
      this.faq_counts.purchase_bonus = this.list.purchase_bonus.length;
      this.faq_counts.affiliate_bonus = this.list.affiliate_bonus.length;
      this.faq_counts.others = this.list.others.length;
  		this.faq_loading = false;
  	});
  }

  readQuestion(id, selector)
  {
    this.data_focus = this.rest.findObjectByKey(this.list.all, 'faq_id', id);
    this.open(selector);
  }

  open(content)
  {
    this.modal_ref = this.modalService.open(content, {size: 'lg'});
  }
}
