<template>
    <div>
        <div class="row">
            <div class="col-lg-5">
                <label for="" style="color:white" class="lead mr-2">Filter:</label>
                <product-component product_type="deposit" @productSelected="productSelected" class="d-inline-block" style="width:500px" v-model="office_id"></product-component>
                <!-- <button type="button" class="btn btn-primary" @click="filter">Add New</button> -->
            </div>
            <div class="col-lg-5">
                <label for="" style="color:white" class="lead mr-2">Filter:</label>
                <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px" v-model="office_id"></v2-select>
                <!-- <button type="button" class="btn btn-primary" @click="filter">Add New</button> -->
            </div>
            <div class="col-lg-2">
                <button type="button" class="btn btn-primary" @click="submit">Search</button>
            </div>
            <div class="col-lg-6 float-right d-flex"  v-if="false">
                <label for="" style="color:white" class="lead mr-2">Search:</label>
                <input type="text" id="search_client" class="form-control border-light pb-2" v-model="query" v-debounce:300ms="inputSearch"/>
                <div>
            </div>  
        </div>
        
		<div class="w-100 px-3 mt-6" >
			<table class="table" >
				<thead>
					<tr v-if="forInterestPosting">
						<td><p class="title"><input type="checkbox" id="check_all" @click="checkAll($event)" v-show="viewableRecords > 0"></p></td>
						<td><p class="title">Deposit Account</p></td>
						<td><p class="title">Client ID</p></td>
						<td><p class="title">Name</p></td>
						<td><p class="title">Balance</p></td>
						<td><p class="title">Accrued Interest</p></td>
						<td style="padding-left:50px"><p class="title">Linked To</p></td>
					</tr>
					
					<tr v-else>
						<td><p class="title"><input type="checkbox" id="check_all" @click="checkAll($event)" v-show="viewableRecords > 0"></p></td>
						<td><p class="title">Deposit Account</p></td>
						<td><p class="title">Client ID</p></td>
						<td><p class="title">Name</p></td>
						<td><p class="title">Balance</p></td>
						<td style="width:400px"><p class="title">Amount</p></td>
						<td style="padding-left:50px"><p class="title">Linked To</p></td>
					</tr>

				</thead>
				<tbody v-if="hasRecords && forInterestPosting">
					<tr  v-for="(item, key) in lists.data" :key="item.id">
						
						<td><input type="checkbox" class="item_checkbox" :id="item.id" @change="checked(item,$event)" :checked="accountOnList(item.id)"></td>
						
						<td><label :for="item.id">{{item.type.name}}</label></td>
						<td><a :href="clientLink(item.client.client_id)">{{item.client.client_id}}</a></td>
						
						<td>{{item.client.firstname + ' ' + item.client.lastname}}</td>
						<td>{{item.balance_formatted}} </td>
						<!-- <td> Account ID : {{item.id}} </td> -->
						
						<td>{{item.accrued_interest}}</td>
							
						<td style="padding-left:50px">{{item.client.office.name}}</td>
					</tr>
				</tbody>
				<tbody v-else>
					<tr  v-for="(item, key) in lists.data" :key="item.id">

						<td><input type="checkbox" class="item_checkbox"  :id="item.id" @change="checked(item,$event)" :checked="accountOnList(item.id)"></td>
						
						<td><label :for="item.id">{{item.type.name}}</label></td>
						<td><a :href="clientLink(item.client.client_id)">{{item.client.client_id}}</a></td>
						
						<td>{{item.client.firstname + ' ' + item.client.lastname}}</td>
						<td>{{item.balance_formatted}} </td>
						<!-- <td> Account ID : {{item.id}} </td> -->
						
						<td>
							<div class="form-group">
								
								<amount-input :readonly="inputDisabled(item.id) "@amountEncoded="amountEncoded" :add_class="errorAddClass(item.id)"  :account_info="item" :tabindex="key+1" ></amount-input>
								<div class="text-danger" v-if="hasInputError(item.id)">
									{{inputErrorMessage(item.id)}}
								</div>
							</div>
						</td>
							
						<td style="padding-left:50px">{{item.client.office.name}}</td>
					</tr>
				</tbody>
				
			</table>
			<p class="lead float-left text-right" style="color:white">Showing Records {{lists.from}} - {{lists.to}} of {{totalRecords}} </p>
			<p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
			<button type="button" class="btn btn-primary" @click="showModal()" v-if="form.accounts.length > 0"> {{transactionTypeDisplay}} </button>
			<div class="clearfix"></div>
			<paginator :dataset="lists" @updated="fetch"></paginator>
		</div>
		
        </div>

        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
		<b-modal id="deposit-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modal_title" :header-bg-variant="background" :body-bg-variant="background" >
		    <form>
		        <div class="form-group mt-4">
		        	<label class="text-lg">Branch</label>
                    <v2-select @officeSelected="assignOffice" list_level="" :default_value="this.office_id" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="hasError('office_id')">
                        {{ errors.office_id[0]}}
                    </div>
		        </div>
		        <div class="form-group" v-if="transactionType!='post_interest'">
		        	<label class="text-lg">Payment Method</label>
					<payment-methods payment_type="for_deposit" @paymentSelected="paymentSelected" v-bind:class="hasError('payment_method') ? 'is-invalid' : ''" ></payment-methods>
					<div class="invalid-feedback" v-if="hasError('payment_method')">
                        {{ errors.payment_method[0]}}
                    </div>
		        </div>
		        <div class="form-group" v-if="transactionType!='post_interest'">
		        	<label class="text-lg">Repayment Date</label>
                    <input type="date" class="form-control" v-model="form.repayment_date" v-bind:class="hasError('repayment_date') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('repayment_date')">
                        {{ errors.repayment_date[0]}}
                    </div>
				</div>
		        <div class="form-group" v-if="transactionType!='post_interest'">
		        	<label class="text-lg">OR #:</label>
                    <input type="text" class="form-control" v-model="form.receipt_number" v-bind:class="hasError('receipt_number') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('receipt_number')">
                        {{ errors.receipt_number[0]}}
                    </div>
				</div>
		        <div class="form-group" v-if="transactionType=='post_interest'">
		        	<label class="text-lg">JV #:</label>
                    <input type="text" class="form-control" v-model="form.journal_voucher_number" v-bind:class="hasError('journal_voucher_number') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('journal_voucher_number')">
                        {{ errors.journal_voucher_number[0]}}
                    </div>
				</div>
				<div>
				<table class="table">
					<thead>
						<tr style="color:white">
							<td><p class="title">Deposit Account</p></td>
							<td><p class="title">Client ID</p></td>
							<td><p class="title">Name</p></td>
							<td><p class="title">Amount</p></td>
						</tr>
					</thead>
					<tbody v-if="forInterestPosting"  style="color:white">
						<tr v-for="account in form.accounts"  :key="account.id">
							<td>{{account.type.name}}</td>
							<td>{{account.client.client_id }}</td>
							<td>{{account.client.firstname + ' ' + account.client.lastname}}</td>
							<td>{{format(account.accrued_interest)}}</td>							
						</tr>
						<tr style="border:none">
							<td></td>
							<td></td>
							<td class="text-right" style="mr-5">Total: </td>
							<td> {{totalAmount()}} </td>
						</tr>
					</tbody>
					<tbody v-else style="color:white">
						<tr v-for="account in form.accounts"  :key="account.id">
							<td>{{account.type.name}}</td>
							<td>{{account.client.client_id }}</td>
							<td>{{account.client.firstname + ' ' + account.client.lastname}}</td>
							<td>{{format(account.amount)}}</td>							
						</tr>
						<tr style="border:none">
							<td></td>
							<td></td>
							<td class="text-right" style="mr-5">Total: </td>
							<td> {{totalAmount()}} </td>
						</tr>
					</tbody>
				</table> 
				</div>
		        <button type="button" class="btn btn-primary float-right"  @click="depositAll">Submit</button>
		        <button type="button" class="btn btn-warning float-right mr-2" @click="cancelModal">Cancel</button>
		    </form>
		</b-modal>
    </div>
</template>

<script>

import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from './PaginatorComponent';
import ProductComponent from './ProductSelectComponent';
import vueDebounce from 'vue-debounce'

Vue.use(vueDebounce, {
  listenTo: 'input'
})

import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
	props: ['transaction'],
    data(){
        return {
			office_id: "",
			lists: [],
			errors: {},
            hasRecords: false,
            isLoading:false,
            query:"",
			toClient: '/client/',
			product_id:null,
			account_ids:[],
			accounts_amount:[],
			selected: [],
			check_all:false,
			form: {
				office_id:"",
				accounts : [],
				payment_method: null,
				repayment_date: null,
				type: null,
			},
			variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
			background:'dark',
			modal:{
				modalState:false,
				modal_title:null,
			},
        }
    },
    components:{
        Loading,
        ProductComponent,
    },	
    methods : {
		hasError(field){
			return this.errors.hasOwnProperty(field)
		},
		inputDisabled(id){
			var res = true;
			this.form.accounts.filter(x=>{
				if(x.id ==id){
					res = false
				}
			})
			return res;
		},
		errorAddClass(account_id){
			if(this.hasInputError(account_id)){
				return 'is-invalid'
			}
			return '';
		},
		inputErrorMessage(account_id){
			var index = this.form.accounts.findIndex(x=> {return x.id ==account_id})
			if(index < 0) {	
				return '';
			}
			if(this.errors['accounts.'+index+'.amount']!=undefined){
				return this.errors['accounts.'+index+'.amount'][0];
			}

		},
		getKeyOfSelectedID(account_id){
		
			return this.account_ids.indexOf(account_id)

		},
		hasInputError(account_id){
			
			var index = this.form.accounts.findIndex(x=> {return x.id ==account_id})
			if(index < 0) {	
				return false;
			}
			if(this.errors['accounts.'+index+'.amount']!=undefined){
				return true
			}

			return false;
		},
		finalizeData(){
			this.form.accounts.forEach(item=>{
				item['repayment_date'] = this.form.repayment_date
			})
		},	
		depositAll(){
			this.finalizeData()
			axios.post(this.postUrl,this.form)
			.then(res=>{
				this.errors = [];
					Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Transaction Successful</span> ',
                        html: 
						`
						<table class="table table-condensed">
						<tbody>
						<thead>
							<th class="text-right" style="width:50%;font-weight:900" >Particulars</th>
							<th class="text-left" style="width:50%;font-weight:900">Value</th>
						</thead>
						<tr>
							<td class="text-right pr-2" >Office: </td>
							<td class="text-left" style="font-weight:900">`+res.data[0].office+`</td>
						</tr>
						<tr>
							<td class="text-right pr-2 " >Payment Method: </td>
							<td class="text-left" style="font-weight:900">`+res.data[0].payment_method+`</td>
						</tr>
						<tr>
							<td class="text-right pr-2 " ># of Accounts: </td>
							<td class="text-left" style="font-weight:900">`+res.data[0].accounts_total+`</td>
						</tr>
						<tr style="border:none">
							<td class="text-right pr-2" > Total Amount: </td>
							<td class="text-left" style="font-weight:900"> `+res.data[0].total_amount+`</td>
						</tr>
						</tbody>
						</table>
						`,
                        confirmButtonText: 'OK'
					}).then(res=>{
						location.reload()
					})
					
			})
			.catch(err => {
				this.isLoading = false
				this.errors = err.response.data.errors
				Swal.fire(
					'Error',
					'Check input errors',
					'error'
				)
			})
		},
		cancelModal(){
			this.modal.modalState = false
		},
		format(value){
			return '₱ ' + numeral(value).format('0,0.00')
		},
		checked(account,event){
			this.removeFromArray(this.form.accounts, account.id)
			// this.account_ids = []
			if(event.target.checked){
				this.form.accounts.push(account)
			}

		},
		removeFromArray(obj, id){
			
			if(obj !== undefined){
				var res = obj.filter(item=>{
					return item.id != id;
				})
				this.form.accounts = res
			}	
		},
		isInFormAccounts(id){
			
			var res = this.form.accounts.filter(item=>{
				
				if(item.id == id){
					return item
				}
			})
			
			return res.length > 0
		},
		showModal(){
			this.modal.modalState =true
			this.modal.modal_title= 'Bulk - '+this.transactionTypeDisplay
			
		},
		paymentSelected(value){
				this.form.payment_method = value['id']
		},
		amountEncoded(value){
			
			var amount = value['amount'];
			var account_id = value['id'];
	
			if(this.isInFormAccounts(account_id)){
				var index = this.form.accounts.findIndex(x=> {return x.id ==account_id} )
				this.form.accounts[index].amount = amount
			}

		},
		accountOnList(id){
			return this.form.accounts.filter(x => { return x.id == id }).length > 0
		},
        isReadOnly(client_id){
			if(this.account_ids == undefined){
				return true;
			}
			if(this.account_ids.includes(client_id)){
				return false;
			}
			return true;

          
		},
		getObject(account_id){
			return form.accounts.filter(item => {return item == account_id})
		},
        clientLink(client_id){
            return this.toClient + client_id
        },
        inputSearch(){
            this.fetch()
        },
        filter(){
            if(this.office_id == null){
                return Swal.fire(
                    '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Alert!</span>',
                    'Please select level',
                    'error',
                )
            }
            this.fetch()
        },
        assignOffice(value){
			this.office_id = value['id']
			this.form.office_id = value['id']
            
        },
        checkIfHasRecords(){
            this.hasRecords = false
            if (this.viewableRecords > 0){
                this.hasRecords = true
            }
            
        },
        noOfficeSelected(){
            if(this.office_id == null){
                return true
            }
            return false
		},
		productSelected(value){
			this.product_id = value['id'];
		},
		submit(){
			this.isLoading = true;
			if(this.office_id=="" || this.product_id == ""){
				this.isLoading = false;
				return;
			}

			this.fetch()
			this.isLoading = false;
		},
        fetch(page){
			this.checkedAccounts = [];

			if($('#check_all').prop('checked')){
				$('#check_all').click()
			}
			
			if(!this.checkForm()){
				return;
			}
			this.account_ids = []
			this.form.accounts = []
			
            this.isLoading =true
            if(page==undefined){
                axios.get(this.queryString)
                .then(res => {
					this.lists = res.data.accounts
                    this.checkIfHasRecords()
                    this.isLoading =false
                })
            }else{
                axios.get(this.queryString+'&page='+page)
                .then(res => {
                    this.lists = res.data.accounts
                    this.checkIfHasRecords()
                    this.isLoading =false
                })
            }

        },
        url(page=1){
            return `/clients/list?office_id=`+this.office_id+`&page=`+page
		},
		checkForm(){
			if(this.formPassed){
				return true
			}
			return false;
		},
		checkAll($event){
			if($event.target.checked){
				$('.item_checkbox').each(function(){
					if(!$(this).prop('checked')){
						$(this).click()
					}
				})
			}else{
				$('.item_checkbox').each(function(){
					if($(this).prop('checked')){
						$(this).click()
					}
				})
			}
			
		},
		totalAmount(){
			var total = 0;
			
			// if(this.form.accounts.length > 0 ){
			// 	if(this.forInterestPosting){
			// 		this.form.accounts.forEach((x)=>{
			// 			total = total + (x.accrued_interest)	
			// 			// total = total + (x.accrued_interest)
			// 		})
			// 	}else{
			// 		this.form.accounts.forEach((x)=>{
			// 			total = total + (x.amount)
			// 			// total = total + (x.amount)
			// 		})
					
					
			// 	}
			// }
			var arr = []
			if(this.form.accounts.length > 0){
				if(this.forInterestPosting){
					this.form.accounts.map(x =>{
						total = total + parseFloat(x.accrued_interest)
					})
				}else{
					this.form.accounts.forEach(x=>{
						total = total + parseFloat(x.amount)
					})

				}
			}

			return this.format(total);
		},
        
    },
    computed : {
		transactionType(){
			return (this.transaction.split('.'))[this.transaction.split('.').length-1]
		},
		forInterestPosting(){
			if(this.transactionType == "post_interest"){
				return true
			}
			return false
		},
		transactionTypeDisplay(){
			return this.transactionType.replace("_"," ").toUpperCase()
		},
		postUrl(){
			return '/bulk/'+this.transactionType
		},
        queryString(){
            var str ="?"
            var params_count=0
            if(this.office_id!=""){
                params_count++
                str+="office_id="+this.office_id
            }
            if(this.product_id!=null){
                params_count++
                if(params_count > 1){
                    str+="&product_id="+this.product_id
                }else{
                    str+="product_id="+this.product_id
                }
            }
            if(this.query!=""){
                params_count++
                if(params_count > 1){
                    str+="&query="+this.query
                }else{
                    str+="query="+this.query
                }
            }else{
				str = str.replace('query','')
			}
            return '/deposits'+str
        },
        totalRecords(){

			if(this.lists == []){
				return 0;
			}
            return numeral(this.lists.total).format('0,0')
        },
        viewableRecords(){
            return Object.keys(this.lists).length
		},
		formPassed(){
			if(this.product_id != null && this.office_id != null){
				return true;
			}	
			return false;
		},
		depositSummary(){
			if(this.form.accounts.length > 0){
				var total = 0 
				this.form.accounts.forEach(item=>{
					total += parseInt(item['amount'])
				});
				return total;
			}
			return 0;
		},
		hasErrors(){
			return Object.keys(this.errors).length > 0;
		},




	},
	mounted(){
		this.form.type = this.transactionType
	}
}
</script>