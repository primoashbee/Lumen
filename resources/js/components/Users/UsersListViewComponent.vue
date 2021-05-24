<template>
        <div class="row">
            <div class="col-lg-6 float-left d-flex">

                <label for="" style="color:white" class="lead mr-2">Filter:</label>
                <v2-select @officeSelected="assignOffice" class="d-inline-block" style="width:500px;" v-model="office_id"></v2-select>
               
            </div>

            <div class="col-lg-6 float-left d-flex">
                <label for="search_item" style="color:white" class="lead mr-2">Search:</label>
                 <input type="text" id="search_item" class="form-control border-light pb-2" v-model="query"
                v-debounce:300ms="inputSearch"/>
            </div>


            <div class="w-100 px-3 mt-6">
                <table class="table" >
                    <thead>
                        <tr>
                            <td><p class="title">ID</p></td>
                            <td><p class="title">Email</p></td>
                            <td><p class="title">Name</p></td>
                            <td><p class="title">Linked To</p></td>
                            <td><p class="title">Role</p></td>
                            <td><p class="title">Action</p></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="user in usersList.data">
                            <td>{{user.id}}</td>
                            <td>{{user.email}}</td>
                            <td>{{user.firstname + ' ' + user.middlename + ' ' + user.lastname}}</td>
                            <td>
                                <span v-for = "office in user.office" class="item">{{office.name}}</span>
                            </td>
                            <td>
                                <span v-for = "role in user.roles" class="item">{{role.name}}</span>  
                            </td>
                            <td>
                                <a :href="userLink(user.id)">
                                    <b-button>
                                        Edit
                                    </b-button>
                                </a>
                                <b-button :id="user.id" @click="showModal">
                                    Reset
                                </b-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="lead float-left text-right" style="color:white">Showing Records {{usersList.from}} - {{usersList.to}} of {{usersList.total}} </p>
                <p class="lead float-right text-right" style="color:white">Total Records: {{usersList.total}} </p>
                <div class="clearfix"></div>
                <paginator :dataset="usersList" @updated="fetch"></paginator>

                <!-- <loading :is-full-page="true" :active.sync="isLoading" ></loading> -->
            </div>

            <b-modal id="office-modal" v-model="show" size="lg" hide-footer modal-title="Change Password" title="Change Password" :header-bg-variant="background" :body-bg-variant="background">
                <form @submit.prevent="submit">
                    
                    <div class="form-group">
                        <label class="text-lg" for="password"> Password</label>
                        <input type="password" v-model="fields.password" id="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="text-lg" for="password-confirm">Confirm Password</label>
                        <input type="password" v-model="fields.password_confirmation" id="password_confirmation" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    
                </form>
            </b-modal>
        </div>    
</template>

<style>
    .item + .item:before {
      content: " , ";
    }
</style>

<script>
    import SelectComponentV2 from '../SelectComponentV2';
    import Swal from 'sweetalert2';
    import Paginator from '../PaginatorComponent';
    import vueDebounce from 'vue-debounce';

    export default{
        data(){
            return {
                usersList:[],
                query:"",
                office_id:"",
                isLoading:false,
                hasRecords: false,
                link:'/users/list',
                variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
                background:'dark',
                show:false,
                fields:{
                    id:"",
                    password:"",
                    password_confirmation:""
                }
            }
        },
        mounted(){
            this.fetch();
        },
        computed:{
            fetchUsers(){
                var str = this.link
                var params_count=0
                if (this.office_id != "") {
                    str+="?&office_id="+this.office_id
                }
                if(this.query!=""){
                    params_count++
                    if(params_count > 1){
                        str+="?&search="+this.query
                    }else{
                        return str+="?&search="+this.query
                    }
                }
                return str
            },
        },
        methods:{
            submit(){
                axios.post('/user/changepass/'+this.fields.id,this.fields).
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
                            console.log(error)
                            this.errors = error.response.data
                        })
            },
            showModal(e){
                this.show = true
                this.fields.id = e.currentTarget.getAttribute('id')
            },
            assignOffice(value){
                this.office_id = value['id'];

                this.fetch()
            },
            inputSearch(){
                this.fetch()
            },
            fetch(page){
                if(page==undefined){
                    axios.get(this.fetchUsers).then(res => {
                        this.usersList = res.data
                        this.checkIfHasRecords()
                        this.isLoading =false
                    })
                }else{
                    axios.get(this.fetchUsers+'?page='+page)
                    .then(res => {
                        this.checkIfHasRecords()
                        this.isLoading =false
                        this.usersList = res.data
                    })
                }
            },
            checkIfHasRecords(){
                this.hasRecords = false
                if (this.viewableRecords > 0){
                    this.hasRecords = true
                }
            },
            userLink(value){
                return '/edit/user/'+value
            }
        }
    }
</script>
