@extends('layouts.user')

@section('content')

<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
				<div class="card-header">
					<h3 class="h3">Error Logs</h3>
				</div>
				<div class="card-body">
                   <div class="well well-lg" style="color:white">
                    <ul>
                    
                    @if($logs->count() > 0)
                    @foreach ($logs as $item)
                        <li> <span style="font-size: 1.5em;">Row # {{$item['row']}} <span>
                            <ul class="errors">
                                <li class="errors"> Errors 
                                    @foreach($item['errors'] as $error)
                                    <ul> - {{($error)}}</ul>
                                    @endforeach
                                </li>
                            </ul>
                            {{-- <ul>
                                <li> Field 
                                    <ul>{{$item['attribute']}}</ul>
                                </li>
                            </ul> --}}
                        </li>
                        
                    @endforeach
                    @endif
                    </ul>
                    </div>

                </div>
			</div>
		</div>
	</div>
</div>

@endsection