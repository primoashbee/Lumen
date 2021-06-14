<template>
  <div class="">
      <table class="table" >
        <thead>
            <tr>
                <td><p class="title">Product</p></td>
                <td><p class="title"># of Clients</p></td>
                <td><p class="title">W/O Loan</p></td>
                <td><p class="title">LR (P)</p></td>
                <td><p class="title">PAR Amount</p></td>
                <td><p class="title">CBU</p></td>
                <td><p class="title">CBU-LR Ratio</p></td>
                <td><p class="title">PAR Ratio</p></td>
            </tr>
        </thead>
        <tbody class="test" v-if="list.length > 0">
            <tr v-for="(item,key) in list" :key="key">
                <td style="text-align:center;">
                    {{item.code}}
                </td>
                <td>
                    {{number(item.number_of_clients)}}
                </td>
                <td>
                    {{number(item.without_loans)}}
                </td>
                <td>
                    {{money(item.loan_receivable)}}
                </td>
                <td>
                    {{money(item.par_amount)}}
                </td>
                <td>
                    {{money(item.cbu)}}
                </td>
                <td>
                    {{item.cbu_lr_ratio}} %
                </td>
                <td>
                    {{item.par_ratio}} %
                </td>
            </tr>
        </tbody>
     </table>
  </div>
</template>

<script>
export default {
    props : ['office_id'],
    mounted(){
        this.fetch();
    },
    data(){
        return {
            list : [],
            errors : []
        }
    },
    methods : {
        money(value){
            return moneyFormat(value);  
        },
        number(value){
            return parseInt(value).toLocaleString();
        },
        fetch(){
            axios.get(this.url)
                .then(res=>{
                    // console.log(res.data.summary)
                    this.list  = res.data.summary
                })
                .catch(err=>{
                    this.errors = err.response.data.errors
                })
        }
    },
    computed : {
        url(){
      return '/wApi/dashboard/'+this.office_id+'/summary'
        }
    }
    
}
</script>


<style scoped>
    .test tr td {
      font-weight: 800;
      text-align: 'center';
    }
    .last-row td{
        background-color:#1e1f2e;
        color:black;
        
    }
</style>
