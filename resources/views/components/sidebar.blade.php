<div class="sidebar" id="sidebar">
	<div class="sidebar-wrapper px-3">
		<div class="logo py-2">
			
			<a href="{{route('dashboard')}}">
				<i class="fas fa-2x fa-lightbulb py-2"></i>
				<p class="text-lg">Lumen</p>
			</a>
			
		</div>

		<div class="sidebar-nav mt-8">
			<ul class="main-nav">
				@can('view_dashboard')
				<li class="{{ request()->is('dashboard') ? 'active' : '' }} py-2">
					<a href="/dashboard">
						<i class="fas fa-2x fa-chart-pie"></i>
						<p>Dashboard</p>
					</a>
				</li>
				@endcan
				<li class="{{ request()->is('create/c*') ? 'active' : '' }} py-2">
					@if(auth()->user()->can('create_client') || auth()->user()->can('create_cluster'))
					<a data-toggle="collapse" href="#create" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
						<i class="fas fa-2x fa-plus-square"></i>
						<p>Create <b class="caret"></b></p>
					</a>
					@endif
					<div class="collapse" id="create">
						  <ul class="sub-collapse">
						  	@can('create_client')
							<li class="sub-list">
								<a class="sub-nav" href="{{ route('precreate.client')}}">
									<i class="">C</i>
									<p>Client</p>
								</a>
							</li>
							@endcan
							@can('create_cluster')
							<li class="sub-list">
								<a class="sub-nav" href="/create/office/cluster">
									<i class="">CL</i>
									<p>Cluster</p>
								</a>
							</li>
							@endcan
						</ul>
					 </div>
				</li>
				@can('view_client')
				<li class="{{ request()->is('clients') ? 'active' : '' }} py-2">
					<a href="{{ route('client.list') }}">
						<i class="fas fa-2x fa-user"></i>
						<p>Client</p>
					</a>
				</li>
				@endcan
				@can('view_cluster')
				<li class="py-2 {{ request()->is('cluster') ? 'active' : '' }}">
					<a href="/clusters">
						<i class="fas fa-2x fa-user-friends"></i>
						<p>Clusters</p>
					</a>
				</li>
				@endcan	
				@canany(['enter_withdrawal',
				'interest_posting',
				'enter_deposit',
				'enter_withdrawal',
				'interest_posting',
				'enter_deposit',
				'disburse_loan'])
				<li class="py-2 {{ request()->is('bulk/*') ? 'active' : '' }}">
					<a data-toggle="collapse" href="#bulk" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
						<i class="fas fa-2x fa-layer-group"></i>
						<p>Bulk<b class="caret"></b></p>
					</a>
					<div class="collapse" id="bulk">
						  <ul class="sub-collapse">
							<li class="sub-list">
								@canany(['enter_withdrawal','interest_posting','enter_deposit'])
									<a class="sub-nav" data-toggle="collapse" href="#bulk-deposit" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
										<i class="fas fa-piggy-bank"></i>
										<p>Deposit<b class="caret"></b></p>
									</a>
								@endcanany
								<div class="collapse" id="bulk-deposit">
									<ul class="sub-collapse">
										@can('enter_withdrawal')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.deposit.withdraw')}}">
												<i class="">W</i>
												<p>Withdrawal</p>
											</a>
										</li>
										@endcan
										@can('interest_posting')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.deposit.post_interest')}}">
												<i class="">IP</i>
												<p>Interest Posting</p>
											</a>
										</li>
										@endcan
										@can('enter_deposit')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.deposit.deposit')}}">
												<i class="">D</i>
												<p>Deposit</p>
											</a>
										</li>
										@endcan
									</ul>
								</div>
							</li>
							<li class="sub-list">
								<a class="sub-nav" data-toggle="collapse" href="#bulk-loans" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
									<i class="fas fa-money-check"></i>
									<p>Loans<b class="caret"></b></p>
								</a>
								<div class="collapse" id="bulk-loans">
									<ul class="sub-collapse">
										@can('create_loan_account')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.create.loans')}}">
												<i>CL</i>
												<p>Create Loans</p>
											</a>
										</li>
										@endcan
										@can('approve_loan')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.approve.loans')}}">
												<i>AL</i>
												<p> Approve Loans</p>
											</a>
										</li>
										@endcan
										@can('disburse_loan')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.disburse.loans')}}">
												<i>DL</i>
												<p>Disburse Loans</p>
											</a>
										</li>
										@endcan
										@can('writeoff_loan_account')
										<li class="second-sub-list">
											<a class="second-sub-nav" href="{{route('bulk.writeoff.loans')}}">
												<i>WO</i>
												<p>Write Off Loans</p>
											</a>
										</li>
										@endcan
									</ul>
								</div>
							</li>
							@can('enter_repayment')
							<li class="sub-list">
								<a class="sub-nav" href="{{route('bulk.repayment')}}" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
									<i class="fas fa-money-check"></i>
									<p>Repayments</p>
								</a>
							</li>
							@endcan
							@can('enter_repayment')
							<li class="sub-list">
								<a class="sub-nav" href="{{route('bulk.repayment')}}" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
									<i class="fas fa-id-card"></i>
									<p>ID CARD</p>
								</a>
							</li>
							@endcan
						</ul>
					 </div>
				</li>
				@endcanany
				@canany(['view_loan_account','view_deposit_account','view_loan_account','view_deposit_account'])
				<li class="{{ request()->is('accounts/*') ? 'active' : '' }} py-2">
					<a data-toggle="collapse" href="#accounts" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
						<i class="fas fa-2x fa-cubes"></i>
						<p>Accounts <b class="caret"></b></p>
					</a>
					<div class="collapse" id="accounts">
						  <ul class="sub-collapse">
						  	@if(auth()->user()->can('view_loan_account') && auth()->user()->can('view_deposit_account'))
							<li class="sub-list">
								<a class="sub-nav" href="{{ route('accounts.list','all')}}">
									<i class="fas fa-object-group"></i>
									<p>All Accounts</p>
								</a>
							</li>
							@endif
							@can('view_loan_account')
							<li class="sub-list">
								<a class="sub-nav" href="{{ route('accounts.list','loan')}}">
									<i class="fas fa-money-check"></i>
									<p>Loan Accounts</p>
								</a>
							</li>
							@endcan
							@can('view_deposit_account')
							<li class="sub-list">
								<a class="sub-nav" href="{{ route('accounts.list','deposit') }}">
									<i class="fas fa-piggy-bank"></i>
									<p>CBU</p>
								</a>
							</li>
							@endcan
						</ul>
					 </div>
				</li>
				@endcanany
				<li class="py-2 {{ request()->is('accounting') ? 'active' : '' }}">
					<a href="">
						<i class="far fa-2x fa-money-bill-alt"></i>
						<p>Accounting</p>
					</a>
				</li>
				@can('view_reports')
				<li class="py-2 {{ request()->is('report') ? 'active' : '' }}">
					<a href="{{route('reports.index')}}">
						<i class="far fa-2x fa-list-alt"></i>
						<p>Reports</p>
					</a>
				</li>
				@endcan
				@role('Super Admin')
				<li class="py-2">
					<a href="{{ route('administration')}}" class ="{{ request()->is('administration') ? 'active' : '' }}">
						<i class="fas fa-2x fa-cogs"></i>
						<p>Administration</p>
					</a>
				</li>
				@endrole
			</ul>
		</div>
	</div>
</div>


