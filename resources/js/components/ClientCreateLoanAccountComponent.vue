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
			<h1 class="title text-2xl">{{name}}</h1>
			<h4 class="title text-base">{{client_id}}</h4>
		</div>
		<div class="card-body">

			<form class="row">
				<div class="col-lg-12">
					<h1 class="text-2xl title">New Loan Account</h1>
					<div class="row mt-4">
						<div class="col-lg-6">
							<div class="form-group">
								<label for="loan_products" class="title text-xl">Product</label>
								<!-- <select id="loan_products" class="form-control" @change="selected">
									<option :value="null">Please Select</option>
									<option v-for="item in loan_products" :key="item.id" :value="JSON.stringify({id:item.id,code:item.code})" :data-name="item.code">{{item.name}}</option>
								</select> -->
								<loan-product-list id="loan_products" @selected="selected"></loan-product-list>
							</div>
							<div class="form-group">
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group">
								<label for="disbursement_date">Disbursement Date</label>
                        		<!-- <date-picker @datePicked="getDate($event, 'disbursement_date')" id="disbursement_date" v-model="form.disbursement_date"></date-picker> -->
								<input type="date" class="form-control" v-model="form.disbursement_date"/>
							</div>
							<div class="form-group">
								<label for="repayment_date">First Repayment Date</label>
								<input type="date" class="form-control" v-model="form.first_payment"/>
                        		<!-- <date-picker  @datePicked="getDate($event, 'first_payment_date')" id="repayment_date" v-model="form.first_payment_date"></date-picker> -->
							</div>
						</div>	
					</div>	

					<hr>
						<h1 class="text-2xl title">Loan Terms</h1>
					<div class="row pb-4">
						
						<div class="d-table-row px-3 mt-4">

							<div class="form-group d-table-cell pl-4">
								<label for="loan_amount" class="title text-xl">Loan Amount</label>
								<input type="number" class="form-control" id="loan_amount" v-model="form.amount" step="1000">
							</div>

						</div>
						
						<div class="d-table-row pl-3 mt-4">
							<div class="d-table-cell form-group">
								<label for="installment" class="title text-xl">Number of Installment</label>
								<select id="installment" class="form-control" v-model="form.number_of_installments">
									<option :value="null"> Please Select</option>
									<option v-for="item in installment_list" :value="item.installments" :key="item.id"> {{item.installments}}</option>
								</select>
							</div>
							<div class="form-group d-table-cell pl-4">
								<label for="Interest" class="title text-xl">Interest</label>
								<input type="text" class="form-control" id="Interest" readonly :value="selected_interest">
							</div>
						</div>
						
					</div>	
					<hr>
					<h1 class="text-2xl title mt-4">Fees</h1>
					<div v-if="calculator!=null	">
						<div class="form-inline py-2" v-for="fee in calculator.fees" :key="fee.id">
							<div class="form-group pl-3">
								<label :for="fee.name">{{fee.name}}</label>
								<input type="text" :id="fee.name" class="form-control ml-3" disabled :value="fee.amount">
							</div>
						</div>
					</div>

					<hr>
					<h1 class="title text-2xl">Minimum Deposit Balance</h1>
					<div class="row">
						<div class="d-flex w-8 px-3 mt-4">
							<div class="w-full form-group">
								<label for="deposit_account" class="title text-xl">Deposit Account</label>
								<select id="deposit_account" class="form-control">
									<option value="">MCBU</option>
									<option value="">RCBU</option>
								</select>
							</div>
							<div class="form-group pl-4 w-full">
								<label for="minimum_balance" class="title text-xl">Minimum Balance</label>
								<input type="number" class="form-control w-7" id="minimum_balance">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="notes" class="title text-2xl">Notes</label>
						<textarea class="form-control" id="" cols="30" rows="10"></textarea>	
					</div>
					<button class="btn btn-primary" @click.prevent="calculate"> Calculate </button>

				
					<div v-if="calculated">
						<h1> Loan {{calculator.code}} </h1>
						<h1> Number of Installments {{calculator.number_of_installments}} </h1>
						<h1> Start Date {{calculator.start_date}} </h1>
						<h1> End Date {{calculator.end_date}} </h1>

						<h1> Principal {{calculator.formatted_principal}} </h1>
						<h1> Interest {{calculator.formatted_interest}} </h1>
						<h1> Total Loan Amount {{calculator.formatted_total_loan_amount}} </h1>

						<h1> Disbursement Amount {{calculator.formatted_disbursement_amount}} </h1>
						<h1> Total Deductions {{calculator.formatted_total_deductions}} </h1>
						<table class="table"  v-if="calculated">
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
								<tr v-for="item in calculator.installments" :key="item.id">
									<td class="text-lg">{{item.installment}}</td>
									<td class="text-lg">{{moment(item.date)}}</td>
									<td class="text-lg">{{item.formatted_amortization}}</td>
									<td class="text-lg">{{item.formatted_principal}}</td>
									<td class="text-lg">{{item.formatted_interest}}</td>
									<td class="text-lg">{{item.formatted_amount_due}}</td>
									<td class="text-lg">{{item.formatted_principal_balance}}</td>
								</tr>
							</tbody>
						</table>
						
					
					<button type="button" @click="createLoan" class="btn btn-primary">Submit</button>
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
	props: ['client_id','name'],
	data(){
		return {
			'loan_products': null,
			'rates':null,
			'calculated':false,
			'code':null,
			form: {
				loan_id:null,
				first_payment:null,
				disbursement_date:null,
				amount:null,
				number_of_installments:null,
				interest_rate:null,

			},
			calculator: null,
			errors: {}
		}
	},
    mounted(){
		this.form.client_id = this.client_id
		this.fetchLoanProducts()
	},
	methods:{
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
		createLoan(){
			this.form.client_id = this.client_id
			this.form.interest_rate = this.selected_interest
			axios.post(this.create_loan_route,this.form)
			.then(res=>{
				Swal.fire({
					icon: 'success',
					title: '<p style="color:green;font-size:1em;font-weight:bold">Success</p>',
					text: res.data.msg,
				})
				.then(res=>{
					location.reload()
				})
				
			})
			.catch(err =>{
				this.errors = error.response.data.errors || {}
			})
		},
		getDate(value,field){

			this.form[field] = value
		},
		calculate(){
			
			this.form.client_id = this.client_id
			this.form.interest_rate = this.selected_interest
			axios.post(this.calculate_route,this.form)
			.then(res=>{
				this.calculator = res.data.data
				this.calculated = true;
			})
			.catch(err=>{
				this.errors = err.response.data.errors || {}
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
		loan_products_route(){
			return '/settings/api/get/loans?has_page=false'
		},
		calculate_route(){
			return '/loan/calculator';
		},
		create_loan_route(){
			return '/client/create/loan';
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