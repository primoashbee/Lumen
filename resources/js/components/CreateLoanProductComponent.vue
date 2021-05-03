<template>
<div class="content pl-32 pr-8 mt-4 pb-20" id="content-full">
	<div class="card">
		<nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="">Settings</a></li>
            <li class="breadcrumb-item"><a href="">Loans</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Loan Product</li>
          </ol>
        </nav>
		<div class="card-header">
			<h1 class="title text-2xl">Create Loan Product</h1>
		</div>
		<div class="card-body">

			<form class="row" @submit.prevent="submit">
				<div class="col-lg-8">
					<h1 class="text-2xl title">Loan Information</h1>
					<div class="row mt-4">
						<div class="form-group col-lg-2">
							<label for="code" class="title text-xl">Code</label>
							<input type="text" class="form-control text-xl" :class="{'is-invalid': hasError('code') }" id="code" v-model="form.code">
                            <div class="text-danger" v-if="hasError('code')">
                                {{errorMessage('code')}}
                            </div>
						</div>
						<div class="form-group col-lg-6">
							<label for="name" class="title text-xl">Name</label>
							<input type="text" class="form-control text-xl" :class="{'is-invalid': hasError('name') }"  id="name" v-model="form.name">
                            <div class="text-danger" v-if="hasError('name')">
                                {{errorMessage('name')}}
                            </div>
						</div>
						<div class="form-group col-lg-4">
							<label for="valid_until" class="title text-xl">Valid Until</label>
							<date-picker id="valid-until" class="text-xl" @datePicked="datePicked" :class="{'is-invalid': hasError('valid_until') }"></date-picker>
                            <div class="text-danger" v-if="hasError('valid_until')">
                                {{errorMessage('valid_until')}}
                            </div>
						</div>
					</div>
					<div class="row mt-4">
						<div class="form-group col-lg-4">
							<label class="text-xl" for="apc">Accounts Per Client</label>
							<input type="number" class="form-control text-xl" :class="{'is-invalid': hasError('account_per_client') }" id="apc" v-model="form.account_per_client">
                            <div class="text-danger" v-if="hasError('account_per_client')">
                                {{errorMessage('account_per_client')}}
                            </div>
						</div>
						<div class="col-lg-12 form-group">
							<label class="title text-xl" for="description">Product Description</label>
							<textarea id="description" class="form-control" :class="{'is-invalid': hasError('description') }" cols="30" rows="10" v-model="form.description"></textarea>
                            <div class="text-danger" v-if="hasError('description')">
                                {{errorMessage('description')}}
                            </div>
						</div>
					</div>
					<div class="row mt-4">

						<div class="form-group col-lg-4">
							<label class="text-xl" for="loan_minimum_amount">Minimum Loan Amount</label>
							<input type="number" class="form-control text-xl" id="loan_minimum_amount" :class="{'is-invalid': hasError('loan_minimum_amount') }" v-model="form.loan_minimum_amount">
                            <div class="text-danger" v-if="hasError('loan_minimum_amount')">
                                {{errorMessage('loan_minimum_amount')}}
                            </div>
                        </div>
						<div class="form-group col-lg-4">
							<label class="text-xl" for="loan_maximum_amount">Maximum Loan Amount</label>
							<input type="number" class="form-control text-xl" id="loan_maximum_amount" :class="{'is-invalid': hasError('loan_maximum_amount') }" v-model="form.loan_maximum_amount">
                            <div class="text-danger" v-if="hasError('loan_maximum_amount')">
                                {{errorMessage('loan_maximum_amount')}}
                            </div>
						</div>
					</div>
					<h1 class="text-2xl title">Installments</h1>
					<div class="row mt-4">
						
						<div class="form-group col-lg-4">
							<label class="title text-xl" for="min_installment">Minimum Installment</label>
							<input type="number" class="form-control text-xl" id="min_installment" :class="{'is-invalid': hasError('minimum_installment') }" v-model="form.minimum_installment">
                            <div class="text-danger" v-if="hasError('minimum_installment')">
                                {{errorMessage('minimum_installment')}}
                            </div>
						</div>
						<div class="form-group col-lg-4">
							<label class="title text-xl" for="default_installment">Default Installment</label>
							<input type="number" class="form-control text-xl" id="default_installment" :class="{'is-invalid': hasError('default_installment') }" v-model="form.default_installment">
                            <div class="text-danger" v-if="hasError('default_installment')">
                                {{errorMessage('default_installment')}}
                            </div>
						</div>
						<div class="form-group col-lg-4">
							<label class="title text-xl" for="max_installment">Maximum Installment</label>
							<input type="number" class="form-control text-xl" id="max_installment" :class="{'is-invalid': hasError('maximum_installment') }" v-model="form.maximum_installment">
                            <div class="text-danger" v-if="hasError('maximum_installment')">
                                {{errorMessage('maximum_installment')}}
                            </div>
						</div>

						<div class="form-group col-lg-6">
							<label class="title text-xl" for="installment_length">Installment Length</label>
							<input type="number" class="form-control text-xl" id="installment_length" :class="{'is-invalid': hasError('installment_length') }" v-model="form.installment_length">
                            <div class="text-danger" v-if="hasError('installment_length')">
                                {{errorMessage('installment_length')}}
                            </div>
						</div>
						<div class="form-group col-lg-6">
							<label class="title text-xl" for="installment_method">Installment Method</label>
							<select name="" id="installment_method" class="form-control" :class="{'is-invalid': hasError('installment_method') }" v-model="form.installment_method">
								<option value="">Please select</option>
								<option value="days">Days</option>
								<option value="weeks">Weeks</option>
								<option value="months">Months</option>
								<option value="years">Years</option>
							</select>
                            <div class="text-danger" v-if="hasError('installment_method')">
                                {{errorMessage('installment_method')}}
                            </div>
						</div>
					</div>
					
					
					<div class="row mt-4">
						<div class="form-group col-lg-6 d-inline-block">
							<label class="title text-xl" for="interest_rate">Interest Rate</label>
							<input type="text" class="form-control text-xl" id="interest_rate" :class="{'is-invalid': hasError('interest_rate') }" v-model="form.interest_rate">
                            <div class="text-danger" v-if="hasError('interest_rate')">
                                {{errorMessage('interest_rate')}}
                            </div>
							
						</div>
						<div class="form-group col-lg-6 d-inline-block">
							<label class="title text-xl" for="interest_interval">Interest Interval</label>
							<select name="interest_interval" id="interest_interval" class="form-control" :class="{'is-invalid': hasError('interest_interval') }" v-model="form.interest_interval">
								<option value="">Please select</option>
								<option value="Daily">Daily</option>
								<option value="Weekly">Weekly</option>
								<option value="Monthly">Monthly</option>
								<option value="Annually">Annually</option>
								<option value="1 Installment Length">1 Installment Length</option>
							</select>
                            <div class="text-danger" v-if="hasError('interest_interval')">
                                {{errorMessage('interest_interval')}}
                            </div>
						</div>
						<div class="form-group col-lg-6 d-inline-block">
							<label class="title text-xl" for="interest_calculation_method_id">Interest Calculation Method</label>
							<select name="interest_calculation_method_id" id="interest_calculation_method_id" class="form-control" :class="{'is-invalid': hasError('interest_calculation_method_id') }" v-model="form.interest_calculation_method_id">
								<option value="">Please select</option>
								<option value="101">Flat</option>
								<option value="102">Declining Balance</option>
								<option value="103">Declining Balance with Equal Installments</option>
							</select>
                            <div class="text-danger" v-if="hasError('interest_calculation_method_id')">
                                {{errorMessage('interest_calculation_method_id')}}
                            </div>
						</div>

						<div class="form-group col-lg-6 d-inline-block">
							<label class="title text-xl" for="grace_period">Grace Period</label>
							<input type="text" class="form-control text-xl" id="grace_period" :class="{'is-invalid': hasError('grace_period') }" v-model="form.grace_period">
                            <div class="text-danger" v-if="hasError('grace_period')">
                                {{errorMessage('grace_period')}}
                            </div>
						</div>

					</div>
					<div class="row">
						<div class="form-group p0 col-lg-6 mt-4 d-table-row">
							<div class="form-check tranch mx-w3 d-table-cell">
		                        <label class="form-check-label" for="has_tranches">
		                            <input class="form-check-input cb-type" id="has_tranches" type="checkbox" @click="checkedTranched($event)">
		                            <span class="form-check-sign" style="top:1px;">
		                            <span class="check"></span>
		                            </span>
		                            <label class="text-lg title" for="has_tranches">Has Tranches</label>
		                        </label>
		                    </div>
		                    <div class="ml-5 px-3 w-7" v-if="form.has_tranches">
		                   	 <input type="number" class="form-control mt-3 text-xl" id="number_of_tranches" :class="{'is-invalid': hasError('number_of_tranches') }"  placeholder="Number of Tranches" v-model="form.number_of_tranches">
                            <div class="text-danger" v-if="hasError('number_of_tranches')">
                                {{errorMessage('number_of_tranches')}}
                            </div>
		                    </div>
	                    </div>
					</div>
					<h1 class="text-2xl title">Fees</h1>
	
					<div class="col-lg-12 px-3">
						<div class="col-lg-12 mt-8">
							<div class="form-group w-100">
								<multi-select :multiple="true" :class="{'is-invalid': hasError('fees') }" track_by="id" label="name" list="fees" @changed="changed"></multi-select>
                                <div class="text-danger" v-if="hasError('fees.fees')">
                                    {{errorMessage('fees.fees')}}
                                </div>
							</div>
						</div>
					</div>
					<h1 class="text-2xl title">General Ledger Accounts</h1>
	
					<div class="col-lg-12 px-3">
						<div class="col-lg-12 mt-8">
							<div class="form-group w-100">
								<label class="title text-lg" for="loan_portfolio_active">Loan Portfolio Active</label>
								<select id="loan_portfolio_active" class="form-control" :class="{'is-invalid': hasError('loan_portfolio_active') }" v-model="form.loan_portfolio_active">
									<option value="">Select GL Account</option>
									<option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_portfolio_active')">
                                    {{errorMessage('loan_portfolio_active')}}
                                </div>
							</div>
							<div class="form-group w-100">
								<label class="title text-lg" for="loan_portfolio_in_arrears">Loan Portfolio In Arrears</label>
								<select id="loan_portfolio_in_arrears" class="form-control" :class="{'is-invalid': hasError('loan_portfolio_in_arrears') }" v-model="form.loan_portfolio_in_arrears">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_portfolio_in_arrears')">
                                    {{errorMessage('loan_portfolio_in_arrears')}}
                                </div>
							</div>

							<div class="form-group w-100">
								<label class="title text-lg" for="loan_portfolio_matured">Loan Portfolio Matured</label>
								<select id="loan_portfolio_matured" class="form-control" :class="{'is-invalid': hasError('loan_portfolio_matured') }" v-model="form.loan_portfolio_matured">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_portfolio_matured')">
                                    {{errorMessage('loan_portfolio_matured')}}
                                </div>
							</div>
						</div>
						
						<div class="col-lg-12 mt-8">
							<div class="form-group w-100">
								<label class="title text-lg" for="loan_interest_income_active">Loan Interest Active</label>
								<select id="loan_interest_income_active" class="form-control" :class="{'is-invalid': hasError('loan_interest_income_active') }" v-model="form.loan_interest_income_active">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_interest_income_active')">
                                    {{errorMessage('loan_interest_income_active')}}
                                </div>
							</div>
							<div class="form-group w-100">
								<label class="title text-lg" for="loan_interest_income_in_arrears">Loan Interest In Arrears</label>
								<select id="loan_interest_income_in_arrears" class="form-control" :class="{'is-invalid': hasError('loan_interest_income_in_arrears') }" v-model="form.loan_interest_income_in_arrears">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_interest_income_in_arrears')">
                                    {{errorMessage('loan_interest_income_in_arrears')}}
                                </div>
							</div>

							<div class="form-group w-100">
								<label class="title text-lg" for="loan_interest_income_matured">Loan Interest Matured</label>
								<select id="loan_interest_income_matured" class="form-control" :class="{'is-invalid': hasError('loan_interest_income_matured') }" v-model="form.loan_interest_income_matured">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_interest_income_matured')">
                                    {{errorMessage('loan_interest_income_matured')}}
                                </div>
							</div>
						</div>
						
						<div class="col-lg-12">
							<div class="form-group w-100">
								<label class="title text-lg" for="loan_write_off">Loan Write Off</label>
								<select id="loan_write_off" class="form-control" :class="{'is-invalid': hasError('loan_write_off') }" v-model="form.loan_write_off">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_write_off')">
                                    {{errorMessage('loan_write_off')}}
                                </div>
							</div>

							<div class="form-group w-100">
								<label class="title text-lg" for="loan_recovery">Loan Recovery</label>
								<select id="loan_recovery" class="form-control" :class="{'is-invalid': hasError('loan_recovery') }" v-model="form.loan_recovery">
									<option value="">Select GL Account</option>
                                    <option value="26">CASH IN BANK</option>
								</select>
                                <div class="text-danger" v-if="hasError('loan_recovery')">
                                    {{errorMessage('loan_recovery')}}
                                </div>
							</div>
						</div>
						
					</div>
					<button type="submit" class="btn btn-primary ml-3" >Submit</button>
				</div>
			</form>
		</div>
	</div>
</div>
</template>

<script>
import DatePicker from './DatePickerComponent'
import Swal from 'sweetalert2'
export default {
    data(){
        return {
            form: {
                code: "",
                name:"",
                valid_until:"",
                account_per_client: "",
                description: "",

                loan_minimum_amount: "",
                loan_maximum_amount: "",

                minimum_installment: "",
                default_installment: "",
                maximum_installment: "",
                
                installment_length: "",
                installment_method: "",

                interest_rate:"",
                interest_interval:"",
                interest_calculation_method_id:"",

                grace_period:"",

                has_tranches: false,
                number_of_tranches:"",

                
				loan_portfolio_active:"",
				loan_portfolio_in_arrears:"",
				loan_portfolio_matured:"",

				loan_interest_income_active:"",
				loan_interest_income_in_arrears:"",
				loan_interest_income_matured:"",

				loan_write_off:"",
				loan_recovery:"",
				fees: []
                
            },
            errors : [],
        }
    },
    methods: {
		changed(value){
			this.form.fees = value
		},
        datePicked(value){
            this.form.valid_until = value
        },
        submit(){
            var data = this.form
            if(data.valid_until==""){
                delete data.valid_until;
            }
            axios.post(this.postUrl,
                data
            ).then(res=>{
				Swal.fire({
					icon: 'success',
					title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Success!</span>',
					text: res.data.msg,
					confirmButtonText: 'OK'
				})
				.then(res=>{
					location.reload()
				})
            })
            .catch(err=>{
                 this.errors = err.response.data.errors || {}
            })
        },
        checkedTranched($event){
            
            if($event.target.checked){
                this.form.has_tranches = true
            }else{
                this.form.has_tranches =false
                this.form.number_of_tranches = ""
            }
        },
        hasError(key){
            if(this.errors[key] != undefined){
                return true
            }
            return false;
        },
        errorMessage(key){
            return this.errors[key][0];
        },
    },
    computed : {
        postUrl(){
            return '/settings/create/loan'
        }
    }
}
</script>

<style>

</style>