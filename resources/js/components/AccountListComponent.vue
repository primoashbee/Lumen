<template>
<div>
        <div class="row">
            <div class="col-lg-4">
                <label for="" style="color:white" class="lead mr-2">Level:</label>
                <v2-select class="d-inline-block" style="width:100%;" @officeSelected="officeSelected" ></v2-select>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead">Products</label>
                <products  :list="type" status="1" multi_values="true" @productSelected="productSelected" ></products>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead"  >Status</label>
                <status @statusSelected="statusSelected" :type="type" :multi_values="true"></status>
            </div>
            
            <div class="my-4 col-12">
                <button type="button" class="btn btn-primary" @click="filter"> Filter </button>
            </div>

            <div class="col-4">
                <label for="search" class="lead text-white"> Search </label>
                <input type="text" v-model="request.search" class="form-control">
            </div>
            <button class="btn btn-primary" @click="download" v-if="exportable">Export</button>
        </div>
        
        
        <div class="w-100 px-3 mt-6" >
            <table class="table" >
                <thead>
                    <tr v-if="type=='loan'">
                        <td><p class="title">Level</p></td>
                        <td><p class="title">Loan Officer</p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Code</p></td>
                        <td><p class="title">Principal</p></td>
                        <td><p class="title">Interest</p></td>
                        <td><p class="title">Total Loan Amount</p></td>
                        <td><p class="title">Balance</p></td>
                        <td><p class="title">Status</p></td>
                    </tr>
                    <tr v-if="type=='deposit'">
                        <td><p class="title">Level</p></td>
                        <td><p class="title">Loan Officer</p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Type</p></td>
                        <td><p class="title">Accr. Int</p></td>
                        <td><p class="title">Balance</p></td>
                        <td><p class="title">Status</p></td>
                    </tr>
                     <tr v-if="type=='all'">
                        <td><p class="title">Level</p></td>
                        <td><p class="title">Loan Officer</p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Code</p></td>
                        <td><p class="title">Principal</p></td>
                        <td><p class="title">Interest</p></td>
                        <td><p class="title">Total Balance</p></td>
                        <td><p class="title">RCBU</p></td>
                        <td><p class="title">MCBU</p></td>
                        <td><p class="title">Status</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <template v-if="type=='loan'">
                        <tr v-for="(item, key) in lists.data" :key="key">
                            <td class="text-lg">{{item.office}}</td>
                            <td class="text-lg">{{item.loan_officer}}</td>
                            <td><a class="text-lg" href="#">{{item.client_id}}</a></td>
                            <td class="text-lg">{{item.fullname}}</td>
                            <td class="text-lg">{{item.code}}</td>
                            <td class="text-lg">{{moneyFormat(item.principal)}}</td>
                            <td class="text-lg">{{moneyFormat(item.interest)}}</td>
                            <td class="text-lg">{{moneyFormat(item.total_loan_amount)}}</td>
                            <td class="text-lg">{{moneyFormat(item.total_balance)}}</td>
                            <td class="text-lg" v-html="status(item.status)"></td>
                        </tr>
                    </template>
                    <template v-if="type=='deposit'">
                        <tr v-for="(item, key) in lists.data" :key="key">
                            <td class="text-lg">{{item.office}}</td>
                            <td class="text-lg">{{item.loan_officer}}</td>
                            <td><a class="text-lg" href="#">{{item.client_id}}</a></td>
                            <td class="text-lg">{{item.fullname}}</td>
                            <td class="text-lg">{{item.code}}</td>
                            <td class="text-lg">{{moneyFormat(item.accrued_interest)}}</td>
                            <td class="text-lg">{{moneyFormat(item.balance)}}</td>
                            <td class="text-lg" v-html="status(item.status)"></td>
                        </tr>
                    </template>
                    <template v-if="type=='all'">
                        <tr v-for="(item, key) in lists.data" :key="key">
                            <td class="text-lg">{{item.office}}</td>
                            <td class="text-lg">{{item.loan_officer}}</td>
                            <td><a class="text-lg" href="#">{{item.client_id}}</a></td>
                            <td class="text-lg">{{item.fullname}}</td>
                            <td class="text-lg">{{item.code}}</td>
                            <td class="text-lg">{{moneyFormat(item.principal)}}</td>
                            <td class="text-lg">{{moneyFormat(item.interest)}}</td>
                            <td class="text-lg">{{moneyFormat(item.total_balance)}}</td>
                            <td class="text-lg">{{moneyFormat(item.RCBU)}}</td>
                            <td class="text-lg">{{moneyFormat(item.MCBU)}}</td>
                            
                            <td class="text-lg" v-html="status(item.status)"></td>
                        </tr>
                    </template>

                    <template v-if="type=='loan'">
                        <tr >
                            <td></td>
                            <td class="text-lg"># of Accounts</td>
                            <td class="text-lg"> {{this.summary.total_accounts}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_principal)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_interest)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_loan_amount)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_balance)}}</td>
                            <td></td>
                        </tr>
                    </template>
                    <template v-if="type=='deposit'">
                        <tr >
                            <td></td>
                            <td></td>
                            <td class="text-lg"># of Accounts</td>
                            <td class="text-lg"> {{this.summary.total_accounts}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_accrued_interest)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_balance)}}</td>
                            <td></td>
                        </tr>
                    </template>

                    <template v-if="type=='all'">
                        <tr>
                           
                            <td class="text-lg"># of Accounts</td>
                            <td class="text-lg"> {{this.summary.total_accounts}}</td>
                            <td class="text-lg text-right" colspan="4"> {{moneyFormat(this.summary.total_principal)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_interest)}}</td>
                            <td class="text-lg"> {{moneyFormat(this.summary.total_balance)}}</td>
                            
                        </tr>
                    </template>
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
    <loading :is-full-page="true" :active.sync="isLoading" ></loading>
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
    props : ['type','action'],
    data(){
        return {
            errors: {},
            request : {
                office_id : null,
                products : null,
                status: null,
                per_page: 25,
                search: null
            },
            lists : [],
            summary : [],
            isLoading:false,
            exportable: false
        }
    },
    methods : {
        moneyFormat(value){
            return moneyFormat(value);
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
        inputSearch(){
            this.fetch()
        },
        filter(){
            this.fetch()    
        },
        fetch(page=1){
            this.isLoading =true;
            let request = Object.assign({}, this.request);
            request['type'] = this.type
            axios.post('/wApi/list/accounts?page='+page, request)
                .then(res=>{
                    this.isLoading =false
                    this.lists = res.data.data
                    this.summary = res.data.summary
                    if(this.lists.data.length > 0){
                        this.exportable = true;
                    }else{
                        this.exportable = false;
                    }

                })
                .catch(err=>{
                    this.isLoading =false;

                })
        },
        download(){
            var data = Object.assign({},this.request);
            data['export'] = true;
            data['type'] = this.type

            axios.post('/wApi/list/accounts?page='+this.request.page, data,
            {
                headers : {
                    'Accept': 'application/vnd.ms-excel' 
                },
                responseType: 'blob',
            })
                .then(res=>{
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.headers.filename);
                    link.setAttribute('id', 'Ashbee');
                    document.body.appendChild(link);
                    link.click();
                    URL.revokeObjectURL(url)
                    // console.log('wata');
                    //     let link=document.createElement('a');
                    //     link.href=window.URL.createObjectURL(res);
                    //     console.log(link);
                    //     link.download=res.headers.filename;
                    //     link.click();
                    this.isLoading = false

                })
                .catch(err=>{
                    this.isLoading = false

                })
        },
        officeSelected(value){
            this.request.office_id = value['id'];
        },
        productSelected(value){
            this.request.products = _.map(value,'id');
            // this.request.products = _.map(_.pick(['type','id'], value));
        },
        statusSelected(value){
            this.request.status = value
        },
        url(page=1){
            return `/client/list?office_id=`+this.office_id+`&page=`+page
        },  
    },    
    computed :{
        hasRecords(){
            return this.lists.hasOwnProperty('data');
        },
        queryString(){
            // return '/accounts/'+this.type+'?office_id='+this.request.office_id+'&status='+this.request.status+'&loan_ids='+this.loanProducts+'&deposit_ids='+this.depositProducts+'&per_page='+this.request.per_page+'&search='+this.request.search
            return '/accounts/' + this.type
        },
        loanProducts(){

            var ids = [];
            if(this.request.products.length > 0){
             
                this.request.products.map(x=>{if(x.type=='loan'){ 
                    ids.push(x.id)
                }})
                
            }
            return JSON.stringify(ids)
        },
        depositProducts(){
            var ids = [];
            if(this.request.products.length > 0){
             
                this.request.products.map(x=>{if(x.type=='deposit'){ 
                    ids.push(x.id)
                }})
                
            }
            return JSON.stringify(ids)
        }
        
    }
}
</script>