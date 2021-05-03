@extends('layouts.user')

@section('content')

<loan-profile client_id="{{$client->client_id}}" loan_account_id="{{$account->id}}"></loan-profile>
</div>

@endsection

@section('scripts')
@include('components.swal-script')
<script defer>
    @if(session()->has('alert'))
        function load(){    
            let title = '{{session()->get('alert')['title']}}'
            let message = '{{session()->get('alert')['message']}}'
            let icon = '{{session()->get('alert')['icon']}}'
            Swal.fire(
                title,
                message,
                icon
            )
        }
        document.onload = load()
    @endif

</script>
@endsection