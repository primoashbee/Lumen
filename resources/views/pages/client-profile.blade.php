@extends('layouts.user')
@section('content')

	<div class="content content pl-32 pr-8 mt-4" id="content-full">
	  <div class="row">
		
		    <div class="col-md-8"> 	
				
		      <div class="card pb-24">
		      	<nav aria-label="breadcrumb">
				  <ol class="breadcrumb">
				    <li class="breadcrumb-item"><a href="/clients">Client List</a></li>
				    <li class="breadcrumb-item active" aria-current="page">Profile</li>
				  </ol>
				</nav>
				@if (count($errors) > 0)
					<div class = "alert alert-danger mx-3">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
						</ul>
					</div>
				@endif
				<div class="row">
					
					<div class="col-lg-4 profile-wrapper pl-8 pr-24">
						<div class="text-center profile-picture">
			            	<img src="{{$client->profile_picture_path == "" ? asset('assets/img/2x2.jpg') : asset('/storage/'.$client->profile_picture_path)}} " class="w-100 img-thumbnail" alt="Profile Photo">
			            </div>
			            <div class="mt-8">
			            	<h5 class="title text-2xl">Personal Details</h5>
			            	<div class="p-details mt-4">
			            		<p class="title text-lg">Birthday</p>
				                <p class="text-light text-lg">{{$client->birthday}}</p>
				            </div>
				            <div class="p-details mt-4">
			            		<p class="title text-lg">Birthplace</p>
				                <p class="text-light text-lg">{{$client->birthplace}}</p>
				            </div>
				            <div class="p-details mt-4">
			            		<p class="title text-lg">Gender</p>
				                <p class="text-light text-lg">{{$client->gender}}</p>
				            </div>
				            <div class="p-details mt-4">
			            		<p class="title text-lg">Civil Status</p>
				                <p class="text-light text-lg">{{$client->civil_status}}</p>
				            </div>
				            <div class="p-details mt-4">
			            		<p class="title text-lg">Educational Attainment</p>
				                <p class="text-light text-lg">{{$client->education}}</p>
				            </div>
				            <div class="p-details mt-4">
			            		<p class="title text-lg">Facebook Account </p>
				                <p class="text-light text-lg">{{$client->fb_account}}</p>
				            </div>
			            </div>
					</div>

					<div class="col-lg-8 profile-wrapper">
						@can('edit_client')
						<a href="/client/{{$client->client_id}}/edit" type="submit" class="btn btn-primary float-right mr-4">Edit Client</a>
						@endcan
						<a href="#status" data-toggle="modal" type="submit" class="btn btn-primary float-right mr-4">Change Status</a>
						<div class="p-details">
							
							<p class="title text-2xl">{{$client->name()}}</p>
							<p class="text-light text-base">Nickname: {{$client->nickname}}</p>
						</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="p-details mt-4 d-inline-block file-input-signature">
				            		<p class="title text-xl mb-2">Signature</p>
					            	<img src="{{$client->signature_path == "" ? asset('assets/img/signature.png') : asset('/storage/'.$client->signature_path)}} " class="w-100 img-thumbnail" alt="Profile Photo">
					            </div>
							</div>
							<div class="col-lg-6">
								<div class="p-details mt-4">
									<p class="title text-xl">{{$client->office->name}}</p>
									<p class="title text-xl">Created at</p>
									<p class="text-light text-lg">{{$client->created_at->format('F, j Y')}} - {{$client->created_at->diffForHumans()}}</p>
								</div>
								<div class="p-details mt-2">
									@if($client->status == 'Active')
									<p class="title text-xl">Status:<span class="badge badge-pill badge-success">{{$client->status}}</span></p>
									@else
									<p class="title text-xl">Status:<span class="badge badge-pill badge-light">{{$client->status}}</span></p>
									@endif
									
								</div>
							</div>
						</div>

						
						


						<div class="profile-menu-tabs mt-8 pr-8">
							<ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="home" aria-selected="true">Business 
                                    Information</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="profile" aria-selected="false">Contact Information</a>
                                </li>
                            </ul>
                            <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
			                    <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="nav-home-tab">
									<div class="accordion" id="businesses">
										@foreach ($client->businesses as $key=>$business)
										<div class="card">
										  <div class="card-header" id="business_{{$key+1}}" class="businesses-wrapper">
											<h2 class="mb-0 business-item">
											  <button class="btn btn-link collapsed w-9 text-left pl-0" type="button" data-toggle="collapse" data-target="#collapse_{{$key+1}}" aria-expanded="false" aria-controls="collapse_{{$key+1}}">
												<span class="title text-xl mr-8 text-white">Business #{{$key + 1}}</span>
											  </button>
											</h2>
										  </div>
										  <div id="collapse_{{$key+1}}" class="collapse" aria-labelledby="business_{{$key+1}}" data-parent="#businesses" style="">
											<div class="card-body">
												<div class="p-details">
													<span class="title text-m mr-8">Business Address:</span>
												   <span class="text-light text-lg">{{$business->business_address}}</span>
												</div>
												<div class="p-details mt-4">
													<span class="title text-m mr-8">Service Type:</span>
												   <span class="text-light text-lg"> {{$business->service_type}}</span> 
												</div>
												<div class="p-details mt-4">
													<span class="title text-m mr-8">Monthly Gross Income</span>
												   <span class="text-light text-lg"> {{money($business->monthly_gross_income,2)}}</span> 
												</div>
												<div class="p-details mt-4">
													<span class="title text-m mr-8">Monthly Operating Expense</span>
												   <span class="text-light text-lg"> {{money($business->monthly_operating_expense,2)}}</span> 
												</div>
												<div class="p-details mt-4">
													<span class="title text-m mr-8">Monthly Net Income</span>
												   <span class="text-light text-lg"> {{money($business->monthly_net_income,2)}}</span> 
												</div>
											  {{-- Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS. --}}
											</div>
										  </div>
										</div>
										@endforeach
									  </div>

			                 		

			                    </div>
			                    <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="nav-profile-tab">
			                      	<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">Phone Number:</span>
										<span class="text-light text-lg">{{$client->contact_number}}</span>
			                 		</div>
			                 		<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">Street Address:</span>
										<span class="text-light text-lg">{{$client->street_address}}</span>
			                 		</div>
			                 		<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">Barangay:</span>
										<span class="text-light text-lg">{{$client->barangay_address}}</span>
			                 		</div>
			                 		<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">City:</span>
										<span class="text-light text-lg">{{$client->city_address}}</span>
			                 		</div>
			                 		<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">Province:</span>
										<span class="text-light text-lg">{{$client->province_address}}</span>
			                 		</div>
			                 		<div class="p-details mt-4">
			                 			<span class="title text-xl mr-8">Zipcode:</span>
										<span class="text-light text-lg">{{$client->zipcode}}</span>
			                 		</div>
			                    </div>
			                 </div>
						</div>
					</div>
				</div>
		        <div class="p-details mt-8 p-4">
		        	<p class="title text-2xl mb-2">Notes</p>
		        	<p>{{$client->notes}}</p>
		        </div>
		      </div>
		    </div>

		    <div class="col-md-4">
		      <div class="card mb-4">
		        <div class="card-header">
		          @can('view_loan_account')
		          <div class="float-left text-center">
		          	<a href="{{route('client.loan.list',$client->client_id)}}"><h4 class="mt-2 text-2xl">Loan Accounts</h4></a>
		          </div>
		          @endcan
				  @if($client->status == 'Active')
					@can('create_loan_account')
					<a href="{{route('client.loan.create',$client->client_id)}}" class="text-base float-right btn-create">Create Account</a>
					@endcan
				  @endif
		        </div>
		        <div class="card-body">
		          <div class="table-accounts table-full-width table-responsive">
		            <table class="table">
		              <tbody>
		              	<tr>
		                  <td>
		                    <p class="text-base">Product</p>
		                  </td>
		                  <td>
		                    <p class="text-base">Amount</p>
		                  </td>
		                  <td>
		                    <p class="text-base">Balance</p>
		                  </td>
		                  <td>
		                    <p class="text-base">Status</p>
		                  </td>
		                </tr>
						@foreach($client->activeLoans() as $item)
						<tr>
		                  <td>
						  	<a href="{{route('loan.account',[$client->client_id,$item->id])}}">
		                      <p class="title text-base">{{$item->product->code}}</p>
		                    </a>
						  </td>
						  <td>
							  <p class="title text-base">{{money($item->getRawOriginal('amount'),2)}}</p>
						  </td>
						  <td>
							  <p class="title text-base">{{money($item->getRawOriginal('total_balance'),2)}}</p>
						  </td>
		                  <td>
							@if($item->status=="In Arrears")
								<span class="badge badge-pill badge-danger">{{$item->status}}</span></h1>
							@elseif($item->status=='Pending Approval')
								<span class="badge badge-pill badge-warning">{{$item->status}}</span></h1>
							@elseif($item->status=='Approved')
								<span class="badge badge-pill badge-primary">{{$item->status}}</span></h1>
							@else
								<span class="badge badge-pill badge-success">{{$item->status}}</span></h1>
							@endif
		                  </td>
						</tr>
						@endforeach
		              </tbody>
		            </table>
		          </div>
		        </div>
		      </div>

		      <div class="card mb-4">
			        <div class="card-header">
			          <div class="float-left text-center">
			          	<h4 class="mt-2 text-2xl">Deposit Accounts</h4>
			          </div>
					  @if($client->status == 'Active')
			          @can('create_deposit_account')
			          <a href="/client/{{$client->client_id}}/create/deposit" class="float-right btn-create text-base">Create Account</a>
			          @endcan
					  @endif
			        </div>
			        <div class="card-body">
			          <div class="table-accounts table-full-width mb-0 table-responsive">
			            <table class="table">
			              <tbody>
			              	<tr>
			                  <td>
			                    <p class="text-base">Deposit Type</p>
			                  </td>
			                  <td>
			                    <p class="text-base">Balance</p>
			                  </td>
			                  <td>
			                    <p class="text-base">Status</p>
			                  </td>
							</tr>
							
							@foreach($client->deposits as $key=>$cbu)
			                <tr>
			                  <td>
							  <a href="{{route('client.deposit',[$cbu->client_id,$cbu->id])}}">
			                      <p class="title text-base">{{$cbu->type->name}}</p>
			                    </a>
			                  </td>
			                  <td>
									{{money($cbu->balance,2)}}
								
			                  </td>
			                  <td>
								@if($cbu->status == 'Active')
								<span class="badge badge-pill badge-success">{{$cbu->status}}</span></h1>
								@else
								<span class="badge badge-pill badge-light">{{$cbu->status}}</span></h1>
								@endif
			                  </td>
							</tr>
							@endforeach
							<tr style="border:none;">
								<td class="text-right pr-2 text-lg">
									Total
								</td>
								<td class="text-lg">
									{{$client->totalDeposits()}}
								</td>
							</tr>
			              </tbody>
			            </table>
			          </div>
			        </div>
		      </div>

		      <div class="card mb-4">
		        <div class="card-header">
		          <div class="float-left text-center">
		          	<h4 class="mt-2 h5">Micro-Insurance</h4>
		          </div>
				  	@if($client->status == 'Active')
						<a href="{{route('client.manage.dependents',$client->client_id)}}" class="float-right btn-create text-xl">Manage</a>
					@endif	
				</div>
				
		        <div class="card-body">
					
					<div class="table-accounts table-full-width table-responsive">
					<table class="table">
						<tr>
							<td> Unit</td>
							<td> App. #</td>
							<td> # of Dpnts </td>
							<td> Expiry</td>
							<td> Status</td>
						</tr>
						<tbody>
							@foreach ($client->dependents as $item)
								<tr>
									<td>{{$item->unit_of_plan}}</td>
									<td>{{$item->application_number}}</td>
									<td>{{$item->count}}</td>
									<td>{{$item->expires_at}}</td>
									<td>{{$item->status}}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					</div>
				</div>
		      </div>
		    	<div id="status" class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
				  <div class="modal-dialog modal-lg">
				    <div class="modal-content">
				    	
				     	<div class="row w-100 mx-0">
							<form method="POST" class="w-100 px-2 py-2" action="/client/change_status/{{$client->client_id}}">
								@csrf
								<h4 class="h4" style="color:#1d253b;">Change Status</h4>
								<label for="status" class="text-xl">Status:</label>
								<select name="status" id="status" class="form-control">
									<option value="">Please Select</option>
									<option value="Closed">Closed</option>
								</select>
								<button class="btn btn-primary mt-4" type="submit">Submit</button>
							</form>
				     	</div>
				    </div>
				  </div>
				</div> 
		    </div>
	  	</div>
	</div>
@endsection