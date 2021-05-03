<template>
  <div>
  <multiselect 
    
    v-model="value" 
    :options="options" 
    :multiple="_multiValues" 
    group-values="data" 
    group-label="type" 
    :group-select="false" 
    :allow-empty="true"
    placeholder="Select Product" 
    track-by="name" 
    label="name"
    @input = "emitToParent"
    
    >
      <span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
    </multiselect>
    <input type="hidden" name="product" :value="value.id" @change="emitToParent">  
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
  components: {
    Multiselect
  },
  props: ['list','status','multi_values'],
  created(){
      this.fetch();
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
    },

    fetch(){
       axios.post('/products',{list: this.list, status: this.status})
        .then(res=>{
          this.options=res.data
          //   if(this.default_value!==undefined){
              
          //     this.options.filter( obj => {
          //       var item = obj.data.filter(office => {
          //          office.id == this.default_value ? this.value = office : ''
          //       })
          //     })
              
          // }
        })
    }

    
  
  },
  computed: {
      _status(){
          if(this.status =="true" || this.status =="1"){
              return true;
          }
          return false;
      },
      _multiValues(){
          if(this.multi_values =="true" || this.multi_values =="1"){
              return true;
          }
          return false;
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

