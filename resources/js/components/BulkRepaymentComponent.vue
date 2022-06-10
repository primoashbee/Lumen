<template>
    <div>
        <form @submit.prevent="fetch">
            <div class="row">

                <div class="col-4">
                    <label for="" style="color:white" class="lead">Filter:</label>
                    <v2-select @officeSelected="assignOffice"></v2-select>
                </div>
                <div class="col-3">
                    <label for="date" style="color:white"  class="lead" >Date</label>
                    <input type="date" id="date" class="form-control" v-model="request.date" />
                </div>
                <div class="col-4">
                    <label for="" style="color:white" class="lead">Loan Product</label>
                    <products @productSelected="loanProductSelected" list="loan" status="1" multi_values="false"></products>
                </div>
                <div class="col-4">
                    <label for="" style="color:white" class="lead">Deposit</label>
                    <products @productSelected="depositProductSelected" list="deposit" status="1" multi_values="true"></products>
                </div>
                <div class="col-1">
                    <button class="btn btn-primary mt-4">Filter</button>
                </div>
                <div class="col-1" v-if="hasRecords">
                    <button class="btn btn-primary mt-4" @click.prevent="download">Print CCR</button>
                </div>
            </div>
        </form>

        <div class="w-100 px-3 mt-6" >
            <table class="table table-striped">
                <thead>
                    <tr>
                        <td><p class="title"><input type="checkbox" @change="checkall($event)"></p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <template v-if="_hasLoan">
                            <td><p class="title">Loan</p></td>
                            <td ><p class="title">Overdue</p></td>
                            <td><p class="title">Inst Due</p></td>
                            <td><p class="title">Total Due</p></td>
                            <td><p class="title">Payment</p></td>
                        </template>
                        <template v-if="_hasDeposit" >
                            <template v-for="(item, key) in _depositList">
                                <td><p class="title">{{item.code}} - Balance</p></td>
                                <td><p class="title">{{item.code}}</p></td>
                            </template>
                        </template>
                    </tr>
                </thead>
                <tbody v-if="this.hasRecords">
                    <tr v-for="(item, key) in list.loan_accounts" :key="item.id">
                        <td><input type="checkbox" class="checkbox" :id="item.id" @change="checked(item,$event)"></td>
                        <td><label :for="item.id"><p class="title"> {{item.client_id}}</p></label></td>
                        <td><a :href="clientLink(item.client_id)"><p class="title"> {{item.client.full_name}}</p></a></td>
                        <template v-if="_hasLoan">
                            <td><a :href="accountLink(item.client_id,item.id)"><p class="title"> {{item.product.code}} </p></a></td>
                            <td><p class="title"> <span class="badge badge-pill badge-danger">{{item.overdue._total}}</span> </p></td>
                            <td><p class="title"> <span class="badge badge-pill badge-dark">{{item.due._total}}</span></p></td>
                            <td><p class="title"> <span class="badge badge-pill badge-info">{{item.total_due.formatted_total}}</span></p></td>
                            <td>
                                <amount-input :readonly="inputDisabled(item.id, 'loan') 
                                    "@amountEncoded="amountEncoded($event,'loan',item.id)"  
                                    :account_info="item" 
                                    :tabindex="tabIndex('loan',key)" 
                                    :add_class="errorAddClass(item.id)"
                                    >
                                </amount-input>
                                <div class="text-danger" v-if="hasInputError(item.id)">
									{{inputErrorMessage(item.id,['amount','repayment_date'],'loan')}}
								</div>
                            </td>
                        </template>
             
                        <template v-if="_hasDeposit" >
                            
                            <template v-for="(_item, key) in item.client.deposits" >

                                <td><p class="title">{{_item.balance_formatted}}</p ></td>                                
                                <td>
                                    <amount-input 
                                        :readonly="inputDisabled(_item.id,'deposit') 
                                        "@amountEncoded="amountEncoded($event,'deposit',_item.id)"  
                                        :account_info="_item" 
                                        :tabindex="tabIndex('loan',key)" 
                                        :add_class="errorAddClass(item.id,'deposit',_item.id)"
                                    >
                                    </amount-input>
                                    <div class="text-danger" v-if="hasInputError(item.id,'deposit',_item.id)" >
                                        {{inputErrorMessage(item.id,['amount','repayment_date'],'deposit',_item.id)}}
                                    </div>
                                </td>
                                    
                            </template>
                        </template>
                    </tr>

        
                </tbody>

            </table>

        </div>

        <button class="btn btn-primary" @click="submit($event)"> Submit </button>

        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
		<b-modal id="deposit-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modal_title" :header-bg-variant="background" :body-bg-variant="background" >
		    <form>
		        <div class="form-group mt-4">
		        	<label class="text-lg">Branch</label>
                    <v2-select @officeSelected="assignOfficeForm" list_level="branch" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="hasError('office_id')">
                        {{ errors.office_id[0]}}
                    </div>
		        </div>
		        <div class="form-group">
		        	<label class="text-lg">Payment Method</label>
					<payment-methods payment_type="for_repayment" @paymentSelected="paymentSelected" v-bind:class="hasError('payment_method') ? 'is-invalid' : ''" ></payment-methods>
					<div class="invalid-feedback" v-if="hasError('payment_method')">
                        {{ errors.payment_method[0]}}
                    </div>
		        </div>
		        <div class="form-group">
		        	<label class="text-lg">Repayment Date</label>
                    <input type="date" class="form-control" v-model="form.repayment_date" v-bind:class="hasError('repayment_date') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('repayment_date')">
                        {{ errors.repayment_date[0]}}
                    </div>
				</div>
		        <div class="form-group">
		        	<label class="text-lg">OR #:</label>
                    <input type="text" class="form-control" v-model="form.receipt_number" v-bind:class="hasError('receipt_number') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('receipt_number')">
                        {{ errors.receipt_number[0]}}
                    </div>
				</div>
		        <div class="form-group">
		        	<label class="text-lg">Notes</label>
                    <!-- <input type="text" class="form-control" v-model="form.receipt_number" v-bind:class="hasError('receipt_number') ? 'is-invalid' : ''"> -->
                    <textarea name="" id="" class="form-control" v-model="form.notes" v-bind:class="hasError('notes') ? 'is-invalid' : ''"></textarea>
					<div class="invalid-feedback" v-if="hasError('notes')">
                        {{ errors.notes[0]}}
                    </div>
				</div>
				<div>
				<table class="table">
                    <thead style="color:white">
                        <tr>
                            <td><p class="title">#</p></td>
                            <td><p class="title">Client ID</p></td>
                            
                            <td><p class="title">Name</p></td>
                            <template v-if="_hasLoan">
                                <td><p class="title">{{loan_product_selected.code}}</p></td>
                            </template>
                            <template v-if="_hasDeposit" >
                                <template v-for="(item, key) in _payment_depositList">
                                    <td><p class="title">{{item.code}}</p></td>
                                </template>
                            </template>
                        </tr>
                    </thead>
                    <tbody v-if="form.accounts.length > 0" style="color:white">
                        <tr v-for="(item, key) in form.accounts" :key="key">
                            <td>{{key+1}}</td>
                            <td>{{item.client_id}}</td>
                            <td>{{item.name}}</td>
                            <template v-if="_hasLoan">
                                <td>{{format(item.loans.amount)}}</td>
                            </template>
                            <template v-if="_hasDeposit">
                                <template v-for="(item, key) in item.deposit">
                                    <td><p class="title">{{format(item.amount)}}</p></td>
                                </template>
                            </template>
                        </tr>

                        <tr style="border-bottom:none">
                            <td></td>
                            <td></td>
                            <td># of Accounts: {{form.accounts.length}}</td>
                            <td>{{format(_total_loan_payment)}}</td>
                            <template v-for="(item,key) in _total_deposit_payment">
                                <td><p class="title">{{format(item.total)}}</p></td>
                            </template>
                        </tr>
                    </tbody>
				</table>
				</div>
		        <button type="button" class="btn btn-primary float-right" @click="makePayment">Submit</button>
		        <button type="button" class="btn btn-warning float-right mr-2" >Cancel</button>
		    </form>
		</b-modal>
    </div>
</template>

<script>

import Loading from 'vue-loading-overlay';
import AmountInputComponent from './AmountInputComponent.vue';

export default {
    components: {
        Loading,
        AmountInputComponent,
    },
    data(){
        return {
            errors : [],
            loan_product_selected : null,
            request : {
                office_id: null,
                date: null,
                loan_product_id:null,
                deposit_products:null,
            },
            form: {
                office_id: null,
                repayment_date: null,
                accounts: [],
                payment_method: null,
                notes: null,
                receipt_number: null,
                jv_number: null,
            },
            list: null,
            isLoading: false,
            url: '/loans/scheduled/list',
			variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
			background:'dark',
			modal:{
				modalState:false,
				modal_title:null,
            },
            tabIndeces: [],
            headers :null
        }
    },
    methods : {
        checkall(e){
            $('.checkbox').each(function(k,v){
                if(e.target.checked){
                    if(!$(v).prop('checked')){
                        $(v).click()
                    }
                }else{
                    if($(v).prop('checked')){
                        $(v).click()
                    }
                }
            })
        },
        download(){
            console.log("eto");
            this.isLoading = true;
            axios.get('/download/ccr',{responseType:'blob'})
                .then(res=>{
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.headers.filename);
                    document.body.appendChild(link);
                    link.click();
                    this.isLoading =false;
                })
        },
        errorAddClass(account_id,type="loan",deposit_account_id){
			if(this.hasInputError(account_id,type,deposit_account_id)){
				return 'is-invalid'
			}
			return '';
		},
        tabIndex(type,key){
            if(type=="loan"){

            }

            if(type=="deposit"){

            }
        },
        format(value){
            return currency +' '+numeral(value).format('0,0.00')
        },
        paymentSelected(value){
            this.form.payment_method = value['id']
            if(value['name']== 'CTLP'){
                this.form.receipt_number  = null
                return this.payment_type = 'NON-CASH';
            }
            this.form.jv_number  = null
            return this.payment_type = 'CASH';
		},
        submit(e){
            e.preventDefault()
            var vm = this
            
            this.form.accounts.map(x=>{
                x.repayment_date = vm.form.repayment_date
            })

            this.modal.modalState = true;
            this.modal.modal_title = 'Bulk Repayment'
        },
        makePayment(){
            var repayment_date = this.form.repayment_date
            this.errors = [];
            this.form.accounts.map((x)=>{
                x.loans.repayment_date = repayment_date
                x.deposit.map((y)=>{
                    y.repayment_date= repayment_date
                })
                
            })
            this.isLoading = true
            axios.post('/bulk/repayments',this.form)
            .then(res=>{
                this.isLoading = false
                if(res.status==200){
                    Swal.fire(
                        'Success',
                        res.data.msg,
                        'success'
                    )
                    this.reset
                }
            })
            .catch(err=>{
                this.isLoading = false
                this.errors = err.response.data.errors
            })
        },
        hasInputError(account_id,type="loan",deposit_account_id=null){
            
            var account_index = this.form.accounts.findIndex(x=> {return x.id ==account_id})

            if(account_index < 0) {	
                return false;
            }
            if(type=="loan"){   
                if(this.errors['accounts.'+account_index+'.loans.amount']!=undefined){
                   return true;
                }
                if(this.errors['accounts.'+account_index+'.loans.repayment_date']!=undefined){
                    return true;
                }
                if(this.errors['accounts.'+account_index+'.loans.id']!=undefined){
                    return true;
                }
                return false;
            }
            
            var deposit_account_index = this.form.accounts[account_index].deposit.findIndex(x=>{
                return x.deposit_account_id == deposit_account_id;
            })
            
            // this._depositList.map(x=>{
            if(this.errors['accounts.'+account_index+'.deposit.'+ deposit_account_index + '.deposit_account_id']!=undefined){
                return true
            }
            if(this.errors['accounts.'+account_index+'.deposit.'+ deposit_account_index + '.amount']!=undefined){
                return  true
            }
            if(this.errors['accounts.'+account_index+'.deposit.'+ deposit_account_index + '.repayment_date']!=undefined){
                return true
            }
        },

        inputErrorMessage(account_id,fields,parent_field,deposit_account_id){
            if(this.hasInputError(account_id)){
                var msg = null;
                if(parent_field == "loan"){
                    
                    var account_index = this.form.accounts.findIndex(x=> {return x.id ==account_id})
                    fields.map(x=>{
                        var index = this.errors['accounts.'+account_index+'.loans.'+x]
                        if(index !=undefined){
                            return msg = index[0]
                        }
                    })  
                }
            }
            if(this.hasInputError(account_id,'deposit',deposit_account_id)){
                if(parent_field == "deposit"){
                    var account_index = this.form.accounts.findIndex(x=> {return x.id ==account_id})
                    var deposit_account_index = this.form.accounts[account_index].deposit.findIndex(x=>{
                        return x.deposit_account_id == deposit_account_id;
                    })
                    fields.map(field=>{
                        var index = this.errors['accounts.'+account_index+'.deposit.'+deposit_account_index+'.'+field]
                        if(index !=undefined){
                            msg = index[0]
                        }
                    })
                }
            }
            return msg;

            
            
        },
        hasError(field){
            return this.errors.hasOwnProperty(field)
        },
        assignOffice(value){
            this.request.office_id = value['id']
        },
        assignOfficeForm(value){
            this.form.office_id = value['id']
        },
        loanProductSelected(value){
            this.request.loan_product_id = value['id'];
            this.loan_product_selected = value;
            this.form.accounts = []
            this.list = null
            
        },
        depositProductSelected(value){
            var list = []
            value.map(x=>{
                var obj = {
                    id: x.id,
                    code: x.code
                }
                list.push(obj)
            })
            this.request.deposit_products = list
            this.form.accounts = []
            this.list = null
        },
        fetch(){
            if(this.canFetch){ 
                this.isLoading = true;
                this.list = null
                this.errors = []
                axios.post(this.url,this.request)
                .then(res=>{
                    this.isLoading= false;
                    this.list = res.data.list
                })
                .catch(err=>{
                    this.isLoading= false;
                })
            }
        },
        accountLink(client_id,id){
            return '/client/'+client_id+'/loans/'+id;
        },
        clientLink(client_id){
            return '/client/'+client_id
        },
        repaymentInfo(item){
            return {
                id: item.id,
                code: item.product.code,
                name: item.client.full_name,
                client_id: item.client_id,
                amount: null
            }
        },
		checked(account,event){
            
            var deposit = []
            
            account.client.deposits.map(x=>{
                
                deposit.push({
                    deposit_account_id: x.id,
                    amount: null,
                    code: x.type.product_id,
                    deposit_type_id: x.deposit_id,
                    repayment_date: null,
                   
                })
                            
            })
            var obj ={
                id: account.id,
                client_id: account.client_id,
                name: account.client.full_name,
                loans : {
                    id: account.id,
                    amount: null,
                    code: account.product.code,
                    total_amount: round(account.total_due.total),
                    repayment_date:null
                },
                deposit: deposit
            }
			this.removeFromArray(this.form.accounts, account.id)
			if(event.target.checked){
				this.form.accounts.push(obj)
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
        removeFromArray(obj, id){
			if(obj !== undefined){
				var res = obj.filter(item=>{
					return item.id != id;
				})
				this.form.accounts = res
			}	
        },
        inputDisabled(id,type){
            var res = true;
            if(type=="loan"){
                this.form.accounts.map(x=>{
                    if(x.id == id){
                        res = false;
                    }
                })
                return res
            }
            if(type=="deposit"){
                this.form.accounts.map(x=>{
                    x.deposit.map(y=>{
                        if(y.deposit_account_id == id){
                            res = false;
                        }
                    })
                })
                return res
            }
            return res;
        },

		amountEncoded(value, type, item_id){
			
			var amount = value['amount'];

            var client_id = value['client_id']
            
            if(type=="loan"){
                this.form.accounts.map(x=>{
                    if(x.client_id == client_id){
                        if(x.loans.id==item_id){
                            x.loans.amount = amount
                        }
                    }
                })
            }


            if(type=="deposit"){
                var deposit_id = value['id']
                this.form.accounts.map(x=>{
                    if(x.client_id == client_id){
                        x.deposit.map(y=>{
                            if(y.deposit_account_id == deposit_id){
                                y.amount = amount;
                            }
                        })
                    }
                })
                
            }


        },
        
        moneyFormat(amount){
            return numeral(amount).format('0,0.00')
        }
    },
    computed : {
        _product_selected(){

        },
        _payment_depositList(){
            var list = [];
            
            if(this.form.accounts.length > 0){
                if(this.form.accounts[0].deposit.length > 0 ){
                    this.form.accounts[0].deposit.map(x=>{
                        var obj = {
                            id: x.deposit_type_id,
                            code: x.code
                        }
                        list.push(obj)
                        list = list.sort((a,b)=> a.id - b.id)
                    })
                }

            }
        
            return list;
        },
        _depositList(){
            var list = [];
            if(this._hasDeposit){
                
                this.request.deposit_products.map(x=>{

                    var obj = {
                        id: x.id,
                        code: x.code
                    }
                    list.push(obj)
                    list  = list = list.sort((a,b)=> a.id - b.id)
                })
                return list;
            }
            return list;
        },
        _hasLoan(){
           if(this.request.loan_product_id !== null){
               return true;
           }
           return false;
        },
        _hasDeposit(){
            
            if(this.request.deposit_products === null){
                return false;
            }
            return this.request.deposit_products.length > 0;
        },
     
        canFetch(){
            
            var check = 0;
            Object.keys(this.request).map(x=>{
                if(x != 'deposit_products'){
                    if(this.request[x] === null){
                        check++;
                    }
                }

            })

            return check == 0;
        },
        totalAccounts(){
            if(this.list === null){
                return 0;
            }
            return this.list.loan_accounts.length;
        },
        hasRecords(){
            if(this.list === null){
                return false;
            }
            return this.totalAccounts > 0
        },
        totalAmountToBePaid(){
            var total = 0;
            this.form.accounts.map(x=>{
                total+=x.amount;
            })
            return currency + ' ' +numeral(total).format('0,0.00');
        },
        currency(){
            return window.currency;
        },
        _total_loan_payment(){
            var total = 0;
            if(this.form.accounts.length > 0){
                this.form.accounts.map(x=>{
                    total += x.loans.amount
                })
            }
            return total;
        },
        _total_deposit_payment(){
            
            var summary = [];
            if(this.form.accounts.length > 0){
                if(this._hasDeposit){
                    this._payment_depositList.map(x=>{
                        var total=0;
                        this.form.accounts.map(acc=>{
                            acc.deposit.map(dep => {
                                if(dep.deposit_type_id == x.id){
                                    total += dep.amount
                                }
                            })

                        })
                        var obj =  {
                            type: x,
                            total: total
                        }
                    
                        summary.push(obj)
                    })
                }
            }
            return summary;
        },
    }
}
</script>