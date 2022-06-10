<template>
    <div>
        <div class="row">
            <div class="col-lg-6">
                <label for="" style="color:white" class="lead mr-2">Filter:</label>
                <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" v-model="office_id"></v2-select>
                <!-- <button type="button" class="btn btn-primary" @click="filter">Add New</button> -->
                
            </div>
            <div class="col-lg-6 float-right d-flex">
                <label for="" style="color:white" class="lead mr-2">Search:</label>
                <input type="text" id="search_client" class="form-control border-light pb-2" v-model="query" v-debounce:300ms="inputSearch"/>
                <div>
            </div>  
        </div>
        
 
        <div class="w-100 px-3 mt-6" >
            <button @click.prevent="generateId" v-if="showExport" class="btn btn-primary float-right px-3">Export ID</button>
            <table class="table" >
                <thead>
                    <tr>
                        <td><p class="title"><input type="checkbox" @change="checkall($event)"></p></td>
                        <td><p class="title">Client ID</p></td>
                        <td><p class="title">Name</p></td>
                        <td><p class="title">Linked To</p></td>
                    </tr>
                </thead>
                <tbody v-if="hasRecords">
                    <tr v-for="client in lists.data" :key="client.id">
                        <td><input type="checkbox" class="checkbox" :id="client.id" @change="checked(client.client_id,$event)"></td>
                        <td><a class="text-lg" :href="clientLink(client.client_id)">{{client.client_id}}</a></td>
                        <td class="text-lg">{{client.firstname + ' ' +client.middlename + ' ' + client.lastname}}</td>
                        <td class="text-lg">{{client.office.name}}</td>
                    </tr>
                </tbody>
            </table>
            <p class="lead float-left text-right" style="color:white">Showing Records {{lists.from}} - {{lists.to}} of {{totalRecords}} </p>
            <p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
            <div class="clearfix"></div>
            <paginator :dataset="lists" @pageSelected="fetch"></paginator>
        </div>
        </div>

        <loading :is-full-page="true" :active.sync="isLoading" ></loading>
    </div>
</template>

<script>
import SelectComponentV2 from './SelectComponentV2';
import Swal from 'sweetalert2';
import Paginator from './PaginatorComponentV2';
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
            errors:{},
            office_id: "",
            lists: [],
            hasRecords: false,
            isLoading:false,
            query:"",
            toClient: '/client/',
            export_id_form:{
                clients_id: [],
            }
        }
    },
    components:{
        Loading
    },
    methods : {
        clientLink(client_id){
            return this.toClient + client_id
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
            return `/client/list?office_id=`+this.office_id+`&page=`+page
        },
        checked(client,event){
            this.removeFromArray(this.export_id_form.clients_id, client)
            if(event.target.checked){
				this.export_id_form.clients_id.push(client)
			}
        },
        generateId(){
            this.isLoading = true;
            this.export_id_form.export = true;
            axios.post('/bulk/generate/id',this.export_id_form,{responseType:'blob'})
            .then(res=>{
                const url = window.URL.createObjectURL(new Blob([res.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', res.headers.filename);
                document.body.appendChild(link);
                link.click();
                this.isLoading =false;
            }).catch(errors => {
                // this.errors = errors.response.data
                console.log(errors);
            })
        },
        removeFromArray(obj, id){
			
			if(obj !== undefined){
				var res = obj.filter(item=>{
					return item != id;
				})
				this.export_id_form.clients_id = res
			}	
		},
        checkall(e){
            
            $('.checkbox').each(function(k,v){
                if(e.target.checked){
                    if(!$(v).prop('checked')){
                        $(v).click()
                    }
                }else{
                    if($(v).prop('checked')){
                        $(v).click()
                    }
                }
                
                
            })
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
            return '/wApi/client/list'+str
        },
        totalRecords(){
            return numeral(this.lists.total).format('0,0')
        },
        viewableRecords(){
            return Object.keys(this.lists.data).length
        },
        showExport(){
            return this.export_id_form.clients_id.length > 0 ? true : false
        }

    }
}
</script>