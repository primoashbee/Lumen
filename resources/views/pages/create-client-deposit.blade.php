@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
    <create-client-deposit client_id="{{$client_id}}" deposit="{{$deposit}}"></create-client-deposit>
</div>

@endsection