<template>
    <div>
        <div class="col-12">
            <label for="" style="color:white" class="lead">Filter:</label>
            <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px" v-model="office_id"></v2-select>
            <button type="button" class="btn btn-primary" @click="filter">Add New</button>
        </div>
        <div v-if="hasRecords">
        <label for="">Search</label>
        <input type="text" class="form-control" v-model="query" v-debounce:300ms="inputSearch"/>
        <paginator :dataset="lists" @updated="fetch"></paginator>
        <table class="table" >
            <thead>
                <tr>
                    <td><p class="title">Client ID</p></td>
                    <td><p class="title">Name</p></td>
                    <td><p class="title">Linked To</p></td>
                </tr>
            </thead>
            <tbody>
                <tr v-for="client in lists.data" :key="client.id">
                    <td><a :href="clientLink(client.client_id)">{{client.client_id}}</a></td>
                    <td>{{client.firstname + ' ' + client.lastname}}</td>
                    <td>{{client.office.name}}</td>
                </tr>
            </tbody>
        </table>
        
        </div>
        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    </div>
</template>

<script>

import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from './PaginatorComponent';
import vueDebounce from 'vue-debounce'

Vue.use(vueDebounce, {
  listenTo: 'input'
})

import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
    data(){
        return {
            office_id: null,
            lists: [],
            hasRecords: false,
            isLoading:false,
            query:null,
            toClient: '/client/'
        }
    },
    components: {
        Loading
    },
    methods : {
        clientLink(client_id){
            return this.toClient + client_id
        },
        inputSearch(){
            console.log(this.query)
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
        },
        checkIfHasRecords(){
            this.hasRecords = false
            if (Object.keys(this.lists).length > 0){
                this.hasRecords = true
            }
               
        },
        fetch(page){
            this.isLoading =true
            axios.get(this.url(page), {
                    'office_id' :this.office_id
            })
            .then(res => {
                this.lists = res.data
                this.checkIfHasRecords()
                this.isLoading =false
            })

        },
        url(page=1){
            return `/clients/list?office_id=`+this.office_id+`&page=`+page
        }
    },
    watch: {
        // page(){
        //     this.fetch();
        // }
    },
    computed : {

    }
}
</script>