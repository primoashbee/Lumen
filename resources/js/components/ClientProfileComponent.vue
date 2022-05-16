<template>
<div class="row">
  <div class="col-lg-8">
    <div class="card pb-24">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/clients">Client List</a></li>
          <li class="breadcrumb-item active" aria-current="page">Profile</li>
        </ol>
      </nav>
      <div v-if="errors > 0" class="alert alert-danger mx-3">
        <ul v-for="(error,key) in errors" :key="key">
          
          <li>{{ error }}</li>
          
        </ul>
      </div>
      <div class="row">
        <div class="col-lg-4 profile-wrapper pl-8 pr-24">
          <div class="text-center profile-picture">
            <img :src="this.client_profile_picture_path" class="w-100 h-100 img-thumbnail" alt="Profile Photo">
          </div>
          <div class="mt-8">
              <h5 class="title text-2xl">Personal Details</h5>
              <div class="p-details mt-4">
                <p class="title text-lg">Birthday</p>
                <p class="text-light text-lg">{{client.birthday}}</p>
              </div>
              <div class="p-details mt-4">
                <p class="title text-lg">Birthplace</p>
                <p class="text-light text-lg">{{client.birthplace}}</p>
              </div>
              <div class="p-details mt-4">
                <p class="title text-lg">Gender</p>
                <p class="text-light text-lg">{{client.gender}}</p>
              </div>
              <div class="p-details mt-4">
                <p class="title text-lg">Civil Status</p>
                <p class="text-light text-lg">{{client.civil_status}}</p>
              </div>
              <div class="p-details mt-4">
                <p class="title text-lg">Educational Attainment</p>
                <p class="text-light text-lg">{{client.education}}</p>
              </div>
              <div class="p-details mt-4">
                <p class="title text-lg">Facebook Account </p>
                <p class="text-light text-lg">{{client.fb_account}}</p>
              </div>
          </div>
        </div>
         <div class="col-lg-8 profile-wrapper">
            <a v-if="can('edit_client') || is('Super Admin')" :href="toEditClient" type="submit" class="btn btn-primary float-right mr-2">Edit Client</a>
            <a href="#status" data-toggle="modal" type="submit" class="btn btn-primary float-right mr-2">Change Status</a>
            <button class="btn btn-primary float-lg-right mr-4" @click.prevent="generateId">
              <i class="fas fa-user"></i>
            </button>
            <div class="p-details">

              <p class="title text-2xl">{{client.firstname + ' '+client.middlename+' '+ client.lastname}}</p>
              <p class="text-light text-base">Nickname: {{client.nickname}}</p>
            </div>
            <div class="row">
              <div class="col-lg-6">
                <div class="p-details mt-4 d-inline-block file-input-signature">
                  <p class="title text-xl mb-2">Signature</p>
                  <img :src="client_signature_path" class="w-100 img-thumbnail" alt="Profile Photo">
                </div>
              </div>
              <div class="col-lg-6">
                <div class="p-details mt-4">
                  <p class="title text-xl">OFFICE</p>
                  <p class="title text-xl">Created at</p>
                  <p class="text-light text-lg">{{moment(client.created_at)}}</p>
                </div>
                <div class="p-details mt-2">
                  
                  <p v-if="client.status == 'Active'" class="title text-xl">Status:<span class="badge badge-pill badge-success">{{client.status}}</span></p>
                  
                  <p v-if="client.status !== 'Active'" class="title text-xl">Status:<span class="badge badge-pill badge-light">{{client.status}}</span></p>
                  

                </div>
              </div>
              <div class="profile-menu-tabs mt-8 pr-8 w-100">
                 <ul class="nav nav-tabs" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#personal" role="tab" aria-controls="home" aria-selected="true">Business
                      Information</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="profile" aria-selected="false">Contact Information</a>
                  </li>
                </ul>
                <div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
                  <div class="tab-pane fade show active" id="personal" role="tabpanel" aria-labelledby="nav-home-tab">
                    <div class="accordion" id="businesses">
                        <div v-for="(business,key) in client.businesses" :key="key" class="card">
                          <div class="card-header businesses-wrapper" :id="'business_'+key+1">
                            <h2 class="mb-0 business-item">
                              <button class="btn btn-link collapsed w-9 text-left pl-0" type="button" data-toggle="collapse" :data-target="'#collapse_'+key+1" aria-expanded="false" :aria-controls="'collapse_'+key+1">
                                <span class="title text-xl mr-8 text-white">Business #{{key + 1}}</span>
                              </button>
                            </h2>
                          </div>
                          <div :id="'collapse_'+key+1" class="collapse" :aria-labelledby="'business_'+key+1" data-parent="#businesses" style="">
                            <div class="card-body">
                              <div class="p-details">
                                <span class="title text-m mr-8">Business Address:</span>
                                <span class="text-light text-lg">{{business.business_address}}</span>
                              </div>
                              <div class="p-details mt-4">
                                <span class="title text-m mr-8">Service Type:</span>
                                <span class="text-light text-lg"> {{business.service_type}}</span>
                              </div>
                              <div class="p-details mt-4">
                                <span class="title text-m mr-8">Monthly Gross Income</span>
                                <span class="text-light text-lg"> {{money(business.monthly_gross_income)}}</span>
                              </div>
                              <div class="p-details mt-4">
                                <span class="title text-m mr-8">Monthly Operating Expense</span>
                                <span class="text-light text-lg"> {{money(business.monthly_operating_expense)}}</span>
                              </div>
                              <div class="p-details mt-4">
                                <span class="title text-m mr-8">Monthly Net Income</span>
                                <span class="text-light text-lg"> {{money(business.monthly_net_income)}}</span>
                              </div>
                            </div>
                          </div>
                      </div>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="nav-profile-tab">
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">Phone Number:</span>
                      <span class="text-light text-lg">{{client.contact_number}}</span>
                    </div>
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">Street Address:</span>
                      <span class="text-light text-lg">{{client.street_address}}</span>
                    </div>
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">Barangay:</span>
                      <span class="text-light text-lg">{{client.barangay_address}}</span>
                    </div>
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">City:</span>
                      <span class="text-light text-lg">{{client.city_address}}</span>
                    </div>
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">Province:</span>
                      <span class="text-light text-lg">{{client.province_address}}</span>
                    </div>
                    <div class="p-details mt-4">
                      <span class="title text-xl mr-8">Zipcode:</span>
                      <span class="text-light text-lg">{{client.zipcode}}</span>
                    </div>
                  </div>
                 
                </div> 
              </div>
            </div>
          </div>
      </div> 
       <div class="p-details mt-8 p-4">
          <p class="title text-2xl mb-2">Notes</p>
          <p>{{client.notes}}</p>
        </div> 
    </div>   
  </div>  
  <div class="col-lg-4">
      <div class="card mb-4">
        <div class="card-header">
          
          <div v-if="can('view_loan_accounts') || is('Super Admin')" class="float-left text-center">
            <a :href="clientLoansList ">
              <h4 class="mt-2 text-2xl">Loan Accounts</h4>
            </a>
          </div>
          <a v-if="client.status == 'Active' && can('create_loan_account') || is('Super Admin')" :href="clientLoanCreate" class="text-base float-right btn-create">Create Account</a>
        </div>
         <div class="card-body">
          <div class="table-accounts table-full-width table-responsive">
            <table class="table">
              <tbody>
                <tr>
                  <td>
                    <p class="text-base">Product</p>
                  </td>
                  <td>
                    <p class="text-base">Amount</p>
                  </td>
                  <td>
                    <p class="text-base">Balance</p>
                  </td>
                  <td>
                    <p class="text-base">Status</p>
                  </td>
                </tr>
                <tr v-for="(loan,key) in client.loans" :key="key">
                  <td>
                    <a :href="toClientLoan(loan.id)">
                      <p class="title text-base">{{loan.type.code}}</p>
                    </a>
                  </td>
                  <td>
                    <p class="title text-base">{{money(loan.amount)}}</p>
                  </td>
                  <td>
                    <p class="title text-base">{{money(loan.total_balance)}}</p>
                  </td>
                  <td>
                    <span v-if="loan.status == 'In Arrears'" class="badge badge-pill badge-danger">{{loan.status}}</span></h1>
                    <span v-else-if="loan.status == 'Pending Approval'" class="badge badge-pill badge-warning">{{loan.status}}</span></h1>
                    <span v-else-if="loan.status == 'Approved'" class="badge badge-pill badge-primary">{{loan.status}}</span></h1>
                    <span v-else class="badge badge-pill badge-success">{{loan.status}}</span></h1>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <div class="float-left text-center">
            <h4 class="mt-2 text-2xl">Deposit Accounts</h4>
          </div>
          <a v-if="client.status == 'Active' && can('create_deposit_account') || is('Super Admin')" :href="createClientDeposit" class="float-right btn-create text-base">Create Account</a>
        </div>
        <div class="card-body">
          <div class="table-accounts table-full-width mb-0 table-responsive">
            <table class="table">
              <tbody>
                <tr>
                  <td>
                    <p class="text-base">Deposit Type</p>
                  </td>
                  <td>
                    <p class="text-base">Balance</p>
                  </td>
                  <td>
                    <p class="text-base">Status</p>
                  </td>
                </tr>

                
                <tr v-for="(deposit,key) in client.deposits" :key="key">
                  <td>
                    <a :href="toClientDepositAccount(deposit.id)">
                      <p class="title text-base">{{deposit.type.name}}</p>
                    </a>
                  </td>
                  <td>
                    {{money(deposit.balance)}}
                  </td>
                  <td>
                    <span v-if="deposit.status == 'Active'" class="badge badge-pill badge-success">{{deposit.status}}</span>
                    <span v-else class="badge badge-pill badge-light">{{deposit.status}}</span>
                  </td>
                </tr>
                
                <tr style="border:none;">
                  <td class="text-right pr-2 text-lg">
                    Total:
                  </td>
                  <td class="text-lg">
                    {{money(totalDepositBalance)}}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header">
          <div class="float-left text-center">
            <h4 class="mt-2 h5">Micro-Insurance</h4>
          </div>
          
          <a v-if="client.status == 'Active'" :href="toClientManageDependents" class="float-right btn-create text-xl">Manage</a>
          
        </div>

        <div class="card-body">

          <div class="table-accounts table-full-width table-responsive">
            <table class="table">
              <tr>
                <td ><p class="text-base"> Unit</p></td>
                <td ><p class="text-base"> App. #</p></td>
                <td ><p class="text-base"> # of Dpnts </p></td>
                <td ><p class="text-base"> Expiry</p></td>
                <td ><p class="text-base"> Status</p></td>
              </tr>
              <tbody>
                
                <tr v-for="(dependent,key) in client.dependents" :key="key">
                  <td>{{dependent.unit_of_plan}}</td>
                  <td>{{dependent.application_number}}</td>
                  <td>{{dependent.count}}</td>
                  <td>{{dependent.expires_at}}</td>
                  <td>{{dependent.status}}</td>
                </tr>
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
  </div>

  
</div>
  
  
</template>

<script>
import moment from 'moment'
  export default {
    props: ['clientinfo'],
    data() {
      return {
        client: null,
        errors:{},
      }
    },

    created() {
		  this.client = JSON.parse(this.clientinfo)
    },

    methods:{
      moment(date,has_time=null){
          if(has_time===null){
        var _date = moment(date).format('MMMM DD, Y')
          }else{
              var _date = moment(date).format('MMMM DD, Y hh:mm:ss A')
          }
        if(_date=="Invalid date"){
          return "------"
        }
        return _date;
      },
      money(value){
        return '₱'+ parseFloat(value).toFixed(2)
      },
      toClientDepositAccount(value){
        return '/client/'+this.client.client_id+'/deposit/'+value
      },
      generateId(){
        //  this.isLoading =true;
            axios.get('/generate/client/'+this.client.client_id,{responseType:'blob'})
                .then(res=>{
                    console.log(res);
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', res.headers.filename);
                    document.body.appendChild(link);
                    link.click();
                    this.isLoading =false;
                })
      },

      formatAmount(value) {
          let val = (value/1).toFixed(2).replace('.', ',')
          return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
      },
      toClientLoan(value){
        return '/client/'+this.client.client_id+'/loans/'+value
      }
    },

    computed: {
      clientInfo() {
        return '/client/' + this.client.client_id
      },
      client_profile_picture_path(){
        if (this.client.profile_picture_path !== "") {
          return '/'+this.client.profile_picture_path
        }
        return '/assets/img/signature.png'
      },
      createClientDeposit(){
        return '/client/'+this.client.client_id+'/create/deposit'
      },
      clientLoanCreate(){
        return '/client/'+this.client.client_id+'/create/loan'
      },
      clientLoansList(){
        return '/client/'+this.client.client_id+'/loans'
      },

      client_signature_path(){
        if (this.client.signature_path !== "") {
          return '/'+this.client.signature_path
        }
        return '/assets/img/2x2.jpg'
      },
      toEditClient(){
        return this.clientInfo+'/edit'
      },
      totalDepositBalance(){
        var totalbalance = 0;
        $.each(this.client.deposits, function(k,v){
          totalbalance+=v.balance
        })
        return totalbalance
      },
      toClientManageDependents(){
        return '/client/'+this.client.client_id+'/manage/dependents/'
      }
    }
  }

</script>

<style>

</style>
