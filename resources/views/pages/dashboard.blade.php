@extends('layouts.user')

@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
    @can('view_dashboard')
        <div class="container-fluid">

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card chart-container">
                        <div class="card-header">
                            
                                <div class="col-md-6" style="max-height: 200px">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Par Movement</h2>
                            
                                </div>
                            
                        </div>

                        <div class="card-body">
                            <chart-par-movement office_id="{{auth()->user()->office->first()->id}}" user_id="{{auth()->user()->id}}"></chart-par-movement>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card chart-container">
                        <div class="card-header">
                                <div class="col-md-6" style="max-height: 200px">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Repayment Trend</h2>
                                </div>
                            
                        </div>
                        <div class="card-body">
                            <chart-repayment-trend office_id="{{auth()->user()->office->first()->id}}" user_id="{{auth()->user()->id}}"></chart-repayment-trend>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12 mb-4">
                    <div class="card chart-container" style="height: 600px">
                    {{-- <div class="card chart-container" style="height: 450px"> --}}
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Disbursement Trend</h2>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <chart-disbursement-trend office_id="{{auth()->user()->office->first()->id}}"></chart-disbursement-trend>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mb-4">
                    <div class="card chart-container" style="height: 600px">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Resigned Vs New Loans</h2>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <chart-client-loans-trend office_id="{{auth()->user()->office->first()->id}}"></chart-client-loans-trend>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card chart-container">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Clients</h2>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="height:400px">
                                <chart-clients office_id="{{auth()->user()->office->first()->id}}"></chart-clients>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card chart-container">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="chart-header">{{ \Carbon\Carbon::now()->format('F d, Y')}}</h5>
                                    <h2 class="chart-title">Summary</h2>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-area" style="height:400px">
                                <chart-summary office_id="{{auth()->user()->office->first()->id}}"></chart-summary>
                            </div>
                        </div>
                    </div>
                </div>
            </div>	
        </div>
    @endcan
   <actions-notification office_id="{{auth()->user()->office->first()->id}}"></actions-notification>


</div>
@endsection