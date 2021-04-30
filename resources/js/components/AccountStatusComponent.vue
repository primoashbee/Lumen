<template>
  <div>
  <multiselect 
    v-model="value" 
    :options="options" 
    :multiple="multi_values" 
    @input = "emitToParent"
    :clear-on-select="true"

    >
    <span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
    </multiselect>
    <input type="hidden" name="status" id="status" :value="value" @change="emitToParent">
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
  components: {
    Multiselect
  },
  props: ['multi_values','add_class','type'],
  mounted(){
    if(this.type=='loan_account'){
      this.options = ["All","Active", "Cancelled", "Closed", "In Arrears", "Inactive", "Matured", "Pending Approval", "Rejected", "Written Off"]
    }
    if(this.type=='deposit'){
      this.options = ['Active','Dormant'];
    }
  },
  data () {
    return {
        lists: null,
        options: [],
        value: null
    }
  },
  methods: {
    emitToParent(){
      if(this.value!=null){
        
        this.$emit('statusSelected', this.value)
      }
    }
  },

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
    .is-invalid .multiselect__tags {
      border: 1px solid red !important;
    }

</style>

