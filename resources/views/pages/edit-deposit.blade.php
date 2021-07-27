@extends('layouts.user')

@section('content')
    <div class="content content pl-32 pr-8 mt-4" id="content-full">
        <edit-deposit :deposit="{{$deposit}}"></edit-deposit>
    </div>   
@endsection