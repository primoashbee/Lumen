<template>
  <div class="row">
    <div class="col-lg-6 float-left d-flex">
      <label for="" style="color: white" class="lead mr-2">Search:</label>
      <input
        type="text"
        id="office_client"
        class="form-control border-light pb-2"
        v-model="query"
        v-debounce:300ms="inputSearch"
      />
    </div>
    
    <div class="col-lg-6 text-right">
        <button class="btn btn-primary" @click="showCreateModal">Create Holiday</button>
    </div>
    <div class="w-100 px-3 mt-6">
      <table class="table">
        <thead>
          <tr>
            <td><p class="title">ID</p></td>
             <td>
              <p class="title">Office</p>
            </td>
            <td><p class="title">Name</p></td>
            <td><p class="title">Date</p></td>
            <td><p class="title">Action</p></td>
          </tr>
        </thead>
        <tbody>
          <tr v-for="holiday in holidayList.data" :key="holiday.id">
            <td>{{ holiday.id }}</td>
            <td>{{holiday.office.name}}</td>
            <td>{{ holiday.name }}</td>
            <td>{{ formatDate(holiday.date) }}</td>
            <td>
              <b-button :id="holiday.id" @click="showUpdateModal">
                  <i class="far fa-edit"></i>
              </b-button>
            </td>
          </tr>
        </tbody>
      </table>
      <p class="lead float-left text-right" style="color: white">
        Showing Records {{ holidayList.from }} - {{ holidayList.to }} of
        {{ totalRecords }}
      </p>
      <p class="lead float-right text-right" style="color: white">
        Total Records: {{ totalRecords }}
      </p>
      <div class="clearfix"></div>
      <paginator :dataset="holidayList" @updated="fetch"></paginator>
      <loading :is-full-page="true" :active.sync="isLoading"></loading>
    </div>

    <b-modal
      v-model="modalForCreate"
      size="lg"
      hide-footer
      modal-title="Create Holiday"
      title="Create Holiday"
      :header-bg-variant="background"
      :body-bg-variant="background"
    >
      <form @submit.prevent="submit">
        <div class="form-group mt-4">
          <label class="text-lg">Assign To:</label>
          <v2-select
            @officeSelected="assignOffice"
            v-bind:class="hasErrors('office_id') ? 'is-invalid' : ''"
          ></v2-select>
          <div class="invalid-feedback" v-if="hasErrors('office_id')">
            {{ errors.office_id[0] }}
          </div>
        </div>
        <div class="form-group">
          <label class="text-lg" for="code">Date</label>
          <div class="input-group mb-3">
            <input
              type="date"
              class="form-control"
              aria-describedby="basic-addon3"
              v-model="fields.date"
              v-bind:class="hasErrors('date') ? 'is-invalid' : ''"
            />
            <div class="invalid-feedback" v-if="hasErrors('date')">
              {{ errors.holiday_date[0] }}
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="text-lg" for="cluster_code">Name:</label>
          <input
            type="text"
            v-model="fields.name"
            id="name"
            class="form-control"
            v-bind:class="hasErrors('name') ? 'is-invalid' : ''"
          />
          <div class="invalid-feedback" v-if="hasErrors('name')">
            {{ errors.name[0] }}
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </b-modal>

    <b-modal
      v-model="modalForUpdate"
      size="lg"
      hide-footer
      modal-title="Edit Holiday"
      title="Edit Holiday"
      :header-bg-variant="background"
      :body-bg-variant="background"
    >
    <form @submit.prevent="updateHoliday" method="POST">
        <div class="form-group mt-4">
          <label class="text-lg">Assign To:</label>
          <v2-select
            @officeSelected="assignOffice"
            v-bind:class="hasErrors('office_id') ? 'is-invalid' : ''"
            :default_value="this.fields.office_id"
          ></v2-select>
          <div class="invalid-feedback" v-if="hasErrors('office_id')">
            {{ errors.office_id[0] }}
          </div>
        </div>
        <div class="form-group">
          <label class="text-lg" for="code">Date</label>
          <div class="input-group mb-3">
            <input
              type="date"
              class="form-control"
              aria-describedby="basic-addon3"
              v-model="fields.date"
              v-bind:class="hasErrors('date') ? 'is-invalid' : ''"
            />
            <div class="invalid-feedback" v-if="hasErrors('date')">
              {{ errors.holiday_date[0] }}
            </div>
          </div>
        </div>

        <div class="form-group">
          <label class="text-lg" for="cluster_code">Name:</label>
          <input
            type="text"
            v-model="fields.name"
            id="name"
            class="form-control"
            v-bind:class="hasErrors('name') ? 'is-invalid' : ''"
          />
          <div class="invalid-feedback" v-if="hasErrors('name')">
            {{ errors.name[0] }}
          </div>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
      </form>
    </b-modal>
  </div>
</template>

<script>
import SelectComponentV2 from "./SelectComponentV2";
import Swal from "sweetalert2";
import Paginator from "./PaginatorComponent";
import vueDebounce from "vue-debounce";
  import moment from 'moment'

Vue.use(vueDebounce, {
  listenTo: "input",
});
import Loading from "vue-loading-overlay";
import "vue-loading-overlay/dist/vue-loading.css";

export default {
    components:{
        Loading
    },
  data() {
    return {
      holidayList: [],
      query: "",
      fields: {
        id:"",
        date: "",
        name: "",
        office: "",
      },
      hasRecords: false,
      isLoading: false,
      code_readonly: true,
      name_readonly: false,
      variants: [
        "primary",
        "secondary",
        "success",
        "warning",
        "danger",
        "info",
        "light",
        "dark",
      ],
      modalForCreate:false,
      modalForUpdate:false,
      background: "dark",
      show: false,
      errors: {},
    };
  },
  mounted() {
    this.fetch();
  },
  computed:{
    totalRecords(){
        return numeral(this.holidayList.total).format('0,0')
    },
    fetchHolidayLink(){
        var str ="/settings/holidays/list"
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
  }
  },
  methods: {
    
    hasErrors(field) {
      return this.errors.hasOwnProperty(field);
    },
    showCreateModal() {
      this.fields.name=""
      this.fields.office=""
      this.fields.date = ""
      this.modalForCreate = true;
    },
    showUpdateModal(e) {
      // console.log(e)
      this.fields.id = e.currentTarget.getAttribute('id')    
      axios.get('/settings/holiday/edit/'+this.fields.id, this.fields).
      then(res => {
        var holiday = res.data
         this.fields.id = holiday.id
         this.fields.name = holiday.name
         this.fields.date = moment(holiday.date).format('YYYY-MM-DD')
         this.fields.office_id = holiday.office_id
      }).
      catch(error => {
          this.errors = error.response.data.errors 
      })
      this.modalForUpdate = true;
    },
    assignOffice(value) {
      this.fields.office_id = value["id"];
    },
    inputSearch() {
      this.fetch();
    },
    fetch(page) {
      if (page == undefined) {
        axios.get(this.fetchHolidayLink).then((res) => {
          this.holidayList = res.data;
          this.checkIfHasRecords();
          this.isLoading = false;
        });
      } else {
        axios.get(this.fetchHolidayLink + "?page=" + page).then((res) => {
          this.checkIfHasRecords();
          this.isLoading = false;
          this.holidayList = res.data;
        });
      }
    },
    
    checkIfHasRecords() {
      this.hasRecords = false;
      if (this.viewableRecords > 0) {
        this.hasRecords = true;
      }
    },
    formatDate(date) {
        return moment(date).format('MMMM DD, YYYY');
    },
    submit(){
      axios.post('/settings/post/holiday', this.fields).
      then(res => {
        Swal.fire({
            icon: 'success',
            title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">Success!</span>',
            text: res.data.msg,
            confirmButtonText: 'OK'
        })
        // location.reload()
      }).
      catch(error=>{
        this.errors = error.response.data.errors || {}
          var html="";
          $.each(this.errors, function(k, v){ 
            html += '<p class="text-left">'+ v +'</p>'
          })
        Swal.fire({
            icon: 'error',
            title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1.875em;font-weight:600">OOPPPSSSSS!</span>',
            html: html +'</ul>'
        })
      });
    },

    updateHoliday(){
      axios.put('/settings/holiday/edit/'+ this.fields.id, this.fields).
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

          $.each(this.fields, function(k){
            k =""
          })
      })
      .catch(
        error => {
            this.errors = error.response.data.errors 
      })
    }
  },
};
</script>
