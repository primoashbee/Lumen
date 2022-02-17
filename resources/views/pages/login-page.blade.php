@extends('layouts.app')
@section('content')

<body>
	<!-- Main Content -->
	<div class="container-fluid">
			
		<div class="row main-content text-center">
			<div class="col-md-4 text-center company__info">
				<div class="gray-scale"></div>
				<span class="company__logo"><h2><span class="fa fa-android"></span></h2></span>
				<img id="logo" src="{{ asset('assets/img/logo.png')}}">
			</div>
			
			<div class="col-md-8 col-xs-12 col-sm-12 login_form ">
				<div class="container-fluid">
					<div class="row header-content text-center">
						<div class="form-head w-100 text-center">
							<h2 class="app-title">LUMEN</h2>
						</div>
					</div>
					<div class="row">
						<h2 class="text-center color-default w-100">Sign in to continue</h2>
					</div>
					<div class="row">
						<form control="" class="form-group w-100">
							<div class="row">
								<input type="text" name="username" id="username" class="form__input" placeholder="Username">
							</div>
							<div class="row">
								<!-- <span class="fa fa-lock"></span> -->
								<input type="password" name="password" id="password" class="form__input" placeholder="Password">
							</div>
							<div class="row justify-content-center">
								<input type="submit" value="Submit" class="btn">
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection