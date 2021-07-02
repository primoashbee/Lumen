<template>
	<div class="col-md-12">
        <div class="card">
            <div class="card-header px-0 py-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                    <li class="breadcrumb-item"><a href="/users">User List</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add User</li>
                    </ol>
                </nav>
                <h3 class="h3 ml-3">Create New User</h3>
            </div>
            <div class="card-body">
                <form class="row" id="user_form" @submit.prevent="submit">
                    
                    <div class="col-lg-8">
                        <div class="col-lg-12 px-0 w-10">
                            <div class="form-group w-3 d-inline-block">
                                <label for="email">Email</label>
                                <input type="email" v-model="fields.email" class="form-control" :class="hasError('email')  ? 'is-invalid' : ''">
                                <div class="invalid-feedback" v-if="hasError('email')">
		                            {{ errors.email[0] }}
		                        </div>
                            </div>
                            <div class="form-group ml-10 d-inline-block align-top mt-8">
                                <div class="p0 form-check">
                                    <label class="form-check-label" for="is_active">
                                        <input class="form-check-input cb-type" type="checkbox" id="is_active" v-model="fields.is_active">
                                        <span class="form-check-sign">
                                        <span class="check"></span>
                                        </span>
                                        <label for="is_active">Active</label>
                                    </label>
                                </div>
                            </div>  
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-4">
                                <label for="">First Name</label>
                                <input type="text" v-model="fields.firstname" class="form-control" :class="hasError('firstname')  ? 'is-invalid' : ''">
                                <div class="invalid-feedback" v-if="hasError('firstname')">
		                            {{ errors.firstname[0] }}
		                        </div>
                            </div>
                            <div class="form-group col-lg-4">
                                <label for="">Middle Name</label>
                                <input type="text" v-model="fields.middlename" class="form-control">
                            </div>
                            <div class="form-group col-lg-4">
                                <label for="">Last Name</label>
                                <input type="text" v-model="fields.lastname" class="form-control" :class="hasError('lastname')  ? 'is-invalid' : ''">
                                <div class="invalid-feedback" v-if="hasError('lastname')">
		                            {{ errors.lastname[0] }}
		                        </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-lg-6">
                                <label for="gender" >Gender:</label>
                                <select input id="gender" type="text" class="form-control" v-model="fields.gender" :class="hasError('gender')  ? 'is-invalid' : ''">
                                    <option value="">Please Select</option>
                                    <option value="MALE">MALE</option>
                                    <option value="FEMALE">FEMALE</option>
                                </select>
                                <div class="invalid-feedback" v-if="hasError('gender')">
		                            {{ errors.gender[0] }}
		                        </div>
                            </div>
                            <div class="form-group col-lg-6">
                                <label for="birthday">Birthday</label>
                                <input id="birthday" type="date" class="form-control" v-model=
                                "fields.birthday" :class="hasError('birthday')  ? 'is-invalid' : ''" >
                                <div class="invalid-feedback" v-if="hasError('birthday')">
		                            {{ errors.birthday[0] }}
		                        </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <role-filter @roleSelected="assignRole"></role-filter>
                            <org-structure @officeSelected="assignOffice" :class="hasError('office_ids')  ? 'is-invalid' : ''"></org-structure> 
                            <div class="invalid-feedback" v-if="hasError('office_ids')">
		                            {{ errors.office_ids[0] }}
		                        </div>
                        </div>
                        <div class="col-lg-12 px-0">
                            <div class="form-group">
                                <label for="Notes">Notes</label>
                                <textarea id="" cols="30" v-model="fields.notes" rows="10" class="form-control"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            Submit
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</template>

<script>

import Swal from 'sweetalert2';
import 'vue-loading-overlay/dist/vue-loading.css';
	export default{
		data(){
			return {
				fields:{
					birthday:"",
					notes:"",
					firstname:"",
					lastname:"",
					middlename:"",
					office_ids:[],
					role_ids:[],
					gender:"",
					email:"",
					is_active:true
				},
				errors:{}
			}	
		},
		
		methods:{
			submit(){
				axios.post('/create/user', this.fields)
				.then(res => {
						Swal.fire({
	                        icon: 'success',
	                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Success!</span>',
	                        text: res.data.msg,
	                        confirmButtonText: 'OK',
	                        allowEnterKey: true // default value
	                        })
	                        .then(res=>{
	                            location.reload()
	                    })
				})
				.catch(error=>{
                	this.errors = error.response.data.errors 
            	})
			},
			assignRole(value){
				this.fields.role_ids= value
			},
			assignOffice(value){
				this.fields.office_ids= value
			},
			hasError(value){
	            return this.errors.hasOwnProperty(value)
	        }
		}
	}
</script>