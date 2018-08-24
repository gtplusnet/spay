@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	Your password was succesfully changed by our team. Please take care of your password next time or use our forget password feature in the website. Thank you!
</p>
<div class="text-center"><strong>New Password:</strong> {{$new_password}}</div>
<div style="text-align: center; padding: 15px 0px;">
	<a href="http://successmall.io/login" target="_blank" style="padding: 12px; border: 1px solid #aa7809; background: #aa7809; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>
@endsection