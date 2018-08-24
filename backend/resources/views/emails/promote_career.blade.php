@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	Your account's career has been promoted to {{ $position["member_position_name"] }}. Thank you for your continuous support!
</p>
<small><em>
	To check your account details. Go to www.successmall.io, <strong>Login</strong> your account and in member dashboard click <strong>My Account</strong>.
</em></small>
<div style="text-align: center; padding: 15px 0px;">
	<a href="http://successmall.io/login" target="_blank" style="padding: 12px; border: 1px solid #aa7809; background: #aa7809; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>
@endsection
