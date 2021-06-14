<template>
    <div>
        <div class="row">
            <div class="col-lg-12">
                <label for="" style="color:white" class="lead mr-2">Filter:</label>
                <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" v-model="office_id"></v2-select>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="product_id" > Product </label>
                    <loan-product-list id="product_id" @selected="selected"></loan-product-list>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group">
                    <label for="disbursement_date">Disbursement Date</label> 
                    <input type="date" class="form-control" v-model="form.disbursement_date">
                </div>
                <div class="form-group">
                    <label for="repayment_date">First Repayment Date</label> 
                    <input type="date" class="form-control" v-model="form.first_payment">
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
             <button class="btn btn-primary" @click.prevent="fetch"> Filter </button>
              

            
        </div>


 
      

        
        <div class="w-100 px-3 mt-6" >
            
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="title"><input type="checkbox" @change="checkAll" v-if="hasRecords"></p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Linked To</p></td>
                        <td ><p class="title">Amount</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <tr v-for="client in lists.data" :key="client.client_id">
                        <td><input type="checkbox" class="checkbox" :id="client.client_id" @change="checked(client,$event)"></td>
                        <td><label :for="client.client_id">{{client.client_id}}</label></td>
                        <td class="text-lg">
                            <a class="text-lg" :href="clientLink(client.client_id)">{{client.firstname + ' ' + client.lastname}}</a>
                            <div class="text-danger" v-if="hasInputError('accounts',client.id,'client_id')">    
                                {{inputErrorMsg('accounts',client.id,'client_id')}}
                            </div>
                        </td>
                        <td class="text-lg">{{client.office.name}}</td>
                        <td class="text-lg" style="max-width:50px">
                          
                            <amount-input :readonly="inputDisabled(client.id) "@amountEncoded="amountEncoded" :add_class="errorInputAddClass('accounts',client.id,'amount')"  :account_info="client" :tabindex="key+1" ></amount-input>
                            <div class="text-danger" v-if="hasInputError('accounts',client.id,'amount')">    
                                {{inputErrorMsg('accounts',client.id,'amount')}}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="lead float-left text-right" style="color:white">Showing Records {{lists.from}} - {{lists.to}} of {{totalRecords}} </p>
            <p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
            <div class="clearfix"></div>
            <paginator :dataset="lists" @updated="fetch"></paginator>
        </div>

            <button class="btn btn-primary" @click.prevent="review">Create</button>
            <loading :is-full-page="true" :active.sync="isLoading" ></loading>
        <b-modal id="loan-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modal_title" :header-bg-variant="background" :body-bg-variant="background"  v-if="form.accounts.length > 0">
            <form>
                <table class="table">
                    <thead style="color:white">
                        <tr>
                            <td><p class="title">Client ID</p></td>
                            <td><p class="title">Name</p></td>
                            <td><p class="title">Loan Amount</p></td>
                        </tr>
                    </thead>
                    <tbody style="color:white">
                        <tr v-for="(item, key) in form.accounts" :key="key">
                            <td><p class="title">{{item.client_id}}</p></td>
                            <td><p class="title">{{item.full_name}}</p></td>
                            <td><p class="title">{{moneyFormat(item.amount)}}</p></td>
                        </tr>
                        <tr>
                            <td><p class="title"># of Accounts: </p></td>
                            <td><p class="title">{{form.accounts.length}}</p></td>
                            <td><p class="title">{{moneyFormat(totalLoanAmount())}}</p></td>
                        </tr>
                    </tbody>
                </table>
                
                <button type="button" class="btn btn-primary float-right" @click="submit" >Submit</button>
                <button type="button" class="btn btn-warning float-right mr-2" >Cancel</button>
            </form>
        </b-modal>
    </div>

</template>

<script>

import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from './PaginatorComponent';
import vueDebounce from 'vue-debounce'

Vue.use(vueDebounce, {
  listenTo: 'input'
})

import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';
import AmountInputComponent from './AmountInputComponent.vue';
import LoanProduct from './Settings/LoanProduct.vue';


export default {
    data(){
        return {
            code: null,
            errors: {},
            loan_product_id:null,
            office_id: "",
            lists: [],  
            isLoading:false,
            query:"",
            installment_list: [],
            toClient: '/client/',
            form  : {
                accounts : [],
                loan_id:null,
                disbursement_date:null,
                first_payment:null,
                interest_rate:null,
            },
            page: 1,
            key: 1,
            rates: [],
            variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
			background:'dark',
			modal:{
				modalState:false,
				modal_title: 'Review Loan Accounts',
            },
        }
    },
    components:{
        Loading,
        AmountInputComponent,
    },
    methods :{
        review(){
            this.modal.modalState = true
        },
        checkAll(e){
            
            if(e.target.checked){
                $('.checkbox').each(function(k,v){
                    if($(v).prop('checked')!=true){
                        $(v).click()
                    }
                })
            }else{
                $('.checkbox').each(function(k,v){
                    if($(v).prop('checked')){
                        $(v).click()
                    }
                })
            }
            
        },
        submit(e){
            e.preventDefault()
            this.isLoading=true
            
            axios.post('/wApi/bulk/create/loans',this.form)
            .then(res=>{
                this.isLoading = false
                Swal.fire(
					'Success',
					res.data.msg,
					'success'
                )
               .then(()=>{
                    // location.reload()
                })
            })
            .catch(err=>{
                this.isLoading = false
                
                this.errors = err.response.data.errors
                Swal.fire(
					'Error',
					'Check input errors',
					'error'
				)
            })
        },
        checked(account,event){
            if(event.target.checked){
                this.form.accounts.push(account)
            }else{
                this.form.accounts = this.form.accounts.filter(x=>{
                    return x.id != account.id
                })
            }
            
        },

		errorInputAddClass(array_name,account_id,field){
            if(this.hasInputError(array_name,account_id,field)){
                return 'is-invalid'
            }
            return;
        },
        selected(e){
            this.code = e.code
            this.loan_product_id = e.id
            this.installment_list = e.rates
            this.form.loan_id = e.id
        },
        amountEncoded(value){
            
			var amount = value['amount'];
            var account_id = value['client_id'];
            this.form.accounts.map(x=>{
                if(x.client_id == account_id){
                    x.amount = amount
                }
            })
			// if(this.isInFormAccounts(account_id)){
			// 	var index = this.form.accounts.findIndex(x=> {return x.id ==account_id} )
			// 	this.form.accounts[index].amount = amount
			// }

		},

		inputDisabled(id){
            var res = true;
			this.form.accounts.filter(x=>{
                if(x.id == id){
                    res = false;
                }
			})
            return res;
		},        
        clientLink(client_id){
            return this.toClient + client_id
        },


        assignOffice(value){
            this.office_id = value['id']
        },
        
        hasInputError(array_name,account_id,field){

            var index = this.form[array_name].findIndex(x=> {return x.id ==account_id});
            if(index >= 0 ){
                var str =  array_name + '.' + index + '.' + field
                
                return this.errors.hasOwnProperty(str);
                
            }
            return false;
        },

        inputErrorMsg(array_name, account_id, field){
            var index = this.form[array_name].findIndex(x=> {return x.id ==account_id});

            if(this.hasInputError(array_name,account_id,field)){
                var errors = this.errors[array_name + '.' + index + '.' + field]

                return errors[0]
            }
            

        },

        fetch(){
            this.isLoading = true;
            this.form.accounts = []
            axios.get(this.queryString+'&page='+this.page)
            // axios.get()
            .then(res => {
                this.lists = res.data
                // this.checkIfHasRecords()
                this.isLoading =false
            })

        },
        moneyFormat(value){
            return moneyFormat(value)
        },
        url(page=1){
            return `/clients/list?office_id=`+this.office_id+`&page=`+page
        },
        totalLoanAmount(){
            let total = 0;
            if(this.form.accounts.length > 0){
                this.form.accounts.map((x)=>{ total+= parseInt(x.amount)})
                return total;
            }
            return total;
        },
        
        
    },
    computed : {
        
        hasRecords(){
            return this.lists.hasOwnProperty('data');
        },
        queryString(){
            var str ="?limited=true&"
            var params_count=0
            if(this.office_id!=""){
                params_count++
                str+="office_id="+this.office_id
            }
            if(this.query!=""){
                params_count++
                if(params_count > 1){
                    str+="&search="+this.query
                }else{
                    str+="search="+this.query
                }
            }
            return '/wApi/client/list'+str
        },
        totalRecords(){
            return numeral(this.lists.total).format('0,0')
        },
        viewableRecords(){
            return Object.keys(this.lists.data).length
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