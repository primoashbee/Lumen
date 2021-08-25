<template>
        <div class="card">
            <div class="card-header px-0 pt-0">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Roles List</li>
                    </ol>
                </nav>
                <div class="row px-3">
                    <div class="col-lg-6">
                        <h3 class="h3 w-7">Roles List</h3> 
                    </div>
                    <div class="col-lg-6">
                        <button class="btn btn-primary float-right" type="button" @click="showModalForCreate">Create Roles</button>
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
                                    <td><p class="title">Action</p></td>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="role in rolesList.data">
                                    <td>{{role.id}}</td>
                                    <td>{{role.name}}</td>
                                    <b-button class="mt-2" :id="role.id" @click="showModalForUpdate">
                                        <i class="fas fa-edit"></i></a>
                                    </b-button>
                                    
                                </tr>
                            </tbody>
                        </table>
                        <p class="lead float-left text-right" style="color:white">Showing Records {{rolesList.from}} - {{rolesList.to}} of {{rolesList.total}} </p>
                        <p class="lead float-right text-right" style="color:white">Total Records: {{rolesList.total}} </p>
                        <div class="clearfix"></div>
                        <paginator :dataset="rolesList" @updated="fetch"></paginator>

                        <!-- <loading :is-full-page="true" :active.sync="isLoading" ></loading> -->
                    </div>
                </div> 
            </div>

            <b-modal id="create-new-role-modal" v-model="modalForCreate" size="lg" hide-footer modal-title="Create New Role" title="Create New Role" :header-bg-variant="background" :body-bg-variant="background">
                <form @submit.prevent="addRole">
                    <div class="form-group">
                        <label for="role">Role name:</label>
                        <input type="text" v-model="fields.name" :class="hasError('name') ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="hasError('name')">
                            {{ errors.name[0] }}
                        </div>
                        <permission-filter @permissionSelected="assignPermission"></permission-filter>
                    </div>
                    <input type="submit" class="btn btn-primary">
                </form>
            </b-modal>

            <b-modal id="create-new-role-modal" v-model="modalForUpdate" size="lg" hide-footer modal-title="Change Role" title="Create New Role" :header-bg-variant="background" :body-bg-variant="background">
                <form @submit.prevent="updateRole">
                    <div class="form-group">
                        <label for="role">Role name:</label>
                        <input type="text" v-model="fields.name" :class="hasError('name') ? 'is-invalid' : ''" class="form-control">
                        <div class="invalid-feedback" v-if="hasError('name')">
                            {{ errors.name[0] }}
                        </div>
                        <permission-filter @permissionSelected="assignPermission" :default="fields.permissions"></permission-filter>
                    </div>
                    <input type="submit" class="btn btn-primary">
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
                rolesList:[],
                isLoading:false,
                hasRecords: false,
                variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
                background:'dark',
                modalForCreate:false,
                modalForUpdate:false,
                errors:{},
                fields:{
                   id:"",
                   permission_ids:[],
                   name:""
                }
            }
        },
        mounted(){
            this.fetch();
        },
        methods:{
            fetch(page){
                if(page==undefined){
                    axios.get('/settings/roles/list?paginate=true').then(res => {
                        this.rolesList = res.data
                        this.checkIfHasRecords()
                    })
                }else{
                    axios.get('/settings/roles/list?paginate=true&page='+page)
                    .then(res => {
                        this.checkIfHasRecords()
                        this.rolesList = res.data
                    })
                }
            },

            addRole(){
                axios.post('/settings/create/role', this.fields).
                then(res => {
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
                }).
                catch(error => {
                    this.errors = error.response.data.errors 
                })
            },
            updateRole(){
                axios.post(this.roleLink(this.fields.id), this.fields)
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
                }).
                catch(error => {
                    this.errors = error.response.data.errors 
                })
            },
            checkIfHasRecords(){
                this.hasRecords = false
                if (this.viewableRecords > 0){
                    this.hasRecords = true
                }
            },
            roleLink(value){
                return '/settings/edit/role/'+value
            },
            hasError(value){
                return this.errors.hasOwnProperty(value)
            },
            showModalForCreate(){
                this.fields.name = ""
                this.modalForCreate = true
            },
            showModalForUpdate(e){
                this.fields.id = e.currentTarget.getAttribute('id')
                
                axios.get(this.roleLink(this.fields.id), this.fields).
                then(res => {
                    var roleInfo = res.data
                    var vm = this
                    $.each(roleInfo,function(k,v){
                        vm.fields[k] = v
                            if (k == 'permissions') {
                                $.each(vm.fields.permissions,function(k,v){
                                    vm.fields.permission_ids.push({key:v.id,value:v.name}) 
                                })
                            }
                    })
                    this.modalForUpdate = true
                }).
                catch(error => {
                    this.errors = error.response.data.errors 
                })

            },
            assignPermission(value){
                this.fields.permission_ids = value
            }
        }
    }
</script>