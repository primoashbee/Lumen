<template>
<div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card pb-12">         
                    
                <div class="card-header">
                    
                    <h3 class="h3"> Client Report </h3> 
                    <div class="row">
                        <div class="col-lg-6 mt-4">
                            <label for="" style="color:white" class="lead mr-2">Branch:</label>
                            <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" ></v2-select>
                        </div>
                        <div class="col-lg-6 text-right">
                            <button class="btn btn-primary" @click="download" v-if="exportable">Export Report</button>
                        </div>
                    </div>     
                    <div class="row mt-4">
                        <div class="filters-container col-md-12 d-inline-block">
                            <h4 class="h4 d-inline-block mr-4">Filters:</h4>
                            <div class="btn-filter-group d-inline-block">
                                <button @click="addToFilter('age')" class="btn-filters" :class="{'active':filter.includes('age')}">Age</button>
                                <button @click="addToFilter('service')" class="btn-filters" :class="{'active':filter.includes('service')}">Service Type</button>
                                <button @click="addToFilter('education')" class="btn-filters" :class="{'active':filter.includes('education')}">Educational Attainment</button>
                                <button @click="addToFilter('status')" class="btn-filters" :class="{'active':filter.includes('status')}">Status</button>
                            </div>
                        </div>   
                    </div>
                    <div class="row mt-4" v-if="filter.includes('age')">
                       <div class="col-lg-2">
                            <label for="" style="color:white" class="lead mr-2">Age From:</label>
                            <input type="number" min="0" step="1" class="form-control" v-model="request.age_from"/>
                        </div>
                        <div class="col-lg-2">
                            <label for="" style="color:white" class="lead mr-2">Age To:</label>
                            <input type="number" min="0" step="1" class="form-control" v-model="request.age_to"/>
                        </div>
                    </div>

                    <div class="row mt-4" v-if="filter.includes('education')">
                        <div class="col-lg-4">
                            <label for="" style="color:white" class="lead mr-2">Educational Attainment:</label>
                            <status :multi_values="true" type="educational_attainment" @statusSelected="statusSelected($event,'educational_attainment')"></status>
                        </div>
                    </div>
                    <div class="row mt-4" v-if="filter.includes('gender')">
                        <div class="col-lg-4">
                            <label for="" style="color:white" class="lead mr-2" >Gender:</label>
                            <status :multi_values="true" type="gender" @statusSelected="statusSelected($event,'gender')"></status>
                        </div>
                    </div>
                    <div class="row mt-4" v-if="filter.includes('service')">
                        <div class="col-lg-4">
                            <label for="" style="color:white" class="lead mr-2">Service Type:</label>
                            <status :multi_values="true" type="service_type" @statusSelected="statusSelected($event,'service_type')"></status>
                        </div>
                    </div>
                    <div class="row mt-4" v-if="filter.includes('status')">
                        <div class="col-lg-4">
                            <label for="" style="color:white" class="lead mr-2">Status</label>
                            <status :multi_values="true" type="client" @statusSelected="statusSelected($event,'status')"></status>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-4" @click="search">Search</button>
                    

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
                                <td><p class="title">{{item.economic_activity}}</p></td>
                                <td><p class="title">{{item.status}}</p></td>
                            </tr>
                            <tr>
                                <td colspan="6"><p class="title text-left"># of Clients:</p></td>
                                <td colspan="2"><p class="title text-right">{{list.summary.total}}</p></td>
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
<style scoped>
.btn-filters.active{
    background: rgb(253 173 125);
}
</style>
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
            filter:[],
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
            var data = Object.assign({},this.request);
            data['export'] = true;
            axios.post(this.url+'?page='+this.request.page, data,
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
        addToFilter(value){
            if (this.filter.includes(value)) {
                const index = this.filter.indexOf(value);
                if(index > -1){
                    this.filter.splice(index,1)
                }

                // console.log(this.filter);
            }else{
                this.filter.push(value) 
                // console.log(this.filter)
            }
            
            
        }
    }
}
</script>