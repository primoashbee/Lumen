@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-8">
			<edit-role role_info="{{$role}}" permissions_list="{{ json_encode($permissions)}}"></edit-role>
		</div>
	</div>		
</div>
@endsection