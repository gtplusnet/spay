@extends('emails.email_layout')
@section('content')
<div>
	Hi Support,
</div>
</br>
<div>
	From : {{$request['email']}}<br>
	Name : {{$request["name"]}}<br>
</div>
<div>
	Message: {{$request["message"]}}
</div>
</br>
<div>
	Thank You.
</div>
</br>
<div>
	Best Regards, </br>
</div>
@endsection