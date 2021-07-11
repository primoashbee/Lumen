@extends('layouts.user')
@section('content')
<div class="content content pl-32 pr-8 mt-4" id="content-full">
	<div class="group-wrapper">
		<div class="card pb-4">
			<h4 class="h4 ml-3 mt-4">Create Cluster</h4>
			<form>
				<div class="form-group col-md-6 mt-4">
		  			<label>Assign To:</label>
		  			<v2-select @officeSelected="assignOffice" v-bind:class="officeHasError ? 'is-invalid' : ''"></v2-select>
		  		</div>
		  	
			  	<div class="form-group col-md-6">
			  		<label for="cluster_code">Cluster Code</label>
			  		<input type="text" name="cluster_code" class="form-control">
			  	</div>

			  	<div class="form-group col-md-6">
			  		<label>Notes</label>
			  		<textarea class="form-control"></textarea>
			  	</div>
			  	<button type="submit" class="ml-3 btn btn-primary">Submit</button>
		  	</form>
		  	
	  	</div> 
	</div>
</div>

@endsection