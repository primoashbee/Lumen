@extends('layouts.user')
@section('content')
<div class="content content pl-32 pr-8 mt-4" id="content-full">
    
    <client-dependents-list client_id="{{$client->client_id}}" full_name="{{$client->full_name}}"></client-dependents-list>
</div>
@endsection

@section('scripts')
    {{-- <script
  src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
  integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs="
  crossorigin="anonymous"></script>

<script>
    $(function(){
        $('.view-row').click(function(){
            var data = JSON.parse($(this).attr('data'));
            var application_number = $(this).attr('application_number');
            console.log(data);
            // $.each(data,(k,v)=>{

            // });
            
            $('#dependents_modal').modal('show')
            $('#tbody>tr').remove()
            var markup;
            $.each(data,function(k,v){
                markup  += '<tr><td>'+v.name+'</td><td>'+v.age+'</td><td>'+v.relationship+'</td></tr>'
                
            })
            $('#tbody').append(markup)
            $('#modal_title').html('Application Number: '+  application_number)
        })
    })
</script> --}}
@endsection
