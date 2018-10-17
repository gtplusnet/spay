
@extends('emails.email_layout')
@section('content')
<h4 style="margin-bottom: 10px;">Hi {{$member["first_name"]}} {{$member["last_name"]}},</h4>
<p style="text-indent: 50px;">
	You have recently placed an order of <strong>{{ $amount }} XS Tokens</strong> via {{ $method }}. Please pay on or before {{ date("F j, Y",strtotime($record["expiration_date"])) }}. Failing to pay the expected payment on time will result to order expiration. Thank you!
</p>
<hr style="margin: 3px 0px">
<p style="text-align: center;">
	<h4 style="color: #555">Copy this wallet address: </h4>
	<h5>{{ $address["member_address"] }}</h5>
	<h6 style="margin: 3px 0px">OR</h6>
	<h4>Scan this QR Code: </h4>
	<img src="http://chart.apis.google.com/chart?cht=qr&chs=200x200&chl={{ $address["member_address"] }}&chld=H|0">
</p>
{{-- <small><em>
	To check your transactions details. Go to www.successmall.io, <strong>Login</strong> your account and in member dashboard click <strong>BTC or ETH Transactions</strong>.
</em></small> --}}
<div style="text-align: center; padding: 15px 0px;">
	<a href="http://successmall.io/login" target="_blank" style="padding: 12px; border: 1px solid #005d12; background: #005d12; color: #eee; cursor: pointer; text-decoration: none; font-size: 13px;">Click Here to Login</a>
</div>

@endsection