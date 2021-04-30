<template>
<div>
      
    <div class="card">
		<div class="card-header">
			<h3 class="h3"><b><a :href="clientLink" style="text-decoration:none;color:white">{{full_name}}</a></b> - Dependents</h3>
		</div>
		<div class="card-body">
            <a :href="linkCreateDependent"><button class="btn btn-primary float-right">Create Dependents</button></a>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <td>
                            <p class="title">Application Number</p>
                        </td>
                        <td>
                            <p class="title">Unit of Plan</p>
                        </td>
                        <td>
                            <p class="title">Dependents</p>
                        </td>
                        <td>
                            <p class="title">Period Covered</p>
                        </td>
                        <td>
                            <p class="title">Expires In</p>
                        </td>
                        <td>
                            <p class="title">Status</p>
                        </td>
                        <td>
                            <p class="title">Action</p>
                        </td>
                    </tr>
                </thead>
                <tbody v-if="list!=null">
                     <tr v-for="item in list" :key="item.id">
                        <td>{{item.application_number}}</td>
                        <td>{{item.unit_of_plan}}</td>
                        <td>{{item.count}}</td>
                        <!-- <td>{{moment(item.activated_at)}} - {{moment(item.expires_at)}}</td> -->
                        <td>{{periodCovered(item.activated_at, item.expires_at)}}</td>
                        <td>{{expires(item.expires_at)}}</td>
                        <td>{{item.status}}</td>
                        <td>
                            <button class="btn btn-light" @click="showModal(item.application_number,item.pivotList)"><i class="fa fa-eye"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
<!-- Modal -->
    <div class="modal fade" id="dependents_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
        <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title text-center"  style="color:black" >Application Number: {{selected_application_number}}<b></b> </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
            <div class="modal-body">
                <table class="table table-condensed">
                    <thead>
                        <th></th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Relationship</th>
                    </thead>
                    <tbody v-if="selected_application_number!=null">
                        <tr v-for="(item,key) in selected_pivot_list" :key="key">
                            <td> {{key+1}}</td>
                         
                            <td> {{item.fullname}}</td>
                            <td> {{item.age}}</td>
                            <td> {{item.relationship}}</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="text-right"># of Dependents</td>
                            <td class="text-right">{{key}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
        </div>
    </div>
</div>
</template>

<script>
import Loading from 'vue-loading-overlay';
import Swal from 'sweetalert2';
import 'vue-loading-overlay/dist/vue-loading.css';
import ToggleButton from 'vue-js-toggle-button'
Vue.use(ToggleButton)
export default {
    components: {
        Loading
    },
    props:['client_id','full_name'],
    data(){
        return {
            list: null,
            "selected_application_number" : null,
            "selected_pivot_list": null
        }
    },
    mounted(){
        this.fetchData()
    },
    methods:{
        fetchData(){
            axios.get('/dependents/'+this.client_id)
            .then(res=>{
                this.list = res.data.list
            })
        },
        showModal(application_number,pivot_list){
            this.selected_application_number =  application_number
            this.selected_pivot_list = pivot_list
            $('#dependents_modal').modal('show')
        },
        periodCovered(start,end){
            if((start === undefined || start === null || start == 'NULL') && (end === undefined || end === null || end == 'NULL')){
                return '-';
            }
            return this.moment(start).format('LL') + ' - ' +  this.moment(end).format('LL')
        },
        moment(date){
            if(date === undefined || date === null || date == 'NULL'){
                return 'Unused';
            }
            return moment(date);
        },
        expires(date){

            if(date === undefined || date === null || date == 'NULL'){
                return '-'
            }
            return this.moment(date).diff(new Date(),'days');
        }
    },
    computed: {
        linkCreateDependent(){
            return '/client/'+this.client_id+'/create/dependents'
        },
   
        clientLink(){
			return '/client/'+this.client_id

        }

    }

}
</script>

<style>
    .swal2-title{
        color:black !important;
    }
</style>