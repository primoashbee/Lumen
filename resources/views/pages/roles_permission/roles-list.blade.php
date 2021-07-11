@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<form class="row">
		<div class="col-lg-12">
			<!-- <permission-filter></permission-filter> -->
			<role-list></role-list>
		</div>
	</form>
</div>
@endsection