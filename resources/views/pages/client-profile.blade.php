@extends('layouts.user')
@section('content')

	<div class="content content pl-32 pr-8 mt-4" id="content-full">
		<client-profile clientinfo="{{$client}}"></client-profile>
	</div>
@endsection