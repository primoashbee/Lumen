<template>
	<div class="group-wrapper">
			<div class="card pb-4">
				<nav aria-label="breadcrumb">
		          <ol class="breadcrumb">
		            <li class="breadcrumb-item"><a :href="toOffice()">Office List</a></li>
		            <li class="breadcrumb-item active" aria-current="page">Create Office</li>
		          </ol>
		        </nav>
				<h4 class="h4 ml-3 mt-4">Create a New {{title}}</h4>
				<form @submit.prevent="submit">
					<div class="form-group col-md-6 mt-4">
			  			<label>Assign To:</label>
			  			<v2-select @officeSelected="assignOffice" :list_level="list_level" v-bind:class="officeHasError ? 'is-invalid' : ''"></v2-select>
			  			<div class="invalid-feedback" v-if="officeHasError">
	                        {{ errors.office_id[0]}}
	                    </div>
			  		</div>

			  		<div class="form-group col-md-6">
			  			<label for="code">Code</label>
						<div class="input-group mb-3">
						  <div class="input-group-prepend mr-0">
						    <span class="input-group-text" v-text="fields.branch_code" id="basic-addon3"></span>
						  </div>
						  <input type="text" class="form-control" id="code" aria-describedby="basic-addon3"
						  v-model="fields.code" v-bind:class="codeHasError ? 'is-invalid' : ''">
						  <div class="invalid-feedback" v-if="codeHasError">
		                        {{ errors.code[0]}}
		                    </div>
						</div>
					
					</div>
					<div class="form-group col-md-6">
						<input type="checkbox" v-model="fields.same_as_code" id="checkbox" @change="same_as_code(levelCode,$event)">

						<label for="checkbox"><span class="pr-5 mt-4" style="color:rgb(169, 169, 178)" > Name is same with Code </span></label>
					</div>

				  	<div class="form-group col-md-6">
				  		<label for="cluster_code">Name:</label>
				  		<input type="text" v-model="fields.name" id="name" class="form-control" v-bind:class="nameHasError ? 'is-invalid' : ''">
				  		<div class="invalid-feedback" v-if="nameHasError">
                            {{ errors.name[0]}}
                        </div>
				  	</div>

					<div class="form-group col-md-6">
				  		<label>Notes</label>
				  		<textarea class="form-control"></textarea>
				  	</div>
				  	<button type="submit" class="ml-3 btn btn-primary">Submit</button>
				  	
			  	</form>
			  	
		  	</div> 
	</div>
</template>
<script type="text/javascript">
	import SelectComponentV2 from './SelectComponentV2';
	import Swal from 'sweetalert2';
	export default{
		components: {
        	SelectComponentV2
   	 	},
   	 	props:['list_level','level'],
   	 	data(){
   	 		return{
   	 			fields:{
   	 				'office_id':"",
   	 				'code':"",
					"branch_code":"######",
					"level_code":null,
   	 				'name':"",
   	 				"level":"",
					"readonly":true,
					"same_as_code":false,
					"level_in_number":""
				},
				withHyphen: ['unit','account_officer','cluster'],	
   	 			errors:{}
   	 		}
   	 	},
   	 	created(){
   	 		if (this.level == 'region') {
                this.fields.level_in_number = 2
            }else if(this.level == 'area'){
                this.fields.level_in_number = 3
            }else if(this.level == 'branch'){
                this.fields.level_in_number = 4
            }else if( this.level == 'unit'){
                this.fields.level_in_number = 5
            }else if( this.level == 'cluster'){
                this.fields.level_in_number = 6
            }
   	 		this.fields.level = this.level
   	 	},
   	 	computed:{
   	 		hasErrors(){
	            return Object.keys(this.errors).length > 0;
	        },
   	 		officeHasError(){
	            return this.errors.hasOwnProperty('office_id')
	        },
	        nameHasError(){
	        	return this.errors.hasOwnProperty('name')
	        },
	        codeHasError(){
	        	return this.errors.hasOwnProperty('code')
			},
			levelCode(){
				if(this.withHyphen.includes(this.level)){
					return this.fields.branch_code + '-'+this.fields.code
				}
				return this.fields.branch_code + this.fields.code
			},
			title(){
				var str = this.level
				str = str.replace('_',' ')
				str = str.toLowerCase().split(' ');
				for (var i = 0; i < str.length; i++) {
					str[i] = str[i].charAt(0).toUpperCase() + str[i].slice(1); 
				}
				return str.join(' ');

			}

			
   	 	},
   	 	methods:{
   	 		toOffice(){
   	 			return "/settings/office/"+this.level
   	 		},
			same_as_code(value,value2){
				if (this.level == 'unit') {
					return this.fields.name = this.levelCode
				}
				return this.fields.name = value + value2
			},

   	 		assignOffice(value){
				// console.log(value);
   	 			// if (this.level == "cluster" || this.level == "unit" || this.level == "account_officer") { 
   	 			// 	this.fields.branch_code = value['code']
				// 	}
				this.fields.branch_code = value.code
					
	            this.fields.office_id = value['id']
			},
			same_as_code(value,e){
				if(e.target.checked){
					return this.fields.name = value
				}
				return this.fields.name = ''
			},

			formatCode(){
				if(this.withHyphen.includes(this.level)){
					return this.fields.level_code = this.fields.branch_code+ "-"+this.fields.code
				}
				return this.fields.level_code = this.fields.branch_code+this.fields.code
			},
	        submit(){
				this.formatCode()
				 if(this.fields.same_as_code){
					 this.fields.name = this.levelCode 
				 }
				 
	        	 axios.post('/create/office', this.fields)
                .then(res=>{
                    this.isLoading = false
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK'
                    })
                    .then(res=>{
                        location.reload();
                    })	
                })
                .catch(error=>{
                    this.errors = error.response.data.errors || {}
                })
	        }
		},
		watch:{
			// "fields.code":function(val){
			// 	if(this.fields.same_as_code){
			// 		this.fields.name = this.levelCode
			// 	}
			// },
			// "fields.same_as_code":function(val){
			// 	if(val){
			// 		this.fields.name = this.levelCode
			// 	}else{
			// 		this.fields.name=""	
			// 	}
			// }
		}	
	}
</script>