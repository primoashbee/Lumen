a<template>
<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">                
                <div class="card-header">   
                    <h3 class="h3"> Deposit Reports</h3>    
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Branch:</label>
                        <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" ></v2-select>
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> From:</label>
                        <input type="date" class="form-control" v-model="request.from_date">
                    </div>
                    
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> To:</label>
                        <input type="date" class="form-control" v-model="request.to_date">
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> Balance from:</label>
                        <input type="number" class="form-control" v-model="request.balance_from">
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> Balance to:</label>
                        <input type="number" class="form-control" v-model="request.balance_to">
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> Transaction By: </label>
                        <user-list @userSelected="userSelected" :multiple="true" ></user-list>
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> Status: </label>
                        <status @statusSelected="statusSelected" :multi_values="true"  type="deposit"></status>
                    </div>
                    <div class="col-4">
                        <label for="date" style="color:white" class="lead mr-2"> Deposit Type:</label>
                        <transaction-method @transactionSelected="transactionSelected" allow_multiple="0" type="deposit"></transaction-method>
                    </div>
                    <button class="btn btn-primary" @click="search">Filter</button>
                    <button class="btn btn-primary" @click="download" v-if="exportable">Export</button>

                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <template v-if="report_class=='detailed'">
                            <tr>
                                <td><p class="title">Office Level</p></td>
                                <td><p class="title">Client ID</p></td>
                                <td><p class="title">Name</p></td>
                                <td><p class="title">Account</p></td>
                                <td><p class="title">Type</p></td>
                                <td><p class="title"> Amount</p> </td>
                                <td><p class="title"> Payment Method </p></td>
                                <td><p class="title">Paid By</p></td>
                                <td><p class="title">Transaction Date</p></td>
                                <td><p class="title">Timestamp</p></td>
                            </tr>
                            </template>
                            <template v-if="report_class=='summary'">
                            <tr>
                                <td><p class="title">Office Level</p></td>
                                <td><p class="title">Number of Payments</p></td>
                                <td><p class="title"> Loan (P)</p> </td>
                                <td><p class="title"> Loan (I) </p></td>
                                <td><p class="title"> Total Amount </p></td>
                            </tr>
                            </template>
                        </thead>
                        <tbody>
                             <template v-if="report_class=='detailed' && hasRecords">
                                <tr v-for="(item,key) in list.data" :key="key">
                                    <td>{{item.office_name}}</td>
                                    <td><a :href="clientLink(item.client_id)">{{item.client_id}}</a></td>
                                    <td><a :href="clientLink(item.client_id)">{{item.fullname}}</a></td>
                                    <td><a :href="loanLink(item.client_id,item.loan_account_id)">{{item.code}}</a></td>
                                    <td>{{moneyFormat(item.principal_paid)}}</td>
                                    <td>{{moneyFormat(item.interest_paid)}}</td>
                                    <td>{{moneyFormat(item.total_paid)}}</td>
                                    <td v-if="request.type=='Loan Payment'">{{(item.payment_method)}}</td>
                                    <td v-else><a :href="depositLink(item.client_id, item.deposit_account_id)">CTLP - {{(item.deposit_name)}} </a></td>
                                    <td>{{(item.paid_by)}}</td>
                                    <td>{{moment(item.created_at)}}</td>
                                </tr>
                             </template>
                            <template v-if="report_class=='summary' && hasRecords">
                            <tr v-for="(item,key) in list.data" :key="key">
                                <td><p class="title">{{item.office_name}}</p></td>
                                <td><p class="title">{{item.number_of_payments}}</p></td>
                                <td><p class="title">{{moneyFormat(item.principal_paid)}}</p></td>
                                <td><p class="title">{{moneyFormat(item.interest_paid)}}</p></td>
                                <td><p class="title">{{moneyFormat(item.total_paid)}}</p></td>
                            </tr>
                            </template>
                             <template v-if="report_class=='detailed' && hasRecords">
                                <tr style="border-style:none;">
                                    <td><p class="title"></p></td>
                                    <td><p class="title"></p></td>
                                    <td><p class="title text-right"># of Accounts: </p></td>
                                    <td><p class="title text-center">{{(summary.number_of_accounts)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.principal_paid)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.interest_paid)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.total_paid)}}</p></td>
                                </tr>
                            </template>
                            <template v-if="report_class=='summary' && hasRecords">
                                <tr style="border-style:none;">
                                    <td><p class="title text-center"># of Accounts: </p></td>
                                    <td><p class="title">{{(summary.number_of_accounts)}}</p></td>
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
    components : {
        Loading
    },
    data(){
        return {
            request : {
                office_id:null,
                from_date: null,
                to_date:null,
                transaction_by: [],
                transaction_type : [],
                balance_from: null,
                balance_to: null,
                status: [],
                page: 1,
                per_page: 20,
            },
            list : [],
            isLoading : false,
            exportable : false,
            summary : [],
        }
    },
    methods : {
        assignOffice(value){
            this.request.office_id = value['id'];
        },
        pageSelected(value){
            this.request.page = value
            this.search()
        },
        search(){
            
        },
        userSelected(value){
            this.request.users = _.map(value,'id');
        },
        transactionSelected(value){
            this.request.transaction_type = value;
        },
        statusSelected(value){
            this.request.status = value;
        }
    },
    computed : {
        hasRecords(){
            return false;
        }
    }
}

</script>