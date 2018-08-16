import { Component, OnInit } from '@angular/core';
import { HttpClient } 			from '@angular/common/http';
import { GlobalConfigService }  from '../../global-config.service';
import { MemberInfoService }   from '../../member/member-info.service';

@Component({
  selector: 'app-become03-ambassador',
  templateUrl: './become03-ambassador.component.html',
  styleUrls: ['./become03-ambassador.component.scss']
})
export class Become03AmbassadorComponent implements OnInit {

        company_name                : string;
        industry                    : string;
        director_name               : string;
        country                     : string;
        number_of_employees         : number;
        annual_revenue              : number;
        prefered_token_name         : string;
        contact_number              : any;
        contact_email               : any;
        remarks                     : string;
        supporting_document         : File = null;
        supporting_document_name    : string;
              
        submitted                   : boolean;
        error_message               : string;
        success_message             : string;
        supporting_document_link    : any;
        uploaded                    : boolean;
        uploading                   : boolean;
        config_url                  : any;
         
        constructor(private http : HttpClient, private rest:MemberInfoService, private globalConfigService:GlobalConfigService) { }

        ngOnInit() {
          this.submitted = false;
          this.error_message          = "no-message";
          this.success_message       = "no-message";
          this.remarks           = "advisor";
          this.supporting_document_name = "";
        }
   
       onFileSelectedDocument(event)
       {
         this.supporting_document = <File>event.target.files[0];
         this.supporting_document_link = "";
       }
       onSubmit()
       {
         this.error_message = "no-message";
         this.success_message = "no-message";
         var _param = {};
         const formData = new FormData();
         if(this.supporting_document_name != "")
         {
           if(this.supporting_document_link == "")
           {
             formData.append('document', this.supporting_document);
             this.uploading = true;
             this.rest.uploadDocumentOnServerForBusiness(formData).subscribe(response=>
             {
               if(response['status'] == 'success')
               {
                 this.uploaded = true;
                 this.uploading = false;
                 this.supporting_document_link = response['full_path'];
               }
               else
               {
                 this.uploading = false;
                 this.uploaded = false;
                 this.error_message = response['message'];
               }
             },
             error =>
             {
               this.error_message = JSON.stringify(error.message);
             });
           }
           setTimeout(() => 
             {
               this.uploading = false;
                 if(this.uploaded)
                 {
                 this.submitted = true;
                   _param["business_company_legal_name"]      = this.company_name;
                   _param["business_line"]                    = this.industry;
                   _param["business_director_name"]           = this.director_name;
                   _param["business_country"]                 = this.country;
                   _param["business_number_of_employees"]     = this.number_of_employees;  
                   _param["business_annual_revenue"]          = this.annual_revenue;
                   _param["business_supporting_documents"]    = this.supporting_document_link;
                   _param["business_pref_token_name"]         = this.prefered_token_name;
                   _param["business_contact_number"]          = this.contact_number;
                   _param["business_contact_email"]           = this.contact_email;
                   _param["business_remarks"]                 = this.remarks;
  
                   this.config_url = this.rest.api_url + "/api/submit_business_registration";
                   this.http.post(this.config_url, _param).subscribe(data=>
                   {
                     if(data['status']=="success")
                     {
                     this.submitted = false;
                       this.error_message = "no-message";
                       this.success_message = data["message"];
                     }
                     else
                     {
                     this.submitted = false;
                       this.success_message = "no-message";
                       this.error_message = data["message"];
                     }
                   });
                 }
             },
           8000);
         }
         else
         {
           this.success_message = "no-message";
           this.error_message = "Please select document.";
         }
       }
}
