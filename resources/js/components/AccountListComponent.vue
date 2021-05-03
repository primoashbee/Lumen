<template>
<div>
        <div class="row">
            <div class="col-lg-3">
                <label for="" style="color:white" class="lead mr-2">Level:</label>
                <v2-select class="d-inline-block" style="width:100%;" @officeSelected="officeSelected" ></v2-select>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead">Products</label>
                <products  :list="type" status="1" multi_values="true" @productSelected="productSelected" ></products>
            </div>
            <div class="col-4">
                <label for="" style="color:white" class="lead"  >Status</label>
                <status @statusSelected="statusSelected"></status>
            </div>
            <button type="button" class="btn btn-primary" @click="filter"> Submit </button>
        </div>

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
            <div class="col-6"></div>
            <div class="col-4">
                <label for="search" class="lead" style="color:white"> Search </label>
                <input type="text" v-model="request.search" class="form-control">
            </div>
        </div>
        <div class="w-100 px-3 mt-6" >
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Type</p></td>
                        <td><p class="title">Acc Int.</p></td>
                        <td><p class="title">Balance</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <tr v-for="account in lists.data" :key="account.id">
                        <td><a class="text-lg" href="#">{{account.client.client_id}}</a></td>
                        <td class="text-lg">{{account.client.firstname + ' ' + account.client.lastname}}</td>
                        <td class="text-lg">{{account.accountable.type.name}}</td>
                        <td class="text-lg">{{account.accountable.accrued_interest_formatted}}</td>
                        <td class="text-lg">{{account.accountable.balance_formatted}}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="clearfix"></div>
            <paginator :dataset="lists" @updated="fetch"></paginator>
        </div>
    <loading :is-full-page="true" :active.sync="isLoading" ></loading>
</div>
</template>

<script>
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
            isLoading:false,
        }
    },
    methods : {
        inputSearch(){
            this.fetch()
        },
        alert(msg){
        },
        filter(){
            this.fetch()    
        },
        fetch(page){
            this.isLoading =true;
            if(page==undefined){
                axios.get(this.queryString,
                    {headers: { 'Content-Type': 'application/json'} }
                )
                .then(res=>{
                    this.lists = res.data
                    this.isLoading = false;
                })
                .catch(err=>{
                    this.isLoading = false;
                    this.errors = err.response.data.errors
                })
            }else{
                axios.get(this.queryString+'&page='+page,
                    {headers: { 'Content-Type': 'application/json'} }
                )
                .then(res=>{
                    this.isLoading = false;
                    this.lists = res.data
                })
                .catch(err=>{
                    this.isLoading = false;
                    this.errors = err.response.data.errors
                })
            }
            
            
        },
        officeSelected(value){
            this.request.office_id = value['id'];
        },

        productSelected(value){
            this.request.products = value;
        },

        statusSelected(value){
            this.request.status = value
        },
        url(page=1){
            return `/clients/list?office_id=`+this.office_id+`&page=`+page
        },  
    },    
    computed :{
        hasRecords(){
            return this.lists.hasOwnProperty('data');
        },
        queryString(){
            return '/accounts/'+this.type+'?office_id='+this.request.office_id+'&status='+this.request.status+'&loan_ids='+this.loanProducts+'&deposit_ids='+this.depositProducts+'&per_page='+this.request.per_page+'&search='+this.request.search
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