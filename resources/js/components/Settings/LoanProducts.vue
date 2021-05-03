<template>
    <div>
        <div class="w-100 px-3" >
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Created At</p></td>
                        <td><p class="title">Status</p></td>
                        <td><p class="title">Action</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <tr v-for="item in lists.data" :key="item.id">
                        <td><a :href="itemLink(item.client_id)">{{item.name}}</a></td>
                        <td>{{timeFormat(item.created_at)}}</td>
                        <td v-if="item.status">
                            <span class="badge badge-success">Enabled</span>
                        </td>
                        <td v-else>
                            <span class="badge badge-danger">Disabled</span>
                        </td>
                        <td>
                            <a class="btn btn-warning" :href="settingsLink(item.id,'edit')" role="button"><i class='fas fa-edit'></i></a>
                            <a class="btn btn-info" :href="settingsLink(item.id,'view')"  role="button"><i class='fas fa-eye'></i></a>
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

import SelectComponentV2 from '../SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from '../PaginatorComponent';
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
            hasRecords: false,
            isLoading:false,
            query:"",
            toLink: '/settings/loan/',
        }
    },
    mounted(){
        axios.get('/settings/api/get/loans')
            .then(res=>{ 
                this.lists  = res.data
            });
    },
    components:{
        Loading
    },
    methods : {
        settingsLink(item_id,type){
            if(type=="edit"){
                return '/settings/loan/edit/'+item_id
            }
                return '/settings/loan/view/'+item_id
        },
        timeFormat(item){
            return moment(item).format('LLL');
        },
        itemLink(item_id,){
            return '/settings/loan/'+item_id
        },
        inputSearch(){
            this.fetch()
        },
        filter(){
            if(this.office_id == null){
                return Swal.fire(
                    '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Alert!</span>',
                    'Please select level',
                    'error',
                )
            }
            this.fetch()
        },
        assignOffice(value){
            this.office_id = value['id']
            this.fetch()
        },
        checkIfHasRecords(){
            this.hasRecords = false
            if (this.viewableRecords > 0){
                this.hasRecords = true
            }
            
        },
        noOfficeSelected(){
            if(this.office_id == null){
                return true
            }
            return false
        },
        fetch(page){
            
            this.isLoading =true
            if(page==undefined){
                axios.get(this.queryString)
                .then(res => {
                    this.lists = res.data
                    this.checkIfHasRecords()
                    this.isLoading =false
                })
            }else{
                axios.get(this.queryString+'&page='+page)
                .then(res => {
                    this.lists = res.data
                    this.checkIfHasRecords()
                    this.isLoading =false
                })
            }

        },
        url(page=1){
            return `/clients/list?office_id=`+this.office_id+`&page=`+page
        },
        
    },
    computed : {
        queryString(){
            var str ="?"
            var params_count=0
            if(this.office_id!=""){
                params_count++
                str+="office_id="+this.office_id
            }
            if(this.query!=""){
                params_count++
                if(params_count > 1){
                    str+="&search="+this.query
                }else{
                    str+="search="+this.query
                }
            }
            return '/settings/api/get/loans'+str
        },
        totalRecords(){
            return numeral(this.lists.total).format('0,0')
        },
        viewableRecords(){
            return Object.keys(this.lists.data).length
        }

    }
}
</script>