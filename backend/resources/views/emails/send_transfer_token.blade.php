@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{ $info->first_name }} {{ $info->last_name }},</h4>
<p style="text-indent: 50px;">
	You have received <strong>{{$log_amount}} XS Tokens </strong> manually transferred by the successmall team.
</p>
<div class="text-center"><strong>Remarks:</strong> {{$remarks}}</div>
<small><em>
	To check your total tokens. Go to www.successmall.io, <strong>Login</strong> your account and in member dashboard you will see the amount of tokens you have.
</em></small>
<div style="text-align: center; padding: 15px 0px;">
	<a href="https://successmall.io/login" target="_blank" style="padding: 12px; border: 1px solid rgba(10,0,39,1); background: rgba(10,0,39,0.8); color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>
@endsection