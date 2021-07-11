<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lumen') }}</title>



    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<style type="text/css">
    .main-content{
    width: 50%;
    border-radius: 20px;
    box-shadow: 0 5px 5px rgba(0,0,0,.4);
    margin: 5em auto;
    display: flex;
}
.header-content{
    width: 50%;
    margin: 2em auto;
    display: flex;
}
.header-content .form-head{
    letter-spacing: 30px;
}
.header-content .form-head h2{
    font-size:64px;
}
.gray-scale{
    position: absolute;
    background: white;
    opacity: 0.8;
    z-index: 1000;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
}
#logo{
    z-index: 10001;
}
.company__info{
    background-image: url('{{ asset('assets/img/light-wallpaper.png')}}');
    background-repeat: no-repeat;
    background-size: cover;
    background-position: center center;
    background-color: #d1d1d1;
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    color: #fff;
}
.fa-android{
    font-size:3em;
}
@media screen and (max-width: 640px) {
    .main-content{width: 90%;}
    .company__info{
        display: none;
    }
    .login_form{
        border-top-left-radius:20px;
        border-bottom-left-radius:20px;
    }
}
@media screen and (min-width: 642px) and (max-width:800px){
    .main-content{width: 70%;}
}
.color-default{
    color:#e47e32;
}

.main-content h2{
    letter-spacing: 2px;
}
.app-title{
    letter-spacing: 12px!important;
}
.login_form{
    background-color: #fff;
    border-top-right-radius:20px;
    border-bottom-right-radius:20px;
    border-top:1px solid #ccc;
    border-right:1px solid #ccc;
}
form{
    padding: 0 2em;
}
.form__input{
    width: 100%;
    border:0px solid transparent;
    border-radius: 0;
    border-bottom: 1px solid #aaa;
    padding: 1em .5em .5em;
    padding-left: 2em;
    outline:none;
    margin:1.5em auto;
    transition: all .5s ease;
}
.form__input:focus{
    border-bottom-color: #e47e33;
    box-shadow: 0 0 5px rgb(132 88 9 / 40%);
    border-radius: 4px;
}
.btn{
    transition: all .5s ease;
    width: 70%;
    border-radius: 30px;
    color:#e48138;
    font-weight: 600;
    background-color: #fff;
    border: 1px solid #e48138;
    margin-top: 1.5em;
    margin-bottom: 1em;
}
.btn:hover, .btn:focus{
    background-color: #e48138;
    color: #fff;
}
</style>
<body>
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>

    </div>
</body>
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('script')
</html>
