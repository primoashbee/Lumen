<style>
    #user_form .tags-input input[type="text"]{
        color: #fff;
    }
    #user_form .tags-input-wrapper-default{
        background: transparent;
    }
   
    #user_form .tags-input-wrapper-default.active{
        box-shadow: none;
        border-color:  #dbdbdb;
    }
</style>
<template>
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header px-0 py-0">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="/settings">Settings</a></li>
                            <li class="breadcrumb-item"><a href="/settings/users">User List</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit User</li>
                            </ol>
                        </nav>
                    <h3 class="h3 ml-3">Change User</h3>
                </div>
                    <div class="card-body">
                        <form class="row" id="user_form" @submit.prevent="submit">
                            <div class="col-lg-8">
                                <div class="col-lg-12 px-0 w-10">
                                    <div class="form-group w-3 d-inline-block">
                                        <label for="email">Email</label>
                                        <input type="text" v-model="fields.email" class="form-control" :class="hasError('email') ? 'is-invalid' : ''">
                                        <div class="invalid-feedback" v-if="hasError('email')">
                                            {{ errors.email[0] }}
                                        </div>
                                    </div>
                                    <div class="form-group ml-10 d-inline-block align-top mt-8">
                                        <div class="p0 form-check">
                                            <label class="form-check-label" for="is_active">
                                                <input class="form-check-input cb-type" type="checkbox" name="is_active" id="is_active" v-model="fields.is_active">
                                                <span class="form-check-sign">
                                                <span class="check"></span>
                                                </span>
                                                <label for="is_active">Active</label>
                                            </label>
                                        </div>
                                    </div>  
                                </div>
                                <div class="row">
                                    <div class="form-group col-lg-4">
                                        <label for="">First Name</label>
                                        <input type="text" v-model="fields.firstname" class="form-control" :class="hasError('firstname') ? 'is-invalid' : ''">
                                        <div class="invalid-feedback" v-if="hasError('firstname')">
                                            {{ errors.firstname[0] }}
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <label for="">Middle Name</label>
                                        <input type="text" v-model="fields.middlename" class="form-control" >
                                    </div>
                                    <div class="form-group col-lg-4">
                                        <label for="">Last Name</label>
                                        <input type="text" v-model="fields.lastname" class="form-control" :class="hasError('lastname') ? 'is-invalid' : ''">
                                        <div class="invalid-feedback" v-if="hasError('lastname')">
                                            {{ errors.lastname[0] }}
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-lg-6">
                                        <label for="gender" >Gender:</label>
                                        <select input id="gender" type="text" class="form-control" v-model="fields.gender" :class="hasError('gender') ? 'is-invalid' : ''">
                                            <option value="">Please Select</option>
                                            <option value="MALE">MALE</option>
                                            <option value="FEMALE">FEMALE</option>
                                        </select>
                                        <div class="invalid-feedback" v-if="hasError('gender')">
                                            {{ errors.gender[0] }}
                                        </div>
                                    </div>
                                    <div class="form-group col-lg-6">
                                        <label for="birthday">Birthday:</label>
                                        <input id="birthday" type="text" class="form-control" v-model="fields.birthday" :class="hasError('birthday') ? 'is-invalid' : ''">
                                        <div class="invalid-feedback" v-if="hasError('birthday')">
                                            {{ errors.birthday[0] }}
                                        </div>
                                    </div>
                                </div>
                                    <role-filter :default="fields.roles" @roleSelected="assignRole"></role-filter>
                                    <org-structure :default="fields.office" @officeSelected="assignOffice" :class="hasError('office_ids') ? 'is-invalid' : ''"></org-structure> 
                                    <div class="invalid-feedback" v-if="hasError('office_ids')">
                                        {{ errors.office_ids[0] }}
                                    </div>
                                <div class="col-lg-12 px-0">
                                    <div class="form-group">
                                        <label for="Notes">Notes</label>
                                        <textarea id="" cols="30" v-model="fields.notes" rows="10" class="form-control"></textarea>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>

</template>



<script>
import Swal from 'sweetalert2';
import 'vue-loading-overlay/dist/vue-loading.css';
    export default {
        props:['user'],
        data(){
            return {
                fields:{
                    id:"",
                    email:"",
                    firstname:"",
                    lastname:"",
                    middlename:"",
                    birthday:"",
                    notes:"",
                    is_active:"",
                    gender:"",
                    role_ids:[],
                    office_ids:[]
                },
                errors:{}

            }
        },
        mounted(){
            var vm = this
            var userInfo = JSON.parse(this.user);
            $.each(userInfo,function(k,v){
                vm.fields[k] = v
                if (k == 'roles') {
                    $.each(vm.fields.roles,function(k,v){
                        vm.fields.role_ids.push({key:v.id,value:v.name}) 
                    })
                }
            })
        },
        methods:{
            assignRole(value){
                this.fields.role_ids = value;
            },
            assignOffice(value){
                this.fields.office_ids = value;
            },
            userLink(value){
                return '/settings/edit/user/'+value
            },
            submit(){
                axios.post(this.userLink(this.fields.id), this.fields).then(res =>
                    {
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
                    }
                ).catch(error=>{
                    this.errors = error.response.data.errors || {}
                })
                
            },
            hasError(value){
                return this.errors.hasOwnProperty(value)
            }
        }
    }
</script>