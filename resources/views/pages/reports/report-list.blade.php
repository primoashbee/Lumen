@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	
	@if($type=='disbursements')
	<report-disbursement report_class="{{$class}}"></report-disbursement>
	@endif
	@if($type=='repayments')
	<report-repayment report_class="{{$class}}"></report-repayment>
	@endif
	@if($type=='deposit')
	<report-deposit report_class="{{$class}}"></report-deposit>
	@endif
	@if($type=='client')
	<report-client report_class="{{$class}}"></report-client>
	@endif
	@if($type=='dst')
	<report-dst report_class="{{$class}}"></report-dst>

	{{-- <report-client report_class="{{$class}}"></report-client> --}}
	@endif
</div>
@endsection