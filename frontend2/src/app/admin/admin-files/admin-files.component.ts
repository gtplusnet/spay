import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import { MemberInfoService }   from '../../member/member-info.service';
import { NgbModal} from '@ng-bootstrap/ng-bootstrap';
import { ClipboardService } from 'ngx-clipboard';

@Component({
	selector: 'app-admin-files',
	templateUrl: './admin-files.component.html',
	styleUrls: ['./admin-files.component.scss']
})
export class AdminFilesComponent implements OnInit {

	constructor(private copy: ClipboardService, private http : HttpClient, private rest: MemberInfoService, private globalConfigService: GlobalConfigService, private modalService : NgbModal) { }

	modal_ref : any;
	file_category : any;
	table : any;

	loading: boolean = false;
	adding: boolean = false;

	file : File = null;
	file_link : string;
	my_file : string;
	files : any = {};

	file_link_copy : string;


	ngOnInit() 
	{
		this.file_category = "all";
		this.files.category = "Official Documents";
		this.loadTable();

	}

	open(content)
	{
		this.modal_ref = this.modalService.open(content);
	}

	onFileSelectedDocument(event)
	{
		this.file 	   = <File>event.target.files[0];
		this.file_link = "";
	}

	loadTable()
	{
		this.loading = true;
		this.http.post(this.rest.api_url+"/api/admin/get_file_list", 
		{
			login_token: this.rest.login_token,
			category: this.file_category
		}).subscribe(response=>
		{
			this.table = response;
			this.loading = false;
		})
	}

	addNewFile()
	{
		this.adding = true;
		const formData = new FormData();
		formData.append('document', this.file);
		this.rest.uploadSystemFiles(formData).subscribe(response=>
		{
			if(response['status'] == 'success')
			{
				this.file_link = response['full_path'];
				this.http.post(this.rest.api_url+"/api/admin/add_new_file",
				{
					login_token: this.rest.login_token,
					file_name: this.files.name,
					file_category: this.files.category,
					file : this.file_link,
					file_type : "pdf"

				}).subscribe(response=>
				{
					this.adding = false;
					// window.location.href = "/admin/settings/system-files";
				});
			}
			else
			{
				this.adding = false;
			}
		});
		
	}

	copyContent(content)
	{
		var copyText = "http://aeolus-storage.sgp1.digitaloceanspaces.com/" + content;
		this.copy.copyFromContent(copyText);
	}

}
