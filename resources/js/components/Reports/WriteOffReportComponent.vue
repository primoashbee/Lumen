<template>
    <div class="row">
        <div class="col-lg-12 card">
            <div class="card-header">
                <h3 class="h3">Write Off - Reports</h3>
            </div>
            
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-12 text-right mx-auto my-auto">
                            <button class="btn btn-primary" @click="download" v-if="exportable">Export Report</button>
                        </div>
                    <div class="col-lg-4">
                        <label for="" style="color:white" class="lead mr-2">Level:</label>
                        <v2-select class="d-inline-block" style="width:100%;" @officeSelected="officeSelected" ></v2-select>
                    </div>
                    <div class="col-lg-4">
                        <label for="date" style="color:white" class="lead mr-2">Date</label>
                        <input type="date" class="form-control" v-model="request.date">
                    </div>
                    <div class="col-4">
                        <label for="" style="color:white" class="lead">Products</label>
                        <products  :list="'loan'" status="1" multi_values="true" @productSelected="productSelected" ></products>
                    </div>
                    <div class="my-4 col-12">
                        <button type="button" class="btn btn-primary" @click="filter"> Filter </button>
                    </div>
                </div>
                <div class="w-100 px-3 mt-6" >
                    <table class="table">
                        <thead>
                            <tr>
                                <td><p class="title">Level</p></td>
                                <td><p class="title">Client ID</p></td>
                                <td><p class="title">Name</p></td>
                                <td><p class="title">Code</p></td>
                                <td><p class="title">Written Off Principal</p></td>
                                <td><p class="title">Written Off Interest</p></td>
                                <td><p class="title">Total Write Off</p></td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="client in lists.data" :key="client.client_id">
                                
                                <td class="text-lg">{{client.branch}}</td>
                                <td><label :for="client.client_id">{{client.client_id}}</label></td>
                                <td class="text-lg">{{client.fullname}}</td>
                                <td class="text-lg">{{client.code}}</td>
                                <td class="text-lg">{{moneyFormat(client.principal)}}</td>
                                <td class="text-lg">{{moneyFormat(client.interest)}}</td>
                                <td class="text-lg">{{moneyFormat(client.total_writeoff)}}</td>
                            </tr>
                            <template v-if="hasRecords">
                                <tr>
                                
                                    <td class="text-lg"># of Accounts</td>
                                    <td class="text-lg" colspan="3"> {{this.summary.total_accounts}}</td>
                                    <td class="text-lg"> {{moneyFormat(this.summary.total_principal)}}</td>
                                    <td class="text-lg"> {{moneyFormat(this.summary.total_interest)}}</td>
                                    <td class="text-lg"> {{moneyFormat(this.summary.total_write_off)}}</td>
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
                        <p style="color:white" class="col-11 lead text-right text-lg" v-if="hasRecords">Showing Records: {{this.lists.from.toLocaleString()}} - {{this.lists.to.toLocaleString()}}  of {{this.lists.total.toLocaleString()}}</p>
                    </div>
                    <div class="clearfix"></div>
                    <paginator :dataset="lists" @pageSelected="pageSelected"></paginator>
                </div>
            </div>
            
           
        </div>
        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    </div>
</template>

<script>
import lodash from 'lodash';
import Loading from 'vue-loading-overlay';
import Paginator from './../PaginatorComponent';
import SelectComponent from '../SelectComponent.vue';
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
            page:1,
            per_page: 25,
            search: null,
            date:null,
        },
        form:{
            office_id:null,
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

    filter(){
        this.fetch()    
    },

    download(){
        var data = Object.assign({},this.request);
        data['export'] = true;
        axios.post('/wApi/reports/writeoff?page='+this.request.page, data,
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

    fetch(page=1){
        this.isLoading =true;
        let request = Object.assign({}, this.request);
        
        axios.post('/wApi/reports/writeoff?page='+page, request)
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
    pageSelected(value){
        this.request.page = value
        this.fetch();
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
    moneyFormat(value){
        return moneyFormat(value);
    }
},
computed:{
    hasRecords(){
        return this.lists.hasOwnProperty('data');
    },

    
}

}
</script>