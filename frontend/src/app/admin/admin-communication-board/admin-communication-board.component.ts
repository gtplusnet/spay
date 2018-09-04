import { Component, OnInit } from '@angular/core';
import { MemberInfoService }    	from '../../member/member-info.service';
import { HttpClient, HttpHeaders } 	from '@angular/common/http';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-communication-board',
  templateUrl: './admin-communication-board.component.html',
  styleUrls: ['./admin-communication-board.component.scss']
})
export class AdminCommunicationBoardComponent implements OnInit {

  constructor(public rest : MemberInfoService, private http : HttpClient, private modalService: NgbModal) { }
  member_position_url 				: any;
  _params			  				: any;
  _list				  				: any;
  selected			  				: any;
  checked			  				: boolean;
  communication_board_url 			: any;
  communication_board_table_url 	: any;
  communication_board_details_url	: any;
  communication_board_update_url	: any;
  success_message					: string;
  error_message						: string;
  cloud_storage_url					: string;
  modal_ref							: any;
  search_title						: string;
  search_inserted_date				: string;
  search_inserted_date_to			: string;
  search_careers					: string;
  _table							: any;
  _details							: any;

  title	   			  				: string;
  subtitle 			  				: string;
  start_date 		  				: string;
  end_date 			  				: string;
  thumbnail			  				: string;
  banner 			  				: string;
  description		  				: any;
  thumbnailImage					: File = null;
  bannerImage						: File = null;
  thumbnail_image					: string;	
  banner_image						: string;
  thumbnail_link					: string;
  banner_link						: string;
  url_thumbnail						: any;
  url_banner						: any;
  submitted							: boolean;
  isChecked							: any;
  id								: number;
  table_loader						: boolean;
  banner_size						: boolean;
  timeout							: number;
  ngOnInit() {
  	this.cloud_storage_url = "https://aeolus-storage.sgp1.digitaloceanspaces.com/";
  	this.member_position_url = this.rest.api_url + "/api/admin/get_member_positions";
  	this.communication_board_url = this.rest.api_url + "/api/admin/communication_board_submit";
  	this.communication_board_table_url = this.rest.api_url + "/api/admin/communication_board_get_list";
	this.communication_board_details_url = this.rest.api_url + "/api/admin/communication_board_get_details";
	this.communication_board_update_url = this.rest.api_url + "/api/admin/communication_board_update";
  	this._params ={}
  	this.selected ={}
  	this.isChecked ={}
  	this.error_message = "no-message";
	this.success_message = "no-message";
	this.banner = null;
	this.thumbnail = null;
	this.url_thumbnail = "https://s3-eu-west-1.amazonaws.com/assets.kim.rixwiki.org/live/static/images/default-placeholder.3fd17a40547d.png";
	this.url_banner = "https://aeolus-storage.sgp1.digitaloceanspaces.com/lokalize/kycphotos/7k22QQ1PP4ePmULZ5kID3oy9AsadlHNcN592iD0o.jpeg";
	this.submitted = false;
	this.table_loader = false;
	this.search_careers = "";
  	this.loadMemberPositions();
  	this.loadTable();
  }

open(content)
{
	this.modal_ref = this.modalService.open(content);
}
openLg(content)
{
	this.modal_ref = this.modalService.open(content, {'size': 'lg'});
}

onSelectFileThumbnail(event)
{
	this.thumbnailImage = null;
    if (event.target.files && event.target.files[0]) 
    {
      var reader = new FileReader();
      this.thumbnailImage = event.target.files[0];
      reader.readAsDataURL(event.target.files[0]); // read file as data url

      reader.onload = (event) => { // called once readAsDataURL is completed
        this.url_thumbnail = reader.result;
      }
    }
}

onSelectFileBanner(event)
 {
 	this.bannerImage = null;
    if (event.target.files && event.target.files[0]) {
      var reader = new FileReader();
      this.bannerImage = event.target.files[0];
      reader.readAsDataURL(event.target.files[0]); // read file as data url

      reader.onload = (event) => { // called once readAsDataURL is completed
        this.url_banner = reader.result;
      }
    }
  }

loadDefault(response)
{
	for(let i = 0; i<response.length; i++)
	{
		this.isChecked[response[i].member_position_name] = false;
	}
}

viewEventDetails(id,selector)
{
	this.success_message = "no-message";
	this.error_message = "no-message";
	if(id != 0)
	{
		this._params = {}
		this.isChecked = {}
		this._params["login_token"] = this.rest.login_token;
		this.id = id;
		this._params["id"] = this.id;
		this.title = "";
		this.subtitle = "";
		this.start_date = "";
		this.end_date = "";
		this.description = "";
		this.url_banner = "";
		this.url_thumbnail = "";

		this.http.post(this.communication_board_details_url,this._params).subscribe(response=>
		{
			this._details 							= response;
			this.title 		    					= this._details.communication_board_title;
			this.subtitle 							= this._details.communication_board_subtitle;
			this.start_date 						= this._details.communication_board_start_date;
			this.end_date 							= this._details.communication_board_end_date;
			this.description 						= this._details.communication_board_description;
			this.url_banner 						= this.cloud_storage_url + this._details.communication_board_banner;
			this.url_thumbnail 						= this.cloud_storage_url + this._details.communication_board_thumbnail;

			this.isChecked["Member"] 				= parseInt(this._details.communication_board_career_member);
			this.isChecked["Community Manager"] 	= parseInt(this._details.communication_board_career_community_manager);
			this.isChecked["Marketing Director"] 	= parseInt(this._details.communication_board_career_marketing_director);
			this.isChecked["Ambassador"] 			= parseInt(this._details.communication_board_career_ambassador);
			this.isChecked["Advisor"] 				= parseInt(this._details.communication_board_career_advisor);
		},
			error=>
			{
				console.log(error);
			})
		this.openLg(selector);
	}
	else
	{
		
		this.title = "";
		this.subtitle = "";
		this.start_date = "";
		this.end_date = "";
		this.description = "";
		this.isChecked["Member"] 				= false;
		this.isChecked["Community Manager"] 	= false;
		this.isChecked["Marketing Director"]	= false;
		this.isChecked["Ambassador"] 			= false;
		this.isChecked["Advisor"] 				= false;
		this.url_banner = "https://aeolus-storage.sgp1.digitaloceanspaces.com/lokalize/kycphotos/7k22QQ1PP4ePmULZ5kID3oy9AsadlHNcN592iD0o.jpeg";
		this.url_thumbnail = "https://aeolus-storage.sgp1.digitaloceanspaces.com/lokalize/kycphotos/iIBAQi7HJ9Kr6cHIHVNXqGVxkWLSPdPMaBOzV3Nt.png";
		this.openLg(selector);
	}
}

loadTable()
{	this.table_loader = true;
	this._params = {}
	this._params["login_token"] = this.rest.login_token;
	this._params["title"] = this.search_title;
	this._params["careers"] = this.search_careers;
	this._params["date_from"] = this.search_inserted_date;
	this._params["date_to"]	= this.search_inserted_date_to;

	this.http.post(this.communication_board_table_url,this._params).subscribe(response=>{
		this._table = response;
		this.table_loader = false;
	},
			error=>
			{
				console.log(error);
			});
}

isSelected(index)
{
	for(let i = 0; i<this._list.length; i++)
	{
		if(index == this._list[i].member_position_name && !this.isChecked[index])
		{
			this.selected[index] = true;
			this.isChecked[index] = true;
		}
		else if(index == this._list[i].member_position_name && this.isChecked[index])
		{
			this.selected[index] = false;
			this.isChecked[index] = false;
		}
	}	
}

loadMemberPositions()
{
	this._params = {}
	this._params["login_token"] = this.rest.login_token;
	this.http.post(this.member_position_url, this._params).subscribe(response=>
	{
		this._list = response;
		this.loadDefault(response);
	},
			error=>
			{
				console.log(error);
			});

}

onSubmit()
{
	this.submitted = true;
	this.error_message = "no-message";
	this.success_message = "no-message";
	this._params = {}

	const formData = new FormData();
 	if(this.thumbnailImage != null)
 	{
 		formData.append('image',this.thumbnailImage);
 		this.rest.uploadImageOnServer(formData,"cb").subscribe(responseThumbnail=>{
 			if(responseThumbnail['status'] == "success")
 			{
 				this.thumbnail_link = responseThumbnail['full_path'];
 			}
 		},
			error=>
			{
				console.log(error);
			});

 	}
 	if(this.bannerImage !=null)
 	{
 		const formData_banner = new FormData();
 		formData_banner.append('image_banner',this.bannerImage);
 		this.rest.uploadImageOnServer(formData_banner,"banner").subscribe(responseBanner=>{
 		this.banner_link = responseBanner['full_path'];
 		if(responseBanner['status'] == "fail")
		{
			this.submitted = false;
			this.banner_size = false;
			this.success_message = "no-message";
			this.error_message = responseBanner['message'];
		}
		else
		{
			this.banner_size = true;
		}
 	
 		});
 	}
 	setTimeout(() => 
	{
		if(this.banner_size)
		{
			this._params["selected"]     = this.isChecked;
			this._params["title"]        = this.title;
			this._params["subtitle"] 	 = this.subtitle;
			this._params["start_date"]	 = this.start_date;
			this._params["end_date"] 	 = this.end_date;
			this._params["thumbnail"]  	 = this.thumbnail_link;
			this._params["banner"] 		 = this.banner_link
			this._params["description"]  = this.description;
			this._params["login_token"]	 = this.rest.login_token;
			
			this.http.post(this.communication_board_url,this._params).subscribe(data=>
			{
		
				if(data['status'] == "success")
				{
					this.error_message = "no-message";
					this.success_message = data['message'];
					this.submitted = false;
					this.loadTable();
					this.modal_ref.close();
				}
				else
				{
					this.success_message = "no-message";
					this.error_message = data['message'];
					this.submitted = false;
				}
			},
			error=>
			{
				console.log(error);
			});
		}
	},5000);	
}

onUpdate()
{
	console.log("testing");
	this.error_message = "no-message";
	this.success_message = "no-message";
	this._params={};
    this.submitted = true;
    this.banner_size = false;
    this.timeout = 0;

	if(this.thumbnailImage != null)
	{
		const formData = new FormData();
		formData.append('image',this.thumbnailImage);
		this.rest.uploadImageOnServer(formData,"cb").subscribe(responseThumbnail=>
		{
			this.thumbnail_link = responseThumbnail['full_path'];
			this.timeout = 5000;
		},
			error=>
			{
				console.log(error);
			}
		);
	}
	if(this.bannerImage != null)
	{
		const formData_banner = new FormData();
 		formData_banner.append('image_banner',this.bannerImage);
 		this.rest.uploadImageOnServer(formData_banner,"banner").subscribe(responseBanner=>{
 		this.banner_link = responseBanner['full_path'];
 		if(responseBanner['status'] == "fail")
		{
			this.submitted = false;
			this.banner_size = false;
			this.success_message = "no-message";
			this.error_message = responseBanner['message'];
		}
		else
		{
			this.banner_size = true;
			this.timeout = 0;
		}
 	
 		},
			error=>
			{
				console.log(error);
			});
	}
	else
	{
		console.log("banner size true");
		this.banner_size = true;
	}
	
		if(this.banner_size)
		{
			console.log("banner size accepted");
			this._params["selected"]     = this.isChecked;
			this._params["title"]        = this.title;
			this._params["subtitle"] 	 = this.subtitle;
			this._params["start_date"]	 = this.start_date;
			this._params["end_date"] 	 = this.end_date;
			this._params["thumbnail"]  	 = this.thumbnail_link;
			this._params["banner"] 		 = this.banner_link
			this._params["description"]  = this.description;
			this._params["login_token"]	 = this.rest.login_token;
			this._params["id"]			 = this.id;
			this.http.post(this.communication_board_update_url,this._params).subscribe(data=>
			{
				if(data['status'] == "success")
				{
					this.error_message = "no-message";
					this.success_message = data['message'];
					this.submitted = false;
					this.loadTable();
					this.modal_ref.close();
				}
				else
				{
					this.success_message = "no-message";
					this.error_message = data['message'];
					this.submitted = false;
				}
			},
			error=>
			{
				console.log(error);
			})
		}
}
}

