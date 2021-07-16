<template>
	<div class="card ">
		<div class="card-header px-0 pt-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                <li class="breadcrumb-item"><a href="/users">Roles List</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
                </ol>
            </nav>
            <h3 class="h3 ml-3">Change Role</h3>
		</div>

		<div class="card-body px-4">
			<form @submit.prevent="submit">
				<div class="form-group">
					<label for="role">Role value:</label>
					<input type="text" v-model="fields.role_name" :class="hasError('role_name') ? 'is-invalid' : ''" class="form-control">
					<div class="invalid-feedback" v-if="hasError('role_name')">
                        {{ errors.role_name[0] }}
                    </div>
				</div>

				<div class="row">
					<div class="col-lg-6">
		                <div class="form-group">
		                	<h3 class="h3">Clients</h3>
		                	<div class="permission-wrap mt-6">

		                		<div class="p0 form-check">
				                	<label class="form-check-label" for="view_client">
			                            <input class="form-check-input cb-type" v-model="fields.permission" value="view_client" id="view_client" type="checkbox">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="view_client">View Client</label>
			                        </label>
			                    </div>

			                	<div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="create_client">
			                            <input class="form-check-input cb-type" v-model="fields.permission" id="create_client" type="checkbox" value="create_client">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="create_client">Create Client</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="edit_client">
			                            <input class="form-check-input cb-type" v-model="fields.permission" id="edit_client" type="checkbox"  value="edit_client">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="edit_client">Edit Client</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="change_status">
			                            <input class="form-check-input cb-type" v-model="fields.permission" id="change_status_client" type="checkbox" value="change_status_client">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="change_status_client">Change Status</label>
			                        </label>
			                    </div>
		                    </div>
		                </div>
	                </div>
	                <div class="col-lg-6">
		                <div class="form-group">
		                	<h3 class="h3">Users</h3>
		                	<div class="permission-wrap mt-6">

		                		<div class="p0 form-check">
				                	<label class="form-check-label" for="view_user">
			                            <input class="form-check-input cb-type" id="view_user" type="checkbox" value="view_user" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="view_user">View User</label>
			                        </label>
			                    </div>

			                	<div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="create_user">
			                            <input class="form-check-input cb-type" id="create_user" type="checkbox" value="create_user" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="create_user">Create User</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="edit_user">
			                            <input class="form-check-input cb-type" id="edit_user" type="checkbox" value="edit_user" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="edit_user">Edit User</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="change_status_user">
			                            <input class="form-check-input cb-type" id="change_status_user" type="checkbox" value="change_status_user" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="change_status_user">Change Status</label>
			                        </label>
			                    </div>
		                    </div>
		                </div>
	                </div>
                </div>

                <div class="row">
					<div class="col-lg-6">
		                <div class="form-group">
		                	<h3 class="h3">Deposit Account</h3>
		                	<div class="permission-wrap mt-6">

		                		<div class="p0 form-check">
				                	<label class="form-check-label" for="view_deposit_account">
			                            <input class="form-check-input cb-type" id="view_deposit_account" type="checkbox" value="view_deposit_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="view_deposit_account">View Deposit Account</label>
			                        </label>
			                    </div>

			                	<div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="create_deposit_account">
			                            <input class="form-check-input cb-type" id="create_deposit_account" type="checkbox" value="create_deposit_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="create_deposit_account">Create Deposit Account</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="edit_deposit_account">
			                            <input class="form-check-input cb-type" id="edit_deposit_account" type="checkbox" value="edit_deposit_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="edit_deposit_account">Edit Deposit Account</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="change_status_deposit_account">
			                            <input class="form-check-input cb-type" id="change_status_deposit_account" type="checkbox" value="change_status_deposit_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="change_status_deposit_account">Change Status</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="interest_posting">
			                            <input class="form-check-input cb-type" id="interest_posting" type="checkbox" value="interest_posting" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="interest_posting">Interest Posting</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="enter_deposit">
			                            <input class="form-check-input cb-type" id="enter_deposit" type="checkbox" value="enter_deposit" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="enter_deposit">Enter Deposit</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="enter_withdrawal">
			                            <input class="form-check-input cb-type" id="enter_withdrawal" type="checkbox" value="enter_withdrawal" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="enter_withdrawal">Enter Withdrawal</label>
			                        </label>
			                    </div>
		                    </div>
		                </div>
	                </div>
	                <div class="col-lg-6">
		                <div class="form-group">
		                	<h3 class="h3">Loan Account</h3>
		                	<div class="permission-wrap mt-6">

		                		<div class="p0 form-check">
				                	<label class="form-check-label" for="view_loan">
			                            <input class="form-check-input cb-type" id="view_loan_account" type="checkbox" value="view_loan_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="view_loan_account">View Loan</label>
			                        </label>
			                    </div>

			                	<div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="create_loan">
			                            <input class="form-check-input cb-type" id="create_loan_account" type="checkbox" value="create_loan_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="create_loan_account">Create Loan</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="edit_loan">
			                            <input class="form-check-input cb-type" id="edit_loan_account" type="checkbox" value="edit_loan_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="edit_loan_account">Edit Loan</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="change_status_loan_account">
			                            <input class="form-check-input cb-type" id="change_status_loan_account" type="checkbox" value="change_status_loan_account" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="change_status_loan_account">Change Status Loan</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="approve_loan">
			                            <input class="form-check-input cb-type" id="approve_loan" type="checkbox" value="approve_loan" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="approve_loan">Approve Loan</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="disburse_loan">
			                            <input class="form-check-input cb-type" id="disburse_loan" type="checkbox" value="disburse_loan" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="disburse_loan">Disburse Loan</label>
			                        </label>
			                    </div>

			                    <div class="p0 mt-2 form-check">
				                	<label class="form-check-label" for="enter_repayment">
			                            <input class="form-check-input cb-type" id="enter_repayment" type="checkbox" value="enter_repayment" v-model="fields.permission">
			                            <span class="form-check-sign">
			                            <span class="check"></span>
			                            </span>
			                            <label for="enter_repayment">Enter Repayment</label>
			                        </label>
			                    </div>
		                    </div>
		                </div>
	                </div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
		</div>
	</div>
</template>

<script>
import Swal from 'sweetalert2';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
	export default{
		props:['role_info','permissions_list'],
		data(){
			return{
				role:"",
				fields:{
					permission:[],
					role_name:""
				},
				errors:{}
			}	
		},
		mounted(){
			this.role = JSON.parse(this.role_info);
			this.fields.role_name = this.role.name
			this.fields.permission = JSON.parse(this.permissions_list);
		},
		methods:{
			submit(){
				
				axios.post(this.roleLink(), this.fields)
				.then(res=>{
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK'
                    })
                    .then(res=>{
                        location.reload();
                    })
                })
                .catch(error=>{
                    this.isLoading = false
                    this.errors = error.response.data.errors || {}
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">OOPPPSSSSS!</span>',
                        text: error.response.data.message
                    })
                })
			},
			roleLink(){
				return '/settings/edit/role/'+this.role.id
			},
			hasError(value){
				return this.errors.hasOwnProperty(value)
			}
		}
	}
</script>