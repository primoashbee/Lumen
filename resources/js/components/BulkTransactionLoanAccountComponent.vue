<template>
    <div>
        <form @submit.prevent="fetch">
        <div class="row">
            <div class="col-lg">
                <div class="form-group">
                    <label for="" style="color:white" class="lead mr-2">Filter:</label>
                    <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" v-model="request.office_id"></v2-select>
                </div>
            </div>
            <div class="col-lg">
                <div class="form-group">
                    <label for="product_id" > Product </label>
                    <loan-product-list id="product_id" @selected="selected"></loan-product-list>
                </div>

                <button  class="btn btn-primary" >Filter</button>
            </div>
            
        </div>
        </form>

        <div class="w-100 px-3 mt-6" >
            
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="title"><input type="checkbox" @change="checkAll($event)" v-if="this.hasRecords" id="check_all"></p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td ><p class="title"># of Inst.</p></td>
                        <td ><p class="title">Amount</p></td>
                        <td ><p class="title">Total Fees</p></td>
                        <td ><p class="title">Disbursement Amount</p></td>
                        
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    
                    <tr v-for="client in lists" :key="client.client_id">
                        <td><input type="checkbox" class="checkbox" :id="client.client_id" @change="checked(client,$event)"></td>
                        <td><label :for="client.client_id">{{client.client_id}}</label></td>
                        <td class="text-lg"><a class="text-lg" :href="clientLink(client.client_id)">{{client.basic_client.full_name}}</a></td>
                        <td> {{client.number_of_installments}} </td>
                        <td> {{client.mutated.amount}} </td>
                        <td> {{client.mutated.total_deductions}} </td>
                        <td> {{client.mutated.disbursed_amount}} </td>
                        
                    </tr>
                </tbody>
            </table>
            <p class="lead float-left text-right" style="color:white">Showing Records {{lists.from}} - {{lists.to}} of {{totalRecords}} </p>
            <p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
            <div class="clearfix"></div>
            <paginator :dataset="lists" @updated="fetch"></paginator>
        </div>
        
        <button class="btn btn-primary" @click="submit">Submit</button>
      
        <loading :is-full-page="true" :active.sync="isLoading" ></loading>

        <b-modal id="deposit-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modalTitle" :header-bg-variant="background" :body-bg-variant="background" >
		    
            <h1> # of Accounts: {{summary.accounts}} </h1>
            <h1> Total Amount: {{summary.amount}} </h1>
            <h1> Total Fees: {{summary.fees}} </h1>
            <h1> Total Disbursement: {{summary.disbursement}} </h1>
            <form @submit.prevent="disburse">
		        <div class="form-group mt-4">
		        	<label class="text-lg">Branch</label>
                    <v2-select @officeSelected="assignOfficeForm" :list_level="list_level" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="hasError('office_id')">
                        {{ errors.office_id[0]}}
                    </div>
		        </div>
		        <div class="form-group mt-4">
		        	<label class="text-lg">Disbursement Date</label>
                    <input type="date" v-model="form.disbursement_date"  class="form-control" v-bind:class="hasError('disbursement_date') ? 'is-invalid' : ''">
                    <div class="invalid-feedback" v-if="hasError('disbursement_date')">
                        {{ errors.disbursement_date[0]}}
                    </div>
		        </div>
		        <div class="form-group mt-4">
		        	<label class="text-lg">First Repayment Date</label>
                    <input type="date" v-model="form.first_repayment_date"  class="form-control" v-bind:class="hasError('first_repayment_date') ? 'is-invalid' : ''">
                    <div class="invalid-feedback" v-if="hasError('first_repayment_date')">
                        {{ errors.first_repayment_date[0]}}
                    </div>
		        </div>
		        <div class="form-group">
		        	<label class="text-lg">Payment Method</label>
					<payment-methods :payment_type="payment_type" @paymentSelected="paymentSelected" v-bind:class="hasError('payment_method') ? 'is-invalid' : ''" ></payment-methods>
					<div class="invalid-feedback" v-if="hasError('payment_method')">
                        {{ errors.payment_method[0]}}
                    </div>
		        </div>
		        <div class="form-group">
		        	<label class="text-lg">CV #:</label>
					<input type="text" class="form-control" v-model="form.cv_number" v-bind:class="hasError('check_voucher') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('check_voucher')">
                        {{ errors.check_voucher[0]}}
                    </div>
		        </div>

		        
		        <button class="btn btn-primary">Submit</button>
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
    props: ['type'],
    data(){
        return {
            errors : [],
            lists : [],
            isLoading:false,
            request : {
                office_id:null,
                loan_id:null
            },
            form :{
                office_id :null,
                accounts : [],
                paymentSelected : null,
                disbursement_date : null,
                first_repayment_date : null,
                cv_number: null,
            },
            selected_list : [],
            url : null,
            post_url: null,
            modal :{
                modalState : false,
                modalTitle : 'Disburse Loan Accounts'
            },
            payment_type:"for_disbursement",
            background: 'dark',
            list_level : 'branch'
        }
    },
    components:{
        Loading,
        AmountInputComponent,
    },
    mounted(){
        if(this.type=="pending"){
            this.url = '/loans/pending/list'
            this.post_url ='/bulk/approve/loans'
        }else if(this.type=='disburse'){
            this.url = '/loans/approved/list'
            this.post_url = '/bulk/disburse/loans'
        }
    },
    methods :{
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
        exportCCR(string){
            this.isLoading =true;
            axios.get('/download/dst/bulk/'+string,{responseType:'blob'})
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
        disburse(){
            this.isLoading = true;
            axios.post(this.post_url,this.form)
            .then(res=>{
                this.isLoading = false;
                this.modal.modalState = false
                this.lists = []
                this.resetForm();
                Swal.fire({
                    title: 'Successful!',
                    text: res.data.msg,
                    icon: 'success',
                    confirmButtonText: 'Download CCR'
                })
                .then(()=>{
                    
                    this.exportCCR(res.data.bulk_disbursement_id);
                })
                
            }).catch(err=>{
                this.isLoading = false;
                this.errors = err.response.data.errors || {}
            })
        },
        hasError(field){
            return this.errors.hasOwnProperty(field)
        },
        paymentSelected(value){
            this.form.payment_method = value['id']
        },
        submit(e){
            e.preventDefault()
                var vm = this;
                if(this.type=='pending'){
                    const swalWithBootstrapButtons = Swal.mixin({
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                        },
                        
                        buttonsStyling: true,
                        
                        })

                        swalWithBootstrapButtons.fire({
                        html: 
                            `
                            <table class="table table-condensed">
                            <tbody>
                            <thead>
                                <th class="text-right" style="width:50%;font-weight:900" >Particulars</th>
                                <th class="text-left" style="width:50%;font-weight:900">Amount</th>
                            </thead>
                            <tr>
                                <td class="text-right pr-2">Accounts : </td>
                                <td class="text-left">`+vm.summary.accounts+`</td>
                            </tr>
                            <tr>
                                <td class="text-right pr-2">Loan Amount: </td>
                                <td class="text-left">`+vm.summary.amount+`</td>
                            </tr>
                            <tr>
                                <td class="text-right pr-2">Fees Amount: </td>
                                <td class="text-left">`+vm.summary.fees+`</td>
                            </tr>
                            <tr>
                                <td class="text-right pr-2">Disbursement Amount: </td>
                                <td class="text-left">`+vm.summary.disbursement+`</td>
                            </tr>
                        
                            </tbody>
                            </table>
                            `,
                            
                            
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:0.8em;font-weight:600">Summary - Approve?</span> ',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        cancelButtonText: 'No',
                        
                        reverseButtons: true
                        }).then((result) => {
                            if (result.value) {
                                vm.isLoading = true
                                axios.post(vm.post_url,vm.form)
                                .then(res=>{
                                    vm.isLoading =false
                                    swalWithBootstrapButtons.fire(
                                    '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:0.8em;font-weight:600">Posted!</span>',
                                    res.data.msg,
                                    'success'
                                    )
                                    .then(()=>{
                                        location.reload()
                                    })
                                })
                                .catch(err=>{
                                    vm.isLoading=false
                                    swalWithBootstrapButtons.fire(
                                    '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:0.8em;font-weight:600">Posted!</span>',
                                    'Error',
                                    'error'
                                    )
                                })
                            } else if (
                                /* Read more about handling dismissals below */
                                result.dismiss === Swal.DismissReason.cancel
                            ) {
                                swalWithBootstrapButtons.fire(
                                '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:0.8em;font-weight:600">Cancelled</span>',
                                'Transaction cancelled',
                                'error'
                                )
                            }
                    })
                }else{
                    this.modal.modalState = true
                }
        },
        resetForm(){
            this.form.office_id = null,
            this.form.accounts = [],
            this.form.paymentSelected = null,
            this.form.disbursement_date = null,
            this.form.first_repayment_date = null,
            this.form.cv_number = null
        },
        checked(account,event){
            if(event.target.checked){
                this.form.accounts.push(account.id)
                this.selected_list.push(account)
            }else{
                this.form.accounts = this.form.accounts.filter(x=>{
                    return x.id != account.id
                })
                this.selected_list = this.selected_list.filter(x=>{
                    return x.id != account.id
                })
               
            }
        },

        clientLink(client_id){
            return '/client/'+ client_id
        },


        assignOffice(value){
            this.request.office_id = value['id']
        },

        assignOfficeForm(value){
            this.form.office_id = value['id']
        },
        selected(value){
            this.request.loan_id = value.id
            
        },
        fetch(){
            this.isLoading = true
            this.form.accounts = []
            axios.post(this.url, this.request)
            .then(res=>{
                this.lists = res.data.list
                this.isLoading=false
            })
        }
        
    },
    computed : {
        hasRecords(){
            return this.lists.length > 0;
        },
        
        totalRecords(){
            return this.lists.length;
        },
        buttonLabel(){
            if(this.type == 'pending'){
                return 'Approve'
            }
            if(this.type == 'approve'){
                return 'Disburse'
            }
        },
        summary(){
            var currency = '₱'

            var total_amount = 0;
            this.selected_list.map(x=>{
                total_amount = total_amount + parseInt(x.amount)
            })

            var total_fees = 0;
            this.selected_list.map(x=>{
                total_fees += parseInt(x.total_deductions)
            })
            var accounts = this.selected_list.length

            var disbursement = 0;
            this.selected_list.map(x=>{
                disbursement += parseInt(x.disbursed_amount)
            })
            return {
                accounts: accounts,
                amount: currency + ' ' +total_amount.toLocaleString(),
                fees: currency + ' ' +total_fees.toLocaleString(),
                disbursement: currency + ' ' + disbursement.toLocaleString()
            }
        }
        
    }
}
</script>