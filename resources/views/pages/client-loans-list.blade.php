@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<div class="row">
		<div class="col-lg-12">
			<div class="card">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="/clients">Client</a></li>
                      <li class="breadcrumb-item"><a href="{{'/client/'.$client->client_id}}">{{$client->client_id}}</a></li>
                      <li class="breadcrumb-item active" aria-current="page">Loans</li>
                    </ol>
                  </nav>
				<div class="card-header">
					<h3 class="h3">Loan List</h3>
				</div>
				<div class="card-body">
					<table class="table">
                            <thead>
                                <tr>
                                    <td><p class="title">Name</p></td>
                                    <td><p class="title">Loan Amount</p></td>
                                    <td><p class="title">Outstanding Balance</p></td>
                                    <td><p class="title">Principal Balance</p></td>
                                    <td><p class="title">Interest Balance </p></td>
                                    <td><p class="title">Status</p></td>
                                    <td><p class="title">Action</p></td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($client->loanAccounts as $item)
                                    <tr>
                                        <td><a href="{{route('loan.account',[$client->client_id,$item->id])}}">{{$item->product->code}}</a></td>
                                        <td>{{money($item->amount,2)}}</td>
                                        <td>{{money($item->getRawOriginal('total_balance'),2)}}</td>
                                        <td>{{money($item->amountDue()->principal,2)}}</td>
                                        <td>{{money($item->amountDue()->interest,2)}}</td>
                                        <td>
                                            @if($item->status=='Pending Approval')
                                            <span class="badge badge-warning">{{$item->status}}</span>
                                            @elseif($item->status=='Approved')
                                            <span class="badge badge-info">{{$item->status}}</span>
                                            @elseif($item->status=='Active')
                                            <span class="badge badge-success">{{$item->status}}</span>
                                            @elseif($item->status=='Closed')
                                            <span class="badge badge-dark">{{$item->status}}</span>
                                            @else
                                            <span class="badge badge-danger">{{$item->status}}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->status=="Pending Approval")
                                            <a href="{{route('loan.approve',$item->id)}}"><button class="btn btn-light">Approve</button></a>
                                            @elseif($item->status=="Approved")
                                            <a href="{{route('loan.disburse',$item->id)}}"><button class="btn btn-light">Disburse</button></a>
                                            @else
                                              
                                            @endif
                                            <button class="btn btn-light">View</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                    </table>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
@endsection