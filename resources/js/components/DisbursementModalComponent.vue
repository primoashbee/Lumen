<template>
    <b-modal id="deposit-modal" v-model="modalState" size="lg" hide-footer title="Disburse Loan" :header-bg-variant="background" :body-bg-variant="background" >
       
        
                <!-- <h1> # of Accounts: {{summary.accounts}} </h1>
                <h1> Total Amount: {{summary.amount}} </h1>
                <h1> Total Fees: {{summary.fees}} </h1>
                <h1> Total Disbursement: {{summary.disbursement}} </h1> -->
            
            
                <div class="row">
                    <div class="col-lg-12 px-4 py-2">
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
                    </div>
                </div>
            
       
    </b-modal>	
</template>

<style scoped>
.modal-content{
    background:rgb(52 58 64)!important;
}
.modal-content.row{
    margin-left: 0!important;
    margin-right: 0!important;
}
</style>
<script>
import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';

import ProductComponent from './ProductSelectComponent';
export default {
    props:['list_level','state'],
    data(){
        return {
            variants: ['primary', 'secondary', 'success', 'warning','danger', 'info', 'light', 'dark'],
			background:'dark',
            errors:{},
            formDisbursement :{
                office_id :null,
                accounts : [],
                paymentSelected : null,
                disbursement_date : null,
                first_repayment_date : null,
                cv_number: null,
            },
            payment_type:"for_disbursement",
            
        }
    },
    methods:{
        disburse(){
            this.isLoading = true;
            axios.post('/wApi/bulk/disbursement/loans',this.form)
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
        resetForm(){
            this.form.office_id = null,
            this.form.accounts = [],
            this.form.paymentSelected = null,
            this.form.disbursement_date = null,
            this.form.first_repayment_date = null,
            this.form.cv_number = null
        },
        hasError(){
			return Object.keys(this.errors).length > 0;
		},
        assignOffice(value){
			this.office_id = value['id']
			this.form.office_id = value['id']
            
        },
        paymentSelected(value){
            this.form.payment_method = value['id']
        },
        assignOfficeForm(value){
            this.form.office_id = value['id']
        },
        clickme(){
            console.log("bobo");
        }
    },
    computed:{
        modalState(){
           return this.state;
        }
    }
}
</script>