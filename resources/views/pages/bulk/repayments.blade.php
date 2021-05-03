
@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<form class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header">
					<h3 class="h3">
						Repayment - Bulk Transaction </h3>
				</div>
				<div class="card-body">
					{{-- <bulk-repayment></bulk-repayment> --}}
					<bulk-repayment-v2></bulk-repayment-v2>
				</div>
			</div>
		</div>
	</form>
</div>
@endsection