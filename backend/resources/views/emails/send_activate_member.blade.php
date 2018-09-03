@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	We have activated your account. You can now login using your email/username and password. Thank you!
</p>
<div style="text-align: center; padding: 15px 0px;">
	<a href="http://ahm.digimahouse.com/login" target="_blank" style="padding: 12px; border: 1px solid #005d12; background: #005d12; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>
@endsection
