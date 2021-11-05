@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
    <client-edit-loan-account client_id="{{$client_id}}" loan_id = "{{$loan_id}}" businesses="{{$client->businesses}}" household_income="{{$client->household_income}}"></client-edit-loan-account>
</div>            

@endsection