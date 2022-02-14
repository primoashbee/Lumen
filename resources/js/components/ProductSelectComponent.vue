<template>
  <div>
  <multiselect 
    
    v-model="value" 
    :options="options" 
    :multiple="false"  
    :group-select="false" 
    :allow-empty="false"
    placeholder="Select Product"
    track-by="name" 
    label="name"
    @input = "emitToParent"
    >
      <span slot="noResult">Oops! No information found. Consider changing the search query.</span>
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
  props: ['product_code','product_type'],
  created(){
    if(this.product_code==null){
	axios.get('/wApi/product?product_type='+this.product_type)
		.then(res=>{
			this.options = res.data
        })
    }
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
        this.$emit('productSelected', this.value);
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

