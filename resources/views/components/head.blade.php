<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>{{ ENV('APP_NAME') }}</title>
	<link rel="icon" href="{{asset('favico.png')}}">

	{{-- <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}"> --}}
	<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/lumen.css') }}">
	<script type="text/javascript" src="{{ asset('js/app.js') }}" defer></script>
	<script type="text/javascript" src="{{ asset('js/lumen.js') }}" defer></script>

</head>