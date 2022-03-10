<template>
<div>
    <div class="row">
        <div class="col-lg-12">
            
            <div class="card">
                
                <div class="card-header">   
                    <h3 class="h3"> Repayments Report</h3>    
                    <div class="row">
                        <div class="col-lg-4">
                            <label for="" style="color:white" class="lead mr-2">Branch:</label>
                            <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:100%;" ></v2-select>
                        </div>
                            <div class="col-lg-3">
                            <label for="date" style="color:white" class="lead mx-2"> Disbursed By:</label>
                            <user-list @userSelected="userSelected" :multiple="true" ></user-list>
                        </div>
                        <div class="col-lg-3">
                            <label for="date" style="color:white" class="lead mr-2"> Repayment Type:</label>
                            <transaction-method @transactionSelected="transactionSelected" :multiple="true" type="loan"></transaction-method>
                        </div>
                        <div class="col-lg-2 text-center">
                            <button class="mt-8 btn btn-primary" @click="download" v-if="exportable">Export Report</button>
                        </div>
                    </div>    
                    <div class="row mt-4">
                        <div class="col-lg-4">
                            <label for="date" style="color:white" class="lead mr-2"> From:</label>
                            <input type="date" class="form-control" v-model="request.from_date">
                        </div>
                        
                        <div class="col-lg-4">
                            <label for="date" style="color:white" class="lead mr-2"> To:</label>
                            <input type="date" class="form-control" v-model="request.to_date">
                        </div>
                    </div>
                    
                    <button class="mt-4 btn btn-primary" @click ="search">Filter</button>
                    

                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <template v-if="report_class=='detailed'">
                            <tr>
                                <td><p class="title">Office Level</p></td>
                                <td><p class="title">Client ID</p></td>
                                <td><p class="title">Name</p></td>
                                <td><p class="title">Type</p></td>
                                <td><p class="title"> Loan (P)</p> </td>
                                <td><p class="title"> Loan (I) </p></td>
                                <td><p class="title"> Total Amount </p></td>
                                <!-- <td><p class="title"> Payment Method </p></td> -->
                                <td><p class="title"> Repayment Date </p></td>
                                <!-- <td><p class="title">Paid By</p></td> -->
                                <td><p class="title">Timestamp</p></td>
                            </tr>
                            </template>
                            <template v-if="report_class=='summary'">
                            <tr>
                                <td><p class="title">Office Level</p></td>
                                <td><p class="title">Number of Payments</p></td>
                                <td><p class="title">Type</p></td>
                                <td><p class="title">Payment Method</p></td>
                                <td><p class="title"> Loan (P)</p> </td>
                                <td><p class="title"> Loan (I) </p></td>
                                <td><p class="title"> Total Amount </p></td>
                            </tr>
                            </template>
                        </thead>
                        <tbody>
                             <template v-if="report_class=='detailed' && hasRecords">
                                <tr v-for="(item,key) in list.data" :key="key">
                                    <td>{{item.office_code}}</td>
                                    <td><a :href="clientLink(item.client_id)">{{item.client_id}}</a></td>
                                    <td><a :href="clientLink(item.client_id)">{{item.client_name}}</a></td>
                                    <td><a :href="loanLink(item.client_id,item.loan_account_id)">{{item.loan_code}}</a></td>
                                    <td>{{moneyFormat(item.principal_paid)}}</td>
                                    <td>{{moneyFormat(item.interest_paid)}}</td>
                                    <td>{{moneyFormat(item.total_paid)}}</td>
                                    <!-- <td >{{(item.payment_method_name)}}</td> -->
                                    <td >{{(item.repayment_date)}}</td>
                                    <!-- <td>{{(item.paid_by)}}</td> -->
                                    <td>{{moment(item.created_at)}}</td>
                                </tr>
                             </template>
                            <template v-if="report_class=='summary' && hasRecords">
                            <tr v-for="(item,key) in list.data" :key="key">
                                <td><p class="title">{{item.office_code}}</p></td>
                                <td><p class="title">{{item.number_of_repayments}}</p></td>
                                <td><p class="title">{{item.loan_code}}</p></td>
                                <td><p class="title">{{item.payment_method_name}}</p></td>
                                
                                <td><p class="title">{{moneyFormat(item.principal_paid)}}</p></td>
                                <td><p class="title">{{moneyFormat(item.interest_paid)}}</p></td>
                                <td><p class="title">{{moneyFormat(item.total_paid)}}</p></td>
                            </tr>
                            </template>
                             <template v-if="report_class=='detailed' && hasRecords">
                                <tr>
                                    <td colspan="3"><p class="title text-left">Principal : {{moneyFormat(summary.principal_paid)}}</p></td>
                                    <td colspan="3"><p class="title text-center">Interest : {{moneyFormat(summary.interest_paid)}}</p></td>
                                    <td colspan="3"><p class="title text-right">Total : {{moneyFormat(summary.total_paid)}}</p></td>
                                </tr>
                                <tr style="border-style:none;">
                                    <td colspan="9"><p class="title text-left"># of Accounts: </p></td>
                                    <td><p class="title text-left">{{(summary.number_of_accounts)}}</p></td>
                                </tr>
                            </template>
                            <template v-if="report_class=='summary' && hasRecords">
                                <tr style="border-style:none;">
                                    
                                    <td><p class="title text-center"># of Accounts: </p></td>
                                    <td><p class="title">{{(summary.number_of_accounts)}}</p></td>
                                    <td></td>
                                    <td><p class="title">{{moneyFormat(summary.principal_paid)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.interest_paid)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.total_paid)}}</p></td>
                                </tr>
                            </template>
                            
                        </tbody>
                    </table>
                    <p style="color:white" v-if="hasRecords">Showing Records: {{this.list.from.toLocaleString()}} - {{this.list.to.toLocaleString()}}  of {{this.list.total.toLocaleString()}}</p>

                    <paginator :dataset="list" @pageSelected="pageSelected"></paginator>
                </div>
            </div>
            
        </div>
    </div>
 <loading :is-full-page="true" :active.sync="isLoading" ></loading>
</div>
</template>

<script>
import MultiSearchComponent from './../MultiSearchComponent';
import _ from 'lodash'
import Paginator from './../PaginatorComponent';
import Loading from 'vue-loading-overlay';

export default {
    props : ['report_class'],
    components: {
        MultiSearchComponent,
        Loading
    },
    data(){
        return {
            request: {
                is_summarized: false,
                from_date: null,
                to_date: null,
                office_id: null,
                users : null,
                page: 1,
                per_page: 20,
                type: null,
            },
            exportable : false,
            isLoading: false,
            list : [],
            summary : [],
        }
    },
    mounted(){
        if(this.report_class =='detailed'){
            this.request.is_summarized  = false
        }else{
            this.request.is_summarized = true
        }
    },
    methods : {
        
        moment(time, format='MMMM D, YYYY, h:mm:ss a'){
            return moment(time).format(format)
        },
        transactionSelected(value){
            this.list = [];
            this.request.type = value['name'];
        },
        depositLink(client_id,deposit_account_id){
            return '/client/'+client_id+'/deposit/'+deposit_account_id
        },
        loanLink(client_id,loan_account_id){
            return '/client/'+client_id+'/loans/'+loan_account_id
        },
        clientLink(client_id){
            return '/client/'+client_id
        },
        moneyFormat(value){
            return moneyFormat(value);
        },
        assignOffice(value){
            this.request.office_id = value['id'];
        },
        userSelected(value){
            this.request.users = _.map(value,'id');
            
        },
        search(page=1){
            this.isLoading = true
            var data = Object.assign({}, this.request);
            axios.post(this.url+'?page='+this.request.page, this.request)
                .then(res=>{
                    this.isLoading = false
                    this.list = res.data.list.data
                    this.summary = res.data.list.summary
                    console.log(this.list.data.length)
                    if(this.list.data.length > 0){
                        this.exportable = true;
                    }else{
                        this.exportable = false;
                    }
                })
                .catch(err=>{
                    this.isLoading = false
                })
            
        },
        pageSelected(value){
            this.request.page = value
            this.search()
        },
        download(){
            var data = Object.assign({},this.request);
            data['export'] = true;
            axios.post(this.url+'?page='+this.request.page, data, {
                        headers:
                            {
                                'Content-Disposition': "attachment; filename=template.xlsx",
                            },
                        responseType: 'blob',
                    }
                )
                .then(res=>{
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.headers.filename);
                    document.body.appendChild(link);
                    link.click();
                    this.isLoading = false
                })
                .catch(err=>{
                    this.isLoading = false

                })
        }
    },
    computed: {
        hasRecords(){
            if(this.list.hasOwnProperty('data')){
                return this.list.data.length > 0;
            } 
            return false;
        },
        url(){
            return '/wApi/reports/repayments'
        }

        
    },
    watch: {
        request : {
            handler(){
                this.exportable = false;
            },
            deep:true
        }
    }
}
</script>