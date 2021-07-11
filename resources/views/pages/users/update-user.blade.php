@extends('layouts.user')
@section('content')
<div class="content pl-32 pr-8 mt-4" id="content-full">
      <edit-user user="{{$user}}"></edit-user>
</div>
@endsection

@section('scripts')
    <script async defer>
        window.addEventListener('DOMContentLoaded', function() {
                $(function(){
		            $('#birthday').datepicker({
		                format: 'mm/dd/yyyy',
		                todayBtn: true
		            });
		        }) 
                
        },(jQuery))
</script>
@endsection