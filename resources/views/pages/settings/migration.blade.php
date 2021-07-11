@extends('layouts.user')

@section('content')

<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header">
					<h3 class="h3"> General Data Import </h3>
					<a href="{{route('download.data-import')}}"> Download Template </a>
				</div>
				<div class="card-body">
					@if(Session::has('message'))
					<p class="alert {{ Session::get('alert-class', 'alert-info') }}">{{ Session::get('message') }}</p>
					@endif
					@if ($errors->any())
						<div class="alert alert-danger">
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
                    <form action="{{route('settings.import.post')}}" enctype="multipart/form-data" method="POST">
						{{csrf_field()}}
                        <input type="file" class="form-control" name="file">
                        <input type="submit" class="btn btn-success">
                    </form>

					<table class="table">
						<thead>
							<td><p class="title">Name </p></td>
							<td><p class="title">Migrated By </p></td>
							<td><p class="title">Status </p></td>
							<td><p class="title">Date Created </p></td>
							<td><p class="title"> Actions </p></td>
						</thead>
						<tbody>
							@foreach ($data as $item)
							<tr>
								<td>{{$item->name}}</td>
								<td>{{$item->user->name()}}</td>
								<td></td>
								{{-- <td>{{!is_null($item->logs) ? $item->logs->last()->message : 'Processing'}}</td> --}}
								<td>{{$item->created_at->format('F d, Y - g:i A')}}</td>
								<td><a href="{{route('settings.import.logs',$item->id)}}"><button class="btn btn-success"> View Logs <i class="fas fa-align-right"></i> </button></a></td>
							</tr>
							@endforeach
						</tbody>
					</table>
                </div>
			</div>
		</div>
	</div>
</div>

@endsection