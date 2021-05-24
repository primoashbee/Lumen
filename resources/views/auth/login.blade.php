@extends('layouts.app')

@section('content')

    <div class="container-fluid">
        <form method="POST" action="{{ route('login') }}">
            @csrf()
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
                        <div class="row w-100 mx-0">
                            <h2 class="text-center color-default w-100">Sign in to continue</h2>
                        </div>
                        <div class="row w-100 mx-0 pb-3">
                            <form control="" class="form-group w-100">
                                <div class="row w-100 mx-0">
                                    <input type="text" name="email" id="Email" class="form__input @error('email') is-invalid @enderror" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email">
                                    @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class="row w-100 mx-0">
                                    <!-- <span class="fa fa-lock"></span> -->
                                    <input type="password" name="password" id="password" class="form__input @error('password') is-invalid @enderror" placeholder="Password"
                                    required autocomplete="current-password">

                                    @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                                <div class=" w-100 mx-0 row justify-content-center">
                                    <input type="submit" value="Submit" class="btn">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </form> 
    </div>



<!-- <div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        {{ __('Remember Me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link" href="{{ route('password.request') }}">
                                        {{ __('Forgot Your Password?') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> -->
@endsection
