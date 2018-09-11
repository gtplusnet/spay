@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	You have recently requested to reset the password of your account, kindly click the link below to reset your password and enter the verification code below.
</p>
<div class="text-center"><strong>Verification Code:</strong> {{$request["verification_code"]}}</div>

<div style="text-align: center; padding: 15px 0px;">
	<a href="http://ahmtoken.io/reset_password/{{$request["forget_account_request_id"]}}" target="_blank" style="padding: 12px; border: 1px solid #005d12; background: #005d12; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Reset Your Password</a>
</div>
@endsection