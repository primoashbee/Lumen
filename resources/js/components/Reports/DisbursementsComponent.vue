<template>
<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                
                <div class="card-header">
                    <h3 class="h3"> Disbursements Report</h3>
                    <div class="row">
                        <div class="co-lg-4 pl-3">
                            <label for="" style="color:white" class="lead mr-2">Branch:</label>
                            <v2-select @officeSelected="assignOffice" style="width:500px;" ></v2-select>
                            <!-- <button type="button" class="btn btn-primary" @click="filter">Add New</button> -->
                        </div>
                        
                        <div class="col-lg-4">
                            <label for="date" style="color:white" class="lead mr-2"> Disbursed By:</label>
                            <user-list @userSelected="userSelected" :multiple="true"></user-list>
                        </div>
                        <div class="col-lg-3 text-right mx-auto my-auto">
                            <button class="btn btn-primary" @click="download" v-if="exportable">Export Report</button>
                        </div>
                        
                    </div>
                    <div class="row mt-4">
                        <div class="col-lg-4">
                            <label for="date" style="color:white" class="lead"> From:</label>
                            <input type="date" class="form-control" v-model="request.from_date">
                        </div>
                        <div class="col-lg-4 pl-0">
                            <label for="date" style="color:white" class="lead"> To:</label>
                            <input type="date" class="form-control" v-model="request.to_date">
                        </div>
                    </div>
                    <button class="btn btn-primary mt-4" @click="search">Filter</button>
                    

                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <template v-if="report_class =='detailed' ">
                            <tr>
                                <td><p class="title">Client ID</p></td>
                                <td><p class="title">Name</p></td>
                                <td><p class="title">Loan Type</p></td>
                                <td><p class="title"> Loan (P)</p> </td>
                                <td><p class="title"> Loan (I) </p></td>
                                <td><p class="title"> Loan (P+I) </p></td>
                                <td><p class="title">Disbursed</p></td>
                                <td><p class="title">Fee</p></td>
                                <td><p class="title">Disbursed By</p></td>
                            </tr>
                            </template>
                            <template v-if="report_class =='summary' ">
                            <tr>
                                <td><p class="title">Office Level</p></td>
                                <td><p class="title">Type</p></td>
                                <td><p class="title">Number of Disbursements</p></td>
                                <td><p class="title"> Loan (P)</p> </td>
                                <td><p class="title"> Loan (I) </p></td>
                                <td><p class="title"> Loan (P+I) </p></td>
                                <td><p class="title"> Disbursed Amount</p></td>
                                <td><p class="title"> Total Fees</p></td>
                            </tr>
                            </template>
                        </thead>
                        <tbody>
                            <template v-if="report_class =='detailed' && hasRecords">

                                <tr v-for="(item,key) in list.data" :key="key">
                                    <td><a :href="clientLink(item.client_id)">{{item.client_id}}</a></td>
                                    <td><a :href="clientLink(item.client_id)">{{item.fullname}}</a></td>
                                    <td><a :href="loanLink(item.client_id,item.id)">{{item.code}}</a></td>
                                    <td>{{moneyFormat(item.principal)}}</td>
                                    <td>{{moneyFormat(item.interest)}}</td>
                                    <td>{{moneyFormat(item.total_loan_amount)}}</td>
                                    <td>{{moneyFormat(item.disbursed_amount)}}</td>
                                    <td>{{moneyFormat(item.total_deductions)}}</td>
                                    <td>{{(item.disbursed_by)}}</td>
                                </tr>
                            </template>
                            <template v-if="report_class =='summary' && hasRecords">
                                <tr v-for="(item,key) in list.data" :key="key">
                                    <td><p class="title">{{(item.office_level)}}</p></td>
                                    <td><p class="title">{{(item.loan_type)}}</p></td>
                                    <td><p class="title">{{(item.number_of_disbursements)}}</p></td>
                                    <td><p class="title">{{moneyFormat(item.principal)}}</p></td>
                                    <td><p class="title">{{moneyFormat(item.interest)}}</p></td>
                                    <td><p class="title">{{moneyFormat(item.total_loan_amount)}}</p></td>
                                    <td><p class="title">{{moneyFormat(item.disbursed_amount)}}</p></td>
                                    <td><p class="title">{{moneyFormat(item.total_deductions)}}</p></td>
                                    <td></td>
                                </tr>
                            </template>




                            <template v-if="report_class =='detailed' && hasRecords">
                                <tr style="border-style:none;">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td><p class="title">{{moneyFormat(summary.principal)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.interest)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.total_loan)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.disbursed)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.deductions)}}</p></td>
                                    <td></td>
                                </tr>
                            </template>
                            <template v-if="report_class =='summary' && hasRecords">
                                <tr style="border-style:none;">
                                    <td></td>
                                    <td> # of Disbursements: </td>
                                    <td><p class="title">{{(summary.number_of_disbursements)}}</p></td>
                                    
                                    <td><p class="title">{{moneyFormat(summary.principal)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.interest)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.total_loan)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.disbursed)}}</p></td>
                                    <td><p class="title">{{moneyFormat(summary.deductions)}}</p></td>
                                    <td></td>
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
                from_date: null,
                to_date: null,
                office_id: null,
                users : null,
                page: 1,
                per_page: 20,
                is_summarized: null,
            },
            exportable : false,
            isLoading: false,
            list : [],
            summary: [],
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
            axios.post('/wApi/reports/disbursements?page='+this.request.page, this.request)
                .then(res=>{
                    this.isLoading = false
                    this.list = res.data.list.data
                    this.summary = res.data.list.summary
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
            axios.post('/wApi/reports/disbursements?page='+this.request.page, data,
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
        }
    },
    computed: {
        hasRecords(){
            if(this.list.hasOwnProperty('data')){
                return this.list.data.length > 0;
            } 
            return false;
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