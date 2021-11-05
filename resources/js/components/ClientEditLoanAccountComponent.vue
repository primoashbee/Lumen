<template>
    <div class="card">
		<nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a :href="linkTo('clients')">Client</a></li>
            <li class="breadcrumb-item"><a :href="linkTo('client',client_id)">{{client_id}}</a></li>
            <li class="breadcrumb-item"><a :href="linkTo('loans',client_id)">Loans</a></li>
            <li class="breadcrumb-item active" aria-current="page">Create Loan Account</li>
          </ol>
        </nav>
		<div class="card-header">
			<h1 class="title text-3xl">{{name}}</h1>
			<h4 class="title text-base">{{client_id}}</h4>
		</div>
		<div class="card-body">
			<form class="row">
				<div class="col-lg-12">
					<h1 class="text-2xl title">Edit Loan Account</h1>
					<p class="text-lg title">Credit Limit: {{formatAmount(credit_limit)}}</p>
					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="form-group">
								<label for="loan_products" class="title text-xl">Product</label>
								<!-- <select id="loan_products" class="form-control" @change="selected">
									<option :value="null">Please Select</option>
									<option v-for="item in loan_products" :key="item.id" :value="JSON.stringify({id:item.id,code:item.code})" :data-name="item.code">{{item.name}}</option>
								</select> -->
								<loan-product-list id="loan_products" @selected="selected" :default_id="form.account.loan_id"></loan-product-list>
							</div>
							<div class="form-group">
							</div>
						</div>
						<div class="row pl-3">
							<div class="col-lg-6">
								<div class="form-group">
									<label for="disbursement_date">Disbursement Date</label>
									<!-- <date-picker @datePicked="getDate($event, 'disbursement_date')" id="disbursement_date" v-model="form.disbursement_date"></date-picker> -->
									<input type="date" class="form-control" v-model="form.disbursement_date"/>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="form-group">
									<label for="repayment_date">First Repayment Date</label>
									<input type="date" class="form-control" v-model="form.first_payment"/>
									<!-- <date-picker  @datePicked="getDate($event, 'first_payment_date')" id="repayment_date" v-model="form.first_payment_date"></date-picker> -->
								</div>
							</div>	
						</div>
						
					</div>	

					<hr>
					<h1 class="text-2xl title">Loan Terms</h1>
					<div class="row pb-4">
						<div class="col-lg-8"> 
							<div class="row">
								<div class="form-group col-lg-4 pl-3">
									<label for="loan_amount" class="title text-xl">Loan Amount</label>
									<input type="number" class="form-control" id="loan_amount" v-model="form.amount" step="1000">
								</div>

								<div class="col-lg-4 form-group">
									<label for="installment" class="title text-xl">Number of Installment</label>
									<select id="installment" class="form-control" v-model="form.number_of_installments">
										<option :value="null"> Please Select</option>
										<option v-for="item in installment_list" :value="item.installments" :key="item.id"> {{item.installments}}</option>
									</select>
								</div>
								<div class="form-group col-lg-4 pl-4">
									<label for="Interest" class="title text-xl">Interest</label>
									<input type="text" class="form-control" id="Interest" readonly :value="selected_interest">
								</div>
							</div>
						</div>	
					</div>	
					<hr>
					<h1 class="text-2xl title mt-4">Fees</h1>
					<div class="col-lg-12">
						<div class="row">
							<div v-for="fee in fees" :key="fee.id">
								<div class="form-group col-lg-12 col-sm-12 pl-0 pb-0">
									<label :for="fee.name">{{fee.name}}</label>
									<input type="text" :id="fee.name" class="form-control" disabled :value="fee.amount">
								</div>
							</div>
						</div>
					</div>

					<hr>
					<h1 class="title text-2xl">Minimum Deposit Balance</h1>
					<div class="col-lg-12 col-md-10">
						<div class="mt-4 row">

							<div class="col-lg-4 col-sm-12 pl-0 form-group">
								<label for="deposit_account" class="title text-xl">Deposit Account</label>
								<select id="deposit_account" class="form-control">
									<option value="">MCBU</option>
									<option value="">RCBU</option>
								</select>
							</div>
							<div class="form-group col-lg-4 col-sm-12 pl-0">
								<label for="minimum_balance" class="title text-xl">Minimum Balance</label>
								<input type="number" class="form-control" id="minimum_balance">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="notes" class="title text-2xl">Notes</label>
						<textarea class="form-control" id="" cols="30" rows="10"></textarea>	
					</div>
					<button class="btn btn-primary" @click.prevent="calculate"> Calculate </button>
					<div>
						<h1 class="text-2xl"> Loan {{form.account.code}} </h1>
						<h1 class="text-lg"> Number of Installments {{form.account.number_of_installments}} </h1>
						<h1 class="text-lg"> Start Date {{moment(form.account.first_payment_date)}} </h1>
						<h1 class="text-lg"> End Date {{moment(form.account.last_payment_date)}} </h1>

						<h1 class="text-lg"> Principal {{formatAmount(form.account.principal)}} </h1>
						<h1 class="text-lg"> Interest {{formatAmount(form.account.interest)}} </h1>
						<h1 class="text-lg"> Total Loan Amount {{formatAmount(form.account.total_loan_amount)}} </h1>

						<h1 class="text-lg"> Disbursement Amount {{formatAmount(form.account.disbursed_amount)}} </h1>
						<h1 class="text-lg"> Total Deductions {{formatAmount(form.account.total_deductions)}} </h1>
						<table class="table">
							<thead>
								<tr>
									<td><p class="title">Installment</p></td>
									<td><p class="title">Date</p></td>
									<td><p class="title">Amortization</p></td>
									<td><p class="title">Principal</p></td>
									<td><p class="title">Interest</p></td>
									<td><p class="title">Payment Due</p></td>
									<td><p class="title">Balance</p></td>
								</tr>
							</thead>
							<tbody>
								<tr v-for="item in form.installments" :key="item.id">
									<td class="text-lg">{{item.installment}}</td>
									<td class="text-lg">{{moment(item.date)}}</td>
									<td class="text-lg">{{formatAmount(item.amortization)}}</td>
									<td class="text-lg">{{formatAmount(item.principal)}}</td>
									<td class="text-lg">{{formatAmount(item.interest)}}</td>
									<td class="text-lg">{{formatAmount(item.amount_due)}}</td>
									<td class="text-lg">{{formatAmount(item.principal_balance + item.interest_balance)}}</td>
								</tr>
							</tbody>
						</table>
						<button type="button" v-if="calculated" @click="updateLoan" class="btn btn-primary">Submit</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>


<script>
import moment from 'moment'
import LoanProduct from './Settings/LoanProduct.vue'
export default {
  components: { LoanProduct },
	props: ['client_id','name','loan_id','businesses','household_income'],
	data(){
		return {
			'loan_products': null,
			'rates':null,
			'calculated':false,
			'code':null,
			'first_payment':null,
			'fees':null,
			form: {
				account:{},
				installments: null,
				loan_account_id:this.loan_id,
				loan_id:null,
				first_payment:null,
				disbursement_date:null,
				amount:null,
				number_of_installments:null,
				interest_rate:null,
				status:null
			},
			calculator: null,
			errors: {}
		}
	},
    mounted(){
		this.form.client_id = this.client_id
		this.fetchLoanProducts()
		this.fetchLoanAccount()
	},
	methods:{
		formatAmount(value) {
			let val = (value/1).toFixed(2).replace('.', '.')
			return '₱ '+val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
		},
		fetchLoanAccount(){
			axios.get(this.fetchLoanAccountLink)
			.then(res => {
				this.form.installments = res.data.installments
				this.fees = res.data.fees
				this.form.account = res.data.loan_account
				this.code = this.form.account.product.code
				this.form.number_of_installments = this.form.account.number_of_installments
				this.form.loan_id = this.form.account.loan_id
				this.form.interest_rate = this.form.account.interest_rate
				this.form.amount = this.form.account.amount
				this.form.disbursement_date	= moment(this.form.account.disbursement_date).format('YYYY-MM-DD')
				this.form.first_payment = moment(this.form.account.first_payment_date).format('YYYY-MM-DD')
				
			})
		},
		moment(date){
			let _date = moment(date).format('MMMM DD, Y')

			if(_date=="Invalid date"){
				return "------"
			}
			return _date;
		},
		selected(e){

			// let selected = JSON.parse(e.target.value)
			this.form.loan_id = e.id
			this.code = e.code
		},
		
		fetchLoanProducts(){
			axios.get(this.loan_products_route)
			.then(res=>{
				this.loan_products = res.data.loans
				this.rates = res.data.rates
			})
		},
		updateLoan(){
			this.form.client_id = this.client_id
			this.form.interest_rate = this.selected_interest
			axios.post(this.fetchLoanAccountLink,this.form)
			.then(res=>{
				Swal.fire({
					icon: 'success',
					title: '<p style="color:green;font-size:1em;font-weight:bold">Success</p>',
					text: res.data.msg,
				})
				.then(res=>{
					// location.reload()
				})
				
			})
			.catch(err =>{
				this.errors = error.response.data.errors || {}
				Swal.fire({
					icon: 'warning',
					title: '<p style="color:green;font-size:1em;font-weight:bold">OOPPPSSSSS</p>',
					text: this.errors,
				})
				
			})
		},
		getDate(value,field){

			this.form[field] = value
		},
		calculate(){
			this.form.account.credit_limit = this.credit_limit
			this.form.client_id = this.client_id
			this.form.interest_rate = this.selected_interest
			this.form.status = this.form.account.status
			axios.post(this.calculate_route,this.form)
			.then(res=>{
				this.calculator = res.data.data
				this.fees = this.calculator.fees
				this.form.installments = this.calculator.installments
				this.form.account.amount = this.calculator.amount
				this.form.account.loan_id = this.form.loan_id
				this.form.account.first_payment = this.calculator.start_date
				this.form.account.last_payment_date = this.calculator.end_date
				this.form.account.principal = this.calculator.principal
				this.form.account.interest = this.calculator.interest
				this.form.account.total_loan_amount = this.calculator.total_loan_amount
				this.form.account.total_deductions = this.calculator.total_deductions
				this.form.account.disbursed_amount = this.calculator.disbursement_amount
				this.form.account.number_of_installments = this.calculator.number_of_installments
				this.calculated = true;
			})
			.catch(err=>{
				this.errors = err.response.data.errors || {}
				var html="<ul>";
				$.each(this.errors, function(k, v){ 
					html += '<p class="text-left">'+ v +'</p>'
				})
				Swal.fire({
					icon: 'error',
					title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Error</span>',
					html: html +'</ul>'
				})
				this.calculated = false;
			});
			
		},
		linkTo(route,client_id=null){
			if(route=='client'){
				return '/client/'+this.client_id;
			}else if(route=='loans'){
				return '/client/'+this.client_id+'/loans';
			}else if(route=='clients'){
				return '/clients';
			}
		}
	},
	computed:{
		credit_limit(){
			var total_business_income = 0;
			var businesses = JSON.parse(this.businesses)
		
			$.each(businesses, function(k,v){
				total_business_income += v.monthly_net_income;
			})
			// total_business_income/=4;
			
			var household_income = JSON.parse(this.household_income)
			var twndi = total_business_income + household_income.total_household_income / 4;
			var pccp = twndi * .7;

			var credit_limit = this.form.number_of_installments * pccp;
			this.form.credit_limit = credit_limit
			return credit_limit;
			
		},
		loan_products_route(){
			return '/settings/api/get/loans?has_page=false'
		},
		calculate_route(){
			return '/loan/calculator';
		},
		create_loan_route(){
			return '/client/create/loan';
		},
		fetchLoanAccountLink(){
			return '/client/'+this.client_id+'/edit/loan/'+this.loan_id;
		},
		installment_list(){
			if(this.code == null){
				return null;
			}	
			let obj = []
			let rates;
			this.rates.map(x=>{
				if(x.code == this.code){
					x.rates.map(r=>{
						obj.push(r)
					})
				}
			});

			
			return obj;
			
		},
		selected_interest(){
			let vm = this;
			if(vm.installment_list==null){
				return null;
			}

			var rate = null
			vm.installment_list.map(x=>{ 
				if(x.installments == vm.form.number_of_installments){
					rate = x.rate;
				}
			});
			return rate;
		}
	}
}
</script>