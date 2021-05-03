@extends('layouts.user')

@section('content')

<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-12">
			<deposit-dashboard deposit_account_id="{{$deposit_account_id}}" client_id="{{$client_id}}"></deposit-dashboard>
		</div>
	</div>
</div>

@endsection