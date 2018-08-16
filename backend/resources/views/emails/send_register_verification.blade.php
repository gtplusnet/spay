@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member->first_name}} {{$member->last_name}},</h4>
<p style="text-indent: 50px;">
	You have recently requested a verification of your email address for your lokalize account. Please click the button below to verify your email address. Thank you!
</p>
<div style="text-align: center; padding: 15px 0px;">
	<a href="https://lokalize.io/verify_email/{{$email->verification_code}}" target="_blank" style="padding: 12px; border: 1px solid rgba(10,0,39,1); background: rgba(10,0,39,0.8); color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Verify Email Address</a>
</div>
@endsection