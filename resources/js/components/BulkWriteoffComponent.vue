<template>
    <div>
        <div class="row">
            <div class="col-lg-4">
                <label for="" style="color:white" class="lead mr-2">Level:</label>
                <v2-select class="d-inline-block" style="width:100%;" @officeSelected="officeSelected" ></v2-select>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead">Products</label>
                <products  :list="'loan'" status="1" multi_values="true" @productSelected="productSelected" ></products>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead"  >Status</label>
                <status @statusSelected="statusSelected" :type="'loan'" :multi_values="true"></status>
            </div>
            
            <div class="my-4 col-12">
                <button type="button" class="btn btn-primary" @click="filter"> Filter </button>
            </div>
        </div>
        <div class="w-100 px-3 mt-6" >
            <table class="table">
                <thead>
                    <tr>
                        <td><p class="title"><input type="checkbox" @change="checkAll($event)" v-if="this.hasRecords" id="check_all"></p></td> 
                        <td><p class="title">Level</p></td>
                        <td><p class="title">Loan Officer</p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Code</p></td>
                        <td><p class="title">Principal</p></td>
                        <td><p class="title">Interest</p></td>
                        <td><p class="title">Balance</p></td>
                        <td><p class="title">Status</p></td>
                    </tr>
                </thead>
                <tbody>
                     <tr v-for="client in lists.data" :key="client.client_id">
                         
                        <td><input v-if="client.total_balance > 0 " type="checkbox" class="checkbox" :id="client.client_id" @change="checked(client,$event)"></td>
                        <td class="text-lg">{{client.office}}</td>
                        <td class="text-lg">{{client.loan_officer}}</td>
                        <td><label :for="client.client_id">{{client.client_id}}</label></td>
                        <td class="text-lg">{{client.fullname}}</td>
                        <td class="text-lg">{{client.code}}</td>
                        <td class="text-lg">{{moneyFormat(client.principal_balance)}}</td>
                        <td class="text-lg">{{moneyFormat(client.interest_balance)}}</td>
                        <td class="text-lg">{{moneyFormat(client.total_balance)}}</td>
                        <td class="text-lg" v-html="status(client.status)"></td>
                    </tr>
                </tbody>
            </table>
            <div class="row">
                <div class="col-1">
                <label for="" class="lead" style="color:white"> Per Page: </label>
                <select v-model="request.per_page" class="form-control" @change="filter">
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                    <option value="40">40</option>
                    <option value="50">50</option>
                </select>
                </div>
                
            </div>
            <div class="clearfix"></div>
            <paginator :dataset="lists" @pageSelected="fetch"></paginator>
        </div>
        <button type="button" class="ml-3 btn btn-primary" @click="openWriteoffModal" v-if="hasRecords">Submit Write Off</button>
        
        <b-modal id="writeoff-modal" v-model="modalState" size="lg" hide-footer :title="'Bulk Write Off Accounts'" :header-bg-variant="background" :body-bg-variant="background" >
		    
            <h1 class="text-lg"> # of Accounts: {{writeOffSummary.accounts}} </h1>
            <h1 class="text-lg"> Total Principal: {{writeOffSummary.principal_balance}} </h1>
            <h1 class="text-lg"> Total Interest: {{writeOffSummary.interest_balance}} </h1>
            <h1 class="text-lg"> Total Amount to be Write Off: {{writeOffSummary.writeoff_amount}} </h1>
            <form @submit.prevent="submit">
		        <div class="form-group mt-4">
		        	<label class="text-lg">Branch</label>
                    <v2-select @officeSelected="formOfficeSelected" :list_level="'branch'" v-bind:class="hasError('office_id') ? 'is-invalid' : ''"></v2-select>
                    <div class="invalid-feedback" v-if="hasError('office_id')">
                        {{ errors.office_id[0]}}
                    </div>
		        </div>
		        <div class="form-group mt-4">
		        	<label class="text-lg">Write Off Date</label>
                    <input type="date" v-model="form.date"  class="form-control" v-bind:class="hasError('date') ? 'is-invalid' : ''">
                    <div class="invalid-feedback" v-if="hasError('date')">
                        {{ errors.date[0]}}
                    </div>
		        </div>
		        <div class="form-group">
		        	<label class="text-lg">JV #:</label>
					<input type="text" class="form-control" v-model="form.journal_voucher" v-bind:class="hasError('journal_voucher') ? 'is-invalid' : ''">
					<div class="invalid-feedback" v-if="hasError('journal_voucher')">
                        {{ errors.journal_voucher[0]}}
                    </div>
		        </div>
		        <button class="btn btn-primary">Submit</button>
		    </form>
		</b-modal>

    </div>
</template>

<script>
import lodash from 'lodash';
import Loading from 'vue-loading-overlay';
import SelectComponent from './SelectComponent.vue';
export default {
    components: {
        Loading,
        SelectComponent
    },
props:['type','action'],
data(){
    return {
       errors: {},
        request : {
            office_id : null,
            products : null,
            status: null,
            type:'loan',
            per_page: 25,
            search: null
        },
        form:{
            accounts:[],
            office_id:null,
            journal_voucher:null,
            date:null
        },
        modalState:false,
        selected_list : [],
        lists : [],
        summary : [],
        isLoading:false,
        exportable: false,
        background: 'dark',
    }
},
methods:{
    openWriteoffModal(){
        this.modalState = true
    },      
    hasError(field){
        if(this.errors != null){
            return this.errors.hasOwnProperty(field)
        }
        return false;
    },

    submit(){
        Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Write Off Account'
                }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('/writeoff/loans/', this.form).
                    then(res => {
                        Swal.fire(
                        'Accounts Written Off!',
                        res.data.msg,
                        'success'
                        )
                        .then(res =>{
                            location.reload()
                        })
                    }).catch(error=>{
                        this.errors = error.response.data.errors;
                    })

                    
                }
            })
    },
    checked(account,event){
        if(event.target.checked){
            this.form.accounts.push(account.id)
            this.selected_list.push(account)
        }else{
            this.form.accounts = this.form.accounts.filter(x=>{
                return x != account.id
            })
            this.selected_list = this.selected_list.filter(x=>{
                return x.id != account.id
            })
            
        }
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
    status(value){
            let status='';
            switch (value){
                case 'Active':
                    status = '<span class="badge badge-success">'+value+'</span>';
                    break;
                case 'In Arrears':
                    status = '<span class="badge badge-danger">'+value+'</span>';
                    break;
                case 'Closed':
                    status = '<span class="badge badge-secondary">'+value+'</span>';
                    break;
                case 'Pending Approval':
                    status = '<span class="badge badge-warning">'+value+'</span>';
                    break;
                case 'Dormant':
                    status = '<span class="badge badge-warning">'+value+'</span>';
                    break;
                case 'Approved':
                    status = '<span class="badge badge-info">'+value+'</span>';
                    break;
                case 'Abandoned':
                    status = '<span class="badge badge-dark">'+value+'</span>';
                    break;   
                case 'Written Off':
                status = '<span class="badge badge-dark">'+value+'</span>';
                break;   
            }

            return status;
        },
        filter(){
            this.fetch()    
        },

        fetch(page=1){
            this.isLoading =true;
            let request = Object.assign({}, this.request);
            
            axios.post('/wApi/list/accounts?page='+page, request)
                .then(res=>{
                    this.isLoading =false
                    this.lists = res.data.data
                    this.summary = res.data.summary
 
                })
                .catch(err=>{
                    this.isLoading =false;

                })
        },

        officeSelected(value){
            this.request.office_id = value['id'];
        },
        formOfficeSelected(value){
            
            this.form.office_id = value['id'];
        },
        productSelected(value){
            this.request.products = _.map(value,'id');
            // this.request.products = _.map(_.pick(['type','id'], value));
        },
        statusSelected(value){
            this.request.status = value
        },
        moneyFormat(value){
            return moneyFormat(value);
        }
},
computed:{
    hasRecords(){
        return this.lists.hasOwnProperty('data');
    },

    writeOffSummary(){
        var writeoff_amount = 0;
        this.selected_list.map(x=>{
            writeoff_amount = writeoff_amount + parseFloat(x.total_balance)
        })

        var interest_balance = 0;
        this.selected_list.map(x=>{
            interest_balance += parseFloat(x.interest_balance)
        })

        var principal_balance = 0;
        this.selected_list.map(x=>{
            principal_balance += parseFloat(x.principal_balance)
        })

        var accounts = this.selected_list.length

        return {
            accounts: accounts,
            writeoff_amount: this.moneyFormat(writeoff_amount),
            interest_balance: this.moneyFormat(interest_balance),
            principal_balance:  this.moneyFormat(principal_balance)
        }
    }
}

}
</script>