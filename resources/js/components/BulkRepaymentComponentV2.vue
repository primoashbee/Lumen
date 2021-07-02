<template>
    <div>
        <form @submit.prevent="fetch">
            <loading :is-full-page="true" :active.sync="isLoading" ></loading>

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
                <div class="col-4 mt-4">
                    <label for="" style="color:white" class="lead">Deposit</label>
                    <products @productSelected="depositProductSelected" list="deposit" status="1" multi_values="true"></products>
                </div>
                <div class="w-100 pl-3">
                    <button class="btn btn-primary mt-12">Filter</button>
                </div>
                <div class="col-1" v-if="hasRecords">
                    <button class="btn btn-primary mt-4" @click.prevent="download">Print CCR</button>
                </div>
            </div>

            <div class="w-100 px-3 mt-6" >
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <td><p class="title"><input type="checkbox" @change="checkedAll($event)"></p></td>
                            <td><p class="title">Client ID</p></td>
                            <td><p class="title">Name</p></td>
                
                            <td><p class="title">Loan</p></td>
                            <td ><p class="title">Overdue</p></td>
                            <td><p class="title">Inst Due</p></td>
                            <td><p class="title">Total Due</p></td>
                            <td><p class="title">Payment</p></td>
                
                            <template v-if="_hasDeposit" >
                                <template v-for="(item, key) in request.deposit_product_ids">
                                    <td><p class="title">{{item.code}} - Balance</p></td>
                                    <td><p class="title">{{item.code}}</p></td>
                                </template>
                            </template>
                        </tr>
                    </thead>
                    <tbody v-if="hasRecords">
                        <tr v-for="(item, key) in list" :key="key">
                            <td> <input type="checkbox" class="repayment_checkbox" :id="'client_'+item.client_id" @change="addToList(item,$event)"></td>
                            <td> <label :for="'client_'+item.client_id">{{item.client_id}} </label></td>
                            <td> <a :href="clientLink(item.client_id)">{{item.fullname}}</a></td>
                            <td> <a :href="loanLink(item.client_id, item.loan_account_id)">{{item.loan_code}}</a> </td>
                            <td> <span class="badge badge-pill badge-danger">{{moneyFormat(item.overdue_due)}}</span></td>
                            <td> <span class="badge badge-pill badge-dark">{{moneyFormat(item.installment_due)}} </span></td>
                            <td> <span class="badge badge-pill badge-primary">{{moneyFormat(item.due_due)}}</span> </td>
                            <td>
                                <amount-input-component :add_class="hasAmountInputErrorClass(item,{'type':'loan','fields': ['amount','repayment_date']})" :account_info="inputFormat(item,'loan')" :readonly="readonly(item)" @amountEncoded="loanAmountEncoded"></amount-input-component>
                                <div class="text-danger" v-if="hasAmountInputError(item,{'type':'loan','fields': ['amount','repayment_date']}).hasError">    
                                    {{amountInputErrorMsg(item,{'type':'loan','fields': ['amount','repayment_date']})}}
                                </div>
                            </td>
                            
                            <template v-if="_hasDeposit">
                                <template v-for="deposit in request.deposit_product_ids">
                                    <td><span class="badge badge-pill badge-success">{{moneyFormat(item[deposit.code])}}</span></td>
                                    <td>
                                        <amount-input-component :add_class="hasAmountInputErrorClass(item,{'type':'deposit', 'id':deposit.id, 'fields': ['amount','repayment_date']})" :account_info="inputFormat(item,'deposit',deposit.id)" :readonly="readonly(item)" @amountEncoded="depositAmountEncoded"></amount-input-component>
                                        <div class="text-danger" v-if="hasAmountInputError(item,{'type':'deposit','id':deposit.id,'fields': ['amount','repayment_date']}).hasError">
                                            {{amountInputErrorMsg(item,{'type':'deposit','id':deposit.id,'fields': ['amount','repayment_date']})}}
                                        </div>    
                                    </td>
                                    <!-- {{tabIndex()}} -->
                                </template>
                            </template>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary" @click="submit" v-if="form.accounts.length > 0"> Make Payment</button>
            </div>

            <b-modal id="deposit-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modal_title" :header-bg-variant="background" :body-bg-variant="background"  v-if="form.accounts.length > 0">
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
                        <payment-methods payment_type="for_repayment" @paymentSelected="paymentSelected" v-bind:class="hasError('payment_method_id') ? 'is-invalid' : ''" ></payment-methods>
                        <div class="invalid-feedback" v-if="hasError('payment_method_id')">
                            {{ errors.payment_method_id[0]}}
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
                                <td><p class="title">{{loan_product_selected.code}}</p></td>
                                
                            <template v-if="_hasDeposit" >
                                <template v-for="(item, key) in request.deposit_product_ids">
                                    <td><p class="title">{{item.code}}</p></td>
                                </template>
                            </template>
                            </tr>
                        </thead>
                        <tbody style="color:white">
                            <tr v-for="(item, key) in form.accounts" :key="key">
                                <td>{{key+1}}</td>
                                <td>{{item.client_id}}</td>
                                <td>{{item.fullname}}</td>
                                <td>{{moneyFormat(item.loan.amount)}}</td>
                                <template v-if="_hasDeposit" >
                                    <template v-for="(item, key) in item.deposits">
                                        <td><p class="title">{{moneyFormat(item.amount)}}</p></td>
                                    </template>
                                </template>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td># of Accts: {{this.summary().accounts}}</td>
                                <td>{{moneyFormat(this.summary().loan_payment)}}</td>
                                <template v-if="_hasDeposit" >
                                    <template v-for="(item, key) in this.summary().deposit_summary">
                                        <td><p class="title">{{moneyFormat(item.amount)}}</p></td>
                                    </template>
                                </template>
                            </tr>
                        </tbody>

                    </table>
                    </div>
                    <button type="button" class="btn btn-primary float-right" @click="makePayment">Submit</button>
                    <button type="button" class="btn btn-warning float-right mr-2"  @click="modal.modalState=false">Cancel</button>
                </form>
            </b-modal>
        </form>
    </div>
</template>


<script>
import Loading from 'vue-loading-overlay';

import AmountInputComponent from './AmountInputComponent.vue'
export default {
  components: { AmountInputComponent, Loading },
    data(){
        return {
            request : {
                office_id: null,
                date: null,
                loan_product_id:null,
                deposit_product_ids:[],
            },
            loan_product_selected: null,
            form : {
                accounts : [],
                office_id: null,
                payment_method_id : null,
                repayment_date: null,
                receipt_number: null,
                notes : null
            },
            variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
			background:'dark',
			modal:{
				modalState:false,
				modal_title:null,
            },
            paymentSummary : [],
            list: [],
            errors: [],
            isLoading: false,

        }
    },
    methods : {
        hasAmountInputError(item, obj){
            var index = this.form.accounts.findIndex(x=> {return x.client_id == item.client_id})
            if(index >= 0){
                if(obj.type=='loan'){
                    var error = 'accounts.'+index+'.loan.';
                    var result = {hasError : false, errors : [], type: obj.type}
                    obj.fields.map(field=>{{
                        if(this.errors.hasOwnProperty(error+field)){
                            result.hasError = true;
                            var errorMsg = this.errors[error+field][0]; 
                            result.errors.push({
                                'msg': errorMsg
                            })
                        }
                    }})
                    return result;
                }
                
                if(obj.type =='deposit'){
                        var result = {hasError : false, errors : [], type: 'deposit'}
                        var deposit_index = this.form.accounts[index].deposits.findIndex(x=>{return x.deposit_id == obj.id})
                        var error = 'accounts.'+index+'.deposits.'+deposit_index+'.';
                        obj.fields.map(field=>{{
                        if(this.errors.hasOwnProperty(error+field)){
                            result.hasError = true;
                            var errorMsg = this.errors[error+field][0]; 
                            result.errors.push({
                                'msg': errorMsg
                            })
                        }
                    }})
                    return result;
                }
            }
            return false
        },
        hasAmountInputErrorClass(item,obj){
            return this.hasAmountInputError(item,obj).hasError ? 'is-invalid' : '';
        },
        amountInputErrorMsg(item,obj){
            var index = this.form.accounts.findIndex(x=> {return x.client_id == item.client_id})
            var res = this.hasAmountInputError(item, obj);
            if(res.hasError){
                if(res.type == 'loan'){
                    return res.errors[0].msg;
                }
                if(res.type == 'deposit'){
                    var str = 'accounts.' + index;
                    var deposit_index = this.form.accounts[index].deposits.findIndex(x=>{return x.deposit_id == obj.id})
                    return res.errors[0].msg
                }
            }
        },
        hasError(input){
            return this.errors.hasOwnProperty(input)
        },
        paymentSelected(value){
            this.form.payment_method_id = value['id']
        },
        resetTable(){
            this.list = []
            this.errors = []
            this.indexStart = 0;
            this.form.accounts = []
            this.form.loan_payments = []
            this.form.deposit_payments = []


        },
        summary(){
            var loan_payment = 0;
            var deposit_summary = [];
            this.request.deposit_product_ids.map(x=>{
                var obj = {
                    id : x.id,
                    code: x.code,
                    amount: 0
                }
                deposit_summary.push(obj)
            })

            this.form.accounts.map(x=>{
                if(x.loan.amount !== null){
                    loan_payment+=parseFloat(x.loan.amount)
                }
                if(this.request.deposit_product_ids.length > 0){
                    x.deposits.map(y=>{
                        var deposit_summary_index = deposit_summary.findIndex(item=>item.id == y.deposit_id)
                        deposit_summary[deposit_summary_index].amount += parseFloat(y.amount)
                    })
                }
            })

            return {
                accounts: this.form.accounts.length,
                loan_payment: loan_payment,
                deposit_summary: deposit_summary
            }
        },
        assignOfficeForm(value){
            this.form.office_id = value['id']
        },
        inputFormat(item,type, deposit_id = null){
            if(type == 'loan'){
                return {
                    client_id: item.client_id,
                    loan_account_id : item.loan_account_id,
                    amount: null
                }
            }
            if(type == 'deposit'){
                
                var data =  {
                    client_id: item.client_id,
                    amount: null,
                    deposit_id: deposit_id,
                    
                }
                
                return data;
            }
        },
        loanAmountEncoded(value,type){
            this.form.accounts.map(x=>{
                if(x.client_id == value['client_id']){
                    x.loan.amount = value['amount']
                }
            })
        },
        depositAmountEncoded(value){
            
            // this.form.deposit_payments.map(x=>{
            //     if(x.client_id == value['client_id']){
            //         var type = this.request.deposit_product_ids.filter(x=>{
            //             return x.id == value['deposit_id'];
            //         })[0]
            
            //     }
            // })
            
            this.form.accounts.map(x=>{

                if(x.client_id == value['client_id']){
                    x.deposits.map(y=>{
                        if(y.deposit_id==value['deposit_id']){
                            y.amount = value['amount']
                        }
                    })
                }
            })
        },
        readonly(item){
            var res = true;
            this.form.accounts.map(x=> {
                if(x.client_id == item.client_id){
                    res = false
                }
            });
            return res
        },
        checkedAll($event){
            const status = $event.target.checked
            $.each($('.repayment_checkbox'), (k,v)=>{
                var el = $(v)
                if(status){
                    if(!el.is(':checked')){
                        el.click()
                    }
                }else{
                    if(el.is(':checked')){
                        el.click()
                    }
                }
            })
        },
        addToList(item,$event){
            this.form.accounts = this.form.accounts.filter(x => x.client_id != item.client_id)
            item['deposits'] = []
            
            if($event.target.checked){
                
                item['loan'] = {
                    loan_account_id: item.loan_account_id,
                    amount: null,
                    repayment_date: null
                }
                this.form.accounts.push(item);
                // this.form.loan_payments.push(this.inputFormat(item,'loan'));
                this.request.deposit_product_ids.map(x=>{
                    var obj = {
                        deposit_id: x.id,
                        amount:null,
                        repayment_date: null
                    }
                    item.deposits.push(obj)
                    // this.form.deposit_payments.push(this.inputFormat(item,'deposit',x.id));
                })
                
            }
        },
        moneyFormat(value){
            return moneyFormat(value);
        },
        fetch(){
            this.resetTable();
            this.isLoading = true;
            axios.post(this.fetchUrl, this.request)
                .then(res=>{
                    this.list = res.data.list
                    this.isLoading = false;
                })
                .catch(err=>{
                    this.errors = err.response.data
                    this.isLoading = false;
                })
            
        },
        assignOffice(value){
            this.request.office_id = value['id']
            this.resetTable()
        },
        loanProductSelected(value){
            this.request.loan_product_id = value['id'];
            this.loan_product_selected = value;
            this.resetTable()
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
            
            this.request.deposit_product_ids = list
            
            this.resetTable()
        },
        submit(e){
            e.preventDefault()
            this.modal.modalState =true
            this.modal.modal_title = 'Repayment'
        },
        makePayment(e){
            
            e.preventDefault()
            this.isLoading = true
            this.form.accounts.map(x=>{
                x.loan['repayment_date'] = this.form.repayment_date
                x.deposits.map(y=>{
                    y['repayment_date'] = this.form.repayment_date
                })
            })
            axios.post(this.paymentUrl, this.form)
                .then(res=>{
                    this.isLoading = false
                    Swal.fire(
                        'Success',
                        res.data.msg,
                        'success'
                    )

                    this.resetTable()
                    this.resetForm()
                    this.modal.modalState = false
                  
                })
                .catch(err=>{
                    this.isLoading = false
                    this.errors = err.response.data.errors
                })
        },
        resetForm(){
            this.form.accounts = []
            this.form.office_id = null
            this.form.payment_method_id = null
            this.form.repayment_date = null
            this.form.receipt_number = null
            this.form.notes = null
        },
        download(){
            this.isLoading = true;
            var data = Object.assign({},this.request);
            data['ccr'] = true
            axios.post(this.fetchUrl,data, {responseType:'blob'})
                .then(res=>{
                    this.isLoading = false;
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.headers.filename);
                    document.body.appendChild(link);
                    link.click();
                    this.isLoading =false;
                })
                .catch(err=>{
                    this.isLoading = false
                })
            
            
        },
        clientLink(client_id){
            return '/client/'+client_id
        },
        loanLink(client_id,loan_account_id){
            return '/client/'+client_id+'/loans/'+loan_account_id
        }
        
    },
    computed : {
        hasRecords(){
            return this.list.length > 0;
        },
        fetchUrl(){
            return '/scheduled/list'
        },
        paymentUrl(){
            return '/bulk/repayments'
        },
        _hasDeposit(){
            return this.request.deposit_product_ids.length > 0;
        },
        

    }
}
</script>