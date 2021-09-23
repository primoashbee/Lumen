<template>
    <div class="card">
		 <nav aria-label="breadcrumb"> 
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/clients">Client</a></li>
            <li class="breadcrumb-item"><a :href="'/client/'+client_id">{{client_id}}</a></li>
            
            <li class="breadcrumb-item active" aria-current="page">Create Deposit Account</li>
          </ol>
        </nav>
        <div class="card-body">
            <h3 class="h3">Create Client Deposit Account</h3>
            <form class="col-lg-4 p-0" @submit.prevent="submit">
                <div class="form-group">
                    <label for="deposit_products">Deposit Product</label>
                    <select v-model="fields.deposit" @change="getAccruedInterest(fields.deposit)" class="form-control text-white" aria-placeholder="Please Select Deposit Product">
                        <option value="" selected>Please Select Deposit Product</option>
                        <option :value="item.id" v-for="item in deposit_product" :key="item.id">
                            {{item.name}}
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</template>
<script>


export default {
    props: ['client_id','deposit'],
    data() {
       return{
           deposit_product:"",
           fields:{
                deposit:"",
           },
           errors:{}

       } 
    },
    mounted(){
        this.deposit_product = JSON.parse(this.deposit)
    },
    methods:{
        toCreate(value){
            return '/client/'+value+'/create/deposit'
        },
        submit(){
            axios.post(this.toCreate(this.client_id), this.fields)
            .then(res=>{
                Swal.fire({
					icon: 'success',
					title: '<p style="color:green;font-size:1em;font-weight:bold">Success</p>',
					text: res.data.msg,
				})
            })
            .catch(err=>{
                Swal.fire({
                    icon: 'error',
					title: '<p style="color:red;font-size:1em;font-weight:bold">Error.</p>',
					text: err.response.data.msg,
				})
            })
        },
        getAccruedInterest(value){
            
            if (value != '') {
                var accrued_interest = this.deposit_product.filter(function(key, val){   
                    return key.id == value
                });
                this.fields.accrued_interest = accrued_interest[0].interest_rate.slice(0,-1) * 100
            }else{
                this.fields.accrued_interest = ''
            }
           
        }
    }
}
</script>