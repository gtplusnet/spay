<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::options('/{id}', 'ApiController@index');
Route::options('/api/{id}', 'ApiController@index');
Route::options('/api/member/{id}', 'ApiController@index');
Route::options('/api/admin/{id}', 'ApiController@index');

Route::post('api/contact_us','ApiController@contact_us');
Route::get('api/google2fa', 'ApiController@googlef2a');
Route::any('/api/get_faqs','ApiController@faqs_list_homepage');

Route::get('api/decrypts', 'ApiController@decrypt_passwords');
Route::get('api/get_system_files', 'ApiController@get_system_files');
Route::get('/test_seed', 'SeedController@test_seed');
Route::post('/api/verify_captcha', 'ApiController@verify_captcha');



//Register and Login Route
Route::any('/api/register', 'ApiController@register');
Route::any('/api/new_register', 'ApiController@new_register');
Route::any('/api/get_country_codes', 'ApiController@get_country_codes');
Route::any('/api/login', 'ApiController@login');
Route::any('/api/new_login', 'ApiController@new_login');
Route::any('/api/verify_code', 'ApiController@check_verify_code');
Route::any('/api/resend_email', 'ApiController@resend_verification');
Route::post('/api/validate_key', 'ApiController@validate_key');
Route::any('/api/verify_email_address', 'ApiController@verify_email_address');
Route::get('/api/google_analytics_data', 'ApiController@google_analytics_data');

//business Registration
Route::post('/api/submit_business_registration','ApiController@submit_business_registration');
Route::post('/api/upload_file_business','ApiController@upload_file_business_application');
Route::post('/api/upload_system_files_documents','ApiController@upload_system_files_documents');
Route::post('/api/upload_proof','ApiController@upload_proof');

//Forget password front end
Route::any('/api/forgot_password','ApiController@forget_account_request');
Route::any('/api/change_password_request','ApiController@get_forget_account_request');
Route::any('/api/change_password_submit','ApiController@change_member_password');

Route::any('/api/balance/{address}', 'ApiController@checkBalance');

//Member API
Route::post('/api/member/dashboard', 'Member\MemberApiController@dashboard');
Route::post('/api/member/place_sponsor', 'Member\MemberApiController@place_sponsor');
Route::post('/api/member/member_info', 'Member\MemberApiController@member_info');
Route::post('/api/member/other_info', 'ApiController@other_info');
Route::post('/api/member/sale_stages', 'ApiController@get_sale_stages');
Route::post('/api/member/update_password', 'Member\MemberApiController@member_update_password');
Route::post('/api/member/update_contact_number', 'Member\MemberApiController@update_contact_number');
Route::post('/api/member/get_btc_transaction', 'Member\MemberApiController@get_btc_transaction');
Route::post('/api/member/cancel_transaction', 'Member\MemberApiController@cancel_transaction');
Route::post('/api/member/get_eth_transaction', 'Member\MemberApiController@get_eth_transaction');
Route::post('/api/member/get_referral_info', 'Member\MemberApiController@get_referral_info');
Route::post('/api/member/get_view_referral_info','Member\MemberApiController@get_view_referral_info');
Route::post('/api/member/check_tokens', 'Member\MemberApiController@check_tokens');
Route::post('/api/member/check_contributions', 'Member\MemberApiController@check_contributions');
Route::post('/api/member/enable_google_2fa', 'Member\MemberApiController@enable_google_2fa');
Route::post('/api/member/pair_google_2fa', 'Member\MemberApiController@pair_google_2fa');
Route::post('/api/member/change_status_2fa', 'Member\MemberApiController@change_status_2fa');
Route::post('/api/member/pair_code_google_2fa', 'Member\MemberApiController@pair_code_google_2fa');
Route::post('/api/member/get_recent_transaction', 'Member\MemberApiController@get_recent_transaction');
Route::post('/api/member/get_manual_transfer_list','Member\MemberApiController@get_manual_transfer_list');
Route::post('/api/member/check_notifications', 'Member\MemberApiController@check_notifications');
Route::post('/api/member/reset_notifications', 'Member\MemberApiController@reset_notifications');
Route::post('/api/member/first_update_information', 'Member\MemberApiController@first_update_information');
Route::post('/api/member/check_pending_order_method', 'Member\MemberApiController@check_pending_order_method');
Route::post('/api/member/get_buy_bonus', 'Member\MemberApiController@get_buy_bonus');

//kyc member
Route::post('/api/member/upload_file', 'Member\MemberApiController@upload_file');
Route::post('/api/member/member_submit_kyc_id','Member\MemberApiController@submit_kyc_id_level_2');
Route::post('/api/member/member_submit_kyc_selfie','Member\MemberApiController@submit_kyc_seflie_level_2');
Route::post('/api/member/get_kyc_status','Member\MemberApiController@get_kyc_status');
Route::post('/api/member/get_kyc_level', 'Member\MemberApiController@get_kyc_level');
Route::post('/api/member/get_verified_mail', 'Member\MemberApiController@get_verified_mail');
Route::post('/api/member/send_verify_email_kyc', 'Member\MemberApiController@send_verify_email_kyc');
Route::post('/api/member/verify_email_kyc', 'Member\MemberApiController@verify_email_kyc');


Route::post('/api/member/get_upcoming_event','Member\MemberApiController@get_upcoming_event');
Route::post('/api/member/get_upcoming_event_details','Member\MemberApiController@get_upcoming_event_details');

//kyc admin
Route::post('/api/admin/get_kyc_pending', 'Admin\AdminApiController@kyc_pending_request');
Route::any('/api/admin/get_kyc_list', 'Admin\AdminApiController@kyc_list');
Route::post('/api/admin/change_status','Admin\AdminApiController@change_kyc_status');

Route::post('/api/member/record_transaction', 'Member\MemberApiController@record_transaction');
//Admin API
Route::post('/api/admin/get_member_list', 'Admin\AdminApiController@get_member_list');
Route::post('/api/admin/active_deactive_user', 'Admin\AdminApiController@active_deactive_user');
Route::post('/api/admin/promote_demote_user', 'Admin\AdminApiController@promote_demote_user');
Route::post('/api/admin/change_user_password', 'Admin\AdminApiController@change_user_password');
Route::post('/api/admin/unactivated_members', 'Admin\AdminApiController@unactivated_members');
Route::post('/api/admin/login_history', 'Admin\AdminApiController@login_history');
Route::post('/api/admin/btc_transactions', 'Admin\AdminApiController@btc_transaction_list');
Route::post('/api/admin/btc_pending_transactions', 'Admin\AdminApiController@btc_pending_transactions');
Route::post('/api/admin/get_member_transactions', 'Admin\AdminApiController@get_member_transactions');
Route::post('/api/admin/get_member_positions', 'Admin\AdminApiController@get_member_positions');
Route::post('/api/admin/get_current_member_positions', 'Admin\AdminApiController@get_current_member_positions');
Route::post('/api/admin/update_current_member_positions', 'Admin\AdminApiController@update_current_member_positions');
Route::any('/api/admin/collaborate_details', 'Admin\AdminApiController@collaborate_details');
Route::post('/api/admin/get_referral_bonus_list', 'Admin\AdminApiController@get_referral_bonus_list');
Route::post('/api/admin/get_salestage_bonus_list', 'Admin\AdminApiController@get_salestage_bonus_list');
Route::post('/api/admin/communication_board_submit','Admin\AdminApiController@communication_board_submit');
Route::post('/api/admin/communication_board_get_list', 'Admin\AdminApiController@get_communication_board_list');
Route::post('/api/admin/communication_board_get_details', 'Admin\AdminApiController@get_communication_board_details');
Route::post('/api/admin/communication_board_update','Admin\AdminApiController@communication_board_update');
Route::post('/api/admin/career_setting','Admin\AdminApiController@get_career_setting_info');
Route::post('/api/admin/career_setting_update','Admin\AdminApiController@update_career_info');
Route::post('/api/admin/get_pending_member','Admin\AdminApiController@get_pending_member');
Route::post('/api/admin/get_total_stored_btc','Admin\AdminApiController@get_total_stored_btc');
Route::post('/api/admin/get_total_stored_eth','Admin\AdminApiController@get_total_stored_eth');
Route::post('/api/admin/get_total_token_release','Admin\AdminApiController@get_total_token_release');
Route::post('/api/admin/get_business_application_list','Admin\AdminApiController@get_business_application_list');
Route::post('/api/admin/get_business_application_details','Admin\AdminApiController@get_business_application_details');
Route::post('/api/admin/career_change_update','Admin\AdminApiController@career_change_update');
Route::post('/api/admin/get_recent_details','Admin\AdminApiController@get_recent_details');
Route::post('/api/admin/check_receiver', 'Admin\AdminApiController@check_receiver');
Route::post('/api/admin/transfer_token', 'Admin\AdminApiController@transfer_token');
Route::post('/api/admin/manual_transfer_list', 'Admin\AdminApiController@manual_transfer_list');
Route::post('/api/admin/total_tokens_transferred', 'Admin\AdminApiController@total_tokens_transferred');
Route::post('/api/admin/get_referral_info','Admin\AdminApiController@get_referral_info');
Route::post('/api/admin/view_referral_info','Admin\AdminApiController@get_view_referral_info');
Route::post('/api/admin/view_all_central_wallet', 'Admin\AdminApiController@view_all_central_wallet');
Route::post('/api/admin/edit_central_wallet', 'Admin\AdminApiController@edit_central_wallet');
Route::post('/api/admin/add_new_central_wallet', 'Admin\AdminApiController@add_new_central_wallet');
Route::post('/api/admin/get_admin_notification','Admin\AdminApiController@get_admin_notification');
Route::post('/api/admin/admin_viewed_notif','Admin\AdminApiController@admin_viewed_notif');
Route::post('/api/admin/get_faqs','Admin\AdminApiController@get_faqs');
Route::post('/api/admin/add_faqs','Admin\AdminApiController@add_faqs');
Route::post('/api/admin/edit_faqs','Admin\AdminApiController@edit_faqs');
// Route::post('/api/admin/get_referral_count_by_career','Admin\AdminApiController@get_referral_count_by_career');
// Route::post('/api/admin/setting_default_wallet_central','Admin\AdminApiController@setting_default_wallet_central');
// Route::post('/api/admin/main_wallet_addresses','Admin\AdminApiController@main_wallet_addresses');
// Route::post('/api/admin/release_wallet','Admin\AdminApiController@release_wallet');
// Route::post('/api/admin/get_file_list','Admin\AdminApiController@get_file_list');
// Route::post('/api/admin/add_new_file','Admin\AdminApiController@add_new_file');
Route::post('/api/admin/get_referral_count_by_career','Admin\AdminApiController@get_referral_count_by_career');
Route::post('/api/admin/setting_default_wallet_central','Admin\AdminApiController@setting_default_wallet_central');
Route::post('/api/admin/setting_update_wallet_central','Admin\AdminApiController@setting_update_wallet_central');
Route::post('/api/admin/main_wallet_addresses','Admin\AdminApiController@main_wallet_addresses');
Route::post('/api/admin/release_wallet','Admin\AdminApiController@release_wallet');
Route::post('/api/admin/batch_release_wallet','Admin\AdminApiController@batch_release_wallet');
Route::post('/api/admin/get_file_list','Admin\AdminApiController@get_file_list');
Route::post('/api/admin/add_new_file','Admin\AdminApiController@add_new_file');
Route::post('/api/admin/setup_wallet_address','Admin\AdminApiController@setup_wallet_address');
Route::post('/api/admin/get_estimated_tx','Admin\AdminApiController@get_estimated_tx');
Route::post('/api/admin/get_total_crypto','Admin\AdminApiController@get_total_crypto');
Route::post('/api/admin/update_user_information','Admin\AdminApiController@update_user_information');
Route::post('/api/admin/get_release_logs','Admin\AdminApiController@get_release_logs');
Route::post('/api/admin/get_kyc_proof','Admin\AdminApiController@get_kyc_proof');

//member referral API
Route::post('/api/member/get_referrals','Member\MemberApiController@get_referrals');

//Admin Excel API
Route::get('/api/admin/excel/export_member_list', 'Admin\AdminExcelController@export_member_list');
Route::get('/api/admin/excel/export_transaction_list', 'Admin\AdminExcelController@export_transaction_list');
Route::get('/api/member/excel/export_member_transaction_list', 'Member\MemberApiController@export_member_transaction_list');
Route::get('/api/admin/excel/export_btc_transaction', 'Admin\AdminExcelController@export_member_transaction_list');
Route::get('/api/admin/excel/export_transfer_token', 'Admin\AdminExcelController@export_transfer_token');
Route::get('/api/admin/excel/export_referral_bonus','Admin\AdminExcelController@export_referral_bonus_list');
Route::get('/api/admin/excel/export_sale_stage_bonus_list','Admin\AdminExcelController@export_sale_stage_bonus_list');
Route::get('/api/admin/excel/export_affiliate_history_list','Admin\AdminExcelController@export_affiliate_history_list');

//Admin Sale Stage
Route::post('/api/admin/conversion','Admin\AdminConversionController@index');
Route::post('/api/admin/sale_stage_update','Admin\AdminConversionController@sale_stage_update');

//Member API


