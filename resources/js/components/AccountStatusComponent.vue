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
    console.log(this.type);
    if(this.type=='loan'){
      this.options = ["Active", "Cancelled", "Closed", "In Arrears", "Inactive", "Matured", "Pending Approval", "Rejected", "Written Off"]
    }
    if(this.type=='deposit'){
      this.options = ['Active','Closed','Dormant'];
    }
    if(this.type=='client'){
      this.options = ["Active", "In Arrears", "Closed", "Written-Off"];
    }
    if(this.type=='all'){
      this.options = ['Active',"Cancelled", "Closed", "In Arrears", "Inactive", "Matured", "Pending Approval", "Rejected", "Written Off",'Dormant'];
    }
    
    if(this.type=='service_type'){
      this.options = ['AGRICULTURE','TRADING/MERCHANDISING','MANUFACTURING','SERVICES','OTHERS'];
    }
    if(this.type=='gender'){
      this.options = ['MALE','FEMALE'];
    }
    if(this.type=='educational_attainment'){
      this.options = ["ELEMENTARY","HIGH SCHOOL","VOCATIONAL","COLLEGE"];
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

