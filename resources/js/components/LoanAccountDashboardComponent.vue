<template>
<div>
<div class="content pl-32 pr-8 mt-4" id="content-full">
    <loading :is-full-page="true" :active.sync="is_loading" ></loading>
	<div class="row" v-if="account!=null">
		<div class="col-lg-12">
			<div class="card">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/clients">Client</a></li>
                        <li class="breadcrumb-item"><a :href="client_profile">{{client_id}}</a></li>
                        <li class="breadcrumb-item"><a :href="client_loans">Loans</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Account</li>
                    </ol>
                  </nav>
				<div class="card-header">
                    <div class="row px-4">
                        <div class="x">
                            <h3 class="h3">{{client.full_name}} - {{loan_type}}</h3>
                        </div>
                        <div class="text-right col-lg-6" v-if="account.disbursed!=0 && account.closed_at==null">
                            <button v-if="can('enter_repayment') || is('Super Admin')" type="button" class="btn btn-primary" data-toggle="modal" @click="modal.modalState=true">
                                Pay
                            </button>
                            <button v-if="can('enter_repayment') || is('Super Admin')"   type="button" class="btn btn-primary" data-toggle="modal" @click="preTerm">
                                PreTerminate
                            </button>
                            <button   type="button" class="btn btn-primary" data-toggle="modal" @click="exportDST">
                                <i class="fas fa-file-invoice"></i> 
                            </button>
                        </div>
                    </div>
                    <div class="row px-4">
                        <p class="title text-xl mt-4 pb-4">Status: 
                            <span v-if="account.status=='Pending Approval'" class="badge badge-info"> Pending Approval </span>
                            <span v-if="account.status=='Approved'" class="badge badge-light"> Approved</span>
                            <span v-if="account.status=='In Arrears'" class="badge badge-danger"> In Arrears</span>
                            <span v-if="account.status=='Active'" class="badge badge-success">Active</span>
                            <span v-if="account.status=='Closed'" class="badge badge-dark">Closed</span>
                            <span v-if="account.status=='Pre-terminated'" class="badge badge-dark">Pre-terminated</span>
                        </p>
                    </div>
                    <div class="row px-4">
                        <div class="content-wrapper d-block mb-12 w-100">
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.amount,2)}}</p>
                                <p class="text-muted text-lg">Loan Amount</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.principal)}}</p>
                                <p class="text-muted text-lg">Principal</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.interest)}}</p>
                                <p class="text-muted text-lg">Interest</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.total_loan_amount)}}</p>
                                <p class="text-muted text-lg">Total Loan Amount</p>
                            </div>
                        </div>    

                        <div class="content-wrapper d-block mb-12 w-100">
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.principal_balance)}}</p>
                                <p class="text-muted text-lg">Principal Balance</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.interest_balance)}}</p>
                                <p class="text-muted text-lg">Interest Balance</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(account.total_balance)}}</p>
                                <p class="text-muted text-lg">Total Balance</p>
                            </div>
                        </div>

                        <div class="content-wrapper d-block mb-12 w-100">
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(total_paid.principal)}}</p>
                                <p class="text-muted text-lg">Principal Paid</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(total_paid.interest)}}</p>
                                <p class="text-muted text-lg">Interest Paid</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(total_paid.total)}}</p>
                                <p class="text-muted text-lg">Total Paid</p>
                            </div>
                            <div class="d-inline-block mr-16">
                                <p class="title text-lg">{{money(pre_term_amount.total)}}</p>
                                <p class="text-muted text-lg">Pre Termination Amount</p>
                            </div>
                        </div>
                    </div>
                </div>
                
				<div class="card-body profile-menu-tabs">

                    <ul class="nav nav-tabs mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="account-installments-tab" data-toggle="pill" href="#account-installment" role="tab" aria-controls="account-installment" aria-selected="true">Installments</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pills-profile-tab" data-toggle="pill" href="#pills-profile" role="tab" aria-controls="pills-profile" aria-selected="false">Activity</a>
                        </li>

                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="account-installment" role="tabpanel" aria-labelledby="account-installments-tab">
                            <h3 class="h3"> Amortization Schedule </h3>
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <td><p class="title">Installment</p></td>
                                        <td><p class="title">Date</p></td>
                                        <td><p class="title">Amortization</p></td>

                                        <td><p class="title">Principal</p></td>
                                        <td><p class="title">Interest</p></td>

                                        <td ><p class="title">Principal Due</p></td>
                                        <td ><p class="title">Interest Due</p></td>
                                        <td ><p class="title">Principal Paid</p></td>
                                        <td ><p class="title">Interest Paid</p></td>
                                        <td ><p class="title">Total Paid</p></td>

                                        <td><p class="title">Amount Due</p></td>
                                        <td><p class="title">Status</p></td>

                                    </tr>
                                </thead>
                                <tbody v-if="loaded"> 
                                    <tr v-for="item in installments" :key="item.transaction_id">
                                        <td>{{item.installment}}</td>
                                        <td>{{moment(item.date)}}</td>
                                        <td>{{money(item.amortization)}}</td>

                                        <td>{{money(item.original_principal)}}</td>
                                        <td>{{money(item.original_interest)}}</td>

                                        <td>{{money(item.principal_due)}}</td>
                                        <td>{{money(item.interest_due)}}</td>
                                        <td>{{money(item.total_paid)}}</td>

                                        <td>{{money(item.principal_paid)}}</td>
                                        <td>{{money(item.interest_paid)}}</td>
                                                                                
                                        <td>{{money(item.amount_due)}}</td>
                                        <td>
                                                
                                                <span v-if="item.status=='In Arrears'" class="badge badge-danger"> In Arrears</span>
                                                
                                                <span v-else-if="item.status=='Paid'" class="badge badge-success">Paid</span>
                                                <span v-else-if="item.status=='Due'" class="badge badge-warning">Due</span>
                                                <span v-else-if="item.status=='Not Due'" class="badge badge-light">Not Due</span>
                                                    
                                                    
                                        </td>
                                        
                                        
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <table class="table table-condensed">
                                <thead>
                                    <tr>
                                        <td><p class="title"></p></td>
                                        <td><p class="title">Transaction ID</p></td>
                                        <td><p class="title">Repayment Date</p></td>
                                        <td><p class="title">Transaction Date</p></td>
                                        <td><p class="title">Particulars</p></td>
                                        <td><p class="title">Amount</p></td>
                                        
                                        <td ><p class="title">Payment Method</p></td>
                                        <td ><p class="title">User</p></td>
                                        <td ><p class="title">Action</p></td>
                                    </tr>
                                </thead>
                                <tbody v-if="loaded && account.disbursed !=0"> 
                                    <tr v-for="(item,key) in activity"  :key="key">
                                        <td>{{key + 1}}</td>
                                        <td>{{item.transaction_number }}</td>
                                        <td>{{moment(item.repayment_date) }}</td>
                                        <td>{{moment(item.transaction_date,true) }}</td>
                                        <td>{{item.particulars}}</td>
                                        
                                        <td>{{money(item.amount)}}</td>
                                        <td>{{(item.payment_method_name)}}</td>

                                        <td>{{item.paid_by}}</td>
                                       
                                        <td>
                                            <span v-if="item.reverted=='0'">
                                                <button @click="revert(item.transaction_number)" class="btn btn-danger"><i class="fa fa-undo" aria-hidden="true"></i></button>
                                            </span>
                                            <span v-else>
                                                Reverted
                                            </span>

                                            
                                        </td>
                                    </tr>
                                   
                                </tbody>
                            </table>
                        </div>

                    </div>

				</div>
                
			</div>
		</div>
	</div>
</div>
<b-modal id="deposit-modal" v-model="modal.modalState" size="lg" hide-footer :title="modal.modal_title" :header-bg-variant="background" :body-bg-variant="background" >
    <form>
        <div class="form-group mt-4">
            <label class="text-lg">Branch</label>
            <v2-select @officeSelected="assignOffice" list_level="branch" :default_value="this.office_id" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
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
            <label class="text-lg">Amount</label>
            <input type="number" class="form-control" v-model="form.amount" v-bind:class="hasError('amount') ? 'is-invalid' : ''" :readonly="form.for_pre_term">
            <p class="" style="color:white" v-if="loaded"> <span v-show="!form.for_pre_term"> Amount Due: {{amount_due.formatted_total}} </span> </p>
            <div class="invalid-feedback" v-if="hasError('amount')">
                {{ errors.amount[0]}}
            </div>
        </div>

        <div class="form-group">
            <label class="text-lg">Repayment Date</label>
            <input type="date" class="form-control" v-model="form.repayment_date" v-bind:class="hasError('repayment_date')  ? 'is-invalid' : ''">
            <div class="invalid-feedback" v-if="hasError('repayment_date') ">
                {{ errors.repayment_date[0]}}
            </div>
        </div>
        <div class="form-group" v-if="this.payment_type=='CASH'">
            <label class="text-lg">OR #</label>
            <input type="text" class="form-control" v-model="form.receipt_number" v-bind:class="hasError('receipt_number')  ? 'is-invalid' : ''">
            <div class="invalid-feedback" v-if="hasError('receipt_number') ">
                {{ errors.receipt_number[0]}}
            </div>
        </div>
        <div class="form-group" v-if="this.payment_type=='NON-CASH'">
            <label class="text-lg">JV #</label>
            <input type="text" class="form-control" v-model="form.jv_number" v-bind:class="hasError('jv_number')  ? 'is-invalid' : ''">
            <div class="invalid-feedback" v-if="hasError('receipt_number') ">
                {{ errors.jv_number[0]}}
            </div>
        </div>
        <div class="form-group">
            <label class="text-lg">Notes</label>
            <textarea cols="30" rows="10" class="form-control" v-model="form.notes" v-bind:class="hasError('notes')  ? 'is-invalid' : ''"></textarea>
            <div class="invalid-feedback" v-if="hasError('notes') ">
                {{ errors.notes[0]}}
            </div>
        </div>
        <button type="button" class="btn btn-primary float-right"  @click="submit">Submit</button>
        <button type="button" class="btn btn-warning float-right mr-2" @click="cancelModal">Cancel</button>

    </form>
</b-modal>

</div>
</template>

<script>
import moment from 'moment'
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    components:{
        Loading
    },
    props: ['client_id','loan_account_id'],
    mounted(){
        
        this.fetchData()
        this.form.loan_account_id = this.loan_account_id
    },
    data(){
        return {
            account:null,
            activity: null,
            installments: null,
            client:null,
            is_loading:false,
            loaded:null,
            variants: ['primary', 'secondary', 'success', 'warning','danger', 'info', 'light', 'dark'],
			background:'dark',
			modal:{
				modalState:false,
				modal_title:'Make Payment',
            },
            errors: null,
            office_id:null,
            
            fees:null,
            form : {
                loan_account_id: null,
                office_id: null,
                amount:null,
                repayment_date:null,
                notes:null,
                for_pre_term:false,
                receipt_number: null,
                jv_number: null,
                payment_method_id: null,
            },
            total_paid: null,
            pre_term_amount: null,
            payment_type : null,
            amount_due: null,
            loan_type: null
        }
    },
    methods:{
        money(item){
            return moneyFormat(item);
        },
        exportDST(){
            this.isLoading = true;
            axios.get('/download/dst/'+this.loan_account_id,{responseType:'blob'})
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
        revert(transaction_number){
        
            var vm = this
            var loan_account_id = this.form.loan_account_id
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
                }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/revert',{
                        transaction_number:transaction_number,
                    })
                    .then(res=>{
                        Swal.fire(
                            'Reverted!',
                            res.data.msg,
                            'success'
                        )
                    })
                    .catch(error=>{
                        Swal.fire(
                            'Alert',
                            error.response.data.errors.transaction_number[0],
                            'error'
                        )
                    })
                }
            })
        },
        preTerm(){
            var account = this.account      
            var vm = this      
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
                    <thead>
                        <th class="text-right" style="width:50%;font-weight:900" >Particulars</th>
                        <th class="text-left" style="width:50%;font-weight:900">Amount</th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Interest</td>
                            <td>`+ vm.money(vm.pre_term_amount.interest)+`</td>
                        </tr>
                        <tr>
                            <td>Principal</td>
                            <td>`+ vm.money(vm.pre_term_amount.principal)+`</td>
                        </tr>
                        <tr>
                            <td>Total Amount</td>
                            <td><b>`+ vm.money(vm.pre_term_amount.total)+`</b></td>
                        </tr>
                    </tbody>
                    </table>
                    `,
                    
                title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Are you sure you want to pre-terminate this account</span> ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                
                }).then((result) => {
                if (result.value) {
                        // swalWithBootstrapButtons.fire(
                        // '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Posted!</span>',
                        // 'Account Pre-Terminated',
                        // 'success'
                        // )
                        vm.modal.modalState = true
                        vm.form.for_pre_term = true
                        vm.form.amount = vm.pre_term_amount.total
                    
                } else if (result.dismiss === Swal.DismissReason.cancel) 
                {
                    swalWithBootstrapButtons.fire(
                    '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Cancelled</span>',
                    'Transaction cancelled',
                    'error'
                    )
                }
            })
        },
        submit(){
            this.is_loading = true;
            axios.post(this.post_url,this.form)
                .then(res=>{
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK'
                    })
                    .then(res=>{
                        // location.reload()
                    })
                    this.is_loading = false;
                })
                .catch(error=>{
                    this.is_loading =false
                    console.log(error.response.data.errors)
                    this.errors = error.response.data.errors || {}
                })
                console.log("posted")
        },
        hasError(field){
            if(this.errors != null){
                return this.errors.hasOwnProperty(field)
            }
            return false;
        },
        cancelModal(){
            this.modal.modalState = false
            this.errors = null
		},
        assignOffice(value){
			this.office_id = value['id']
            this.form.office_id = value['id']
        },
        paymentSelected(value){
			this.form.payment_method_id = value['id']
            if(value['name']== 'CTLP'){
                this.form.receipt_number  = null
                return this.payment_type = 'NON-CASH';
            }
            this.form.jv_number  = null
            return this.payment_type = 'CASH';
        },
        moment(date,has_time=null){
            if(has_time===null){
			    var _date = moment(date).format('MMMM DD, Y')
            }else{
                var _date = moment(date).format('MMMM DD, Y hh:mm:ss A')
            }
			if(_date=="Invalid date"){
				return "------"
			}
			return _date;
		},
        async fetchData(){
            var config = {
                headers:{
                    'Content-Type':'application/json',
                    'Accept':'application/json'
                }
            }
            this.is_loading = true;
            await axios.get(this.fetch_url,config).then(response=>{
                this.account = response.data.account
                this.installments = response.data.installments
                this.client = response.data.client
                this.repayments = response.data.repayments

                this.fees = response.data.fees
                this.loan_type = response.data.loan_type
                this.activity = response.data.activity
                this.total_paid = response.data.total_paid
                this.pre_term_amount = response.data.pre_term_amount
                this.amount_due = response.data.amount_due

                this.is_loading = false
                this.loaded = true

                

            }).catch(error=>{
                console.log(error);
                this.is_loading = false;
            });
        
        }
    
    },
    computed:{
       
        client_profile(){
            return '/client/'+this.client_id
        },
        client_loans(){
            return '/client/'+this.client_id+'/loans'
        },
        post_url(){
            if(this.form.for_pre_term){
                return '/loans/preterm'
            }
            return '/loans/repay'
        },
        fetch_url(){
            return '/client/'+this.client_id+'/loans/'+this.loan_account_id
        },
        disbursed(){
            if(this.account != null){
                return this.account.disbursed
            }
            return null;
        },
        

    },

    watch:{
        'modal.modalState':function(newVal,oldVal){
            if(!newVal){
                
                this.form.office_id = null,
                this.form.amount = null,
                this.repayment_date = null,
                this.payment_method = null
                this.errors = null
                this.form.for_pre_term = false
            }
        }
    }
    
}
</script>
