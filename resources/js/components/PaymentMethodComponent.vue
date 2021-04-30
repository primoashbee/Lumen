<template>
  <div>
  <multiselect 
    
    v-model="value" 
    :options="options" 
    :multiple="false"  
    :group-select="false" 
    :allow-empty="false"
    placeholder="Select Level"
    track-by="name" 
    label="name"
    @input = "emitToParent"
    >
      <span slot="noResult">Oops! No payment method found. Consider changing the search query.</span>
    </multiselect>
    <input type="hidden" name="payment_method" :value="value.id" @change="emitToParent">
    
  
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
  components: {
    Multiselect
  },
  props: ['payment_type'],
  created(){
    axios.get('/payment/methods?payment_type='+this.payment_type)
    .then(res=>{
      this.options = res.data.methods
      this.value = res.data.default_payment
      this.emitToParent();
    })
  },
  data () {
    return {
        lists: null,
        options: [],
        value: []
    }
  },
  methods: {
    emitToParent(){
      if(this.value!=null){
        this.$emit('paymentSelected', this.value);
      }
    }
  }
}

</script>
<style>
    @import "~vue-multiselect/dist/vue-multiselect.min.css";
    .multiselect__tags{
      background: #27293d;
    }
    .multiselect__input{
      background: #27293d!important;
      border-color:#2b3553
    }
    .multiselect__single{
      background: #27293d!important;
      color: white;
    }

</style>

