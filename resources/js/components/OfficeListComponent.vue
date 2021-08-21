 <template>
 
        <div class="row">
            <div class="col-lg-6 float-left d-flex">
                <label for="" style="color:white" class="lead mr-2">Search:</label>
                <input type="text" id="office_client" class="form-control border-light pb-2" v-model="query"
                v-debounce:300ms="inputSearch"/>
            </div>
            <div class="col-lg-6 text-right">
                <a :href="createOfficeLink()" type="submit" class="btn btn-primary px-8">Create {{this.officeName(this.level)}}</a>
            </div>  
            <div class="w-100 px-3 mt-6">
                <table class="table" >
                    <thead>
                        <tr>
                            <td><p class="title">Code</p></td>
                            <td><p class="title">Name</p></td>
                            <td><p class="title">Linked To</p></td>
                            <td><p class="title">Action</p></td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="office in officeList.data">
                            <td>{{office.code}}</td>
                            <td>{{office.name}}</td>
                            <td>{{office.parent.name}}</td>
                            <td>
                                <b-button :id="office.id" @click="showModal">
                                    <i class="far fa-edit"></i>
                                </b-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p class="lead float-left text-right" style="color:white">Showing Records {{officeList.from}} - {{officeList.to}} of {{totalRecords}} </p>
                <p class="lead float-right text-right" style="color:white">Total Records: {{totalRecords}} </p>
                <div class="clearfix"></div>
                <paginator :dataset="officeList" @updated="fetch"></paginator>

                <loading :is-full-page="true" :active.sync="isLoading" ></loading>
            </div>

             <b-modal id="office-modal" v-model="show" size="lg" hide-footer modal-title="Change Office" title="Edit Office" :header-bg-variant="background" :body-bg-variant="background">
                <form @submit.prevent="submit">
                    <div class="form-group mt-4">
                        <label class="text-lg">Assign To:</label>
                        <v2-select @officeSelected="assignOffice" :list_level="list_level" :default_value="fields.parent_id" v-bind:class="officeHasError ? 'is-invalid' : ''"></v2-select>
                        <div class="invalid-feedback" v-if="officeHasError">
                            {{ errors.office_id[0]}}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="text-lg" for="code">Code</label>
                        <div class="input-group mb-3">
                          <input type="text" class="form-control" id="code" aria-describedby="basic-addon3"
                          v-model="fields.code" v-bind:class="codeHasError ? 'is-invalid' : ''" :readonly="code_readonly">
                          <div class="invalid-feedback" v-if="codeHasError">
                                {{ errors.code[0]}}
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-lg" for="cluster_code">Name:</label>
                        <input type="text" v-model="fields.name" id="name" class="form-control" v-bind:class="nameHasError ? 'is-invalid' : ''" :readonly="checkLevel()">
                        <div class="invalid-feedback" v-if="nameHasError">
                            {{ errors.name[0]}}
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="text-lg">Notes</label>
                        <textarea class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    
                </form>
            </b-modal>

        </div>

       

</template>
<style type="text/css">
    @import "~vue-multiselect/dist/vue-multiselect.min.css";
    .modal-body .close,.modal-header .close{
        color: #fff!important;
    }
    .modal.fade.show{
        background: rgba(255,255,255,0.3);
    }
    .modal-content{
        border-color: #fff;
    }
    .modal-title{
        font-size: 1.4rem;
    }
    .multiselect__tags{
      border-color:#2b3553!important;
    }
    .multiselect__input,.modal .multiselect__single, .multiselect__tags{
      background: transparent!important;
      
    }
    
</style>

<script>
    import SelectComponentV2 from './SelectComponentV2';
    import Swal from 'sweetalert2';
    import Paginator from './PaginatorComponent';
    import vueDebounce from 'vue-debounce'

    Vue.use(vueDebounce, {
      listenTo: 'input'
    })
    import Loading from 'vue-loading-overlay';
    import 'vue-loading-overlay/dist/vue-loading.css';
    export default{
        props:['level','list_level'],
        components:{
            Loading
        },
        data(){
           return { 
                officeList:[],
                toOffice:"/settings/edit/office/",
                query:"",
                fields:{
                    "id":"",
                    "office_id":"",
                    "parent_id":"",
                    "level":"",
                    "code":"",
                    "name":"",
                    
                },
                hasRecords: false,
                isLoading:false,
                code_readonly:true,
                name_readonly:false,
                variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
                background:'dark',
                show:false,
                errors:{}
           }
        },
        methods:{
            checkLevel(){
                if (this.fields.level == "cluster") {
                    return this.name_readonly = true
                }
            },
            createOfficeLink(){
                return '/settings/create/office/'+this.level
            },
            inputSearch(){
                this.fetch()
            },
            toEditOfficeLink(office_code){
                return this.toOffice + office_code
            },
            officeName(string) 
            {
                return string.charAt(0).toUpperCase() + string.slice(1).replace(/_/,' ');
            },
            showModal(e){
                this.fields.id = e.currentTarget.getAttribute('id')
                this.getSingleOffice()
            },
            getSingleOffice(){
                axios.get(this.toEditOfficeLink(this.fields.id)).
                then(res => {
                    var vm = this
                    $.each(res.data,function(k,v){
                        vm.fields[k] = v
                    })
                    vm.fields.office_id = vm.fields.parent_id;
                    vm.show = true
                }).catch(error =>{
                    Swal.fire({
                        icon: 'error',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Error!</span>',
                        text: "Office not found",
                        confirmButtonText: 'OK'
                    })
                })

            },
            checkIfHasRecords(){
                this.hasRecords = false
                if (this.viewableRecords > 0){
                    this.hasRecords = true
                }
            },
            assignOffice(value){
                this.fields.office_id = value['id']
            },
            submit(){
                if (this.fields.level == "cluster") {
                    this.fields.name = this.officeInfo.code
                }
                axios.post(this.toEditOfficeLink(this.fields.id), this.fields)
                .then(res=>{
                    this.isLoading = false
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK'
                    })
                    .then(res=>{
                        location.reload();
                    })  
                })
                .catch(error=>{
                    this.errors = error.response.data.errors || {}
                })
            },
            fetch(page){
                if(page==undefined){
                    axios.get(this.fetchOfficeLink).then(res => {
                        this.officeList = res.data
                        this.checkIfHasRecords()
                        this.isLoading =false
                    })
                }else{
                    axios.get(this.fetchOfficeLink+'?page='+page)
                    .then(res => {
                        this.checkIfHasRecords()
                        this.isLoading =false
                        this.officeList = res.data
                    })
                }
            }
        },
        mounted() {
            

            this.$root.$on('bv::modal::hidden', (bvEvent) => {
                this.errors = {}
            })
            this.$root.$on('bv::modal::close', (bvEvent) => {
                this.errors = {}
            })
        },
        created(){  
            this.fetch()
        },
        computed:{
            totalRecords(){
                return numeral(this.officeList.total).format('0,0')
            },
            viewableRecords(){
                return Object.keys(this.officeList.data).length
            },
            fetchOfficeLink(){
                var str ="/settings/office/list/"+this.level
                var params_count=0
                if(this.query!=""){
                    params_count++
                    if(params_count > 1){
                        str+="?&search="+this.query
                    }else{
                        str+="?&search="+this.query
                    }
                }
                
                return str
            },
            hasErrors(){
                return Object.keys(this.errors).length > 0;
            },
            officeHasError(){
                return this.errors.hasOwnProperty('office_id')
            },
            nameHasError(){
                return this.errors.hasOwnProperty('name')
            },
            codeHasError(){
                return this.errors.hasOwnProperty('code')
            }
  
        }

    }
</script>