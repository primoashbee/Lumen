<template>
<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">                
                <div class="card-header">   
                    <h3 class="h3"> Client Report </h3>    
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Filter:</label>
                        <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" ></v2-select>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Age From:</label>
                        <input type="number" min="0" step="1" class="form-control" v-model="request.age_from"/>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Age To:</label>
                        <input type="number" min="0" step="1" class="form-control" v-model="request.age_to"/>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Educational Attainment:</label>
                        <status :multi_values="true" type="educational_attainment" @statusSelected="statusSelected($event,'educational_attainment')"></status>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2" >Gender:</label>
                        <status :multi_values="true" type="gender" @statusSelected="statusSelected($event,'gender')"></status>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Service Type:</label>
                        <status :multi_values="true" type="service_type" @statusSelected="statusSelected($event,'service_type')"></status>
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead mr-2">Status</label>
                        <status :multi_values="true" type="client" @statusSelected="statusSelected($event,'status')"></status>
                    </div>

                    <button class="btn btn-primary" @click="search">Search</button>
                    <button class="btn btn-primary" @click="download" v-if="exportable">Export</button>

                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <td><p class="title">Level</p></td>
                                <td><p class="title">Client ID</p></td>
                                <td><p class="title">Name</p></td>
                                <td><p class="title">Age</p></td>
                                <td><p class="title">Educ.</p></td>
                                <td><p class="title">Gender</p></td>
                                <td><p class="title">Business</p></td>
                                <td><p class="title">Status</p></td>
                            </tr>
                        </thead>
                        <tbody v-if="list.hasOwnProperty('data')">
                            <tr v-for="(item, key) in list.data.data" :key="key">
                                
                                
                                <td><p class="title">{{item.level}}</p></td>
                                <td><p class="title">{{item.client_id}}</p></td>
                                <td><p class="title">{{item.fullname}}</p></td>
                                <td><p class="title">{{item.age}}</p></td>
                                <td><p class="title">{{item.education}}</p></td>
                                <td><p class="title">{{item.gender}}</p></td>
                                <td><p class="title">Business</p></td>
                                <td><p class="title">Status</p></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><p class="title"># of Clients</p></td>
                                <td><p class="title">{{list.summary.total}}</p></td>
                            </tr>
                        </tbody>
                    </table>
                    <paginator :dataset="list.data" @pageSelected="pageSelected"></paginator>
                </div>
            </div>
            
        </div>
    </div>
 <loading :is-full-page="true" :active.sync="isLoading" ></loading>
</div>
</template>
<script>
import _ from 'lodash'
import Paginator from './../PaginatorComponent';
import Loading from 'vue-loading-overlay';
import AccountStatusComponent from '../AccountStatusComponent.vue';
export default {
    props: ['report_class'],
    components : {
        Loading
    },
    data(){
        return {
            isLoading: false,
            exportable : false,
            list: [],
            request : {
                is_summarized: false,
                office_id : null,
                status : [],
                per_page: 25,
                service_type : [],
                age_from: null,
                age_to: null,
                educational_attainment: [],
                gender: ['MALE'],
                page: 1,
            },
            url : '/wApi/reports/client-status'
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
        pageSelected(page){
            this.request.page = page
            this.search()
        },
        statusSelected(value, field=null){
            this.request[field] = value
        },
        assignOffice(value){
            this.request.office_id = value['id']
        },
        search(){
            this.exportable = false;
            axios.post(this.url+'?page='+this.request.page, this.request)
                .then(res=>{
                    this.list = res.data
                    if(this.list.summary.total > 0){
                        this.exportable = true;
                    }
                })
        },
        download(){

        },
    }
}
</script>