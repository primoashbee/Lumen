@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
	<form class="row">
		<div class="col-lg-12">
			<div class="card">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb">
					<li class="breadcrumb-item"><a href="/settings">Settings</a></li>
					<li class="breadcrumb-item active" aria-current="page">User List</li>
					</ol>
				</nav>
				<div class="card-header">
					<h3 class="h3 w-5 d-inline-block">Users List</h3>
					<a href="/settings/create/user" class="btn btn-primary d-inline-block float-right">Create User</a>
				</div>
				<div class="card-body">
					<users-list></users-list>
				</div>
			</div>
		</div>
	</form>
</div>
@endsection