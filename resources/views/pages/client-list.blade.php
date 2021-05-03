@extends('layouts.user')

@section('content')
<form action="{{ url()->current()}}" method="GET">
<div class="content pl-32 pr-8 mt-4" id="content-full">
		
	<form class="row">
		<div class="col-lg-12">
			<div class="card">
				
				<div class="card-header">
					<h3 class="h3">Partner Clients</h3>
				</div>
				<div class="card-body">
					<client-list></client-list>
				</div>
			</div>
		</div>
	</form>
</div>
</form>
@endsection