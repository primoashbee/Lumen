<template>
        <div class="card">
            <div class="card-header px-0 pt-0">
                <nav aria-label="breadcrumb px-4">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Permission List</li>
                    </ol>
                </nav>
                <div class="row px-4">
                    <div class="col-lg-6">
                        <h3 class="h3 w-7">Permission List</h3> 
                    </div>
                    <div class="col-lg-6">
                        <b-button class="float-right" variant="outline-warning" v-if="can('create_permission') || is('Super Admin')" @click="showModal">Create Permission</b-button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="w-100 px-3 mt-6">
                        <table class="table" >
                            <thead>
                                <tr>
                                    <td><p class="title">ID</p></td>
                                    <td><p class="title">Name</p></td>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="permission in permissionList.data">
                                    <td>{{ permission.id }}</td>
                                    <td>{{ permission.name }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="lead float-left text-right" style="color:white">Showing Records {{ permissionList.from}} - {{permissionList.to}} of {{permissionList.total}} </p>
                        <p class="lead float-right text-right" style="color:white">Total Records: {{ permissionList.total}} </p>
                        <div class="clearfix"></div>
                        <paginator :dataset="permissionList" @updated="fetch"></paginator>

                        <!-- <loading :is-full-page="true" :active.sync="isLoading" ></loading> -->
                    </div>
                </div> 
            </div>

            <b-modal id="permission-modal" v-model="show" size="lg" hide-footer modal-title="Create New Permission" title="Create New Permission" :header-bg-variant="background" :body-bg-variant="background">
                <form @submit.prevent="submit">
                    
                    <div class="form-group">
                        <label class="text-lg" for="permission_name">Permission Name</label>
                        <input type="text" v-model="fields.permission_name" id="permission_name" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    
                </form>
            </b-modal>
        </div>   
</template>


<script>
	import SelectComponentV2 from '../SelectComponentV2';
    import Swal from 'sweetalert2';
    import Paginator from '../PaginatorComponent';
    import vueDebounce from 'vue-debounce';

    export default{
    	data(){
    		return {
    			permissionList:[],
    			isLoading:false,
    			hasRecords: false,
                show:false,
                variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
                background:'dark',
                errors:{},
                fields:{
                    permission_name:""
                }
    		}
    	},
    	mounted(){
    		this.fetch();
    	},
    	methods:{
            fetch(page){

            	if(page==undefined){
                    axios.get('/settings/permissions/list?paginate=true').then(res => {                        
                        this.permissionList = res.data
                        this.checkIfHasRecords()
                    })
                }else{
                    axios.get('/settings/permissions/list?paginate=true&page='+page)
                    .then(res => {
                        this.permissionList = res.data
                        this.checkIfHasRecords()
                    })
                }
            },
            checkIfHasRecords(){
                this.hasRecords = false
                if (this.viewableRecords > 0){
                    this.hasRecords = true
                }
            },
            showModal(){
                this.show = true
            },
            submit(){
                axios.post('/settings/create/permission', this.fields)
                .then(res => {
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK',
                        allowEnterKey: true // default value
                        })
                        .then(res=>{
                            location.reload()
                    })
                    .catch(error => {
                 
                            this.errors = error.response.data
                    })
                })
            }
    	}
    }
</script>