@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<form class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header">
					<h3 class="h3">
						{{
							ucwords(
								str_replace('_',' ',
									explode('.',request()->route()->getName())[
										count(explode('.',request()->route()->getName()))-1
									]
								)
							)
						}} - Bulk Transaction </h3>
				</div>
				<div class="card-body">
					<bulk-deposit-transaction transaction="{{request()->route()->getName()}}"></bulk-deposit-transaction>
				</div>
			</div>
		</div>
	</form>
</div>
@endsection