@extends('layouts.user')
@section('content')
	<div class="content content pl-32 pr-2 mt-4" id="content-full">
		<div class="row setting-wrapper">
			
			<div class="col-lg-9 pr-0 setting-container">
				<div class="row mt-4">
					<div class="filters-container col-md-6">
						<h4 class="h4">Filter:</h4>
						<div class="btn-filter-group d-inline-block">
							<button data-filter="*" class="btn-filters">Show All</button>
							<button data-filter=".deposit" class="btn-filters">Deposit</button>
							<button data-filter=".loans" class="btn-filters">Loans</button>
							<button data-filter=".client" class="btn-filters">Clients</button>
							<button data-filter=".reports" class="btn-filters">Reports</button>
							<button data-filter=".summary" class="btn-filters">Summary</button>
							<button data-filter=".detailed" class="btn-filters">Detailed</button>
						</div>
					</div>

					<div class="col-md-6">
						<h4 class="h4">Search:</h4>
						<input type="text" id="search_menu" class="form-control">
					</div>

				</div>
				
				<ul class="settings mt-6" id="setting-tabs" role="tablist">
				
					<li class="settings-item client reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','client'])}}">
							<i class="fas fa-3x fa-users"></i>
							<p class="title text-center mt-2">Clients - Detailed</p>
						</a>
						
					</li>
					<li class="settings-item loans reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','dst'])}}">
							
							<i class="fas fa-3x fa-file-contract"></i>
							<p class="title text-center mt-2">Disclosure Statements </p>
						</a>
						
					</li>
					<li class="settings-item loans reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','disbursements'])}}">
							<i class="fas fa-3x fa-sign-out-alt"></i>
							<p class="title text-center mt-2">Disbursements - Detailed</p>
						</a>
					</li>
					<li class="settings-item loans reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','loan-in-arrears'])}}">
							<i class="fas fa-3x fa-sign-out-alt"></i>
							<p class="title text-center tx-lg mt-2" style="font-size:12px;">Loan In Arrears - Principal</p>
						</a>
						
					</li>
					<li class="settings-item loans reports summary">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['summary','disbursements'])}}">
							
							<i class="fas fa-3x fa-sign-out-alt"></i>
							
							<p class="title text-center mt-2">Disbursements - Summary </p>
						</a>
						
					</li>

					<li class="settings-item loans reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','repayments'])}}">
							
							<i class="fas fa-3x fa-money-bill-alt"></i>
							<p class="title text-center mt-2">Repayments - Detailed</p>
						</a>
					</li>
					<li class="settings-item loans reports summary">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['summary','repayments'])}}">
							<i class="fas fa-3x fa-money-bill-alt"></i>
							<p class="title text-center mt-2">Repayments - Summary</p>
						</a>
					</li>
					<li class="settings-item deposit reports summary">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['summary','deposit'])}}">
							
							<i class="fas fa-3x fa-piggy-bank"></i>
							<p class="title text-center mt-2">Deposit - Summary</p>
						</a>
					</li>
					<li class="settings-item deposit reports detailed">
						<a class="nav-link active" id="home-tab" href="{{route('reports.view',['detailed','deposit'])}}">
							
							<i class="fas fa-3x fa-piggy-bank"></i>
							<p class="title text-center mt-2">Deposit - Detailed</p>
						</a>
					</li>
					
			</div>
		</div>

	</div>
@endsection