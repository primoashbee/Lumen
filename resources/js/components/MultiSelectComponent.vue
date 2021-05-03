<template>
  <div>
  <multiselect 
    
    v-model="value" 
    :options="options" 
    :multiple="multiple" 
    group-values="data" 
    group-label="level" 
    :group-select="false" 
    :allow-empty="false"
    placeholder="Select Level" 
    :track-by="track_by" 
    :label="label"
    @input = "emitToParent"
    >
      <span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
    </multiselect>

    <input type="hidden" :value="value.id" @change="emitToParent">
    
  
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
  components: {
    Multiselect
  },
  props: ['default_value','type','multiple','track_by','label','list'],

  mounted(){
        this.loadList(this.list)
        this.value =this.default_value
        
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
            this.$emit('changed', this.value);
      }
    },
    loadList(type){
        if(type=='fees'){
          axios.get('/fees')
          .then(res=>{
              this.options = res.data
          })
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

