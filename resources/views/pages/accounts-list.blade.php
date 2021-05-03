@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
				
				<div class="card-header">   
					<h3 class="h3"> All Accounts</h3>
				</div>
				<div class="card-body">
                    <account-list type="{{request()->type}}" action="{{url()->current()}}"></account-list>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection