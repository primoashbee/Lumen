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
            
        </div>
        
        <vue-good-table
        mode="remote"
        @on-page-change="onPageChange"
        @on-sort-change="onSortChange"
        @on-column-filter="onColumnFilter"
        @on-per-page-change="onPerPageChange"
        
        :totalRows="totalRecords"
        :rows="rows"
        :columns="columns"
        
        :line-numbers="true"
        theme="nocturnal">

        </vue-good-table>
        
        <button type="button" class="btn btn-primary" @click="loadItems"> Submit </button>

    <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    <span> 
        <li v-for></li>
    </span>
</div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import 'vue-good-table/dist/vue-good-table.css'
import { VueGoodTable } from 'vue-good-table';

export default {
    components: {
        Loading,
        VueGoodTable
    },
    props : ['type','action'],
    data(){
        return {
            errors: {},
            request : {
                office_id : null,
                products : [],
                status: null,
            },
            totalRecords: null,
            response : [],
            isLoading:false,
            serverParams: {
            // a map of column filters example: {name: 'john', age: '20'}
                columnFilters: {
                },
                sort: [
                    {
                    field: '', // example: 'name'
                    type: '' // 'asc' or 'desc'
                    }
                ],

                page: 1, // what page I want to show
                perPage: 25 // how many items I'm showing per page
            },
            
            columns: [
                {
                    label : 'Client ID',
                    field : 'client_id'
                },
                {
                    label : 'Name',
                    field : 'client.full_name'
                },
                {
                    label : 'Type',
                    field : 'accountable.type.name'
                },
                {
                    label : 'Balance',
                    field : 'accountable.balance_formatted'
                }
            ],
            rows : []


            
            
        }
    },
    methods : {
        officeSelected(value){
            this.request.office_id = value['id'];
        },

        productSelected(value){
            this.request.products = value;
        },

        statusSelected(value){
            this.request.status = value
        },


        //vgt methods
        updateParams(newProps) {
            console.log(newProps)
            this.serverParams = Object.assign({}, this.serverParams, newProps);
        },
        
        onPageChange(params) {
            console.log(params)
            this.updateParams({page: params.currentPage});
            this.loadItems();
        },

        onPerPageChange(params) {
            console.log(params)
            this.updateParams({perPage: params.currentPerPage});
            this.loadItems();
        },

        onSortChange(params) {
            console.log(params)
            this.updateParams({
                sort: [{
                type: params.sortType,
                field: this.columns[params.columnIndex].field,
                }],
            });
        this.loadItems();
        },
        
        onColumnFilter(params) {
            this.updateParams(params);
            this.loadItems();
        },

        // load items is what brings back the rows from server
        loadItems(){
            var vm = this;
            axios.get(vm.queryString,
                {headers: { 'Content-Type': 'application/json'} }
            )
            .then(res=>{
                vm.rows  = res.data.data
                vm.response = res.data
                vm.totalRecords = res.total
            })
            .catch(err=>{
                vm.errors = err.response.data.errors
            })
        }
    },
    computed :{
        hasRecords(){
            return this.lists.hasOwnProperty('data');
        },
        queryString(){
            return '/accounts/'+this.type+'?office_id='+this.request.office_id+
            '&status='+this.request.status+'&loan_ids='+this.loanProducts+
            '&deposit_ids='+this.depositProducts
            +'&per_page='+this.serverParams.perPage+'&page='+this.serverParams.page
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