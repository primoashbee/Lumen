<div class="sidebar" id="sidebar">
	<div class="sidebar-wrapper px-3">
		<div class="logo py-2">
			
			<a href="">
				<i class="fas fa-2x fa-lightbulb py-2"></i>
				<p class="text-lg">Lumen</p>
			</a>
			
		</div>

		<div class="sidebar-nav mt-8">
			<ul class="main-nav">
				<li class="{{ request()->is('dashboard') ? 'active' : '' }} py-2">
					<a href="/dashboard">
						<i class="fas fa-2x fa-chart-pie"></i>
						<p>Dashboard</p>
					</a>
				</li>
				<li class="{{ request()->is('create/c*') ? 'active' : '' }} py-2">
					<a data-toggle="collapse" href="#create" role="button" aria-expanded="false" aria-controls="create" class="has-sub">
						<i class="fas fa-2x fa-plus-square"></i>
						<p>Create <b class="caret"></b></p>
					</a>
					<div class="collapse" id="create">
						  <ul class="sub-collapse">
							<li class="sub-list">
								<a class="sub-nav" href="{{ route('precreate.client')}}">
									<i class="">C</i>
									<p>Client</p>
								</a>
							</li>
							<li class="sub-list">
								<a class="sub-nav" href="">
									<i class="">CL</i>
									<p>Cluster</p>
								</a>
							</li>
						</ul>
					 </div>
				</li>
				<li class="{{ request()->is('client') ? 'active' : '' }} py-2">
					<a href="{{ route('client.list') }}">
						<i class="fas fa-2x fa-user"></i>
						<p>Client</p>
					</a>
				</li>
				<li class="py-2">
					<a href="{{ request()->is('cluster') ? 'active' : '' }}">
						<i class="fas fa-2x fa-user-friends"></i>
						<p>Clusters</p>
					</a>
				</li>
				<li class="py-2">
					<a href="{{ request()->is('accounting') ? 'active' : '' }}">
						<i class="far fa-2x fa-money-bill-alt"></i>
						<p>Accounting</p>
					</a>
				</li>
				<li class="py-2">
					<a href="{{ request()->is('report') ? 'active' : '' }}">
						<i class="far fa-2x fa-list-alt"></i>
						<p>Reports</p>
					</a>
				</li>
				<li class="py-2">
					<a href="{{ route('administration')}}" class ="{{ request()->is('administration') ? 'active' : '' }}">
						<i class="fas fa-2x fa-cogs"></i>
						<p>Administration</p>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>