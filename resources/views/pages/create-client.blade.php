@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
    <div class="">
   
      {{-- <step-form></step-form> --}}
      {{-- <create-client-form></create-client-form> --}}
      <create-client></create-client>
      {{-- <upload-file></upload-file> --}}
    </div>
   </div>
@endsection

@section('scripts')
@endsection