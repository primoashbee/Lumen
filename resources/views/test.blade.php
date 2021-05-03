@extends('layouts.user')

@section('content')
<div class="content content pl-32 pr-8 mt-4" id="content-full">
	<div class="group-wrapper">
		<div class="form-group">
			<form method="post" action="/import" enctype="multipart/form-data">
				{{ csrf_field() }}
				<input type="file" name="file" />
				<button type="submit" name="submit" class="btn btn-success"> Submit </button>
			</form>
		</div>
	</div>
</div>
@endsection
