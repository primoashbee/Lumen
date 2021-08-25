@extends('layouts.user')

@section('content')
	<div class="content pl-32 pr-8 mt-4" id="content-full">

		<div class="card">
			<div class="card-header">
				<h3 class="h3">Clusters</h3>
			</div>
			<div class="card-body">
				<cluster-list list_level="unit" office_id="{{auth()->user()->office()->first()->id}}"></cluster-list>
			</div>	
		</div>
		
	</div>
@endsection