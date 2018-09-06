@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	Thank you for verifying your email address! Please use the temporary password we generated for you. Please Logout if you are currently logged in and You can change it later in your 'My Account' > 'Profile' page of the website.
</p>
<div class="text-center"><strong>Temporary Password:</strong> {{$passkey}}</div>
<div style="text-align: center; padding: 15px 0px;">
	<a href="http://successmall.io/login" target="_blank" style="padding: 12px; border: 1px solid #005d12; background: #005d12; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>
@endsection