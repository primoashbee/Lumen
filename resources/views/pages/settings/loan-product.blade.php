@extends('layouts.user')

@section('content')

<div class="" id="content-full">
    <loan-product id="{{$loan->id}}" type="{{$type}}"></loan-product>
</div>

@endsection