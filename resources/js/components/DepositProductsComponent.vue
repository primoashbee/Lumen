<template>
    <div class="card">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Deposit Products</li>
            </ol>
        </nav>
        <h1 class="h3 ml-3">Deposit Products List</h1>
        <div class="w-100 px-3" >
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="text-white title">Name</p></td>
                        <td><p class="text-white title">Created At</p></td>
                        <td><p class="text-white title">Status</p></td>
                        <td><p class="text-white title">Action</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <tr v-for="item in lists.data" :key="item.id">
                        <td><a :href="itemLink(item.id)">{{item.name}}</a></td>
                        <td class="text-white">{{timeFormat(item.created_at)}}</td>
                        <td v-if="item.is_active">
                            <span class="badge badge-success">Enabled</span>
                        </td>
                        <td v-else>
                            <span class="badge badge-danger">Disabled</span>
                        </td>
                        <td>
                            <a class="btn btn-warning" :href="settingsLink(item.id,'edit')" role="button"><i class='fas fa-edit'></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="lead float-left text-right" style="color:white">Showing Records {{lists.from}} - {{lists.to}} of {{totalRecords}} </p>
            <p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
            <div class="clearfix"></div>
            <paginator :dataset="lists" @updated="fetch"></paginator>
        </div>
        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    </div>
</template>

<script>

import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from './PaginatorComponent';
import vueDebounce from 'vue-debounce'
import moment from 'moment'
Vue.use(vueDebounce, {
  listenTo: 'input'
})

import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    data(){
        return {
            lists: [],
            isLoading:false,
            query:"",
        }
    },
    components:{
        Loading
    },
    mounted(){
        this.fetch()
    },
    methods : {
        settingsLink(item_id){
                return '/settings/deposit/edit/'+item_id
        },
        
        timeFormat(item){
            return moment(item).format('LLL');
        },
        fetch(page){
            
            if(page==undefined){
                    axios.get('/settings/deposit/list').then(res => {
                        this.lists = res.data
                        this.isLoading =false
                    })
                }else{
                    console.log(page)
                    axios.get('/settings/deposit/list'+'?page='+page)
                    .then(res => {
                        this.isLoading =false
                        this.lists = res.data
                    })
                }

        },
        itemLink(value){
            return '/settings/deposit/'+value;
        }
        
    },
    computed : {
        totalRecords(){
            return numeral(this.lists.total).format('0,0')
        },
        viewableRecords(){
            return Object.keys(this.lists.data).length
        },
        hasRecords(){
            if(this.lists.hasOwnProperty('data')){
                return true;
            }
            return false;
        }
        

    }
}
</script>